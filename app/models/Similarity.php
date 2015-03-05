<?php

use \NlpTools\Tokenizers\WhitespaceTokenizer;
use \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use \NlpTools\Tokenizers\ClassifierBasedTokenizer;

use \NlpTools\Similarity\JaccardIndex;
use \NlpTools\Similarity\CosineSimilarity;
use \NlpTools\Similarity\Simhash;

class Similarity {
	
	public function __construct() {
        $this->simHash = new Simhash(16);
        $this->jaccardIndex = new JaccardIndex();
        $this->cosineSimilarity = new CosineSimilarity();
    }

    public function jaccardIndex($setA, $setB) {
    	return $this->jaccardIndex->similarity($setA, $setB);
    }

    public function simHash($setA, $setB) {
        return $this->simHash->similarity($setA, $setB);
    }

    public function cosine($setA, $setB) {
        return $this->cosineSimilarity->similarity($setA, $setB);
    }

    public function customJaccardIndex($setA, $setB) {
        $intersect = count($this->getIntersectingTokens($setA, $setB));
        $union = count(array_fill_keys($setA,1)) + count(array_fill_keys($setB,1)) - $intersect;
        return $intersect/$union;
    }

    public function getIntersectingTokens($setA, $setB) {
        $setA = array_fill_keys($setA,1);
        $setB = array_fill_keys($setB,1);

        $intersects = array();
        foreach ($setA as $wordA => $valueA) {
            foreach ($setB as $wordB => $valueB) {
                if ($this->similarWords($wordA, $wordB)) {
                    $intersects[] = (count($wordA) > count($wordB)) ? $wordA : $wordB ;
                }
            }
        }
        return $intersects;
    }

    public function similarWords($wordA, $wordB) {
        $shorterWord = $wordA;
        $longerWord = $wordB;

        if (count($wordA) > count($wordB)) {
            $shorterWord = $wordB;
        }

        if ($shorterWord == $wordB) {
            $longerWord = $wordA;
        }

        return starts_with($longerWord, $shorterWord);
    }
}


