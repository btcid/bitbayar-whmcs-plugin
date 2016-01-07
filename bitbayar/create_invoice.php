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

//~ Required File 
include '../../../init.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';

$gatewaymodule = 'bitbayar';
$GATEWAY = getGatewayVariables($gatewaymodule);


//~ get invoice
$invoiceId = (int) $_POST['invoice_id'];
$price     = $currency = false;
$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$invoiceId");
$data_respon      = mysql_fetch_assoc($result);

if (!$data_respon) {
    die('[ERROR] In modules/gateways/bitbayar/createinvoice.php: Invalid invoice id #' . $invoiceId);
}

$price    = $data_respon['total'];
$currency = $data_respon['code'];
$status   = $data_respon['status'];

if ($status != 'Unpaid') {
    die('[ERROR] In modules/gateways/bitbayar/createinvoice.php: Bad invoice status of ' . $status);
}

logTransaction($GATEWAY['name'], 'order', print_r($_POST['currency'].$_POST['amount'], true));

//~ Create invoice
$data = array(
    'token'			=>$GATEWAY['apiToken'],
    'invoice_id'	=>$invoiceId,
    'rupiah'		=>$_POST['rupiah'],
    'memo'			=>$_POST['memo'],
    'callback_url'	=>$_POST['callback_url'],
    'url_success'	=>$_POST['url_success'],
    'url_failed'	=>$_POST['url_failed']
);

$url = 'https://bitbayar.com/api/create_invoice';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$return = curl_exec($ch);
curl_close($ch);
$response = json_decode($return);


if($response->success){
	header('Location: '.$response->payment_url);
	exit;
}
else{
	logTransaction($GATEWAY['name'], $response->error_message, 'BitBayar Error!');
	exit('Bitbayar API error '.$response->error_message);
}