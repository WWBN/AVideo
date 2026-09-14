<?php
$report = ['id' => 'dt2', 'title' => 'Comment reactions', 'description' => 'Current likes and dislikes on each person\'s comments, last updated during the selected period. Removed votes are excluded; changes of vote are not a historical event log. People without reactions are omitted.', 'dated' => true, 'endpoint' => 'view/report2.json.php', 'columns' => [['data' => 'user', 'title' => 'Person'], ['data' => 'thumbsUp', 'title' => 'Likes'], ['data' => 'thumbsDown', 'title' => 'Dislikes']]];
include __DIR__ . '/reportTable.php';
