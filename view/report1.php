<?php
$report = ['id' => 'dt1', 'title' => 'Views by channel', 'description' => 'Recorded viewing sessions grouped by channel, including unpublished videos. Uses the same session start dates as Video performance. Channels without views are omitted.', 'dated' => true, 'endpoint' => 'view/report1.json.php', 'columns' => [['data' => 'channel', 'title' => 'Channel'], ['data' => 'views', 'title' => 'Views']]];
include __DIR__ . '/reportTable.php';
