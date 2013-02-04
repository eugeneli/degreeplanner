<?php
function generateFingerprint()
{
    $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    $ua = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'no ua';
    $charset = !empty($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : 'no charset';
    $ip = substr($ip, 0, strrpos($ip, '.') - 1);
    return md5($ua . $ip . $charset);
}

function isValidFingerprint($fingerprint)
{
	if(isset($_SESSION['fingerprint']))
	{
		return $_SESSION['fingerprint'] == $fingerprint;
	}
	else
		return false;
}

?>