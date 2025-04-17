<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

echo "🔧 Starting YPTSocket server setup...\n";

// Verifica se está em CLI
if (!isCommandLineInterface()) {
    die("❌ Command line only\n");
}

echo "📦 Loading plugin data...\n";
$SocketDataObj = AVideoPlugin::getDataObject("YPTSocket");
$SocketDataObj->serverVersion = YPTSocket::getServerVersion();

echo "🔌 Closing output buffers and sessions...\n";
ob_end_flush();
_mysql_close();
_session_write_close();

echo "🛠️ Killing any process using the port...\n";
killProcessOnPort();

// Renovar certificados SSL com output
echo "🔐 Renewing SSL certificates...\n";
exec('certbot renew 2>&1', $certbotOutput, $certbotReturn);
echo "🔐 certbot output:\n" . implode("\n", $certbotOutput) . "\n";
echo "🔐 certbot return code: {$certbotReturn}\n";

// Comando para iniciar o servidor
$startCommand = "sudo " . YPTSocket::getStartServerCommand();
echo "🚀 Starting server with command:\n{$startCommand}\n";

// Executar o comando e capturar saída
exec($startCommand . ' 2>&1', $output, $return_var);
echo "📤 Command output:\n" . implode("\n", $output) . "\n";
echo "🔚 Command finished with exit code: {$return_var}\n";
