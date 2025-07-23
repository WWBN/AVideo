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

// Get all available current_order values (sorted ascending)
$availableOrders = array_column($sections, 'current_order');
sort($availableOrders);

// Remove duplicates to ensure unique order values
$availableOrders = array_unique($availableOrders);
$availableOrders = array_values($availableOrders); // Re-index array

// If we don't have enough unique orders, generate additional ones
$totalChannels = count($sections);
$uniqueOrdersCount = count($availableOrders);

if ($uniqueOrdersCount < $totalChannels) {
    $maxOrder = max($availableOrders);
    for ($i = $uniqueOrdersCount; $i < $totalChannels; $i++) {
        $availableOrders[] = ++$maxOrder;
    }
}

// Reassign the lowest available order to the user with most subscribers
$index = 0;
foreach ($sections as $key => &$section) {
    $newOrder = $availableOrders[$index];
    $obj->$key = $newOrder;
    $section['new_order'] = $newOrder;
    $index++;
}

// Save the new object data
AVideoPlugin::setObjectData('Gallery', $obj);

// Final check to ensure no duplicates
$finalOrders = array_column($sections, 'new_order');
$duplicates = array_diff_assoc($finalOrders, array_unique($finalOrders));

if (!empty($duplicates)) {
    echo "WARNING: Duplicate orders detected! Fixing...\n";

    // Fix duplicates by reassigning sequential orders
    $orderCounter = 1;
    foreach ($sections as $key => &$section) {
        $obj->$key = $orderCounter;
        $section['new_order'] = $orderCounter;
        $orderCounter++;
    }

    // Save again with fixed orders
    AVideoPlugin::setObjectData('Gallery', $obj);
    echo "Fixed: Assigned sequential orders starting from 1.\n";
}

// Optional: output results
echo "Reordered Channels:\n";
foreach ($sections as $data) {
    $identification = User::getNameIdentificationById($data['users_id']);
    $u = new User($data['users_id']);
    $channelName = $u->getChannelName();
    echo "User: [{$data['users_id']}][$channelName]{$identification} | New Order: {$data['new_order']} | Subscribers: {$data['total_subscribers']}\n";
}
