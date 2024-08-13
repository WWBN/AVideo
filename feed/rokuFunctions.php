<?php

function rokuRating($avideoRating)
{
    switch (strtolower($avideoRating)) {
        case 'g':
            return 'G';
        case 'pg':
            return 'PG';
        case 'pg-13':
            return 'PG13';
        case 'r':
            return 'R';
        case 'nc-17':
            return 'NC17';
        case 'ma':
            return '18+';
        default:
            return 'G';
    }
}

function rokuRatingSearch($avideoRating)
{
    switch (strtolower($avideoRating)) {
        case 'g':
            return 'G';
        case 'pg':
            return 'PG';
        case 'pg-13':
            return 'PG13';
        case 'r':
            return 'R';
        case 'nc-17':
            return 'NC17';
        case 'ma':
            return 'UR';
        default:
            return 'G';
    }
}

function rowToRokuSearch($row)
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
    $movie->title = UTF8encode($row['title']);
    $movie->description = _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490);
    $movie->longDescription = _substr($movie->description, 0, 490);
    $movie->shortDescription = _substr($movie->description, 0, 200);
    $movie->duration = durationToSeconds($row['duration']);  // Assume this is in "HH:MM:SS" format
    $movie->rating = !empty($row['rrating']) ? rokuRatingSearch($row['rrating']) : 'TV-G';
    $movie->genres = [matchGenre($row['category'])];  // Match category to genre
    $movie->thumbnail = Video::getRokuImage($row['id']);
    $movie->releaseDate = date('Y-m-d', strtotime($row['created']));
    $movie->directors = !empty($row['director']) ? explode(',', $row['director']) : [];  // Array of directors
    $movie->actors = !empty($row['actors']) ? explode(',', $row['actors']) : [];  // Array of actors
    $movie->url = Video::getSourceFileURL($row['filename'], false, 'video');  // URL to the video content

    return $movie;
}

function rowToRoku($row)
{
    global $global;
    if (!is_array($row)) {
        $row = object_to_array($row);
    }
    if (empty($row)) {
        return false;
    }
    $videoSource = Video::getSourceFileURL($row['filename'], false, 'video');
    $videoResolution = Video::getResolutionFromFilename($videoSource);
    //var_dump($videoSource);
    if (empty($videoSource)) {
        _error_log("Roku Empty video source {$row['id']}, {$row['clean_title']}, {$row['filename']}");
        return false;
    }

    $movie = new stdClass();
    $movie->id = 'video_' . $row['id'];
    $movie->videos_id = $row['id'];
    $movie->title = UTF8encode($row['title']);
    $movie->longDescription = _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490);
    if (empty($movie->longDescription)) {
        $movie->longDescription = $movie->title;
    }
    $movie->shortDescription = _substr($movie->longDescription, 0, 200);
    $movie->thumbnail = Video::getRokuImage($row['id']);
    $movie->tags = [_substr(UTF8encode($row['category']), 0, 20)];
    $movie->genres = ["special"];
    $movie->releaseDate = date('c', strtotime($row['created']));
    $movie->categories_id = $row['categories_id'];
    $rrating = $row['rrating'];
    $movie->rating = new stdClass();
    if (!empty($rrating)) {
        $movie->rating->rating = rokuRating($rrating);
        $movie->rating->ratingSource = 'MPAA';
    } else {
        $movie->rating->rating = 'UNRATED';  // ROKU DIRECT PUBLISHER COMPLAINS IF NO RATING OR RATING SOURCE
        $movie->rating->ratingSource = 'MPAA';
    }

    $content = new stdClass();
    $content->dateAdded = date('c', strtotime($row['created']));
    $content->captions = [];
    $content->duration = durationToSeconds($row['duration']);
    $content->language = "en";
    $content->adBreaks = ["00:00:00"];
    if (AVideoPlugin::isEnabledByName('GoogleAds_IMA')) {
        $content->vmap_xml = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=vmap&videos_id={$movie->videos_id}";
        $content->vmap_json = "{$content->vmap_xml}&json=1";
        $content->vast = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=vast&videos_id={$movie->videos_id}";
    } else {
        $content->vmap_xml = "";
        $content->vmap_json = "";
        $content->vast = "";
    }

    $video = new stdClass();
    $video->url = $videoSource;
    $video->quality = getResolutionTextRoku($videoResolution);
    $video->videoType = Video::getVideoTypeText($row['filename']);
    $content->videos = [$video];

    if (function_exists('getVTTTracks') || AVideoPlugin::isEnabledByName('SubtitleSwitcher')) {
        $captions = getVTTTracks($row['filename'], true);
        if (!empty($captions)) {
            $content->captions = array();
            foreach ($captions as $value) {
                $value = object_to_array($value);
                $content->captions[] = array(
                    'language' => $value['srclang'],
                    'captionType' => $value['label'],
                    'url' => $value['src']
                );
            }
        }
    }

    $movie->content = $content;
    return $movie;
}


function matchGenre($category)
{

    $validGenres = [
        "action",
        "action sports",
        "adventure",
        "aerobics",
        "agriculture",
        "animals",
        "animated",
        "anime",
        "anthology",
        "archery",
        "arm wrestling",
        "art",
        "arts/crafts",
        "artistic gymnastics",
        "artistic swimming",
        "athletics",
        "auction",
        "auto",
        "auto racing",
        "aviation",
        "awards",
        "badminton",
        "ballet",
        "baseball",
        "basketball",
        "3x3 basketball",
        "beach soccer",
        "beach volleyball",
        "biathlon",
        "bicycle",
        "bicycle racing",
        "billiards",
        "biography",
        "blackjack",
        "bmx racing",
        "boat",
        "boat racing",
        "bobsled",
        "bodybuilding",
        "bowling",
        "boxing",
        "bullfighting",
        "bus./financial",
        "canoe",
        "card games",
        "ceremony",
        "cheerleading",
        "children",
        "children-music",
        "children-special",
        "children-talk",
        "collectibles",
        "comedy",
        "comedy drama",
        "community",
        "computers",
        "canoe/kayak",
        "consumer",
        "cooking",
        "cricket",
        "crime",
        "crime drama",
        "curling",
        "cycling",
        "dance",
        "dark comedy",
        "darts",
        "debate",
        "diving",
        "docudrama",
        "documentary",
        "dog racing",
        "dog show",
        "dog sled",
        "drag racing",
        "drama",
        "educational",
        "entertainment",
        "environment",
        "equestrian",
        "erotic",
        "event",
        "exercise",
        "fantasy",
        "faith",
        "fashion",
        "fencing",
        "field hockey",
        "figure skating",
        "fishing",
        "football",
        "food",
        "fundraiser",
        "gaelic football",
        "game show",
        "gaming",
        "gay/lesbian",
        "golf",
        "gymnastics",
        "handball",
        "health",
        "historical drama",
        "history",
        "hockey",
        "holiday",
        "holiday music",
        "holiday music special",
        "holiday special",
        "holiday-children",
        "holiday-children special",
        "home improvement",
        "horror",
        "horse",
        "house/garden",
        "how-to",
        "hunting",
        "hurling",
        "hydroplane racing",
        "indoor soccer",
        "interview",
        "intl soccer",
        "judo",
        "karate",
        "kayaking",
        "lacrosse",
        "law",
        "live",
        "luge",
        "martial arts",
        "medical",
        "military",
        "miniseries",
        "mixed martial arts",
        "modern pentathlon",
        "motorcycle",
        "motorcycle racing",
        "motorsports",
        "mountain biking",
        "music",
        "music special",
        "music talk",
        "musical",
        "musical comedy",
        "mystery",
        "nature",
        "news",
        "newsmagazine",
        "olympics",
        "opera",
        "outdoors",
        "parade",
        "paranormal",
        "parenting",
        "performing arts",
        "playoff sports",
        "poker",
        "politics",
        "polo",
        "pool",
        "pro wrestling",
        "public affairs",
        "racquet",
        "reality",
        "religious",
        "ringuette",
        "road cycling",
        "rodeo",
        "roller derby",
        "romance",
        "romantic comedy",
        "rowing",
        "rugby",
        "running",
        "rhythmic gymnastics",
        "sailing",
        "science",
        "science fiction",
        "self improvement",
        "shooting",
        "shopping",
        "sitcom",
        "skateboarding",
        "skating",
        "skeleton",
        "skiing",
        "snooker",
        "snowboarding",
        "snowmobile",
        "soap",
        "soap special",
        "soap talk",
        "soccer",
        "softball",
        "special",
        "speed skating",
        "sport climbing",
        "sports",
        "sports talk",
        "squash",
        "standup",
        "sumo wrestling",
        "surfing",
        "suspense",
        "swimming",
        "table tennis",
        "taekwondo",
        "talk",
        "technology",
        "tennis",
        "theater",
        "thriller",
        "track/field",
        "track cycling",
        "travel",
        "trampoline",
        "triathlon",
        "variety",
        "volleyball",
        "war",
        "water polo",
        "water skiing",
        "watersports",
        "weather",
        "weightlifting",
        "western",
        "wrestling",
        "yacht racing"
    ];
    $categoryLower = strtolower($category);

    // Check for exact match
    foreach ($validGenres as $genre) {
        if (strtolower($genre) === $categoryLower) {
            return $genre;
        }
    }

    // Check if any word in category matches a genre
    $categoryWords = explode(' ', $categoryLower);
    foreach ($categoryWords as $word) {
        foreach ($validGenres as $genre) {
            if (strpos(strtolower($genre), $word) !== false) {
                return $genre;
            }
        }
    }

    // Default genre if no match is found
    return "special";
}
