<?php
/**
* The MIT License (MIT)
*
* Copyright (c) 2014 Teddy Fresnel
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

/**
* WHMCS Bitcoin payment module using Bitbayar system
* https://bitbayar.com
* @author Teddy Fresnel <teddyfresnel.com>
*/

function bitbayar_config()
	{
	$configarray = array(
		'FriendlyName' => array(
			'Type' => 'System',
			'Value'=>'Bitcoin (BitBayar)'
		),
		'apiToken' => array(
			'FriendlyName' => 'API Token from your bitbayar.com merchant account.',
			'Type'         => 'text'
		),
		'paymentInfo' => array(
			'FriendlyName' => 'Payment Info',
			'Type'         => 'textarea',
			'Rows'         => '2',
			'Description'  => 'payment information you want to be shown on the client invoice'
		)
	);

	return $configarray;
}

function bitbayar_link($params)
	{
	if (false === isset($params) || true === empty($params)) {
		die('[ERROR] In modules/gateways/bitbayar.php::bitbayar_link() function: Missing or invalid $params data.');
	}

	//~ Bitbayar Variables
	$api_token    = $params['apiToken'];
	$callback_url = $params['systemurl'] . "/modules/gateways/callback/bitbayar.php";
	$redirect_url = $params['systemurl'] . "/viewinvoice.php?id=" . $params['invoiceid'];

	//~ Invoice Variables
	$invoiceid   = $params['invoiceid'];
	$description = $params["description"];
	$amount      = $params['amount']; # Format: ##.##
	$currency    = $params['currency']; # Currency Code

	//~ Client Variables
	$firstname = $params['clientdetails']['firstname'];
	$lastname  = $params['clientdetails']['lastname'];
	$email     = $params['clientdetails']['email'];
	$address1  = $params['clientdetails']['address1'];
	$address2  = $params['clientdetails']['address2'];
	$city      = $params['clientdetails']['city'];
	$state     = $params['clientdetails']['state'];
	$postcode  = $params['clientdetails']['postcode'];
	$country   = $params['clientdetails']['country'];
	$phone     = $params['clientdetails']['phonenumber'];

	//~ System Variables    
	$systemurl = $params['systemurl'];
	$companyname = $params['companyname'];
	$currency = $params['currency'];

	$results = localAPI('getcurrencies',$values,'admin');
	foreach($results['currencies']['currency'] as $newCurrency){
		$dataCurrency[$newCurrency['code']]=$newCurrency['rate'];
	}

	if($dataCurrency['IDR']){
		$final_amount=round($amount*$dataCurrency['IDR']);
	}else{
		$info_currency='<span style="color:red;">IDR currency require for bitbayar payment</span>';
	}


	$post = array(
		'currency'		=>$currency,
		'amount'		=>$amount,
		'token'			=>$api_token,
		'invoice_id'	=>$invoiceid,
		'rupiah'		=>$final_amount,
		'memo'			=>$description,
		'callback_url'	=>$callback_url,
		'url_success'	=>$redirect_url,
		'url_failed'	=>$redirect_url
	);

	$form = '<form action="' . $systemurl . '/modules/gateways/bitbayar/create_invoice.php" method="POST">';

	foreach ($post as $key => $value) {
		$form .= '<input type="hidden" name="' . $key . '" value = "' . $value . '" />';
	}

	if($info_currency){
		$form .= $info_currency;
	}else{		
		$form .= '<br/><button type="submit" style="border:none;padding:0;background:0;"><img src="https://bitbayar.com/images/button/buy-white-small.png"/></button><br/>'.$params['paymentInfo'];
	}
	$form .= '</form>';
	return $form;
}