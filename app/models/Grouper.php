<?php

class Grouper {
	
	public function __construct() { }

    public function execute($similarPairs) {    
        $outputGroups = array();
        $nodes = $this->getUniqueValues($similarPairs);

        for ($i = count($nodes); $i >= 2; $i--) {
            $possibleGroups = new Combinations($nodes, $i);
            foreach ($possibleGroups as $possibleGroup) {
                $pairsNeededByPossibleGroup = new Combinations($possibleGroup, 2);
                if (count($similarPairs) < count($pairsNeededByPossibleGroup)) {
                    break; // we can skip to look at possible groups of smaller sizes
                }

                if ($this->isSubsetOf($pairsNeededByPossibleGroup, $similarPairs)) {
                    $candidateGroupIsSubsetOfAConfirmedGroup = false;
                    foreach ($outputGroups as $outputGroup) {
                        if ($this->isSubsetOf($pairsNeededByPossibleGroup, $outputGroup)) {
                            $candidateGroupIsSubsetOfAConfirmedGroup = true;
                        }
                    }

                    if (!$candidateGroupIsSubsetOfAConfirmedGroup) {
                        $outputGroups[] = $pairsNeededByPossibleGroup;
                    }
                }
            }
        }

        /* Convert the output groups from pairs to clusters */
        foreach ($outputGroups as $index => $outputGroup) {
            $outputGroups[$index] = $this->getUniqueValues($outputGroup);
        }

        return $outputGroups;
    }

    private function getUniqueValues($setOfPairs) {
        $output = array();
        foreach ($setOfPairs as $pair) {
            foreach ($pair as $value) {
                if (!in_array($value, $output)) {
                    $output[] = $value;
                }
            }
        }
        return $output;
    }

    private function isSubsetOf($candidatePairs, $superSetOfPairs) {
        foreach ($candidatePairs as $candidatePair) {
            $matchedOnce = false;
            foreach($superSetOfPairs as $superPair){
                if ($this->isSamePair($candidatePair, $superPair)) {
                    $matchedOnce = true;
                    break;
                }
            }
            if (!$matchedOnce) {
                return false;
            }
        }
        return true;
    }

    private function isSamePair($a, $b) {
        sort($a);
        sort($b);
        if ($a == $b) { //equal
            return true;
        }
        return false;
    }
}