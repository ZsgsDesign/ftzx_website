<?php
	require_once './PHPMailer.php';
	$config = require('./config.php');

	// Sanitize input
	$fname	= filter_var($_POST["fname"], FILTER_SANITIZE_STRING);
	$lname = "";
	if(array_key_exists('lname', $_POST)) {
		$lname = $_POST["lname"];
	}
	$website = "";
	if(array_key_exists('website', $_POST)) {
		$website = $_POST["website"];
	}
	if (!preg_match("~^(?:f|ht)tps?://~i", $website)) $website = "http://" . $website;
	$website = filter_var($website, FILTER_VALIDATE_URL);
	$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
	$message = filter_var($_POST["message"], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	if ( empty($website) ){
		$website = "No website entered.";
	}

	// Build Message
	$email_content = "First Name: $fname\n";
	if(array_key_exists('lname', $_POST)) {
		$email_content .= "Last Name: $lname\n";
	}
	if(array_key_exists('website', $_POST)) {
		$email_content .= "Website: $website\n";
	}
	$email_content .= "Email: $email\n\n";
	$email_content .= "Message:\n$message\n\n\n";
	$email_content .= "CLIENT IP:\n".get_client_ip()."\n";
	$email_content .= "HOST IP:\n".$_SERVER['SERVER_ADDR']."\n";

	// Build PHPMailer
	$mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->CharSet='UTF-8';
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "ssl";
	$mail->Port       = $config['port'];
    $mail->Host       = $config['host'];
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->From       = $config['from'];
    $mail->FromName   = $config['fromName'];
	$mail->AddAddress($email);
    $mail->Subject  = "方糖智行网络科技有限公司 回复";
	$mail->WordWrap   = 80;
	$mail->Sender = $config['from'];
	$mail->AddReplyTo($email);
	$mail->Body = $email_content;
    $result = $mail->Send();

// Check if sent
try {
	
	if( $result === TRUE ) {
		returnAndExitAjaxResponse(
			constructAjaxResponseArray(
				TRUE
			)
		);
	} else {
		returnAndExitAjaxResponse(
			constructAjaxResponseArray(
				FALSE,
				'ERROR_AT_PHPMAIL',
				array('error_information'=> error_get_last() )
			)
		);
	}
} catch (Exception $_e) {
	returnAndExitAjaxResponse(
		constructAjaxResponseArray(
			TRUE,
			'ERROR_AT_PHPMAIL',
			array('error_message'=> $_e->getMessage())
		)
	);
}

/*
	Construct ajax response array
	Input: Result (bool), Message (optional), Data to be sent back in array
*/
function constructAjaxResponseArray ($_response, $_message = '', $_json = null) {
	$_responseArray = array();
	$_response = ( $_response === TRUE ) ? TRUE : FALSE;
	$_responseArray['response'] = $_response;
	if(isset($_message)) $_responseArray['message'] = $_message;
	if(isset($_json)) $_responseArray['json'] = $_json;

	return $_responseArray;
}
/*
	Returns in the Gframe ajax format.
	Input: data array processed by constructAjaxResponseArray ()
	Outputs as a html stream then exits.
*/
function returnAndExitAjaxResponse ($_ajaxResponse) {
	if(!$_ajaxResponse){
		$_ajaxResponse = array('response'=>false,'message'=>'Unknown error occurred.');
	}
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($_ajaxResponse);
	die();
}


// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if(isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if(isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}