<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/source/Jacwright/RestServer/RestServer.php';
require 'controller.php';

set_time_limit(999);

$controllers = new Controllers();

// Fill our account with money
$controllers->setCurrencyBalance("BTC", "0.515");


$query['ticker'] = "USD-TEST";
$tickerResponse = $controllers->getTicker($query);
var_export($tickerResponse);

// Test returning zero orders
$query['ticker'] = "USD-TEST";
$eraseOrderResponse = $controllers->eraseOrders($query);
if($eraseOrderResponse != null && $eraseOrderResponse->success == true) {
    var_export($eraseOrderResponse);
} else {
    echo "Failed erasing orders...\n";
    die();
}

// Place an order
$query['market'] = "BTC-TEST";
$query['quantity'] = "1.5";
$query['rate'] = $tickerResponse->result->Ask;
$placeOrderResponse = $controllers->buyLimit($query);
var_export($placeOrderResponse);


// Get open orders
 
$orderHistory = $controllers->getOrderHistory($query);
var_export($orderHistory);
