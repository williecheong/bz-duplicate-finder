<?php

class Grouper {
	
	public function __construct() { 
        $this->similarity = new Similarity();
    }

    public function getSimilarPairsFromProcessedBugs($bugs) {
        $similarPairs = array();
        foreach ($bugs as $bugI) {
            foreach ($bugs as $bugJ) {
                if ( $bugJ->id == $bugI->id ) {
                    break;
                }

                $similarity = 0;
                if ($bugI->product == $bugJ->product) { 
                    // Must belong to same product to be considered for duplication
                    $similarity = $this->similarity->customJaccardIndex(
                        $bugI->processedSummary, 
                        $bugJ->processedSummary
                    );
                }

                if ($similarity > Config::get('constants.SIMILARITY_REQUIREMENT')) {
                    $similarPairs[] = array($bugI->id, $bugJ->id);
                }
            }
        }
        return $similarPairs;
    }

    public function clusterPairsToGroups($similarPairs) {    
        $outputGroups = array();
        $nodes = $this->getUniqueValues($similarPairs);
        for ($i = Config::get('constants.MAXIMUM_GROUP_SIZE'); $i >= 2; $i--) {
            $possibleGroups = (count($nodes) >= $i) ? new Combinations($nodes, $i) : array();
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

        /* Flatten the output groups from pairs to clusters */
        foreach ($outputGroups as $index => $outputGroup) {
            $outputGroups[$index] = $this->getUniqueValues($outputGroup);
        }

        return $outputGroups;
    }


    public function getIntersectingTokens($bagsOfTokens) {
        $bugIds = array_keys($bagsOfTokens);
        $pairingCombinations = new Combinations($bugIds, 2);
        $keywords = array();
        foreach ($pairingCombinations as $combination) {
            $setA = $bagsOfTokens[ $combination[0] ];
            $setB = $bagsOfTokens[ $combination[1] ];
            $intersections = $this->similarity->getIntersectingTokens($setA, $setB);
            foreach ($intersections as $intersectingWord) {
                if (!in_array($intersectingWord, $keywords)) {
                    $keywords[] = $intersectingWord;
                }
            }
        }
        return $keywords;
    }

    public function getAverageSimilarity($bagsOfTokens) {
        $bugIds = array_keys($bagsOfTokens);
        $pairingCombinations = new Combinations($bugIds, 2);
        $similarityPairingValues = array();
        foreach ($pairingCombinations as $combination) {
            $similarityPairingValues[] = $this->similarity->jaccardIndex(
                $bagsOfTokens[ $combination[0] ], 
                $bagsOfTokens[ $combination[1] ]
            );
        }
        // Calculate average of similarity between bug pairings within the group
        return array_sum($similarityPairingValues) / count($similarityPairingValues);
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
        sort($a); sort($b);
        if ($a[0] == $b[0] && $a[1] == $b[1]) { //equal
            return true;
        }
        return false;
    }
}