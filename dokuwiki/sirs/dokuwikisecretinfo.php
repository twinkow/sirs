<?php

// [SIRS]
require_once(DOKU_INC.'inc/auth.php');

class DokuWikiSecretInfo {
    
    static function generateDokuWikiKey(){

        $password = '';
        $c = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v = 'aeiou'; //vowels
        $a = $c.$v; //both
        $s = '!$%&?+*~#-_:.;,'; // specials

        //use thre syllables...
        for($i = 0; $i < 64; $i++) {
            $password .= $c[auth_random(0, strlen($c) - 1)];
            $password .= $v[auth_random(0, strlen($v) - 1)];
            $password .= $s[auth_random(0, strlen($s) - 1)];
            $password .= $a[auth_random(0, strlen($a) - 1)];
        }
        //... and add a nice number and special
        $password .= auth_random(10, 99).$s[auth_random(0, strlen($s) - 1)];

        return $password;
    }

    static function storeDokuWikiKey($key){
        $file = 'sirs/securelocation/sirs.txt';
        $text = 'DokuWikiManager@' . $key;
        file_put_contents($file, $text, LOCK_EX);
    }

    static function retrieveDokuWikiKey(){
        $file = 'sirs/securelocation/sirs.txt';
        $text = file_get_contents($file);
        $atKey = strstr($text, '@');
        return explode('@', $atKey)[1];
    }
}

?>