<?php

class DuplicatesController extends BaseController {
	
	public function __construct() {
		$this->NLP = new NLP();
    	$this->BM25F = new BM25F();
		$this->bugzilla = new Bugzilla();
	}

	public function index() {
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

		$bugs = $this->bugzilla->retrieveByIds( $bugs );

		foreach ($bugs as $key => $bug) {
			$processedSummary = $bug->summary;

			$processedSummary = $this->NLP->tokenization($processedSummary);
			$processedSummary = $this->NLP->stemming($processedSummary);
			$processedSummary = $this->NLP->stopWordsRemoval($processedSummary);

			$bugs[$key]->processedSummary = $processedSummary;
		}

		$output = array();

		foreach ($bugs as $key => $bug) {
			$output[] = new DuplicateGroup(array(), $bug->processedSummary);
		} 

		return $this->makeSuccess($output);
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