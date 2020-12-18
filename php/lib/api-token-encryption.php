<?php
/**
 * @author Perfecto Web
 * @copyright 2009-2021
 * @site https://perfecto-web.com
 * 
 * Encryption - (AES-128-ECB-PKCS5Padding)
 * Below the functions return encrypted result in Base64 encoding.
 *
 */


/*-----------------------------------------------------------------
| [PHP 5 >= 5.3.0, PHP 7, PHP 8]
------------------------------------------------------------------*/
function encrypt_apikey($api_key, $api_secret) {
	$alg = 'AES-128-ECB';
	$ivsize = openssl_cipher_iv_length($alg);
	$iv = openssl_random_pseudo_bytes($ivsize);
	$encrypted = openssl_encrypt($api_key, $alg, $api_secret, OPENSSL_RAW_DATA, $iv); 
	$encrypted = base64_encode($encrypted);
	return $encrypted;
}

/*-----------------------------------------------------------------
| [PHP 4 >= 4.0.2, PHP 5, PHP 7 < 7.2.0, PECL mcrypt >= 1.0.0]
------------------------------------------------------------------*/
function encrypt_apikey($api_key, $api_secret) {
	$alg = MCRYPT_RIJNDAEL_128;
	$block_size = mcrypt_get_block_size($alg, $mode);
	$pad = $block_size - (strlen($api_key) % $block_size);
	$api_key .= str_repeat(chr($pad), $pad);
	$encrypted = mcrypt_encrypt($alg, $api_secret, $api_key, $mode);
	$encrypted = base64_encode($encrypted);
	return $encrypted;
}

?>