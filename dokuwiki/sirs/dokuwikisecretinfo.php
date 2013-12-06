<?php

// [SIRS]
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'sirs/common/pagesrecipher.php');

class DokuWikiSecretInfo {
    
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

        $newKey = DokuWikiSecretInfo::generateDokuWikiKey();
        DokuWikiSecretInfo::storeDokuWikiKey($newKey);
        
        // Decipher all pages with the previous key
        // Recipher all pages with the new key
        PagesReCipher::handleKeyRenewal($previousKey, $newKey);

        return $previousKey;
    }

    static function storeDokuWikiKey($key){
        $file = DOKU_INC.'sirs/securelocation/sirs-DokuWiki-secretkey.txt';
        $text = 'DokuWikiManager@' . $key . '=' . time(); 
        file_put_contents($file, $text, LOCK_EX);
    }

    static function retrieveDokuWikiKey(){

        $file = DOKU_INC.'sirs/securelocation/sirs-DokuWiki-secretkey.txt';
        $text = file_get_contents($file);
        $key = '';

        if($text){
            $atKeyTime = strstr($text, '@');
            $atKey = explode('@', $atKeyTime)[1];
            $time = explode('=', strstr($atKey, '='))[1];
            $key = explode('=', strstr($atKey, '=', true))[0];
    
            $elapsedTime = time() - $time;

            if($elapsedTime > 100)
                $key = DokuWikiSecretInfo::regenarateDokuWikiKey($key);
        }
        return $key;
    }

    static function generateCertificateRequest(){
        
        $privKey = new Crypt_RSA();
        extract($privKey->createKey());
        $privKey->loadKey($privatekey);

        $x509 = new File_X509();
        $x509->setPrivateKey($privKey);
        $x509->setDNProp('id-at-organizationName', 'DokuWiki');

        $csr = $x509->signCSR();
        $certRequest = $x509->saveCSR($csr);

        file_put_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-publickey.pem', $publickey);
        file_put_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-privatekey.pem', $privatekey);
        file_put_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-certrequest.pem', $certRequest);

        return $certRequest;
    }

    static function getCertificateSignedByCA($certificateRequest){

        $url = 'http://ca:8888/certificateauthorityapi.php';
        $fields = array(
                        'userCSR' => urlencode('DokuWiki'),
                        'fileCSR' => urlencode($certificateRequest),
                        'submitCSRText' => urlencode('Get Certificate'),
                );

        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $flag = true;
        while($flag){
            $result = curl_exec($ch);
            if(DokuWikiSecretInfo::verifyCertificate($result)){
                file_put_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-certificate.pem', $result);
                curl_close($ch);
                $flag = false;
            }   
        }
    }

    static function getCertificateSelfSignedByCA(){
        $url = 'http://ca:8888/certificateauthorityapi.php';
        $fields = array(
                    'submitCA' => urlencode("Get CA's Certificate"),
                );

        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));  
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        file_put_contents(DOKU_INC.'sirs/securelocation/sirs-ca-certificate.pem', $result);
    }

    static function verifyCertificate($certificate){
        $caCertificate = file_get_contents(DOKU_INC.'sirs/securelocation/sirs-ca-certificate.pem');
        $x509 = new File_X509();
        $x509->loadCA($caCertificate);
        $cert = $x509->loadX509($certificate);
        return ($x509->validateSignature() && $x509->validateDate());
    }

    static function encrypt($text, $key){
        if($key == 'doku') $key = file_get_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-publickey.pem');
        $rsa = new Crypt_RSA();
        $rsa->loadKey(trim($key)); 
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        return $rsa->encrypt($text);
    }

    static function decryptText($ciphertext, $key){
        if($key == 'doku') $key = file_get_contents(DOKU_INC.'sirs/securelocation/sirs-DokuWiki-privatekey.pem');
        $rsa = new Crypt_RSA();
        $rsa->loadKey(trim($key));
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1); 
        return $rsa->decrypt($ciphertext);
    }
    
    static function getUserCertificate($user){
        $url = 'http://ca:8888/certificateauthorityapi.php';
        $fields = array(
                    'getUserCert' => urlencode($user),
                );

        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));  
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $flag = true;
        while($flag){
            //execute post
            error_log("loop get user certificate");
            $result = curl_exec($ch);
            if(!empty($result)){
                @mkdir(DOKU_INC."sirs/securelocation/$user");
                file_put_contents(DOKU_INC."sirs/securelocation/$user/sirs-$user-certificate.pem", $result);
                //close connection
                curl_close($ch);
                $flag = false;
            }
        }
    }
}
?>