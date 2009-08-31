<?php
/*
SimpleMap Plugin
sminc.php: Workaround for file_get_contents error
*/
if (!defined("ch")) {
	function setupch() {
		$ch = curl_init();
		$c = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		return($ch);
	}

	define("ch", setupch());

	function curl_get_contents($url) {
		$c = curl_setopt(ch, CURLOPT_URL, $url);
		return(curl_exec(ch));
	}
}
?>