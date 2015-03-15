<?php

class Jargons {
	
    protected $jargons = array();
    
    public function __construct( $jargonsByProduct = array() ) { 
        $this->jargons["?"] = array(); // for query specific special terms
        foreach ($jargonsByProduct as $product => $jargons) {
            $this->jargons[$product] = array_fill_keys( // building a hashmap for O(1) lookup
                $jargons,
                true
            );
        }
    }

    public function addJargon($word, $product) {
        if (!isset($this->jargons[$product])) { // we do not store jargons for this product yet
            $this->jargons[$product] = array();
        }

        $this->jargons[$product][$word] = true;
        return;
    }

    public function isJargonOf($word, $product) {
        if (!isset($this->jargons[$product])) { // we do not store jargons for this product
            return false;
        }

        if (!isset($this->jargons[$product][$word])) {
            return false;
        }

        return true;
    }
}