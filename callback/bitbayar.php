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
 
// Required File Includes
include '../../../dbconnect.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';

require_once '../bit-bayar/bb_lib.php';

$gatewaymodule = 'bitbayar';
$GATEWAY       = getGatewayVariables($gatewaymodule);

if (!$GATEWAY['type']) {
	logTransaction($GATEWAY['name'], $_POST, 'Not activated');
	bbLog('[ERROR] In modules/gateways/callback/bitbayar.php: BitBayar module not activated');
	die('[ERROR] In modules/gateways/callback/bitbayar.php: BitBayar module not activated.');
}

// Checks invoice ID is a valid invoice number or ends processing
$invoiceid = $_POST['invoice_id'];
$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY['name']);

// Checks transaction number isn't already in the database and ends processing if it does
$transid = $_POST['id'];        
checkCbTransID($transid);

// Get invoice
$result    = mysql_query("SELECT id, total from tblinvoices where id=".mysql_real_escape_string($invoiceid));
$data      = mysql_fetch_assoc($result);

// Check btc_value
if($_POST['rp']!=(int) $data['total']){
	logTransaction($GATEWAY['name'], $_POST, 'Jumlah Rupiah yang dikirim tidak benar: '.$_POST['rp'].'!='.(int)$data['total'] );
	return;
}

// Check double_spend
if($_POST['auto_sell']!=1){			
	logTransaction($GATEWAY['name'], $_POST, 'Double Transaksi.');
	return;
}

// Double check to https://bitbayar.com/api/check_invoice
$options['token']	= $GATEWAY['apiKey'];
$options['id']      = $_POST['id'];

$url = 'https://bitbayar.com/api/check_invoice';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
$return = curl_exec($ch);
curl_close($ch);
$response = json_decode($return);

// Check if status has paid and ends processing if it does
if($response->success!=1 && $response->status!='paid'){
	logTransaction($GATEWAY['name'], $_POST, 'Status Bitbayar belum PAID.');
	return;
}

// Successful
$fee = 0;

// left blank, this will auto-fill as the full balance
$amount = '';

//add transaction payment
addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule);
logTransaction($GATEWAY['name'], $_POST, 'The transaction is now complete.');