<?php

	include('File/X509.php');
	include('Crypt/RSA.php');

	function generateCASelfSignedCertificate(){
		// create private key for CA cert
		// (you should probably print it out if you want to reuse it)
		$CAPrivKey = new Crypt_RSA();
		extract($CAPrivKey->createKey());
		$CAPrivKey->loadKey($privatekey);

		$pubKey = new Crypt_RSA();
		$pubKey->loadKey($publickey);
		$pubKey->setPublicKey();

		// create a self-signed cert that'll serve as the CA
		$subject = new File_X509();
		$subject->setPublicKey($pubKey);
		$subject->setDNProp('id-at-organizationName', 'SIRS CA');

		$issuer = new File_X509();
		$issuer->setPrivateKey($CAPrivKey);
		$issuer->setDN($CASubject = $subject->getDN());

		$x509 = new File_X509();
		$x509->setStartDate('now');
		$x509->setEndDate('+1 year');
		$x509->setSerialNumber(rand(0,100000));
		$x509->makeCA();

		$result = $x509->sign($issuer, $subject);

		$cert = $x509->saveX509($result);

		file_put_contents('securelocation/sirs-ca-privatekey.pem', $privatekey);
		file_put_contents('securelocation/sirs-ca-certificate.pem', $cert);
	}

	function handleUserSignRequest($certRequest){
		$keyContents = file_get_contents("securelocation/sirs-ca-privatekey.pem");
		$CAPrivKey = new Crypt_RSA();
		$CAPrivKey->loadKey($keyContents);

		$certContents = file_get_contents("securelocation/sirs-ca-certificate.pem");
		$issuer = new File_X509();
		$issuer->setPrivateKey($CAPrivKey);
		$issuer->loadX509($certContents);

		$subject = new File_X509();
		$subject->loadCSR($certRequest); 

		$x509 = new File_X509();
		$x509->setStartDate('now');
		$x509->setEndDate('+1 year');
		$x509->setSerialNumber(rand(0,100000));

		$result = $x509->sign($issuer, $subject);

		// $x509->loadX509($result);
		// $x509->setExtension('id-ce-keyUsage', array('digitalSignature'));
		// $result = $x509->sign($issuer, $x509);

		$cert = $x509->saveX509($result);

		return $cert;

	}

	function revokeUserCertificateRequest($certificate){
		$keyContents = file_get_contents("securelocation/sirs-ca-privatekey.pem");
		$CAPrivKey = new Crypt_RSA();
		$CAPrivKey->loadKey($keyContents);

		$certContents = file_get_contents("securelocation/sirs-ca-certificate.pem");
		$issuer = new File_X509();
		$issuer->setPrivateKey($CAPrivKey);
		$issuer->loadX509($certContents);

		$x509 = new File_X509();
		$cert = $x509->loadX509($certificate);

	}

	if(!empty($_POST['submitCA'])){
		if(!file_exists('securelocation/sirs-ca-certificate.pem')){
			generateCASelfSignedCertificate();
		}

		// We'll be outputting a TXT
		header('Content-type: application/txt');

		// It will be called downloaded.pdf
		header("Content-Disposition: attachment; filename=sirs-ca-certificate.pem");

		readfile("securelocation/sirs-ca-certificate.pem");
		
		exit();
	}

	if (!empty($_POST['submitCSR'])) {
		$submitCSR = $_POST['submitCSR'];
		$user = $_POST['userCSR'];
		error_log($user);
		if ($_FILES["fileCSR"]["error"] > 0){
		  echo "Error: " . $_FILES["fileCSR"]["error"] . "<br>";
		} else {
		  	$certRequest = file_get_contents($_FILES["fileCSR"]["tmp_name"]);
		  	@mkdir("certificates/$user");
			file_put_contents("certificates/$user/sirs-$user-certificate.pem", handleUserSignRequest($certRequest));

			// We'll be outputting a TXT
			header('Content-type: application/txt');

			// It will be called downloaded.pdf
			header("Content-Disposition: attachment; filename=sirs-$user-certificate.pem");

			readfile("certificates/$user/sirs-$user-certificate.pem");
			
			exit();
		}
	}

	if (!empty($_POST['submitCSRText'])) {
		$submitCSR = $_POST['submitCSRText'];
		$user = $_POST['userCSR'];
		$certRequest = $_POST['fileCSR'];

	  	@mkdir("certificates/$user");
		file_put_contents("certificates/$user/sirs-$user-certificate.pem", handleUserSignRequest($certRequest));

		// // We'll be outputting a TXT
		header('Content-type: application/txt');

		// // It will be called downloaded.pdf
		header("Content-Disposition: attachment; filename=sirs-$user-certificate.pem");

		readfile("certificates/$user/sirs-$user-certificate.pem");

		exit();
	}

	if(!empty($_POST['getUserCert'])){
		$user = $_POST['getUserCert'];
		$certDir = "certificates/$user/sirs-$user-certificate.pem";
		if(file_exists($certDir)){
			// We'll be outputting a TXT
			header('Content-type: application/txt');

			// It will be called downloaded.pdf
			header("Content-Disposition: attachment; filename=sirs-$user-certificate.pem");

			readfile("certificates/$user/sirs-$user-certificate.pem");
			
			exit();
		}
	}

?>