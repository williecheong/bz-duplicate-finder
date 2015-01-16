<?php

/**********
    * Tokenization
**********/
use \NlpTools\Tokenizers\WhitespaceTokenizer;
use \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

/**********
    * Stemming
**********/
use \NlpTools\Stemmers\PorterStemmer;

/**********
    * Stop Words Removal
**********/
use \NlpTools\Utils\StopWords;

class NLP {
    
    public function __construct() {
        $this->tokenizer = new WhitespaceAndPunctuationTokenizer();
        $this->stemmer = new PorterStemmer();
        $this->stopWords = new StopWords(Config::get('constants.STOP_WORDS'));
        $this->similarity = new Similarity();
    }

    public function tokenization( $inputString ) {
        $output = $inputString;

        $output = str_replace("(", " ", $output);
        $output = str_replace("{", " ", $output);
        $output = str_replace("}", " ", $output);
        $output = str_replace("[", " ", $output);
        $output = str_replace("]", " ", $output);
        $output = str_replace(")", " ", $output);
        $output = str_replace(":", " ", $output);
        $output = str_replace(";", " ", $output);
        $output = str_replace("#", " ", $output);
        $output = str_replace("/", " ", $output);
        $output = str_replace(",", " ", $output);
        $output = str_replace(".", " ", $output);
        $output = str_replace("!", " ", $output);
        $output = str_replace("-", " ", $output);
        $output = str_replace("_", " ", $output);
        $output = str_replace("~", " ", $output);
        $output = str_replace("\\", " ", $output);
        $output = str_replace("\"", " ", $output);
        $output = str_replace("\'", " ", $output);
        
        $output = strtolower($output);

        return $this->tokenizer->tokenize($output);              
    }

    public function stemming( $tokens ) {
        return $this->stemmer->stemAll($tokens);
    }

    public function stopWordsRemoval( $tokens ) {
        $output = array();
        foreach ( $tokens as $token ) {
            if ( !is_null($this->stopWords->transform($token)) ) {
                $output[] = $token;
            }
        } 
        return $output;
    }

    public function spellCheck( $tokens ) {
        if (function_exists('pspell_config_create')) {
            $pspell_config = pspell_config_create("en");
            $pspell_link = pspell_new_config($pspell_config);
            foreach ($tokens as $key => $token) {
                if (!pspell_check($pspell_link, $token)) {
                    $suggestions = pspell_suggest($pspell_link, $token);
                    if (isset($suggestions[0])) {
                        $tokens[$key] = $suggestions[0];
                    }
                }
            }
        }
        return $tokens;
    }

    public function synonymReplacement( $tokens ) {
        return $tokens;
    }

    public function getIntersectingTokens($bagsOfTokens) {
        $bugIds = array_keys($bagsOfTokens);
        $pairingCombinations = new Combinations($bugIds, 2);
        $keywords = array();
        foreach ($pairingCombinations as $combination) {
            $setA = $bagsOfTokens[ $combination[0] ];
            $setB = $bagsOfTokens[ $combination[1] ];
            $intersections = array_intersect($setA, $setB);
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
}