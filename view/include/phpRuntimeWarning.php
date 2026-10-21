<?php
// HTML template only: never register this notice in request/API bootstrap hooks.
if (!class_exists('User', false) || !User::isLogged() || !User::isAdmin()) {
    return;
}
require_once __DIR__ . '/../../objects/PHPRuntimeSecurity.php';
if (PHPRuntimeSecurity::isHTMLResponse(headers_list(), $_SERVER['SCRIPT_NAME'] ?? '') && !headers_sent()) {
    // Also prevent browser/proxy caches from reusing an admin observation on another node.
    header('Cache-Control: private, no-store, max-age=0');
}
echo PHPRuntimeSecurity::renderAdminNotice(PHPRuntimeSecurity::detect(), !empty($phpRuntimeSecurityShowPassing));
