<?php
/**
 * SDK for AvanChange API <https://api.avanchange.com>
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see <https://api.avanchange.com>
 *
 * @copyright Copyright (c) 2021, Perfecto Web
 * @link https://perfecto-web.com
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @version: 1.0.2
 */

class AvanChange {
	
	public $curl;
	public $method 			= null;
	public $version 		= '1.0.2';
	public $content_type 	= 'application/json';
	public $server 			= 'https://api.avanchange.com/v1/'; 
	public $lang 			= 'en-EN';
	public $lang_allowed 	= array('en-EN', 'ru-RU');
	public $timeout 		= 30;
	public $endpoints 		= array(
								'account/info',
								'account/balance',
								'exchange/currencies',
								'exchange/rates',
								'exchange/rate',
								'order/create',
								'order/check',
								'order/confirm',
								'order/cancel'
							);
	
	public $headers 		= [
								'Cache-Control: no-cache',
								'Content-Type: application/json; charset=utf-8',
								'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
							];
							
	public $header_lang 	= ['Accept-Language: en-US'];
	
	private $json_pattern 	= '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
	private $xml_pattern 	= '~^(?:text/|application/(?:atom\+|rss\+|soap\+)?)xml~i';
	
	private $token 			= null;
	private $last_endpoint	= null;



	/**
	 * Construct
	 *
	 * @access public
	 * @param string $api_key
	 * @param string  $api_secret
	 * @throws \ErrorException
	 */
	public function __construct($api_key, $api_secret) {
	
		if (!extension_loaded('curl')) {
			throw new \ErrorException('cURL library is not loaded');
		}
		$this->token = $this->encrypt_apikey($api_key, $api_secret);
		
	}
	
	
	
	
	/**
	 * Set API language
	 *
	 * @access public
	 * @param string $lang language in ISO 639-1, ex: en-EN
	 *
	 * @return string
	 */
	public function set_lang($lang) {
		
		$this->lang 		= (in_array($lang, $this->lang_allowed)) ? $lang : 'en-EN';
		$this->header_lang 	= ['Accept-Language: '.$this->lang];
		
		return $lang;
	}
	
	
	
	
	/**
	 * Post
	 *
	 * @access public
	 * @param string $endpoint
	 * @param array $data
	 * @param bool $auth
	 * @param string $method 
	 *
	 * @return mixed Returns the value provided by endpoint method.
	 */
	public function post($endpoint, $data = [], $auth = true, $method = 'POST') {
		
		# for logging
		$this->last_endpoint = $endpoint;
		
		$headers 	= $auth ? array_merge($this->headers, ['X-API-TOKEN: '.$this->token]) : $this->headers;
		$headers 	= array_merge($headers, $this->header_lang);
		$endpoint	= $this->server.$endpoint;
		
		$ch = curl_init( $endpoint );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->to_json($data) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1);
		$body 		= curl_exec($ch);
		$http_code 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $body;
		
	}
	
	
	
	
	/**
	 * Get
	 *
	 * @access public
	 * @param string $endpoint
	 * @param array $query
	 * @param bool $auth
	 *
	 * @return mixed Returns the value provided by endpoint method.
	 */
	public function get($endpoint, $query = [], $auth = true) {
		
		# for logging
		$this->last_endpoint = $endpoint;
		
		$headers	= $auth ? array_merge($this->headers, ['X-API-TOKEN: '.$this->token]) : $this->headers;
		$headers 	= array_merge($headers, $this->header_lang);
		$query		= $this->parse_get_params($query);
		$endpoint	= $this->server.$endpoint.$query;
		
		$ch = curl_init( $endpoint );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt( $ch, CURLOPT_POST, false);
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1);
		$body 		= curl_exec($ch);
		$http_code 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $body;
		
	}
	
	
	
	
	/**
	 * Parse params for query string
	 *
	 * @access public
	 * @param array $query
	 *
	 * @return query string
	 */
	public function parse_get_params($query = []) {
		
		if (empty($query) || !is_array($query)) return;
		return '?'.http_build_query($query);
		
	}
	
	
	
	
	/**
	 * Create order
	 *
	 * @access public
	 * @param array $data
	 *
	 * @return mixed return created order data or error
	 */
	public function create_order($data) {
		$result = $this->post('order/create', $data, true);
		return $result;
	}
	
	
	
	
	/**
	 * Confirm order
	 *
	 * @access public
	 * @param string $hash
	 *
	 * @return array $result
	 */
	public function confirm_order($hash = null) {
		
		if (is_null($hash)) return;
		
		$params = ['hash' => $hash];
		$result = $this->post('order/confirm', $params, true, 'PATCH');
		
		return $result;
		
	}
	
	
	
	
	/**
	 * Cancel order
	 *
	 * @access public
	 * @param string $hash
	 *
	 * @return array $result
	 */
	public function cancel_order($hash = null) {
		
		if (is_null($hash)) return;
		
		$params = ['hash' => $hash];
		$result = $this->post('order/cancel', $params, true, 'PATCH');
		
		return $result;
		
	}
	
	
	
	
	/**
	 * Check order
	 *
	 * @access public
	 * @param string $hash
	 *
	 * @return array $result
	 */
	public function check_order($hash = null) {
		
		if (is_null($hash)) return;
		return $this->get('order/check/'.$hash);
		
	}
	
	
	
	
	/**
	 * Get account info
	 *
	 * @access public
	 *
	 * @return array $result
	 */
	public function get_account_info() {
		return $this->get('account/info');
	}
	
	
	
	
	/**
	 * Get account balance
	 *
	 * @access public
	 *
	 * @return array $result
	 */
	public function get_balance() {
		return $this->get('account/balance');
	}
	
	
	
	
	/**
	 * Get Currencies
	 *
	 * @access public
	 *
	 * @return mixed currencies list from endpoint
	 */
	function get_currencies() {
		return $this->get('exchange/currencies');
	}
	
	
	
	
	/**
	 * Get Exchange Rates
	 *
	 * @access public
	 * @param array $query
	 *
	 * @return mixed exchange rates with filters
	 */
	function get_rates($query = []) {
		return $this->get('exchange/rates', $query);
	}
	
	
	
	
	/**
	 * Get Single Exchange Rate
	 *
	 * @access public
	 * @param string $symbol
	 *
	 * @return mixed exchange rate for selected symbol (ex: BTC-ETH)
	 */
	function get_rate($symbol = 'BTC-ETH') {
		return $this->get('exchange/rate/'.$symbol);
	}
	
	
	
	
	/**
	 * Array to JSON
	 *
	 * @access public
	 * @param array $array
	 *
	 * @return mixed
	 */
	public function to_json($array) {
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
	
	
	
	
	/**
	 * JSON to Array
	 *
	 * @access public
	 * @param array $json
	 *
	 * @return mixed json of array
	 */
	public function json_to_array($json) {
		return json_decode($json, true);
	}
	
	
	
	
	/**
	 * Encrypt API Key
	 *
	 * @access public
	 * @param string $api_key
	 * @param string $api_secret
	 *
	 * @return string API Token in Base64 encoding.
	 */
	public function encrypt_apikey($api_key, $api_secret) {
		$alg = 'AES-128-ECB';
		$ivsize = openssl_cipher_iv_length($alg);;
		$iv = openssl_random_pseudo_bytes($ivsize);
		$encrypted = openssl_encrypt($api_key, $alg, $api_secret, OPENSSL_RAW_DATA, $iv); 
		$encrypted = base64_encode($encrypted);
		return $encrypted;
	}
	
	
	
	
	/**
	 * Pretty var_dump
	 *
	 * @access public
	 * @param array $array
	 *
	 * @return mixed pretty formatted array
	 */
	function dump($array) {
		echo "<pre>"; print_r($array); echo "</pre>";
	}
	
	
	
	
	/**
	 * Pretty display
	 *
	 * @access public
	 * @param object $json
	 *
	 * @return mixed
	 */
	function display($json) {
		$url = $this->server.$this->last_endpoint;
		echo '<h3>Endpoint: <a href="'.$url.'">' . $this->last_endpoint . '</a></h3>';
		echo '<code style="padding-left:10px;display: block;font: 13px \'Lucida Console\', monospace; color:#353541">'.$json.'</code>';
	}



}
?>