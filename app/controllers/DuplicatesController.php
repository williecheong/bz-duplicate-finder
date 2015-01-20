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
		$useStemming = Input::get('stemming', true) ? true : false;
		$useStopWordsRemoval = Input::get('stopWordsRemoval', true) ? true : false;
		$useSpellCheck = Input::get('spellCheck', true) ? true : false;
		$useSynonymReplacement = Input::get('synonymReplacement', true) ? true : false;
		$outputVerbose = Input::get('debug', false) ? true : false;

		/* Input bug retrieval and validation */
		$bugs = Input::get('bugs', false);
		
		if ( $bugs == false ) {
			return $this->makeError("A list of bugs in CSV format must be specified");
		}

		$bugs = str_replace(" ", "", $bugs);
		$bugs = explode(',', $bugs);
		
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

		/*	Converting each bug into a bag of words */
		$bugs = $this->processor->executeAll($bugs);
		
		/* Finding similar pairs of bugs */
		$similarPairs = $this->grouper->getSimilarPairsFromProcessedBugs($bugs);

		/* Forming duplicate groups based on similar pairs */
		$groupsAsBugIds = $this->grouper->clusterPairsToGroups($similarPairs);

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

		$timeStop = microtime(true);

		$output = array();
		$output["duplicates"] = $duplicateGroups;
		if ($outputVerbose) {
			$output["runtimeInSeconds"] = $timeStop - $timeStart;
			$output["inputBugCount"] = count($bugs);
			$output["useProcessor"] = array(
				"stemming" => $useStemming,
				"stopWordsRemoval" => $useStopWordsRemoval,
				"spellCheck" => $useSpellCheck,
				"synonymReplacement" => $useSynonymReplacement
			);
		}

		return $this->makeSuccess($output);
	}
}
