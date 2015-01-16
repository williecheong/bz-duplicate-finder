<?php

class DebugController extends BaseController {
	
	public function __construct() { }

	public function index() {
		phpinfo();
	}

	public function grouping() {
		/* Input retrieval and validation */
		$pairs = Input::get('pairs', false);
		
		if ( $pairs == false ) {
			return $this->makeError("A list of pairs in CSV format must be specified e.g. /grouping?pairs=1,2|1,3|1,4|2,3");
		}

		$pairs = str_replace(" ", "", $pairs);
		$pairs = explode('|', $pairs);
		foreach ($pairs as $key => $pair) {
			$pair = explode(',', $pair);

			if (count($pair) != 2) {
				return $this->makeError("One of the listed groups is not a pair. Check your input again.");
			}

			$pairs[$key] = $pair;
		}
		
		$grouperClass = new Grouper();
		return $this->makeSuccess($grouperClass->clusterPairsToGroups($pairs));
	}
}