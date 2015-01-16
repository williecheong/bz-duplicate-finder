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

class Processor {
    
    public function __construct() {
        $this->tokenizer = new WhitespaceAndPunctuationTokenizer();
        $this->stemmer = new PorterStemmer();
        $this->stopWords = new StopWords(Config::get('constants.STOP_WORDS'));
        
        if (function_exists('pspell_new')) {
            $this->pspell_link = pspell_new('en_US', 'american');
        }
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
        if (isset($this->pspell_link)) {
            foreach ($tokens as $key => $token) {
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

    public function synonymReplacement( $tokens ) {
        return $tokens;
    }
}