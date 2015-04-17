<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 BitBayar
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once 'bb_options.php';

/**
 * @param string $contents
 */
function bbLog($contents)
{
    error_log($contents);
}

/**
 * @param  string      $url
 * @param  string      $apiKey
 * @param  bool|string $post
 * @return array
 * bb : BitBayar
 */
function bbCurl($url, $apiKey, $post = false)
{
    global $bbOptions;
    
    $ch = curl_init($url);
	if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $length = strlen($post);
    }
	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	$return = curl_exec($ch);
	curl_close($ch);
	$response = json_decode($return);

	print_r($response);
    return $response;
}

function bbCreateInvoice($orderId, $price, $options = array())
{
    global $bbOptions;

    $options = array_merge($bbOptions, $options);    // $options override any options found in bb_options.php

	$options['token']  		= $options['token'];
    $options['invoice_id']  = $orderId;
    $options['rupiah']  	= $price;
    $options['memo']  		= 'Invoice #'.$orderId;
    $options['callback_url']= $options['callback_url'];
    $options['url_success'] = $options['url_success'];
    $options['url_failed']  = $options['url_failed'];
        
    $network = 'https://bitbayar.com/api/create_invoice';

    $postOptions = array('token', 'invoice_id', 'rupiah', 'memo', 'callback_url', 'url_success', 'url_failed');

    foreach ($postOptions as $o) {
        if (array_key_exists($o, $options)) {
            $post[$o] = $options[$o];
        }
    }


    $post     = json_encode($options);
    
    $url = 'https://bitbayar.com/api/create_invoice';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	$return = curl_exec($ch);
	curl_close($ch);
	$response = json_decode($return);
    
    return $response;
    
	
}

/**
 * Call from your notification handler to convert $_POST data to an object containing invoice data
 *
 * @param  string $apiKey
 * @param  null   $network
 * @return array
 */
function bbVerifyNotification($apiKey = false, $network = null)
{
    global $bbOptions;

    if (!$apiKey) {
        $apiKey = $bbOptions['token'];
    }

    $post = $_POST;

    if (!$post) {
        return 'No post data';
    }

    if (!array_key_exists('invoice_id', $post)) {
        return 'Cannot find invoice ID';
    }

    return bbGetInvoice($post['invoice_id'], $apiKey, $network);
}

/**
 * $options can include ('apiKey')
 *
 * @param  string $invoiceId
 * @param  string $apiKey
 * @param  string $network
 * @return array
 */
function bbGetInvoice($invoiceId, $apiKey = false, $network = null)
{
    global $bbOptions;

    if (!$apiKey) {
        $apiKey = $bbOptions['token'];
    }
    
    $network = 'https://bitbayar.com/api/create_invoice';

    $response = bbCurl($network . $invoiceId, $apiKey);

    if (is_string($response)) {
        return $response; // error
    }

    $response['posData'] = json_decode($response['posData'], true);

    if($bbOptions['verifyPos']) {
        $response['posData'] = $response['posData']['posData'];
    }

    return $response;
}