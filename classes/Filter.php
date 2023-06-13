<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of Filter
 *
 * @author lukas
 */
class Filter {

    public function filter_post($request) {
        return filter_input(INPUT_POST, $request);
    }

    public function filter_int($request) {
        return filter_var($request, FILTER_VALIDATE_INT);
    }

    public function filter_url($request) {
        return filter_var($request, FILTER_VALIDATE_URL);
    }

    public function filter_type($request) {
        $white_list = array('public', 'private');
        if (in_array($request,$white_list)) {
            return 1;
        } else {
            return 0;
        }
    }
    
    public function filter_string($request){
        //purifier
        require './htmlpurifier-4.15.0/library/HTMLPurifier.auto.php';
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return filter_var($purifier->purify($request),FILTER_SANITIZE_SPECIAL_CHARS,array('flags'=>FILTER_FLAG_ENCODE_LOW|FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_AMP));
    }

    //put your code here
}
