<?php

// [SIRS]
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'sirs/common/encryptcommon.php');

class DokuWikiSecretInfo {
    
    static $keyStoreTime = 0;
    static function generateDokuWikiKey(){

        $key = '';
        $c = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v = 'aeiou'; //vowels
        $a = $c.$v; //both
        $s = '!$%&?+*~#-_:.;,'; // specials

        for($i = 0; $i < 8; $i++) {
            $key .= $c[auth_random(0, strlen($c) - 1)];
            $key .= $v[auth_random(0, strlen($v) - 1)];
            $key .= $s[auth_random(0, strlen($s) - 1)];
            $key .= $a[auth_random(0, strlen($a) - 1)];
        }

        return $key;
    }

    static function regenarateDokuWikiKey($previousKey){

        error_log("regenarateDokuWikiKey");
        // $newKey = DokuWikiSecretInfo::generateDokuWikiKey();
        // DokuWikiSecretInfo::storeDokuWikiKey($newKey);
        
        // Decipher all pages with the previous key


        // Recipher all pages with the new key

    }

    static function storeDokuWikiKey($key){
        $file = DOKU_INC.'sirs/securelocation/sirs.txt';
        $text = 'DokuWikiManager@' . $key;
        file_put_contents($file, $text, LOCK_EX);
        DokuWikiSecretInfo::$keyStoreTime = time();
    }

    static function retrieveDokuWikiKey(){

        $file = DOKU_INC.'sirs/securelocation/sirs.txt';
        $text = file_get_contents($file);
        $key = '';

        if($text){
            $atKey = strstr($text, '@');
            $key = explode('@', $atKey)[1];
        }
        
        // if(DokuWikiSecretInfo::$keyStoreTime - time() > 10)
        //     DokuWikiSecretInfo::regenarateDokuWikiKey($key);
        
        return $key;
    }
}

?>