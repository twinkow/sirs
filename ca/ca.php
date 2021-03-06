<html>
  <head>
  	<title>Certificate Authority for SIRS</title>
  	<link rel="stylesheet" type="text/css" href="ca.css"/>
  </head>
  <body>
  	<h1>SIRS Certificate Authority</h1>

	<div class="element">
		<form action="/certificateauthorityapi.php" method="post">
			<input type="submit" name="submitCA" value="Get CA's Certificate">
		</form>
	</div>

  	<div class="element">
	  	<form enctype="multipart/form-data" method="post" action="/certificateauthorityapi.php"> 
	  		<label for="username">Entity Name:</label>
  			<input type="text" name="username" id="username">
	  		<br/><br/>
			<label for="certificate">Certificate Signing Request:</label>
			<input type="file" name="fileCSR" id="fileCSR">
			<br/><br/>
			<input type="submit" name="submitCSR" value="Generate Certificate">
			<input style="visibility: hidden;" type="submit" name="submitCSRText" value="Generate Certificate"/>
		</form>
	</div>

	<!-- GUC : Get User Certificate -->
	<div class="element">
	  	<form method="post" action="/certificateauthorityapi.php">
	  		<label for="username">Entity Name:</label>
  			<input type="text" name="username" id="username"> 
	  		<br/><br/>
			<label for="userCertificate">User Certificate Request</label>
			<input type="submit" name="getUserCert" value="Get User Certificate">
		</form>
	</div>

	<!-- Missing Back End-->
	<!-- <div class="element">
	  	<form enctype="multipart/form-data" method="post" action="/certificateauthorityapi.php"> 
			<label for="user">Entity Name:</label>
		  	<input type="text" name="userC" id="userC">
		  	<br/><br/>
			<label for="certificate">Certificate:</label>
			<input type="file" name="fileC" id="fileC">
			<br/><br/>
			<input type="submit" name="submitC" value="Revoke Certificate">
		</form>
	</div> -->
  </body>
</html>