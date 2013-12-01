<?php

	include('File/X509.php');
	include('Crypt/RSA.php');

	function generateUserSignRequest($user){

		$privKey = new Crypt_RSA();
		extract($privKey->createKey());
		$privKey->loadKey($privatekey);

		$x509 = new File_X509();
		$x509->setPrivateKey($privKey);
		$x509->setDNProp('id-at-organizationName', $user);

		$csr = $x509->signCSR();
		$certRequest = $x509->saveCSR($csr);

		file_put_contents('securelocation/'.$user.'/sirs-'. $user .'-privatekey.pem', $privatekey);
		file_put_contents('securelocation/'.$user.'/sirs-'. $user .'-certrequest.pem', $certRequest);

		return $certRequest;
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

	@mkdir('securelocation/'.$user);
	$certRequest = generateUserSignRequest($user);
?>