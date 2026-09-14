<?php
$report = ['id' => 'dt3', 'title' => 'Video reactions', 'description' => 'Current recorded likes and dislikes on each channel\'s videos. Imported or manually adjusted display counters are excluded. These totals are not filtered by date. Channels without reactions are omitted.', 'dated' => false, 'endpoint' => 'view/report3.json.php', 'columns' => [['data' => 'channel', 'title' => 'Channel'], ['data' => 'thumbsUp', 'title' => 'Likes'], ['data' => 'thumbsDown', 'title' => 'Dislikes']]];
include __DIR__ . '/reportTable.php';
