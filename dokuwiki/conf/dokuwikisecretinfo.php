<?php

require_once(DOKU_INC.'inc/auth.php');

class DokuWikiSecretInfo {
    
    static generateDokuWikiKey(){

        $data = array(
            'password' => '',
            'foruser'  => 'DokuWikiManager'
        );

        $c = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v = 'aeiou'; //vowels
        $a = $c.$v; //both
        $s = '!$%&?+*~#-_:.;,'; // specials

        //use thre syllables...
        for($i = 0; $i < 3; $i++) {
            $data['password'] .= $c[auth_random(0, strlen($c) - 1)];
            $data['password'] .= $v[auth_random(0, strlen($v) - 1)];
            $data['password'] .= $a[auth_random(0, strlen($a) - 1)];
        }
        //... and add a nice number and special
        $data['password'] .= auth_random(10, 99).$s[auth_random(0, strlen($s) - 1)];

        return $data['password'];
    }
}

?>