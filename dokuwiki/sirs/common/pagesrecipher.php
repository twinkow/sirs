<?php

// [SIRS]
class PagesReCipher {
    
    static function readWikiCipheredPage($page, $oldKey){

    }

    static function writeWikiCipheredPage($page, $newKey){

    }

    static function handleKeyRenewal($oldKey, $newKey){

        $files = scandir('.');
        foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
            print $file . '<br>';
        }
    }
}

?>