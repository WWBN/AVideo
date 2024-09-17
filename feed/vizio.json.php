<?php

require_once __DIR__ . '/rokuFunctions.php';

function getVizioImagePoster($videos_id)
{
    global $global;
    $images = Video::getImageFromID($videos_id);
    $imagePath = $images->posterPortraitPath;
    $destinationImage = str_replace(".jpg", "_vizioPoster.jpg", $imagePath);
    if (ImagesPlaceHolders::isDefaultImage($imagePath) || convertImageIfNotExists($imagePath, $destinationImage, 960, 1440, true)) {
        $relativePath = str_replace($global['systemRootPath'], '', $destinationImage);
        return getURL($relativePath);
    }
    return ImagesPlaceHolders::getVideoPlaceholderPortrait(ImagesPlaceHolders::$RETURN_URL);
}

function getVizioImageWide($videos_id)
{
    global $global;
    $images = Video::getImageFromID($videos_id);
    $imagePath = $images->posterLandscapePath;
    $destinationImage = str_replace(".jpg", "_vizioWide.jpg", $imagePath);
    if (ImagesPlaceHolders::isDefaultImage($imagePath) || convertImageIfNotExists($imagePath, $destinationImage, 848, 477, true)) {
        $relativePath = str_replace($global['systemRootPath'], '', $destinationImage);
        return getURL($relativePath);
    }
    return ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
}
function vizioRatingSearch($avideoRating)
{
    // Return rating based on the VIZIO Schema
    switch (strtolower($avideoRating)) {
        case 'g':
            return 'G';
        case 'pg':
            return 'PG';
        case 'pg-13':
            return 'PG-13';
        case 'r':
            return 'R';
        case 'nc-17':
            return 'NC-17';
        case 'ma':
            return 'UR';
        default:
            return 'G';
    }
}

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

    // Rating (using vizioRatingSearch for compatibility)
    $movie->contentRatings = [
        [
            "id" => "rating_" . $row['id'],  // Ensure a unique ID
            "ratingBody" => "MPAA",
            "rating" => !empty($row['rrating']) ? vizioRatingSearch($row['rrating']) : 'G',
            "isExplicit" => false
        ]
    ];

    // Get poster and widescreen images with correct resolutions
    $posterImages = getVizioImagePoster($row['id']);
    $widescreenImages = getVizioImageWide($row['id']);

    // Poster and widescreen images with required aspect ratios and resolutions
    $movie->posterImages = [
        [
            "url" => addQueryStringParameter($posterImages, 'videos_id', $row['id'])
        ]
    ];
    $movie->widescreenImages = [
        [
            "url" => addQueryStringParameter($widescreenImages, 'videos_id', $row['id'])
        ]
    ];

    // Release date and original air date
    $movie->releaseDate = [
        "dateTime" => date('Y-m-d\TH:i:s\Z', strtotime($row['created'])),
        "precision" => "Day"
    ];
    $movie->originalAirDate = date('Y-m-d', strtotime($row['created']));

    // Directors and actors
    $movie->directors = !empty($row['director']) ? explode(',', $row['director']) : [];
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
    $feed->configurationFeed->apps = [
        [
            'id' => $global['VizioAppID'],
            'priceModels' => ['Ad-supported'],  // Fix for priceModels error
        ]
    ];

    $feed->configurationFeed->countries = [
        [
            'id' => 'usa',
            'isoCode' => 'USA',
            'isoType' => 'ISO 3166-1 alpha-3',
        ]
    ];

    $feed->configurationFeed->deeplinkTemplates = [
        [
            "id" => "1",
            "template" => "https://flixhouse.com/video/[id]",
            "tokenDefinitions" => [
                [
                    "token" => "id",
                    "path" => "$.AvailabilityFeed.OnDemandOfferings.CustomAttributes.Id"
                ]
            ],
            "action" => 0
        ]
    ];

    // Content Feed (productions)
    $feed->contentFeed = new stdClass();
    $feed->contentFeed->sourceId = $feed->configurationFeed->source->id;
    $feed->contentFeed->productions = [];

    // Availability Feed
    $feed->availabilityFeed = new stdClass();
    $feed->availabilityFeed->sourceId = $feed->configurationFeed->source->id;
    $feed->availabilityFeed->onDemandOfferings = [];

    foreach ($rows as $row) {
        $movie = rowToVizioSearch($row);
        if (!empty($movie)) {
            $production = new stdClass();
            $production->id = $movie->id;
            $production->externalIds = [
                [
                    "id" => $movie->externalIds[0]->id ?? 'default_id',
                    "idType" => $movie->externalIds[0]->idType ?? 'Source'
                ]
            ];
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

            // Add genres as objects
            $production->genres = [
                [
                    "id" => "genre_" . $row['id'],
                    "value" => [
                        "rawLocale" => "en-US",
                        "text" => matchGenre($row['category'])  // Replace with actual genre
                    ]
                ]
            ];

            // Add poster and widescreen images
            $production->posterImages = [["url" => $movie->posterImages[0]['url']]];
            $production->widescreenImages = [["url" => $movie->widescreenImages[0]['url']]];

            // Add release date with valid countryId
            $production->releases = [
                [
                    "type" => "Unknown",
                    "date" => $movie->releaseDate,
                    "countryId" => 'usa'  // Fixed countryId
                ]
            ];

            // Add duration
            $production->duration = $movie->duration;

            // Add content ratings
            $production->contentRatings = $movie->contentRatings;

            $feed->contentFeed->productions[] = $production;

            $feed->availabilityFeed->onDemandOfferings[] = [
                "production" => [
                    "id" => $movie->id,
                    "scope" => "Movie"
                ],
                "appId" => [
                    $global['VizioAppID']
                ],
                "templateId" => "1",
                "resolutions" => [
                    "HD 720P"
                ],
                "color" => "Standard",
                "id" => "offeringmovie{$movie->id}",
                "payStructure" => "Ad-supported",
                "customAttributes" => [
                    "id" => ($row['id'].'')
                ]
            ];
        }
    }


    // Cache the generated output
    $output = json_encode($feed, JSON_PRETTY_PRINT);
    ObjectYPT::setCache($cacheFeedName, $output);
}

if (!is_string($output)) {
    $output = json_encode($output);
}

die($output);
