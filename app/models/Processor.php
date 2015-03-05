<?php

/**********
    * Stop Words Removal
**********/
use \NlpTools\Utils\StopWords;

class Processor { // This is obviously the core natural language processor class
    
    public function __construct() {
        $this->stemmer = new PorterStemmer2();
        $this->stopWords = new StopWords(Config::get('constants.STOP_WORDS'));
        $this->jargons = new Jargons(Config::get('constants.PRODUCT_JARGONS'));

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
        $output = str_replace("?", " ", $output);
        $output = str_replace("-", " ", $output);
        $output = str_replace("_", " ", $output);
        $output = str_replace("~", " ", $output);
        $output = str_replace("\\", " ", $output);
        $output = str_replace("\"", " ", $output);
        
        $output = preg_replace('/[0-9]+/', '', $output);

        $output = strtolower($output);

        $output = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $output);

        return explode(' ', $output);              
    }

    public function stopWordsRemoval( $tokens, $bug ) {
        $output = array();
        foreach ( $tokens as $token ) {
            if ( $this->jargons->isJargonOf($token, "*") ) {
                $output[] = $token;
                continue;
            }

            if ( $this->jargons->isJargonOf($token, $bug->product) ) {
                $output[] = $token;
                continue;
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
            $tokens[$key] = $this->stemmer->stem($token);
        }
        
        return $tokens;
    }

    public function spellCheck( $tokens, $bug ) {
        if (isset($this->pspell_link)) {
            foreach ($tokens as $key => $token) {
                if ( $this->jargons->isJargonOf($token, "*") ) {
                    continue;
                }

                if ( $this->jargons->isJargonOf($token, $bug->product) ) {
                    continue;
                }

                if (!pspell_check($this->pspell_link, $token)) {
                    $suggestions = pspell_suggest($this->pspell_link, $token);
                    var_dump($token);
                    echo "<br>";
                    var_dump($suggestions);
                    foreach ($suggestions as $suggestion) {
                        if (ctype_alpha($suggestion)) {
                            // Only accept the suggested word if it looks normal
                            $tokens[$key] = $suggestion;
                            var_dump($suggestion);
                            echo "<br><br>";
                            break;
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