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

class Processor { // This is obviously the core natural language processor class
    
    public function __construct() {
        $this->tokenizer = new WhitespaceAndPunctuationTokenizer();
        $this->stemmer = new PorterStemmer();
        $this->stopWords = new StopWords(Config::get('constants.STOP_WORDS'));
        
        if (function_exists('pspell_new')) {
            $this->pspell_link = pspell_new('en_US', 'american');
        }
    }

    public function executeAll( $bugs, 
                                $useStopWordsRemoval = true, 
                                $useStemming = true, 
                                $useSpellCheck = true, 
                                $useSynonymReplacement = true) {
        foreach ($bugs as $bugId => $bug) {
            $processedSummary = $bug->summary;
            $processedSummary = $this->tokenization($processedSummary);

            if ($useStopWordsRemoval) {
                $processedSummary = $this->stopWordsRemoval($processedSummary, $bug);
            }

            if ($useStemming) {
                $processedSummary = $this->stemming($processedSummary, $bug);
            }

            if ($useSpellCheck) {
                $processedSummary = $this->spellCheck($processedSummary, $bug);
            }
    
            if ($useSynonymReplacement) {
                $processedSummary = $this->synonymReplacement($processedSummary, $bug);
            }

            $bugs[$bugId]->processedSummary = $processedSummary;
        }
        return $bugs;
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
        
        $output = strtolower($output);

        return $this->tokenizer->tokenize($output);              
    }

    public function stopWordsRemoval( $tokens, $bug ) {
        $output = array();
        foreach ( $tokens as $token ) {
            if ( $bug->product == "Firefox OS" ) {
                if (array_key_exists($token, Config::get('constants.FIREFOX_OS_PRODUCT_JARGON'))) {
                    $output[] = $token;
                    continue;
                }
            }

            if ( !is_null($this->stopWords->transform($token)) ) {
                $output[] = $token;
                continue;
            }
        } 
        return $output;
    }

    public function stemming( $tokens, $bug ) {
        foreach ($tokens as $key => $token) {
            $tokens[$key] = str_singular($token);
            $tokens[$key] = $this->stemmer->transform($token);
        }
        
        return $tokens;
    }

    public function spellCheck( $tokens, $bug ) {
        if (isset($this->pspell_link)) {
            foreach ($tokens as $key => $token) {
                if ( $bug->product == "Firefox OS" ) {
                    if (array_key_exists($token, Config::get('constants.FIREFOX_OS_PRODUCT_JARGON'))) {
                        continue;
                    }
                }

                if (!pspell_check($this->pspell_link, $token)) {
                    $suggestions = pspell_suggest($this->pspell_link, $token);
                    foreach ($suggestions as $suggestion) {
                        if (ctype_alpha($suggestion)) {
                            // Only accept the suggested word if it looks normal
                            $tokens[$key] = $suggestion;
                        }
                    }
                }
            }
        }
        return $tokens;
    }

    public function synonymReplacement( $tokens, $bug ) {
        return $tokens;
    }
}