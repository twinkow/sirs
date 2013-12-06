<?php

	include('File/X509.php');
	include('Crypt/RSA.php');
	include('Math/BigInteger.php');

	function getUserCertificate($user,$targetuser){
        $url = 'http://ca:8888/certificateauthorityapi.php';
        $fields = array(
                    'username' => urlencode("$targetuser"),
                    'getUserCert' => urlencode("Get User Certificate"),
                );

        $fields_string = "";
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
                @mkdir("securelocation/$user");
                file_put_contents("securelocation/$user/sirs-$targetuser-certificate.pem", $result);
                //close connection
                curl_close($ch);
                $flag = false;
            }
        }
    }

	function validateCertificate($certificate){
		$caCertificate = file_get_contents('securelocation/sirs-ca-certificate.pem');
        $x509 = new File_X509();
        $x509->loadCA($caCertificate);
        $cert = $x509->loadX509($certificate);
        return ($x509->validateSignature() && $x509->validateDate()) ? 'Valid Certificate' : 'Invalid Certificate';
    }

    function getCertificateSelfSignedByCA(){
        $url = 'http://ca:8888/certificateauthorityapi.php';

        $fields = array('submitCA' => urlencode("Get CA's Certificate"));

        $fields_string = "";
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

        file_put_contents('securelocation/sirs-ca-certificate.pem', $result);
    }

	function generateUserSignRequest($user){

		$privKey = new Crypt_RSA();
		extract($privKey->createKey());
		$privKey->loadKey($privatekey);

		$x509 = new File_X509();
		$x509->setPrivateKey($privKey);
		$x509->setDNProp('id-at-organizationName', $user);

		$csr = $x509->signCSR();
		$certRequest = $x509->saveCSR($csr);

		file_put_contents('securelocation/'.$user.'/sirs-'. $user .'-publickey.pem', $publickey);
		file_put_contents('securelocation/'.$user.'/sirs-'. $user .'-privatekey.pem', $privatekey);
		file_put_contents('securelocation/'.$user.'/sirs-'. $user .'-certrequest.pem', $certRequest);

		return $certRequest;
	}

	function getCertificateFromCa($user){
		$url = 'http://ca:8888/certificateauthorityapi.php';
	    $fields = array(
	                    'username' => urlencode("$user"),
	                    'fileCSR' => urlencode(generateUserSignRequest($user)),
	                    'submitCSRText' => urlencode('Get Certificate'),
	            );

        $fields_string = "";
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
	        if(validateCertificate($result)){
	            file_put_contents("securelocation/$user/sirs-$user-certificate.pem", $result);
	            curl_close($ch);
	            $flag = false;
	        }   
	    }
	}

	/* How to run?: 
	 * php client.php <args> 
	 * <args>:= username => generate user certificate
	 * <args>:= username certificatefile => verifies the validity of the given certificate
	 * <args>:= username targetuser => gets the certificate of targetuser
	 **/

	if(isset($argv[1]) && isset($argv[2])){
		$user = $argv[1];
		if(is_file($argv[2])){
			echo validateCertificate(file_get_contents($argv[2]));
			echo "\n";
		}
		else
			getUserCertificate($user, $argv[2]);
	}
	else if(isset($argv[1])){
		$user = $argv[1];
	}
	else if(!isset($user)){
		echo "Missing user argument\n";
		exit();
	}

	getCertificateSelfSignedByCA();
	@mkdir("securelocation/$user");
	getCertificateFromCa($user);

?>