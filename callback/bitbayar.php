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
include '../../../dbconnect.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';

$gatewaymodule = 'bitbayar';
$GATEWAY       = getGatewayVariables($gatewaymodule);

if (!$GATEWAY['type']) {
	logTransaction($GATEWAY['name'], $_POST, 'Modul BitBayar tidak aktif');
	die('[ERROR] In modules/gateways/callback/bitbayar.php: BitBayar module not activated.');
}

//~ Checks invoice ID is a valid invoice number or ends processing
$invoiceid = $_POST['invoice_id'];
$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY['name']);

//~ Required File Checks transaction number isn't already in the database and ends processing if it does
$transid = $_POST['id'];        
checkCbTransID($transid);

//~ Get invoice
$result    = mysql_query("SELECT id, total from tblinvoices where id=".mysql_real_escape_string($invoiceid));
$data_respon      = mysql_fetch_assoc($result);

//~ Check IDR value
if($_POST['rp']!=(int) $data_respon['total']){
	logTransaction($GATEWAY['name'], $_POST, 'Jumlah Rupiah yang dikirim tidak benar: '.$_POST['rp'].'!='.(int)$data_respon['total'] );
	return;
}

//~ Check double spend
if($_POST['auto_sell']!=1){			
	logTransaction($GATEWAY['name'], $_POST, 'Double Transaksi.');
	return;
}

//~ Double check to https://bitbayar.com/api/check_invoice
$data['token']	= $GATEWAY['apiToken'];
$data['id']      = $transid;

$url = 'https://bitbayar.com/api/check_invoice';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$return = curl_exec($ch);
curl_close($ch);
$response = json_decode($return);

//~ Check if status has paid and ends processing if it does
if($response->success!=1 && $response->status!='paid'){
	logTransaction($GATEWAY['name'], $_POST, 'Pembayaran Belum Lengkap.');
	return;
}

//~ Successful
$fee = 0;

//~ Left blank, this will auto-fill as the full balance
$amount = '';

//~ Add transaction payment
addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule);
logTransaction($GATEWAY['name'], $_POST, 'Pembayaran Sukses.');