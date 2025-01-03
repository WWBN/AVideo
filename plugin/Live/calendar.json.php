<?php
global $global, $config;

require_once __DIR__ . '/../../videos/configuration.php';


/**
 * Generate a calendar event.
 *
 * @param int $id Event ID.
 * @param int $weekday Day of the week (0 = Sunday, ..., 6 = Saturday).
 * @param int $startMinute Start time in minutes (from 00:00 UTC).
 * @param int $endMinute End time in minutes (from 00:00 UTC).
 * @param string $customTitle Event title.
 * @param string $backgroundColor Background color.
 * @param string $borderColor Border color.
 * @param string $textColor Text color.
 * @param array $extendedProps Additional properties.
 * @param int|null $weekOffset Week offset for recurring events. Null for single events.
 * @return array The formatted event.
 */
function createEvent($id, $weekday, $startMinute, $endMinute, $customTitle = "Default Title", $backgroundColor = "#cccccc", $borderColor = "#999999", $textColor = "#000000", $extendedProps = [], $weekOffset = null)
{
    if ($weekday === null || $startMinute === null || $endMinute === null) {
        throw new InvalidArgumentException("Mandatory fields 'weekday', 'startMinute', and 'endMinute' are required.");
    }

    // Base date calculation (start of the week, optionally offset by weeks)
    $startDateTime = (new DateTime('last Sunday', new DateTimeZone('UTC')))
        ->modify($weekOffset !== null ? "+{$weekOffset} weeks" : "") // Add week offset if specified
        ->modify("+{$weekday} days")
        ->setTime(0, 0)
        ->modify("+{$startMinute} minutes");

    $endDateTime = (clone $startDateTime)->modify("+" . ($endMinute - $startMinute) . " minutes");

    return [
        'id' => $id,
        'title' => $customTitle ?: "Default Title",
        'start' => $startDateTime->format('Y-m-d\TH:i:s\Z'),
        'end' => $endDateTime->format('Y-m-d\TH:i:s\Z'),
        'allDay' => false,
        'backgroundColor' => $backgroundColor ?: "#cccccc",
        'borderColor' => $borderColor ?: "#999999",
        'textColor' => $textColor ?: "#000000",
        'extendedProps' => $extendedProps,
    ];
}

/**
 * Generate a calendar event.
 *
 * @param int $id Event ID.
 * @param int|string $startTimestamp Start time as a Unix timestamp.
 * @param int|string $endTimestamp End time as a Unix timestamp.
 * @param string $customTitle Event title.
 * @param string $backgroundColor Background color.
 * @param string $borderColor Border color.
 * @param string $textColor Text color.
 * @param array $extendedProps Additional properties.
 * @return array The formatted event.
 */
function createEventFromTimestamps($id, $startTimestamp, $endTimestamp, $customTitle = "Default Title", $backgroundColor = "#cccccc", $borderColor = "#999999", $textColor = "#000000", $extendedProps = [])
{
    if (!$startTimestamp || !$endTimestamp) {
        throw new InvalidArgumentException("Start and End timestamps are required.");
    }

    $startDateTime = (new DateTime())->setTimestamp($startTimestamp)->setTimezone(new DateTimeZone('UTC'));
    $endDateTime = (new DateTime())->setTimestamp($endTimestamp)->setTimezone(new DateTimeZone('UTC'));

    return [
        'id' => $id,
        'title' => $customTitle,
        'start' => $startDateTime->format('Y-m-d\TH:i:s\Z'),
        'end' => $endDateTime->format('Y-m-d\TH:i:s\Z'),
        'allDay' => false,
        'backgroundColor' => $backgroundColor,
        'borderColor' => $borderColor,
        'textColor' => $textColor,
        'extendedProps' => $extendedProps,
    ];
}

$calendarEvents = [];

// Add VideoPlaylistScheduler events
if (AVideoPlugin::isEnabledByName('VideoPlaylistScheduler')) {
    $repeatWeeks = 12; // Number of weeks to repeat the events

    // Fetch events from VideoPlaylistScheduler
    $eventsData = VideoPlaylistScheduler::getSchedulerEvents();

    foreach ($eventsData as $event) {
        try {
            $id = $event['id'];
            $weekday = $event['extendedProps']['weekday'] ?? null;
            $startMinute = $event['extendedProps']['start_minute'] ?? null;
            $endMinute = $event['extendedProps']['end_minute'] ?? null;
            $customTitle = $event['extendedProps']['custom_title'] ?? "Default Title";
            $backgroundColor = $event['backgroundColor'] ?? "#cccccc";
            $borderColor = $event['borderColor'] ?? "#999999";
            $textColor = $event['textColor'] ?? "#000000";
            $extendedProps = $event['extendedProps'] ?? [];

            // Generate recurring events for the specified number of weeks
            for ($weekOffset = 0; $weekOffset < $repeatWeeks; $weekOffset++) {
                $calendarEvents[] = createEvent(
                    $id,
                    $weekday,
                    $startMinute,
                    $endMinute,
                    $customTitle,
                    $backgroundColor,
                    $borderColor,
                    $textColor,
                    $extendedProps,
                    $weekOffset
                );
            }
        } catch (InvalidArgumentException $e) {
            error_log("Error creating VideoPlaylistScheduler event with ID $id: " . $e->getMessage());
        }
    }
}

// Add Playlists_schedules events
if (AVideoPlugin::isEnabledByName('PlayLists')) {
    $rows = Playlists_schedules::getAllActive();
    //var_dump($rows);exit;
    foreach ($rows as $playlist) {
        try {
            $id = $playlist['id'];
            $startTimestamp = $playlist['start_datetime'];
            $endTimestamp = $playlist['finish_datetime'];
            $customTitle = $playlist['name'] ?? "Playlist Event";
            $backgroundColor = "#4caf50"; // Default green for playlists
            $borderColor = "#388e3c";
            $textColor = "#ffffff";
            $extendedProps = [
                'description' => $playlist['description'] ?? '',
                'repeat' => $playlist['repeat'] ?? 'n',
                'parameters' => $playlist['parameters'] ?? '',
                'playlists_id' => $playlist['playlists_id'] ?? null,
            ];

            $calendarEvents[] = createEventFromTimestamps(
                $id,
                $startTimestamp,
                $endTimestamp,
                $customTitle,
                $backgroundColor,
                $borderColor,
                $textColor,
                $extendedProps
            );
        } catch (InvalidArgumentException $e) {
            error_log("Error creating PlayLists event with ID $id: " . $e->getMessage());
        }
    }
}


// Add Live application array events
$appArray = AVideoPlugin::getLiveApplicationArray();

foreach ($appArray as $app) {
    try {
        //var_dump($app['type']);
        $isPlayingNow = empty($app['comingsoon']);

        $id = $app['liveLinks_id'] ?? $app['live_schedule_id'] ?? uniqid('live_');
        
        // Determine start and end timestamps
        $startTimestamp = $isPlayingNow ? time() : strtotime($app['start_date'] ?? $app['scheduled_time'] ?? 'now');
        $endTimestamp = $isPlayingNow ? strtotime('+1 hour') : strtotime($app['end_date'] ?? '+1 hour', $startTimestamp);
        
        // Set custom title and styling
        $customTitle = "[app][{$app['type']}] ".str_replace('&zwnj;', '', $app['title'] ?? ($app['description'] ?? ($isPlayingNow ? "Playing Now" : "Live Event"))) ;
        $backgroundColor = $isPlayingNow ? "#00c853" : "#ff5722"; // Green for playing now, orange for scheduled
        $borderColor = $isPlayingNow ? "#009624" : "#e64a19";
        $textColor = "#ffffff";
        
        // Additional properties
        $extendedProps = [
            'description' => $app['description'] ?? '',
            'href' => $app['href'] ?? '',
            'photo' => $app['photo'] ?? '',
            'UserPhoto' => $app['UserPhoto'] ?? '',
            'status' => $isPlayingNow ? 'Playing Now' : 'Scheduled',
        ];

        $ev = createEventFromTimestamps(
            $id,
            $startTimestamp,
            $endTimestamp,
            $customTitle,
            $backgroundColor,
            $borderColor,
            $textColor,
            $extendedProps
        );

        //var_dump($ev);

        $calendarEvents[] = $ev;
    } catch (InvalidArgumentException $e) {
        var_dump("Error creating Live App event with ID $id: " . $e->getMessage());
        error_log("Error creating Live App event with ID $id: " . $e->getMessage());
    }
}


// live

// livelinks

//ppv

// Output the calendar events in JSON format
header('Content-Type: application/json');
echo json_encode($calendarEvents, JSON_PRETTY_PRINT);
