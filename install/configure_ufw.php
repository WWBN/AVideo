<?php
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

function executeCommand($command) {
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);
    return ['output' => $output, 'returnVar' => $returnVar];
}

function isRuleExists($rule) {
    $result = executeCommand("ufw status | grep -w \"$rule\"");
    return !empty($result['output']);
}

// Enable UFW if it is not enabled
$ufwStatus = executeCommand('ufw status');
if (strpos(implode("\n", $ufwStatus['output']), 'Status: inactive') !== false) {
    echo "Enabling UFW...\n";
    executeCommand('ufw --force enable');
}

// Set default policies
echo "Setting default policies to deny incoming and outgoing traffic...\n";
executeCommand('ufw default deny incoming');
executeCommand('ufw default deny outgoing');

// Allow SSH
echo "Allowing SSH...\n";
if (!isRuleExists('22/tcp')) {
    executeCommand('ufw allow in ssh');
    executeCommand('ufw allow out ssh');
}

// Allow specified ports for both incoming and outgoing traffic
$ports = [
    80,    // Apache HTTP
    443,   // Apache HTTPS
    8080,  // Nginx HTTP
    8443,  // Nginx HTTPS
    2053,  // Sockets
    1935   // RTMP connection
];

foreach ($ports as $port) {
    $rule = "$port/tcp";
    echo "Allowing port $port for incoming and outgoing traffic...\n";
    if (!isRuleExists("$port/tcp")) {
        executeCommand("ufw allow in $port");
        executeCommand("ufw allow out $port");
    }
}

// Ensure UFW is enabled on reboot
echo "Ensuring UFW is enabled on reboot...\n";
executeCommand('systemctl enable ufw');

// Reload UFW to apply changes
echo "Reloading UFW to apply changes...\n";
executeCommand('ufw reload');

echo "UFW configuration complete.\n";

?>
