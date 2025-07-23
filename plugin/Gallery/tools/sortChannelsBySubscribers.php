<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load plugin configuration
$obj = AVideoPlugin::getObjectData('Gallery');

$sections = array();

// Extract user IDs, subscriber counts, and current order values
foreach ($obj as $key => $value) {
    if (preg_match('/Channel_([0-9]+)_Order$/', $key, $matches)) {
        $user_id = $matches[1];
        $total_subscribers = Subscribe::getTotalSubscribes($user_id);
        $sections[$key] = array(
            'users_id' => $user_id,
            'total_subscribers' => $total_subscribers,
            'current_order' => (int) $value
        );
    }
}

// Sort sections by subscriber count (descending)
uasort($sections, function ($a, $b) {
    return $b['total_subscribers'] <=> $a['total_subscribers'];
});

// Get all existing order values and sort them
$availableOrders = array_column($sections, 'current_order');
sort($availableOrders);

// Assign the sorted order values to the sorted channels
$orderIndex = 0;
foreach ($sections as $key => &$section) {
    $obj->$key = $availableOrders[$orderIndex];
    $section['new_order'] = $availableOrders[$orderIndex];
    $orderIndex++;
}

// Save the new object data
AVideoPlugin::setObjectData('Gallery', $obj);

// Optional: output results
echo "Reordered Channels:\n";
foreach ($sections as $data) {
    $identification = User::getNameIdentificationById($data['users_id']);
    $u = new User($data['users_id']);
    $channelName = $u->getChannelName();
    echo "User: [{$data['users_id']}][$channelName]{$identification} | New Order: {$data['new_order']} | Subscribers: {$data['total_subscribers']}\n";
}
