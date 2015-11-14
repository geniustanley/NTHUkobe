<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69950711-1', 'auto');
  ga('send', 'pageview');

</script>

<?php

if(isset($_POST["message"]) && !empty($_POST["message"]) && isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"])) {


	$servername = "";
	$username = "";
	$password = "";
	$dbname = "";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	   $ip = $_SERVER['HTTP_CLIENT_IP'];
	}else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	   $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
	   $ip= $_SERVER['REMOTE_ADDR'];
	}

	$msg = $_POST[message];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($ch, CURLOPT_POST, true); // start POST
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array(
		"secret" => "", 
		"response" => $_POST['g-recaptcha-response'])));
	$result_recaptcha = curl_exec($ch);
	$result_json = json_decode($result_recaptcha, true);
	echo $result_json["success"];
	if ($result_json["success"]) {

		//echo $ip . $msg;
		//$sql = "SELECT id, firstname, lastname FROM MyGuests";
		$conn->query("SET NAMES utf8");
		$sql = "INSERT INTO  nthukobe (
		`id` ,
		`ip` ,
		`time` ,
		`message`
		)
		VALUES (
		NULL , '$ip', 
		CURRENT_TIMESTAMP ,  '$msg'
		)";
		if ($conn->query($sql) === TRUE) {
		    $last_id = $conn->insert_id;
		} else {
		    echo "Error: " . $sql . "<br>" . $conn->error;
		}

		$msg = '#閒聊青椒' . $last_id . "\n\n" . $msg;
		$ch_fb = curl_init();
		curl_setopt($ch_fb, CURLOPT_URL, "https://graph.facebook.com/v2.5/169899620025506/feed");
		curl_setopt($ch_fb, CURLOPT_POST, true);
		curl_setopt($ch_fb, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch_fb, CURLOPT_POSTFIELDS, http_build_query( array(
			"message" => $msg, 
			"access_token"=>"")));
		$result = json_decode(curl_exec($ch_fb), true);
		echo $last_id;
		echo "<script>window.location = 'https://www.facebook.com/" . $result["id"] . "';</script>";


		


		$conn->close();
	}
}
?>
