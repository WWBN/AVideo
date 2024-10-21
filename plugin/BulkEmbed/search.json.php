<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');

if(!BulkEmbed::canBulkEmbed()){
    forbiddenPage('You cannot search');
}

$query = $_POST['query'] ?? '';
$pageToken = $_POST['pageToken'] ?? '';

if (empty($query)) {
    die(json_encode(['error' => true, 'msg'=>'Search query cannot be empty']));
}

$obj = AVideoPlugin::getObjectData("BulkEmbed");
$apiKey = $obj->API_KEY;

// Construct the YouTube API URL with pagination (if pageToken is provided)
$youtubeApiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($query) . "&type=video&maxResults=50&videoEmbeddable=true&key=" . $apiKey;

if (!empty($pageToken)) {
    $youtubeApiUrl .= "&pageToken=" . $pageToken;
}

$response = url_get_contents($youtubeApiUrl);
$responseData = json_decode($response, true);

if (empty($responseData) || !isset($responseData['items'])) {
    _error_log('Failed to retrieve data from YouTube ' . $youtubeApiUrl);
    $msg = 'Failed to retrieve data from YouTube';
    if(!empty($responseData['error']) && !empty($responseData['error']['message'])){
        $msg .= '<br>'.$responseData['error']['message'];
    }

    die(json_encode(['error' => true, 'msg'=>$msg]));
}

// Prepare the result array to include the embedding status
$results = [];

foreach ($responseData['items'] as $item) {
    if (!isset($item['id']['videoId'])) {
        continue; // Skip if videoId is not present
    }
    
    $videoId = $item['id']['videoId'];
    $link = "https://youtube.com/embed/" . $videoId;

    // Check if the video is already embedded in the database
    $isEmbedded = false;
    $embeddedVideoId = null;
    
    $sql = "SELECT id, videoLink FROM `videos` WHERE `videoLink` = ? LIMIT 1";
    $res = sqlDAL::readSql($sql, "s", [$link]);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);

    if (!empty($data['id'])) {
        $isEmbedded = true;
        $embeddedVideos_Id = $data['id'];  // Capture the videoId when already embedded
    }

    // Add item data to results
    $results[] = [
        'link' => $link,
        'videoId' => $videoId,
        'title' => $item['snippet']['title'],
        'description' => $item['snippet']['description'],
        'thumbs' => $item['snippet']['thumbnails']['high']['url'],
        'date' => $item['snippet']['publishedAt'],
        'isEmbedded' => $isEmbedded,
        'embeddedVideos_Id' => $embeddedVideos_Id // Add embedded videoId if embedded
    ];
}

// Return the data and pagination tokens to the client
die(json_encode([
    'data' => [
        'items' => $results,
        'nextPageToken' => $responseData['nextPageToken'] ?? null,
        'prevPageToken' => $responseData['prevPageToken'] ?? null
    ]
]));
