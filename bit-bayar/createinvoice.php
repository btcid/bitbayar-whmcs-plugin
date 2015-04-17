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

include '../../../dbconnect.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';

require 'bb_lib.php';
$gatewaymodule = 'bitbayar';
$GATEWAY = getGatewayVariables($gatewaymodule);


// get invoice
$invoiceId = (int) $_POST['invoice_id'];
$price     = $currency = false;
$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$invoiceId");
$data      = mysql_fetch_assoc($result);

if (!$data) {
    bbLog('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: No invoice found for invoice id #' . $invoiceId);
    die('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: Invalid invoice id #' . $invoiceId);
}

$price    = $data['total'];
$currency = $data['code'];
$status   = $data['status'];

if ($status != 'Unpaid') {
    bbLog('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: Invoice status must be Unpaid.  Status: ' . $status);
    die('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: Bad invoice status of ' . $status);
}


// create invoice
$options = $_POST;

unset($options['invoice_id']);
unset($options['systemURL']);

$options['token']         	 = $GATEWAY['apiKey'];
$options['invoice_id']       = $invoiceId;
$options['rupiah']           = $data['total'];
$options['memo']         	 = 'Invoice #'.$invoiceId;
$options['callback_url'] 	 = $_POST['systemURL'].'/modules/gateways/callback/bitbayar.php';
$options['url_success']      = $_POST['systemURL'];
$options['url_failed']    	 = $_POST['systemURL'];

$url = 'https://bitbayar.com/api/create_invoice';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
$return = curl_exec($ch);
curl_close($ch);
$response = json_decode($return);


if($response->success){
	header('Location: '.$response->payment_url);
	exit;
}
else{
	bbLog('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: Invoice error: ' . var_export($response['error'], true));
    die('[ERROR] In modules/gateways/bit-bayar/createinvoice.php: Invoice error: ' . var_export($response['error']['error_message'], true));
}