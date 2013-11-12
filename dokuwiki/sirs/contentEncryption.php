<?php

class Doku_ContentEncryptionCBC {
    
    var $key = '';
    var $iv = '';
    var $iv_size = 0;

    function Doku_ContentEncryptionCBC($key) {
        $this->name = $name;
        # create a random IV to use with CBC encoding
        $this->iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $this->iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
    }

    function encrypt($text) {

        # creates a cipher text compatible with AES (Rijndael block size = 128)
        # to keep the text confidential 
        # only suitable for encoded input that never ends with value 00h
        # (because of default zero padding)
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key,
                                     $text, MCRYPT_MODE_CBC, $this->iv);

        # prepend the IV for it to be available for decryption
        $ciphertext = $iv . $ciphertext;
        
        # encode the resulting cipher text so it can be represented by a string
        $ciphertext_base64 = base64_encode($ciphertext);

        error_log("ENCRYPT: " . $ciphertext_base64);

        return $ciphertext_base64;
    }

    function decrypt($cipherText) {

        $ciphertext_dec = base64_decode($cipherText);
    
        # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $this->iv_size);
        
        # retrieves the cipher text (everything except the $iv_size in the front)
        $ciphertext_dec = substr($ciphertext_dec, $this->iv_size);

        # may remove 00h valued characters from end of plain text
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
                                        $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        
        error_log("DECRYPT: " . $plaintext_dec);

        return $plaintext_dec;
    }
}

?>