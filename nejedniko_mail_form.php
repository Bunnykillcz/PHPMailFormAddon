<div class='nejedniko_mail_form'><?php 
//php email form module :: Author: nejedniko.cz
//Copyright | y2019_m10_d03 | Nejedlý Nikola
//USES PHPMailer, LGPL-2.1 :: https://github.com/PHPMailer/PHPMailer

//SETUP:
$email_receiver = "info@somewhere.com";
$email_server   = "smtp.domain.com";
$SMTP_auth	    = true;
	$SMTP_username  = "someone@somewhere.com";
	$SMTP_password  = "somepass";
	$SMTP_port      = 25;
$subject		= "Message subject";

$field_sure_name_enable = true;
$field_email_from_enable = true;
$field_phone_enable = true;
$field_message_enable = true;
$text_orcall_enable = true;

$antispam_delay = 1800; // 30 minutes
//antispam uses session, make sure your page uses "session_start()" before accessing this form;

$style_file_path = "./mail_form.css";
$path_to_this = "./";

//language options - set these to your langage or leave be

$or_call 		 	= "Nebo volejte <b> +420 000 000 000</b>"; // = "Or call 000 000 000."
$fill_all_fields 	= "Pro odeslání musí být vyplněna všechna pole."; // = "All fields has to be filled to send the message."
$wrong_email_format = "Špatný formát zadaného emailu.";	// = "The email privided has an incorrect format."
$cannotsosoon 		= "Nelze odesílat zprávy tak brzy po sobě. Zkuste to znovu později. "; // = "You cannot send messages in such a short period. Try it again later."
$disclaimer			= "Využitím formuláře souhlasíte s použitím cookies na ochranu proti spamu."; // = "By using this form you agree with the use of cookies in order to block spamming"

$button_send     = "Odeslat"; // Send
$title_name	     = "Jméno"; // Name
$title_surename  = "Příjmení"; // Surename
$title_email     = "E-mail"; // E-mail
$title_phone     = "Telefon"; // Phone number
$title_message   = "Vaše zpráva"; // Your message

//--------------------------------------------------------------------------------------------------------------------------

//INIT:
$name = "";
$surename = "";
$email_from = "";
$phone = "";
$message = "";

if (session_status() == PHP_SESSION_NONE)
	session_start();

ini_set("session.gc_maxlifetime", $antispam_delay);

if(isset($_SESSION['LAST_ACTIVITY']))
if(time() - $_SESSION['LAST_ACTIVITY'] >= $antispam_delay)
	if(isset($_SESSION["spamblock"]))
	{
		unset($_SESSION['LAST_ACTIVITY']);
		session_unset(); 
		session_destroy();
	}

//if(!isset($_SESSION["spamblock"]))
//	$_SESSION["spamblock"] = 0;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './'.$path_to_this.'/PHPMailer/src/Exception.php';
require './'.$path_to_this.'/PHPMailer/src/PHPMailer.php';
require './'.$path_to_this.'/PHPMailer/src/SMTP.php';

//THE REAL STUFF:

echo "<script>
		 var style = document.createElement('link');
		 style.href = '$style_file_path';
		 style.type = 'text/css';
		 style.rel = 'stylesheet';
		 
		 var font = document.createElement('link');
		 font.href = 'https://fonts.googleapis.com/css?family=Open+Sans';
		 font.type = 'text/css';
		 font.rel = 'stylesheet';
		 
		var elementExists = document.getElementsByName('head');
		if(elementExists != null)
		{
			document.head.append(style);
			document.head.append(font);
		}
		else
		{
			document.html.append(\"<head></head>\");
			document.head.append(style);
			document.head.append(font);
		}   
      </script>";

$rcv = true;

if( $field_message_enable )
	if( empty($_POST['mail_message']) )
		$rcv = false;
	else
		$message = htmlspecialchars( $_POST['mail_message']);

if( $field_phone_enable )
	if( empty($_POST['mail_phone']) )
		$rcv = false;
	else
		$phone = htmlspecialchars( $_POST['mail_phone'] );

if( $field_sure_name_enable )
	if( empty($_POST['mail_surename']) )
		$rcv = false;
	else
		$surename =  htmlspecialchars( $_POST['mail_surename']);
	
if( $field_sure_name_enable )
	if( empty($_POST['mail_name']))
		$rcv = false;
	else
		$name = htmlspecialchars(  $_POST['mail_name']);

if( $field_email_from_enable )
	if( empty($_POST['mail_from']) )
		$rcv = false;
	else
		$email_from =  htmlspecialchars( $_POST['mail_from']);
	
if (!filter_var($email_from, FILTER_VALIDATE_EMAIL)) 
		$rcv = false;
	

if($rcv) //receiving POST method
{
	$message = "Jméno: $name $surename \r\nTelefon: $phone \r\nEmail: $email_from \r\nZpráva: ".$message;
	
	$mail = new PHPMailer(true);
	if($_SESSION["spamblock"] == 0 && !isset($_SESSION['LAST_ACTIVITY']))
	{
		try 
		{
			$mail->IsSMTP();
			$mail->CharSet = 'UTF-8';

			$mail->Host       = $email_server;
			$mail->SMTPDebug  = 0;   
			$mail->SMTPAuth   = $SMTP_auth;
			$mail->Port       = $SMTP_port;
			$mail->Username   = $SMTP_username; 
			$mail->Password   = $SMTP_password; 

			$mail->isHTML(false);
			$mail->Subject = $subject;
			
			$mail->setFrom($email_from, 'Getaway uživatel');
			$mail->addAddress($email_receiver);
			$mail->Body    = $message;

			$mail->send();
			
			$_SESSION["spamblock"] = 1;
			$_SESSION['LAST_ACTIVITY'] = time();
			
			echo "<div class='report_success'>Zpráva odeslána.</div>";
		} 
		catch (Exception $e) 
		{
			echo "<div class='report_error'>Došlo k chybě při odesílání; kontaktujte správce. Chyba: ". $mail->ErrorInfo ."</div>";
			$_SESSION["spamblock"] = 0;
		}
	}
	else
	{
		echo "<div class='report_repeated'>$cannotsosoon</div>";
			$_SESSION["spamblock"] = 0;
	}
}
else //normal state
{
	$isName 		= !empty($_POST['mail_name']) ? true : false;
	$isSurename 	= !empty($_POST['mail_surename']) ? true : false;
	$isMailfrom 	= !empty($_POST['mail_from']) ? true : false;
	$isPhone		= !empty($_POST['mail_phone']) ? true : false;
	$isMessage 		= !empty($_POST['mail_message']) ? true : false;
	
	$en = "disabled";
	$en1 = $field_message_enable ? "" : $en;
	$en2 = $field_email_from_enable ? "" : $en;
	$en3 = $field_phone_enable ? "" : $en;
	$en4 = $field_sure_name_enable ? "" : $en;
	
	$all_empty = (!$isName && !$isSurename && !$isMailfrom && !$isPhone && !$isMessage);
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	if($all_empty)
	{
		echo "<form class='form_email' id='email' method='post' action='$actual_link' style='display: block;'>
			<div class='half'><b id='pre_name'>$title_name</b>		<input id='name' type='text' name='mail_name' value='".$name."' maxlength=\"64\" $en4></div>
			<div class='half'><b id='pre_surename'>$title_surename</b><input id='surename' type='text' name='mail_surename' value='".$surename."' maxlength=\"64\" $en4></div>
			<div class='half'><b id='pre_email'>$title_email</b>		<input id='email' type='text' name='mail_from' value='".$email_from."' maxlength=\"64\" $en2></div>
			<div class='half'><b id='pre_phone'>$title_phone</b>		<input id='phone' type='text' name='mail_phone' value='".$phone."' maxlength=\"13\" $en3></div>
			<div class='full'><b id='pre_message'>$title_message</b>	<textarea id='message' name='mail_message' form='email' maxlength=\"600\" $en1>".$message."</textarea></div>
			<div class='or_call'>$or_call</div>
			<input id='submit' type=\"submit\" value=\"$button_send\" form='email'>
			</form>";
		echo "<div class='form_disclaimer'>".$disclaimer."</div>";
	}
	else //something is not filled out!
	{
		$e = " style='border:1px red solid;' ";
		$e1 = ""; $e2 = ""; $e3 = ""; $e4 = ""; $e5 = "";
		
		if (!filter_var($email_from, FILTER_VALIDATE_EMAIL)) 
		{
			echo "<div class='report_missing'>$wrong_email_format</div>";
			$e3 = $e;
		}
		
		if(!$isName || !$isSurename || !$isMailfrom || !$isPhone || !$isMessage)
			echo "<div class='report_missing'>$fill_all_fields</div>";
		
		if(!$isName)
			$e1 = $e;
		if(!$isSurename)
			$e2 = $e;
		if(!$isMailfrom)
			$e3 = $e;
		if(!$isPhone)
			$e4 = $e;
		if(!$isMessage)
			$e5 = $e;
		
		echo "<form class='form_email' id='email' method='post' action='$actual_link' style='display: block;'>
			<div class='half'><b id='pre_name'>$title_name</b>		<input $e1 id='name' type='text' name='mail_name' value='".$name."' maxlength=\"64\" $en4></div>
			<div class='half'><b id='pre_surename'>$title_surename</b><input id='surename' $e2 type='text' name='mail_surename' value='".$surename."' maxlength=\"64\" $en4></div>
			<div class='half'><b id='pre_email'>$title_email</b>		<input $e3 id='email' type='text' name='mail_from' value='".$email_from."' maxlength=\"64\" $en2></div>
			<div class='half'><b id='pre_phone'>$title_phone</b>		<input $e4 id='phone' type='text' name='mail_phone' value='".$phone."' maxlength=\"13\" $en3></div>
			<div class='full'><b id='pre_message'>$title_message</b>	<textarea $e5 id='message' name='mail_message' form='email' maxlength=\"600\" $en1>".$message."</textarea></div>
			<div class='or_call'>$or_call</div>
			<input id='submit' type=\"submit\" value=\"$button_send\" form='email'>
			</form>";
		echo "<div class='form_disclaimer'>".$disclaimer."</div>";
	}
}
?>
</div>






