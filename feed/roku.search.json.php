<?php

require_once __DIR__.'/rokuFunctions.php';

header('Content-Type: application/json');

// Define cache settings
$cacheFeedName = "feedCache_ROKU_SearchFeed" . json_encode($_REQUEST);
$lifetime = 43200;
$output = ObjectYPT::getCache($cacheFeedName, $lifetime);

if (empty($rows)) {
    $rows = array();
}

if (empty($output)) {
    $feed = new stdClass();
    $feed->version = "1";
    $feed->defaultLanguage = "en";
    $feed->defaultAvailabilityCountries = [
        "us", "ca", "gb", "au", "de", "fr", "it", "es", "br", 
        "mx", "jp", "kr", "cn", "in", "ru", "za", "ar", "cl"
    ];
    $feed->assets = [];

    foreach ($rows as $row) {
        $movie = rowToRokuSearch($row);
        if (!empty($movie)) {
            $asset = new stdClass();
            $asset->id = $movie->id;
            $asset->type = "shortform";
            $asset->titles = [
                [
                    "value" => $movie->title,
                    "language" => "en"
                ]
                // Add other languages if needed
            ];
            $asset->shortDescriptions = [
                [
                    "value" => $movie->shortDescription,
                    "language" => "en"
                ]
                // Add other languages if needed
            ];
            $asset->longDescriptions = [
                [
                    "value" => $movie->longDescription,
                    "language" => "en"
                ]
                // Add other languages if needed
            ];
            $asset->releaseDate = $movie->releaseDate;
            $asset->genres = $movie->genres;
            $asset->advisoryRatings = [
                [
                    "source" => "MPAA",
                    "value" => $movie->rating
                ]
            ];
            $asset->images = [
                [
                    "type" => "main",
                    "url" => $movie->thumbnail,
                    "languages" => ["en"] // Add other languages if needed
                ]
            ];
            $asset->durationInSeconds = $movie->duration;
            $asset->content = new stdClass();
            $asset->content->playOptions = [
                [
                    "license" => "free",
                    "quality" => "HD", // or 'SD', '4K' based on your video quality
                    "playId" => $movie->id,
                    "availabilityStartTimeStamp" => strtotime($row['created']) * 1000,
                    "availabilityEndTimeStamp" => 2524546800000, // Example timestamp for a distant future date
                    "availabilityInfo" => [
                        "country" => ["us", "mx"]
                    ]
                ]
            ];

            $feed->assets[] = $asset;
        }
    }

    $output = json_encode($feed, JSON_PRETTY_PRINT);
    ObjectYPT::setCache($cacheFeedName, $output);
} else {
    // Cached output
}

if (!is_string($output)) {
    $output = json_encode($output);
}

die($output);

