<?php

class DuplicatesController extends BaseController {
	
	public function __construct() {
		$this->bugzilla = new Bugzilla();
		$this->processor = new Processor();
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
		if ($bugs == false) {
			return $this->makeError("Failed to retrieve bugs from Bugzilla");
		}

		/*	Converting each bug into a bag of words */
		foreach ($bugs as $key => $bug) {
			$processedSummary = $bug->summary;

			$processedSummary = $this->processor->tokenization($processedSummary);
			$processedSummary = $this->processor->stemming($processedSummary);
			$processedSummary = $this->processor->stopWordsRemoval($processedSummary);
			$processedSummary = $this->processor->spellCheck($processedSummary);
			$processedSummary = $this->processor->synonymReplacement($processedSummary);

			$bugs[$key]->processedSummary = $processedSummary;
		}
		
		/* Finding similar pairs of bugs */
		$similarPairs = $this->grouper->getSimilarPairsFromProcessedBugs($bugs);

		/* Forming duplicate groups based on similar pairs */
		$groupsAsBugIds = $this->grouper->clusterPairsToGroups($similarPairs);

		/* Preparing the final outputs */
		$output = array();
		foreach ($groupsAsBugIds as $groupAsBugIds) {
			$tokensOfEachBugInTheGroup = array();
			foreach ($groupAsBugIds as $bugId) {
				$tokensOfEachBugInTheGroup[$bugId] = $bugs[$bugId]->processedSummary;
			}

			$output[] = new DuplicateGroup(
				$groupAsBugIds,
				$this->grouper->getIntersectingTokens($tokensOfEachBugInTheGroup),
				$this->grouper->getAverageSimilarity($tokensOfEachBugInTheGroup)
			);
		}

		return $this->makeSuccess($output);
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