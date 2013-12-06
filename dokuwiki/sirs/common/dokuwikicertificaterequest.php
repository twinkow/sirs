<?php

   	if(isset($_GET['DokuWiki'])){
   		// We'll be outputting a TXT
		header('Content-type: application/txt');

		// It will be called downloaded.pdf
		header("Content-Disposition: attachment; filename=sirs-DokuWiki-certificate.pem");

		readfile("../securelocation/sirs-DokuWiki-certificate.pem");
		
		exit();
   	}

   	if(isset($_GET['user'])){
   		echo file_get_contents("../../data/tmp/tmpuser");
   	}

?>