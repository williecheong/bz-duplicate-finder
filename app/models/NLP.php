<?php

/**********
    * Tokenization
**********/
use \NlpTools\Tokenizers\WhitespaceTokenizer;
use \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use \NlpTools\Tokenizers\ClassifierBasedTokenizer;

/**********
    * Stemming
**********/
use \NlpTools\Stemmers\Stemmer;
use \NlpTools\Stemmers\GreekStemmer;
use \NlpTools\Stemmers\LancasterStemmer;
use \NlpTools\Stemmers\PorterStemmer;
use \NlpTools\Stemmers\RegexStemmer;

/**********
    * Stop Words Removal
**********/
use \NlpTools\Utils\StopWords;

/**********
    * Clustering
**********/
use NlpTools\Clustering\KMeans;
use NlpTools\Similarity\Euclidean;
use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\TokensDocument;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Clustering\CentroidFactories\Euclidean as EuclideanCF;

/**********
    * Similarity
**********/
use \NlpTools\Similarity\JaccardIndex;
use \NlpTools\Similarity\CosineSimilarity;
use \NlpTools\Similarity\Simhash;

class NLP {
    
    public function __construct() {
        $this->tokenizer = new WhitespaceTokenizer();
        $this->stemmer = new GreekStemmer();
        $this->stopWords = new StopWords(Config::get('constants.STOP_WORDS'));
    }

    public function tokenization( $input ) {
        $output = $input;

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

    public function stemming( $inputArray ) {
        return $this->stemmer->stemAll($inputArray);
    }

    public function stopWordsRemoval( $inputArray ) {
        $output = array();
        foreach ( $inputArray as $token ) {
            if ( !is_null($this->stopWords->transform($token)) ) {
                $output[] = $token;
            }
        } 
        return $output;
    }

    public function spellCheck( $inputArray ) {
        return $inputArray;
    }

    public function synonymReplacement( $inputArray ) {
        return $inputArray;
    }
}