<?php

chdir('../../');
require_once 'api/Simpla.php';
require_once 'autoload.php';
require_once 'YooMoneyCallbackHandler.php';


$simpla  = new Simpla();
$handler = new YooMoneyCallbackHandler($simpla);

$order_id   = $simpla->request->post('customerNumber');
$invoice_id = $simpla->request->post('invoiceId');

$action = $simpla->request->get('action');

if ($action == 'notify') {
    $handler->processNotification();
} else {
    $handler->processReturnUrl();
}

