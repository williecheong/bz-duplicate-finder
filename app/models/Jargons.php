<?php

class Jargons {
	
    protected $jargons = array();
    
    public function __construct( $jargonsByProduct = array() ) { 
        foreach ($jargonsByProduct as $product => $jargons) {
            $this->jargons[$product] = array_fill_keys( // building a hashmap for O(1) lookup
                $jargons,
                true
            );
        }
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