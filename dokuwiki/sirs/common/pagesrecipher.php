<?php

require_once DOKU_INC.'sirs/common/encryptcommon.php';

// [SIRS]
class PagesReCipher {
    
    static function handleKeyRenewal($oldKey, $newKey){

        $Directory = new RecursiveDirectoryIterator(DOKU_INC.'data/pages/');
        $Iterator = new RecursiveIteratorIterator($Directory);

        foreach($Iterator as $name => $object){
            if ((strpos($name,'playground.txt') == false) and 
                (strpos($name,'dokuwiki.txt') == false) and
                (strpos($name,'syntax.txt') == false) and 
                (strpos($name, 'welcome.txt') == false)) {

                $fileContents = file_get_contents($name);
                $recipheredContents = ContentEncryptionCBC::encrypt(ContentEncryptionCBC::decrypt($fileContents, $oldKey),$newKey);
                file_put_contents($name, $recipheredContents);
            }
        }
    }
}

?>