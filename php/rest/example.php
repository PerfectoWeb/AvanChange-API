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

include "avanchange.inc.php";

# Init tests class
# You can get your credentials from your account
# on avanchange.com
$merchant_id 	= '__YOUR_MERCHANT_ID__';
$api_key 		= '__YOUR_API_KEY__';
$api_secret 	= '__YOUR_API_SECRET__';

$avanchange = new AvanChange($api_key, $api_secret);


# Set API language
$avanchange->set_lang('en-EN'); // ex.: ru-RU, en-EN


/**
 * Get currencies list
 * Testing "exchange/currencies" endpoint
 *
 * Authorization: X-API-TOKEN | not important
 */
$currencies = $avanchange->get_currencies();
$avanchange->display($currencies);



/**
 * Example: Get Rates
 * Testing "exchange/rates" endpoint
 *
 * Authorization: X-API-TOKEN | not important
 */
$params = array(
	'start'    => 0, 
	'limit'    => 20, 
	'give'     => 'BTC'
);
$rates = $avanchange->get_rates($params);
$avanchange->display($rates);



/**
 * Example: Get Rate
 * Testing "exchange/rate" endpoint
 *
 * Authorization: X-API-TOKEN | not important
 */
$rate = $avanchange->get_rate('BTC-QWRUB');
$avanchange->display($rate);



/**
 * Example: Create order
 * Testing "order/create" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$params = array(
	'give'             => 'BTC',
	'give_amount'      => null,
	'give_purse'       => null,
	'take'             => 'QWRUB_WRONG',
	'take_amount'      => 1000,
	'take_purse'       => '79000000000',
	'customer_name'    => 'John Wick',
	'customer_email'   => 'john.wick@example.com',
	'customer_contact' => 'https://vk.com/john.wick'
);
$order = $avanchange->create_order($params);
$avanchange->display($order);



/**
 * Example: Confirm order
 * Testing "order/confirm" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$hash  = '__ORDER_HASH_HERE__';
$order = $avanchange->confirm_order($hash);
$avanchange->display($order);



/**
 * Example: Cancel order
 * Testing "order/cancel" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$hash  = '__ORDER_HASH_HERE__';
$order = $avanchange->cancel_order($hash);
$avanchange->display($order);



/**
 * Example: Check order
 * Testing "order/check" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$hash  = '__ORDER_HASH_HERE__';
$order = $avanchange->check_order($hash);
$avanchange->display($order);



/**
 * Example: Check order
 * Testing "account/info" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$account_info = $avanchange->get_account_info();
$avanchange->display($account_info);



/**
 * Example: Check order
 * Testing "account/balance" endpoint
 *
 * Authorization: X-API-TOKEN
 */
$account_balance = $avanchange->get_balance();
$avanchange->display($account_balance);


 
?>