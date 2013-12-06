<?php

	include('File/X509.php');
	include('Crypt/RSA.php');

	function validateCertificate($certificate){
		$caCertificate = file_get_contents('securelocation/sirs-ca-certificate.pem');
        $x509 = new File_X509();
        $x509->loadCA($caCertificate);
        $cert = $x509->loadX509($certificate);
        return ($x509->validateSignature() && $x509->validateDate());
    }

    function getCertificateSelfSignedByCA(){
        $url = 'http://ca:8888/certificateauthorityapi.php';

        $fields = array('submitCA' => urlencode("Get CA's Certificate"));
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
	                    'userCSR' => urlencode("$user"),
	                    'fileCSR' => urlencode(generateUserSignRequest($user)),
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
	        if(validateCertificate($result)){
	            file_put_contents("securelocation/$user/sirs-$user-certificate.pem", $result);
	            curl_close($ch);
	            $flag = false;
	        }   
	    }
	}

	if (isset($argv[1])) {
	    $user = $argv[1];
	}
	else if(isset($_GET['user'])){
	    $user = $_GET['user'];
	}
	else if(!isset($user)){
		echo "Missing user argument\n";
		exit();
	}
	getCertificateSelfSignedByCA();
	@mkdir("securelocation/$user");
	getCertificateFromCa($user);

?>