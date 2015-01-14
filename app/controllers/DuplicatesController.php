<?php

class DuplicatesController extends BaseController {
	
	public function __construct() {
		$this->NLP = new NLP();
    	$this->BM25F = new BM25F();
		$this->bugzilla = new Bugzilla();
	}

	public function index() {
		/* Input retrieval and validation */
		$bugs = Input::get('bugs', false);
		
		if ( $bugs == false ) {
			return $this->makeError("A list of bugs in CSV format must be specified");
		}

		$bugs = str_replace(" ", "", $bugs);
		$bugs = explode(',', $bugs);
		
		foreach ($bugs as $key => $bug) {
			if ( !ctype_digit($bug) ) {
				return $this->makeError("Specified bug IDs must be numeric");
			}
		}
		
		/* Bug retrieval from Bugzilla */
		$bugs = $this->bugzilla->retrieveByIds( $bugs );

		/*	Converting each bug into a bag of words */
		foreach ($bugs as $key => $bug) {
			$processedSummary = $bug->summary;

			$processedSummary = $this->NLP->tokenization($processedSummary);
			$processedSummary = $this->NLP->stemming($processedSummary);
			$processedSummary = $this->NLP->stopWordsRemoval($processedSummary);

			$bugs[$key]->processedSummary = $processedSummary;
		}
		
		/* Similarity pairing between bugs */
		$similarityPairings = array();
		foreach ($bugs as $i => $bugI) {
			$similarityPairings[$bugI->id] = array();

			foreach ($bugs as $j => $bugJ) {
				if ( $bugJ->id == $bugI->id ) {
					break;
				}

				$similarityPairings[$bugI->id][$bugJ->id] = $this->BM25F->similarityCheck(
					$bugI->processedSummary, 
					$bugJ->processedSummary
				);
			}
		}

		/* Forming duplicate groups based on similarity pairings */
		$groups = array();
		foreach ($similarityPairings as $idI => $pairings) {
			foreach ($pairings as $idJ => $similarity) {
				if ($similarity > Config::get('constants.TOLERANCE')) {
					$groups[] = array($idI, $idJ);
				}
			}
		}


		return $this->makeSuccess($groups);
	}



	private function makeError( $content = "" ) {
		if ( is_string($content) ) {
			$content = array(
				"message" => $content
			);
		} 

		$response = Response::make($content, "400");
		$response->header('Content-Type', 'application/json');
		return $response;
	}

	private function makeSuccess( $content = "" ) {
		if ( is_string($content) ) {
			$content = array(
				"message" => $content
			);
		}

		$response = Response::make($content, "200");
		$response->header('Content-Type', 'application/json');
		return $response;
	}
}

class DuplicateGroup {
	public function __construct(	$bugs = array(), 
									$keywords = array(), 
									$similarity = 0.0) {
		$this->bugs = $bugs;
		$this->keywords = $keywords;
		$this->similarity = $similarity;
	}
}