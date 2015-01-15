<?php

class DuplicatesController extends BaseController {
	
	public function __construct() {
		$this->bugzilla = new Bugzilla();
		$this->NLP = new NLP();
    	$this->BM25F = new BM25F();
		$this->grouper = new Grouper();
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
		
		/* Finding similar pairs of bugs */
		$similarPairs = array();
		foreach ($bugs as $i => $bugI) {
			foreach ($bugs as $j => $bugJ) {
				if ( $bugJ->id == $bugI->id ) {
					break;
				}

				$similarity = $this->BM25F->similarityCheck(
					$bugI->processedSummary, 
					$bugJ->processedSummary
				);

				if ($similarity > Config::get('constants.TOLERANCE')) {
					$similarPairs[] = array($bugI->id, $bugJ->id);
				}
			}
		}

		/* Forming duplicate groups based on similar pairs */
		$similarPairs = array(
			array(1, 2),
			array(1, 3),
			array(1, 4),
			array(2, 3),
			array(21, 4)
		);
		$groups = $this->grouper->execute($similarPairs);


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