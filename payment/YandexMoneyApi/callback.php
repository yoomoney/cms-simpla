<?php

chdir('../../');
require_once('api/Simpla.php');
require_once('vendor/autoload.php');
require_once('YandexMoneyCallbackHandler.php');


$simpla  = new Simpla();
$handler = new YandexMoneyCallbackHandler($simpla);

$order_id   = $simpla->request->post('customerNumber');
$invoice_id = $simpla->request->post('invoiceId');

$action = $simpla->request->get('action');

if ($action == 'notify') {
    $body           = @file_get_contents('php://input');
    $callbackParams = json_decode($body);
    $handler->processNotification($simpla, $callbackParams);
} else {
    $handler->processReturnUrl($simpla);
}

