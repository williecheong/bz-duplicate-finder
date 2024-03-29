<?php

class Bugzilla {

    public function retrieveByIds( $bugIds = array() ) {
        $fields = 'id,summary,product,component';
        $url = 'https://bugzilla.mozilla.org/rest/bug?include_fields='.$fields.'&bug_id=';
        $query = implode(',', $bugIds);
        $fullUrl = $url . $query;

        try {
            $response = $this->restCurl( $fullUrl );
            $response = json_decode( $response );      
        } catch ( Exception $e ) {
            return false;
        }

        if ( !isset($response->bugs) ) {
            return false;
        }
        
        /* Make the returned list of bugs an associative array by Id */
        $tempBugs = array();
        foreach ($response->bugs as $bug) {
            $tempBugs[$bug->id] = $bug;
        }

        return $tempBugs;
    }


    private function restCurl( $url, $type = "GET", $params = array() ) {
        $ch = curl_init();
        $timeout = 10; // set to zero for no timeout
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if ( $type == "POST" ) {
            $postData = json_encode($params);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, 
                array(
                    'Content-Type: application/json'
                )
            );
        }

        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }
}
