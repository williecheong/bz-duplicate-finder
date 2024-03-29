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

	public function test() {
		return View::make('debug.test');
	}

	public function clique() {
		/* Input retrieval and validation */
		$pairs = Input::get('pairs', false);
		
		if ( $pairs == false ) {
			return $this->makeError("A list of pairs in CSV format must be specified e.g. /clique?pairs=1,2|1,3|1,4|2,3");
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

		$nodes = array();
		$edges = array();
		foreach ($pairs as $pair) {
			$nodes[$pair[0]] = 1;
			$nodes[$pair[1]] = 1;
			
			if (!isset($edges[$pair[0]])) {
				$edges[$pair[0]] = array();
			}
			$edges[$pair[0]][$pair[1]] = 1; 
			
			if (!isset($edges[$pair[1]])) {
				$edges[$pair[1]] = array();
			}
			$edges[$pair[1]][$pair[0]] = 1;
		}

		$graph = new Graph($nodes, $edges);

		$cliqueFinder = new CliqueFinder($graph);
		$cliqueFinder->find_all_cliques();

		$cliques = array();
		foreach ($cliqueFinder->get_cliques() as $key => $clique) {
			$cliques[$key] = array_keys($clique);
		}

		return $this->makeSuccess($cliques);
	}
}