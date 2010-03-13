<?php
// The message


$sendto = "webmaster@pukhto.net";
$subject = "php mail test";
$message = 'Test message using PHP mail()';
$header = "From: webmaster@".$_SERVER["SERVER_NAME"]."\n";
$header .= "Content-Type: text/html; charset=iso-8859-1\n";

$message = 'Test message using PHP mail()';
$subject='Test';
// Send
if (mail($sendto,$subject,$message,$header, "-fwebmaster@".$_SERVER["SERVER_NAME"]))
{
 echo 'Mail sent!';
} else
{
 echo 'Error! Mail was not sent.';
};

?>