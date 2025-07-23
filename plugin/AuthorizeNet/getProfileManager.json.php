<?php

require_once __DIR__ . '/../../videos/configuration.php';
require_once 'AuthorizeNet.php';
header('Content-Type: application/json');

try {
    $users_id = User::getId();
    $result = AuthorizeNet::generateManageProfileToken($users_id);
    echo json_encode($result);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'msg'   => $e->getMessage(),
        'line'  => __LINE__
    ]);
    exit;
}
