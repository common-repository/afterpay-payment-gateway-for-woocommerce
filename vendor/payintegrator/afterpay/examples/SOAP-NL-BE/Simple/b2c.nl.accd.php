<?php

// Load AfterPay Library
require_once(__DIR__ . '/vendor/autoload.php'); // Change to correct url

// Create new AfterPay Object
$Afterpay = new \Afterpay\Afterpay();

// Set up the bill to address
$aporder['billtoaddress']['city'] = 'Heerenveen';
$aporder['billtoaddress']['housenumber'] = '90';
$aporder['billtoaddress']['housenumberaddition'] = 'a';
$aporder['billtoaddress']['isocountrycode'] = 'NL';
$aporder['billtoaddress']['postalcode'] = '8441ER';
$aporder['billtoaddress']['referenceperson']['dob'] = '1980-12-12T00:00:00';
$aporder['billtoaddress']['referenceperson']['email'] = 'test@afterpay.nl';
$aporder['billtoaddress']['referenceperson']['gender'] = 'M';
$aporder['billtoaddress']['referenceperson']['initials'] = 'A';
$aporder['billtoaddress']['referenceperson']['isolanguage'] = 'NL';
$aporder['billtoaddress']['referenceperson']['lastname'] = 'de Tester';
$aporder['billtoaddress']['referenceperson']['phonenumber'] = '0513744112';
$aporder['billtoaddress']['streetname'] =  'KR Poststraat';

// Set up the ship to address
$aporder['shiptoaddress']['city'] = 'Heerenveen';
$aporder['shiptoaddress']['housenumber'] = '90';
$aporder['shiptoaddress']['isocountrycode'] = 'NL';
$aporder['shiptoaddress']['postalcode'] = '8441ER';
$aporder['shiptoaddress']['referenceperson']['dob'] = '1980-12-12T00:00:00';
$aporder['shiptoaddress']['referenceperson']['email'] = 'test@afterpay.nl';
$aporder['shiptoaddress']['referenceperson']['gender'] = 'M';
$aporder['shiptoaddress']['referenceperson']['initials'] = 'A';
$aporder['shiptoaddress']['referenceperson']['isolanguage'] = 'NL';
$aporder['shiptoaddress']['referenceperson']['lastname'] = 'de Tester';
$aporder['shiptoaddress']['referenceperson']['phonenumber'] = '0513744112';
$aporder['shiptoaddress']['streetname'] =  'KR Poststraat';

// Set up the additional information
$aporder['ordernumber'] = 'ORDER1234567-05';
$aporder['bankaccountnumber'] = '';
$aporder['currency'] = 'EUR';
$aporder['ipaddress'] = $_SERVER['REMOTE_ADDR'];

// Set up order lines, repeat for more order lines
$sku = 'PRODUCT1';
$name = 'Product name 1';
$qty = 3;
$price = 3000; // in cents
$tax_category = 1; // 1 = high, 2 = low, 3, zero, 4 no tax
$Afterpay->create_order_line($sku, $name, $qty, $price, $tax_category);

// Create the order object for B2C or B2B
$Afterpay->set_order($aporder, 'B2C');

// Set up the AfterPay credentials and sent the order
$authorisation['merchantid'] = '';
$authorisation['portfolioid'] = '';
$authorisation['password'] = '';
$modus = 'test'; // for production set to 'live'

// Show request in debug
var_dump(array('AfterPay Request' => $Afterpay));

$Afterpay->do_request($authorisation, $modus);

// Show result in debug
var_dump(array('AfterPay Result' => $Afterpay->order_result));
