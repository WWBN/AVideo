<?php
// Check if the script is being run from the command line
if (php_sapi_name() !== 'cli') {
    die("Error: This script must be run from the command line.");
}

echo "Starting the restart script...".PHP_EOL;

// Define the commands to stop and start services
// First, we'll stop the MySQL and Apache services
$commands = [
    'sudo systemctl stop mysql' => 'Stopping MySQL service...',
    'sudo systemctl stop apache2' => 'Stopping Apache service...',
    'sudo systemctl start mysql' => 'Starting MySQL service...',
    'sudo systemctl start apache2' => 'Starting Apache service...'
    // RESTART ENCODER
    // rERSTART SOCKET
];

// Execute each command
foreach ($commands as $command => $message) {
    echo $message . PHP_EOL;
    
    // Execute the command
    exec($command, $output, $return_var);

    // Check for errors in the execution of the command
    if ($return_var !== 0) {
        echo "Error occurred: Command execution failed.".PHP_EOL;
        echo implode(PHP_EOL, $output);
        exit($return_var);
    }

    // Display the output of the command
    echo implode(PHP_EOL, $output);
    echo PHP_EOL;
}

echo "MySQL and Apache services have been successfully restarted.".PHP_EOL;
echo "Script execution completed.".PHP_EOL;
?>
