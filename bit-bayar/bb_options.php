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

global $bbOptions;

// Please look carefully throught these options and adjust according to your installation.
// Alternatively, any of these options can be dynamically set upon calling the functions in bb_lib.

// REQUIRED Api key you created at bitbayar.com
// example: $bbOptions['token'] = 'GD1FA2CC2A3357FDF45B84744FFF7102';
$bbOptions['token'] = '';

// url where bitbayar server should send update notifications.  See API doc for more details.
# example: $bbOptions['callback_url'] = 'http://www.mywebsite.com/bitbayar_callback.php';
$bbOptions['callback_url'] = '';

// url where the customer should be directed to after paying for the order
# example: $bbOptions['url_success'] = 'http://www.mywebsite.com/bitbayar_payment_success.php';
$bbOptions['url_success'] = '';

// url where the customer should be directed to after canceled the order
# example: $bbOptions['url_failed'] = 'http://www.mywebsite.com/bitbayar_payment_failed.php';
$bbOptions['url_failed'] = '';