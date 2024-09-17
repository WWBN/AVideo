<?php

function rowToVizioSearch($row)
{
    global $global;

    if (!is_array($row)) {
        $row = object_to_array($row);
    }
    if (empty($row)) {
        return false;
    }

    $movie = new stdClass();
    $movie->id = 'video_' . $row['id'];

    // Primary title
    $movie->primaryTitle = new stdClass();
    $movie->primaryTitle->localizedStrings = [
        [
            "rawLocale" => "en-US",
            "text" => UTF8encode($row['title'])
        ]
    ];

    // Primary description
    $movie->primaryDescription = new stdClass();
    $movie->primaryDescription->localizedStrings = [
        [
            "rawLocale" => "en-US",
            "text" => _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490)
        ]
    ];

    // Long and short descriptions
    $description = _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490);
    $movie->longDescription = [
        [
            "rawLocale" => "en-US",
            "text" => _substr($description, 0, 490)
        ]
    ];
    $movie->shortDescription = [
        [
            "rawLocale" => "en-US",
            "text" => _substr($description, 0, 200)
        ]
    ];

    // Duration (in "PT[seconds]S" format)
    $movie->duration = "PT" . durationToSeconds($row['duration']) . "S";

    // Rating (using rokuRatingSearch for compatibility)
    $movie->contentRatings = [
        [
            "id" => "rating_id",
            "ratingBody" => "MPAA",
            "rating" => !empty($row['rrating']) ? rokuRatingSearch($row['rrating']) : 'TV-G',
            "isExplicit" => false
        ]
    ];

    // Poster and widescreen images
    $movie->posterImages = [
        [
            "url" => Video::getRokuImage($row['id'])
        ]
    ];

    $movie->widescreenImages = [
        [
            "url" => Video::getRokuImage($row['id'])  // Optional widescreen image logic
        ]
    ];

    // Release date
    $movie->releaseDate = [
        "dateTime" => date('Y-m-d\TH:i:s\Z', strtotime($row['created'])),
        "precision" => "Day"
    ];

    // Directors
    $movie->directors = !empty($row['director']) ? explode(',', $row['director']) : [];

    // Actors
    $movie->actors = !empty($row['actors']) ? explode(',', $row['actors']) : [];

    // External IDs and source URL
    $movie->externalIds = [
        [
            "id" => 'video_' . $row['id'],
            "idType" => "Source"
        ]
    ];

    // URL to the video content
    $movie->url = Video::getSourceFileURL($row['filename'], false, 'video');

    return $movie;
}

header('Content-Type: application/json');

// Define cache settings
$cacheFeedName = "feedCache_VIZIO_ConfigurationFeed" . json_encode($_REQUEST);
$lifetime = 43200;
$output = ObjectYPT::getCache($cacheFeedName, $lifetime);

if (empty($rows)) {
    $rows = array();
}

if (empty($output)) {
    $feed = new stdClass();
    $feed->configurationFeed = new stdClass();
    $feed->configurationFeed->source = new stdClass();
    $feed->configurationFeed->source->id = $global['VizioSourceID'];
    $feed->configurationFeed->source->name = $title;

    // Content Feed (productions)
    $feed->contentFeed = new stdClass();
    $feed->contentFeed->sourceId = $feed->configurationFeed->source->id;
    $feed->contentFeed->productions = [];

    foreach ($rows as $row) {
        $movie = rowToVizioSearch($row); // Converting rows to movie details
        if (!empty($movie)) {
            $production = new stdClass();
            $production->id = $movie->id;
            $production->externalIds = [["id" => $movie->externalIds[0]->id, "idType" => $movie->externalIds[0]->idType]];
            $production->productionType = "Movie";
            $production->primaryTitle = [
                "localizedStrings" => [
                    ["rawLocale" => "en-US", "text" => $movie->primaryTitle->localizedStrings[0]['text']]
                ]
            ];
            $production->primaryDescription = [
                "localizedStrings" => [
                    ["rawLocale" => "en-US", "text" => $movie->primaryDescription->localizedStrings[0]['text']]
                ]
            ];

            // Add genres, ratings, and images
            $production->genres = $movie->genres;
            $production->posterImages = [["url" => $movie->posterImages[0]['url']]];
            $production->widescreenImages = [["url" => $movie->widescreenImages[0]['url']]];

            $feed->contentFeed->productions[] = $production;
        }
    }

    // Availability Feed
    $feed->availabilityFeed = new stdClass();
    $feed->availabilityFeed->sourceId = $feed->configurationFeed->source->id;

    // Cache the generated output
    $output = json_encode($feed, JSON_PRETTY_PRINT);
    ObjectYPT::setCache($cacheFeedName, $output);
}

if (!is_string($output)) {
    $output = json_encode($output);
}

die($output);

