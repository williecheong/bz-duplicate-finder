<?php

class DuplicatesController extends BaseController {
	
	public function __construct() {
		set_time_limit(0); // no limit to processing, avoid fatal error
		$this->bugzilla = new Bugzilla();
		$this->processor = new Processor();
		$this->grouper = new Grouper();
	}

	public function index() {
		/* Initialize timer and retrieve processor switches if any */
		$timeStart = microtime(true);
		$time = array(); 
		$outputVerbose = Input::get('debug', false) ? true : false;
		$useProcessor = array(
			'stopWordsRemoval' => Input::get('stopWordsRemoval', true) ? true : false,
			'stemming' => Input::get('stemming', true) ? true : false,
			'spellCheck' => Input::get('spellCheck', true) ? true : false,
			'synonymReplacement' => Input::get('synonymReplacement', true) ? true : false
		);

		/************************************** 
		 * Input bug retrieval and validation */
		$bugs = Input::get('bugs', false);
		
		if ( $bugs == false ) {
			return $this->makeError("A list of bugs in CSV format must be specified");
		}

		$bugs = str_replace(" ", "", $bugs);
		$bugs = str_replace("%20", "", $bugs);
		$bugs = array_unique(explode(',', $bugs));
		
		foreach ($bugs as $bug) {
			if ( !ctype_digit($bug) ) {
				return $this->makeError("Specified bug IDs must be numeric");
			}
		}
		
		/* Bug retrieval from Bugzilla as an associative array by BugId */
		$bugs = $this->bugzilla->retrieveByIds( $bugs );
		if ($bugs == false) {
			return $this->makeError("Failed to retrieve bugs from Bugzilla");
		}

		$time['bugzillaForBugs'] = microtime(true) - array_sum($time) - $timeStart;
		/* End of bug retrieval from Bugzilla 
		*************************************/

		/*******************************************
		 * Converting each bug into a bag of words */
		$bugs = $this->processor->executeAll($bugs);
		$time['bugsToBagsOfWords'] = microtime(true) - array_sum($time) - $timeStart;
		/* End of converting each bug to a bag of words
		***********************************************/

		/*********************************
		 * Finding similar pairs of bugs */
		$similarPairs = $this->grouper->getSimilarPairsFromProcessedBugs($bugs);
		$time['bagOfWordsToSimilarPairs'] = microtime(true) - array_sum($time) - $timeStart;
		/* End of finding similar pairs of bugs
		***************************************/

		/***************************************************
		 * Forming duplicate groups based on similar pairs */
		$groupsAsBugIds = $this->grouper->clusterPairsToGroups($similarPairs);
		$time['similarPairsToGroups'] = microtime(true) - array_sum($time) - $timeStart;
		/* End of forming duplicate groups based on similar pairs
		***********************************************/

		/* Preparing the final outputs */
		$duplicateGroups = array();
		foreach ($groupsAsBugIds as $groupAsBugIds) {
			$tokensOfEachBugInTheGroup = array();
			foreach ($groupAsBugIds as $bugId) {
				$tokensOfEachBugInTheGroup[$bugId] = $bugs[$bugId]->processedSummary;
			}

			$duplicateGroups[] = array(
				"bugs" => $groupAsBugIds,
				"keywords" => $this->grouper->getIntersectingTokens($tokensOfEachBugInTheGroup),
				"similarity" => $this->grouper->getAverageSimilarity($tokensOfEachBugInTheGroup)
			);
		}

		$time['postProcessingForBugGroups'] = microtime(true) - array_sum($time) - $timeStart;
		$time['totalRuntime'] = microtime(true) - $timeStart;

		$output = array();
		$output["duplicates"] = $duplicateGroups;
		if ($outputVerbose) {
			$output["inputBugCount"] = count($bugs);
			$output["similarityRequirement"] = Config::get('constants.SIMILARITY_REQUIREMENT');
			$output["runtimeInSeconds"] = $time;
			// $output["useProcessor"] = $useProcessor;
		}

		return $this->makeSuccess($output);
	}
}
