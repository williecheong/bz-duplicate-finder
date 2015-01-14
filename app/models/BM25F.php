<?php

use \NlpTools\Tokenizers\WhitespaceTokenizer;
use \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use \NlpTools\Tokenizers\ClassifierBasedTokenizer;

use \NlpTools\Similarity\JaccardIndex;
use \NlpTools\Similarity\CosineSimilarity;
use \NlpTools\Similarity\Simhash;

class BM25F {
	
	public function __construct() {
        $this->simHash = new Simhash(16);
        $this->jaccardIndex = new JaccardIndex();
        $this->cosineSimilarity = new CosineSimilarity();
    }

    public function similarityCheck($setA, $setB) {
    	// return $this->simHash->similarity($setA, $setB);
    	// return $this->cosineSimilarity->similarity($setA, $setB);
        return $this->jaccardIndex->similarity($setA, $setB);
    }
}