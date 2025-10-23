<?php
/**
 * USAGE
 * php importSiteFromAPI.php https://yoursiteURL.com/ APISecret [users_id] [categories_id]
 * if does not pass users_id it will also import users
 * if does not pass categories id it will also import categories
 *
 */

$siteURL = trim(@$argv[1]);
$APISecret = trim(@$argv[2]);
$imported_users_id = intval(@$argv[3]);
$imported_categories_id = intval(@$argv[4]);
$total_to_import = intval(@$argv[5]);
$type = trim(@$argv[6]);

// Log script start with parameters
_error_log("=== SCRIPT START ===");
_error_log("Parameters: siteURL='$siteURL', APISecret='" . substr($APISecret, 0, 5) . "...', imported_users_id=$imported_users_id, imported_categories_id=$imported_categories_id, total_to_import=$total_to_import, type='$type'");
_error_log("PHP Version: " . phpversion() . ", Memory Limit: " . ini_get('memory_limit'));

//streamer config
require_once '../videos/configuration.php';
_error_log("Configuration loaded successfully");

if (!isCommandLineInterface()) {
    _error_log("ERROR: Not running in command line interface");
    return die('Command Line only');
}

ob_end_flush();

function download($url, $filename, $path, $forceDownload = false) {
    global $stats;

    _error_log("download() called: url='$url', filename='$filename', path='$path', forceDownload=" . ($forceDownload ? 'true' : 'false'));

    $stats['downloads_attempted']++;

    $parts = explode("/{$filename}/", $url);

    if (empty($parts[1])) {
        if (preg_match("/\.mp3$/", $url)) {
            $parts[1] = "{$filename}.mp3";
            _error_log("download() detected mp3 file: " . $parts[1]);
        }
    }

    if (empty($parts[1])) {
        _error_log("importVideo::download ERROR on download {$url} - parts[1] is empty");
        $stats['downloads_failed']++;
        return false;
    }

    $parts2 = explode('?', $parts[1]);
    $file = $parts2[0];
    $destination = $path . $file;

    if ($forceDownload || !file_exists($destination)) {
        _error_log("importVideo::download starting download to [$destination]");
        $result = wget($url, $destination, true);
        if ($result) {
            $stats['downloads_successful']++;
            _error_log("importVideo::download SUCCESS for [$destination]");
        } else {
            $stats['downloads_failed']++;
            _error_log("importVideo::download FAILED for [$destination]");
        }
        return $result;
    } else {
        _error_log("importVideo::download skipped (file exists) [$destination]");
        $stats['downloads_skipped']++;
    }
    return false;
}function cleanupMemoryAndConnections() {
    global $global;

    // Force garbage collection
    gc_collect_cycles();

    // Log current memory usage
    $memory = memory_get_usage(true);
    $peak = memory_get_peak_usage(true);
    _error_log("Memory cleanup - Current: " . number_format($memory / 1024 / 1024, 2) . "MB, Peak: " . number_format($peak / 1024 / 1024, 2) . "MB");

    // Check if we need to reconnect database
    if (!_mysql_is_open()) {
        _error_log("Database connection lost, reconnecting...");
        _mysql_connect();
    }

    // Log execution time so far
    static $start_time = null;
    if ($start_time === null) {
        $start_time = time();
    }
    $elapsed = time() - $start_time;
    _error_log("Execution time so far: " . gmdate("H:i:s", $elapsed) . " (" . $elapsed . " seconds)");

    // Small sleep to reduce CPU pressure
    usleep(100000); // 0.1 seconds

    return true;
}

// Add signal handler for graceful shutdown (if supported)
if (function_exists('pcntl_signal')) {
    _error_log("Setting up signal handlers for graceful shutdown");
    pcntl_signal(SIGTERM, function($signo) {
        _error_log("SIGTERM received - shutting down gracefully");
        exit(0);
    });
    pcntl_signal(SIGINT, function($signo) {
        _error_log("SIGINT received - shutting down gracefully");
        exit(0);
    });
} else {
    _error_log("PCNTL extension not available - signal handling disabled");
}

set_time_limit(360000);
ini_set('max_execution_time', 360000);

// Memory optimization settings
ini_set('memory_limit', '2G');
gc_enable();

_error_log("Time limit set to 360000 seconds, memory limit set to 2G, garbage collection enabled");

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
_error_log("Global settings configured - rowCount and limitForUnlimitedVideos set to 999999");

while (empty($siteURL) || !isValidURL($siteURL)) {
    _error_log("Prompting for site URL (current: '$siteURL')");
    $siteURL = readline('Enter a valid URL: ');
}
_error_log("Site URL validated: $siteURL");

while (empty($APISecret)) {
    _error_log("Prompting for API Secret");
    $APISecret = readline('Enter a valid APISecret: ');
}
_error_log("API Secret provided (length: " . strlen($APISecret) . ")");

if (empty($imported_users_id)) {
    _error_log("Prompting for users_id");
    $imported_users_id = (int) readline('Enter a users id to be the video owner or 0 to also import the users: ');
}
_error_log("Users ID set to: $imported_users_id");

if (empty($imported_categories_id)) {
    _error_log("Prompting for categories_id");
    $imported_categories_id = (int) readline('Enter a category id to be linked to the video or 0 to also import the categories: ');
}
_error_log("Categories ID set to: $imported_categories_id");

if (empty($total_to_import)) {
    _error_log("Prompting for total to import");
    $total_to_import = (int) readline('How many videos do you want to import? type 0 to import all: ');
}
_error_log("Total to import set to: $total_to_import");

$rowCount = 50;
$current = 1;
$hasNewContent = true;

// Add memory management
$processedCount = 0;
$memoryCleanupInterval = 10; // Clean up every 10 items
$batchSize = 5; // Process in smaller batches for better memory management

_error_log("importSite: start {$siteURL} imported_users_id=$imported_users_id imported_categories_id=$imported_categories_id total_to_import=$total_to_import");

//exit;
if ($type !== 'm3u8') {
    _error_log("=== STARTING CATEGORIES IMPORT ===");
    if (empty($imported_categories_id) || $imported_categories_id < 0) {
        _error_log("Categories import enabled - importing categories from remote site");
        // get categories
        while ($hasNewContent) {
            _error_log("Categories import - Processing page $current");
            $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=category&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}";
            _error_log("Categories API call: $APIURL");

            try {
                $content = url_get_contents($APIURL, "", 30);
                $stats['api_calls']++;
            } catch (Exception $e) {
                _error_log("Categories API call failed with exception: " . $e->getMessage());
                $stats['api_calls']++;
                $stats['api_errors']++;
                $content = false;
            }

            $hasNewContent = false;
            $current++;

            if (!empty($content)) {
                _error_log("importCategory: SUCCESS - Got response from API (length: " . strlen($content) . ")");
                $json = _json_decode($content);
                if (!empty($json) && !empty($json->response) && !empty($json->response->totalRows) && !empty($json->response->rows)) {
                    _error_log("importCategory: JSON SUCCESS totalRows={$json->response->totalRows}, rows count: " . count($json->response->rows));
                    $hasNewContent = true;

                    foreach ($json->response->rows as $key => $value) {
                        _error_log("Processing category $key: {$value->clean_name}");
                        $stats['categories_processed']++;

                        $cat = Category::getCategoryByName($value->clean_name);

                        if (!empty($cat)) {
                            _error_log("importCategory: category exists [{$cat['id']}]{$cat['clean_name']} - skipping");
                            $stats['categories_skipped']++;
                            continue;
                        }

                        _error_log("importCategory: Creating new category '{$value->name}' with clean_name '{$value->clean_name}'");
                        $o = new Category(0);
                        $o->setName($value->name);
                        $o->setClean_name($value->clean_name);
                        $o->setDescription($value->description);
                        $o->setIconClass($value->iconClass);
                        $o->setPrivate($value->private);
                        $o->setAllow_download($value->allow_download);
                        $o->setOrder($value->order);
                        $o->setSuggested($value->suggested);
                        $o->setNextVideoOrder($value->nextVideoOrder);
                        $o->setUsers_id(1);

                        _error_log("importCategory: Saving category object...");
                        $id = $o->save(true);
                        if ($id) {
                            _error_log("importCategory: SUCCESS - Category saved with ID: {$id}");
                            $stats['categories_created']++;
                        } else {
                            _error_log("importCategory: ERROR - Failed to save category '{$value->name}'");
                        }

                        // Memory management
                        $processedCount++;
                        if ($processedCount % $memoryCleanupInterval == 0) {
                            _error_log("Categories - Running memory cleanup (processed: $processedCount)");
                            cleanupMemoryAndConnections();
                        }
                        unset($o); // Explicitly free the object
                        //exit;
                    }
                } else {
                    _error_log("importCategory: JSON ERROR - Invalid response structure or empty data");
                    _error_log("Response content: {$content}");
                    if (!empty($json)) {
                        _error_log("JSON decode error: " . json_last_error_msg());
                    }
                }
            } else {
                _error_log("importCategory: ERROR - Empty response from API: {$APIURL}");
                break; // Exit loop if we get empty content
            }
        }
        // Final cleanup for categories section
        _error_log("Categories import completed - Running final cleanup");
        _error_log("CATEGORIES STATS: Processed={$stats['categories_processed']}, Created={$stats['categories_created']}, Skipped={$stats['categories_skipped']}");
        cleanupMemoryAndConnections();
        $processedCount = 0; // Reset counter for next section
        _error_log("=== CATEGORIES IMPORT FINISHED ===");
    } else {
        _error_log("Categories import skipped - using provided category ID: $imported_categories_id");
    }
    if (empty($imported_users_id)) {
        _error_log("=== STARTING USERS IMPORT ===");
        $current = 1;
        $hasNewContent = true;
        // get users
        while ($hasNewContent) {
            _error_log("Users import - Processing page $current");
            $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=users_list&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}";
            _error_log("Users API call: $APIURL");

            try {
                $content = url_get_contents($APIURL, "", 30);
                $stats['api_calls']++;
            } catch (Exception $e) {
                _error_log("Users API call failed with exception: " . $e->getMessage());
                $stats['api_calls']++;
                $stats['api_errors']++;
                $content = false;
            }

            $hasNewContent = false;
            $current++;

            if (!empty($content)) {
                _error_log("importUsers: SUCCESS - Got response from API (length: " . strlen($content) . ")");
                $json = _json_decode($content);
                if (!empty($json) && !empty($json->response)) {
                    _error_log("importUsers: JSON SUCCESS - Users count: " . count($json->response));
                    $hasNewContent = true;

                    foreach ($json->response as $key => $value) {
                        _error_log("Processing user $key: {$value->user} ({$value->email})");
                        $stats['users_processed']++;

                        $user = User::getUserFromEmail($value->email);

                        if (empty($user)) {
                            $user = User::getUserFromChannelName($value->channelName);
                        }

                        if (!empty($user)) {
                            _error_log("importUsers: user exists [{$user['id']}]{$user['user']} - skipping");
                            $stats['users_skipped']++;
                            continue;
                        }

                        _error_log("importUsers: Creating new user '{$value->user}' with email '{$value->email}'");
                        $o = new User(0);
                        $o->setUser($value->user);
                        $o->setPassword($value->user);
                        $o->setName($value->name);
                        $o->setEmail($value->email);
                        $o->setIsAdmin(0);
                        $o->setStatus($value->a);
                        $o->setCanStream($value->canStream);
                        $o->setCanUpload($value->canUpload);
                        $o->setCanCreateMeet($value->canCreateMeet);
                        $o->setCanViewChart($value->canViewChart);
                        $o->setChannelName($value->channelName);
                        $o->setEmailVerified($value->emailVerified);
                        $o->setAbout($value->about);
                        $o->setAnalyticsCode($value->analyticsCode);
                        $o->setExternalOptions($value->externalOptions);
                        $o->setFirst_name($value->first_name);
                        $o->setLast_name($value->last_name);
                        $o->setAddress($value->address);
                        $o->setZip_code($value->zip_code);
                        $o->setCountry($value->country);
                        $o->setRegion($value->region);
                        $o->setCity($value->city);
                        $o->setDonationLink($value->donationLink);
                        $o->setExtra_info($value->extra_info);
                        $o->setPhone($value->phone);
                        $o->setIs_company($value->is_company);

                        _error_log("importUsers: Saving user object...");
                        $id = $o->save(false);
                        if ($id) {
                            _error_log("importUsers: SUCCESS - User saved with ID: {$id}");
                            $stats['users_created']++;

                            if (!empty($value->photo)) {
                                _error_log("Downloading user photo: {$value->photo}");
                                $photoResult = wget($value->photo, "{$global['systemRootPath']}videos/userPhoto/photo{$id}.png", true);
                                _error_log("Photo download result: " . ($photoResult ? 'SUCCESS' : 'FAILED'));
                            }
                            //wget($value->background, "{$global['systemRootPath']}videos/userPhoto/photo{$id}.png", true);
                        } else {
                            _error_log("importUsers: ERROR - Failed to save user '{$value->user}'");
                            $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                        }

                        // Memory management
                        $processedCount++;
                        if ($processedCount % $memoryCleanupInterval == 0) {
                            _error_log("Users - Running memory cleanup (processed: $processedCount)");
                            cleanupMemoryAndConnections();
                        }
                        unset($o); // Explicitly free the object
                        //exit;
                    }
                } else {
                    _error_log("importUsers: JSON ERROR - Invalid response structure");
                    if (!empty($json)) {
                        _error_log("JSON decode error: " . json_last_error_msg());
                    }
                    _error_log("Response content: {$content}");
                }
            } else {
                _error_log("importUsers: ERROR - Empty response from API: {$APIURL}");
                break; // Exit loop if we get empty content
            }
        }
        // Final cleanup for users section
        _error_log("Users import completed - Running final cleanup");
        _error_log("USERS STATS: Processed={$stats['users_processed']}, Created={$stats['users_created']}, Skipped={$stats['users_skipped']}");
        cleanupMemoryAndConnections();
        $processedCount = 0; // Reset counter for next section
        _error_log("=== USERS IMPORT FINISHED ===");
    } else {
        _error_log("Users import skipped - using provided user ID: $imported_users_id");
    }
}
_error_log("=== STARTING VIDEOS IMPORT ===");
$current = 1;
$hasNewContent = true;
$total_imported = 0;

// Add statistics tracking
$stats = [
    'videos_processed' => 0,
    'videos_created' => 0,
    'videos_updated' => 0,
    'videos_skipped' => 0,
    'videos_errors' => 0,
    'api_calls' => 0,
    'api_errors' => 0,
    'downloads_attempted' => 0,
    'downloads_successful' => 0,
    'downloads_failed' => 0,
    'downloads_skipped' => 0,
    'encoder_submissions' => 0,
    'categories_processed' => 0,
    'categories_created' => 0,
    'categories_skipped' => 0,
    'users_processed' => 0,
    'users_created' => 0,
    'users_skipped' => 0
];

_error_log("Statistics tracking initialized");

// import videos
while ($hasNewContent) {
    _error_log("Videos import - Processing page $current (total imported so far: $total_imported)");
    $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=video&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}&sort[created]=desc";
    _error_log("Videos API call: $APIURL");

    try {
        $content = url_get_contents($APIURL, "", 30);
        $stats['api_calls']++;
    } catch (Exception $e) {
        _error_log("Videos API call failed with exception: " . $e->getMessage());
        $stats['api_calls']++;
        $stats['api_errors']++;
        $content = false;
    }

    $hasNewContent = false;
    $current++;

    if (!empty($content)) {
        _error_log("importVideos: SUCCESS - Got response from API (length: " . strlen($content) . ")");
        $json = _json_decode($content);
        if (!empty($json) && !empty($json->response) && !empty($json->response->totalRows) && !empty($json->response->rows)) {
            _error_log("importVideo: JSON SUCCESS totalRows={$json->response->totalRows}, rows count: " . count($json->response->rows));
            $hasNewContent = true;
            foreach ($json->response->rows as $key => $value) {
                _error_log("=== Processing video $key: '{$value->title}' (filename: {$value->filename}) ===");
                $stats['videos_processed']++;

                if ($type == 'm3u8') {
                    if (empty($value->videos->m3u8)) {
                        _error_log("Skipping video - no m3u8 found and type is m3u8");
                        $stats['videos_skipped']++;
                        continue;
                    }
                }

                $videos_id = 0;
                $is_new_video = true;

                $row = Video::getVideoFromFileNameLight($value->filename);
                if (!empty($row)) {
                    _error_log("importVideo: Video found in database with ID: " . $row['id']);
                    $videos_id = $row['id'];
                    $is_new_video = false;
                } else {
                    _error_log("importVideo: Video NOT found in database - will create new");
                }

                // Determine user ID
                if (empty($imported_users_id)) {
                    $users_id = 1;
                    $user = User::getUserFromEmail($value->email);
                    if (empty($user)) {
                        $user = User::getUserFromChannelName($value->channelName);
                    }
                    if (!empty($user)) {
                        $users_id = $user['id'];
                        _error_log("Found user ID $users_id for email/channel: {$value->email}/{$value->channelName}");
                    } else {
                        _error_log("No user found for email/channel: {$value->email}/{$value->channelName}, using default ID 1");
                    }
                } else {
                    $users_id = $imported_users_id;
                    _error_log("Using provided user ID: $users_id");
                }

                // Determine category ID
                if (empty($imported_categories_id) || $imported_categories_id < 0) {
                    $cat = Category::getCategoryByName($value->clean_category);
                    $categories_id = $cat['id'];
                    _error_log("Found category ID $categories_id for: {$value->clean_category}");
                } else {
                    $categories_id = $imported_categories_id;
                    _error_log("Using provided category ID: $categories_id");
                }

                _error_log("Creating video object with users_id=$users_id, categories_id=$categories_id");
                $video = new Video($value->title, $value->filename, $videos_id);

                $video->setCreated("'$value->created'");
                $video->setDuration($value->duration);
                $video->setType($value->type);
                $video->setVideoDownloadedLink($value->videoDownloadedLink);
                $video->setDuration_in_seconds($value->duration_in_seconds);
                $video->setDescription($value->description);
                $video->setUsers_id($users_id);
                $video->setCategories_id($categories_id);

                $path = getVideosDir() . $value->filename . DIRECTORY_SEPARATOR;
                $size = getDirSize($path);
                _error_log("Video path: $path, current size: " . humanFileSize($size) . " ($size bytes)");

                if ($size < 10000000) {
                    if(empty($videos_id)){
                        $video->setStatus(Video::STATUS_TRANFERING);
                        _error_log("importVideo status: transfering ($size) " . humanFileSize($size));
                    }else{
                        if ($size > 1000000) {
                            $video->setStatus(Video::STATUS_ACTIVE);
                        }
                        _error_log("importVideo status: else ($size) " . humanFileSize($size));
                    }
                }
                if(empty($videos_id)){
                    $video->setStatus(Video::STATUS_TRANFERING);
                    _error_log("Setting new video status to TRANSFERRING");
                } else {
                    _error_log("Updating existing video ID: $videos_id");
                }

                _error_log("importVideo: Saving video object...");
                $id = $video->save(false, true);
                if ($id) {
                    _error_log("importVideo: SUCCESS - Video saved with ID: {$id} categories_id=$categories_id ($value->clean_category) created=$value->created");

                    if ($is_new_video) {
                        $stats['videos_created']++;
                    } else {
                        $stats['videos_updated']++;
                    }

                    make_path($path);

                    // download images
                    if (!empty($value->images->poster)) {
                        _error_log("Downloading poster image: {$value->images->poster}");
                        download($value->images->poster, $value->filename, $path);
                    }

                    if (!empty($value->images->thumbsGif)) {
                        _error_log("Downloading thumbs GIF: {$value->images->thumbsGif}");
                        download($value->images->thumbsGif, $value->filename, $path);
                    }

                    // Download MP4 files
                    if (!empty($value->videos->mp4)) {
                        foreach ($value->videos->mp4 as $key2 => $value2) {
                            _error_log("importVideo MP4: key = {$key} key2 = {$key2} URL = $value2");
                            download($value2, $value->filename, $path);
                        }
                    }

                    // Download MP3 file
                    if (!empty($value->videos->mp3)) {
                        _error_log("importVideo MP3: {$value->videos->mp3}");
                        download($value->videos->mp3, $value->filename, $path);
                    }

                    $video->setStatus(Video::STATUS_ACTIVE);

                    // Handle M3U8
                    if (!empty($value->videos->m3u8)) {
                        if ($size < 10000000) {
                            if(empty($videos_id)){
                                _error_log("importVideo m3u8: Sending to encoder - {$value->videos->m3u8->url} (size: " . humanFileSize($size) . ")");
                                $encoderResult = sendToEncoder($id, $value->videos->m3u8->url);
                                $stats['encoder_submissions']++;
                                _error_log("Encoder result: " . ($encoderResult ? 'SUCCESS' : 'FAILED'));
                            }

                            if(empty($videos_id)){
                                $video->setStatus(Video::STATUS_ENCODING);
                                _error_log("Setting video status to ENCODING");
                            }
                        } else {
                            _error_log("importVideo m3u8 NOT SENT to encoder - size too large: " . humanFileSize($size));
                        }
                    }

                    if(empty($videos_id)){
                        $total_imported++;
                        _error_log("Incremented total_imported to: $total_imported");
                    }

                    if (!empty($total_to_import) && $total_to_import > 0 && $total_imported >= $total_to_import) {
                        _error_log("importVideo completed: total_imported=$total_imported >= total_to_import=$total_to_import - STOPPING");
                        $hasNewContent = false;
                        break;
                    }else{
                        _error_log("importVideo continue: total_imported=$total_imported < total_to_import=$total_to_import");
                    }
                } else {
                    _error_log("importVideo: ERROR - Failed to save video '{$value->title}'");
                    $stats['videos_errors']++;
                    $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                }

                _error_log("Final save of video object...");
                $finalSaveResult = $video->save(false, true);
                _error_log("Final save result: " . ($finalSaveResult ? 'SUCCESS' : 'FAILED'));

                // Memory management
                $processedCount++;
                if ($processedCount % $memoryCleanupInterval == 0) {
                    _error_log("Videos - Running memory cleanup (processed: $processedCount)");
                    _error_log("CURRENT VIDEOS STATS: Processed={$stats['videos_processed']}, Created={$stats['videos_created']}, Updated={$stats['videos_updated']}, Skipped={$stats['videos_skipped']}, Errors={$stats['videos_errors']}");
                    cleanupMemoryAndConnections();
                }
                unset($video); // Explicitly free the object
                _error_log("=== Finished processing video $key ===");
                //exit;
            }
        } else {
            _error_log("importVideo: JSON ERROR - Invalid response structure");
            if (!empty($json)) {
                _error_log("JSON decode error: " . json_last_error_msg());
            }
            _error_log("Response content: {$content}");
        }
    } else {
        _error_log("importVideo: ERROR - Empty response from API: {$APIURL}");
        break; // Exit loop if we get empty content
    }
}

// Final cleanup
cleanupMemoryAndConnections();
_error_log("=== IMPORT PROCESS COMPLETED ===");

// Comprehensive statistics report
_error_log("=== FINAL STATISTICS REPORT ===");
_error_log("VIDEOS:");
_error_log("  - Total Processed: {$stats['videos_processed']}");
_error_log("  - Created New: {$stats['videos_created']}");
_error_log("  - Updated Existing: {$stats['videos_updated']}");
_error_log("  - Skipped: {$stats['videos_skipped']}");
_error_log("  - Errors: {$stats['videos_errors']}");
_error_log("  - Success Rate: " . ($stats['videos_processed'] > 0 ? round((($stats['videos_created'] + $stats['videos_updated']) / $stats['videos_processed']) * 100, 2) : 0) . "%");

_error_log("CATEGORIES:");
_error_log("  - Total Processed: {$stats['categories_processed']}");
_error_log("  - Created New: {$stats['categories_created']}");
_error_log("  - Skipped (existing): {$stats['categories_skipped']}");

_error_log("USERS:");
_error_log("  - Total Processed: {$stats['users_processed']}");
_error_log("  - Created New: {$stats['users_created']}");
_error_log("  - Skipped (existing): {$stats['users_skipped']}");

_error_log("API CALLS:");
_error_log("  - Total API Calls: {$stats['api_calls']}");
_error_log("  - API Errors: {$stats['api_errors']}");
_error_log("  - API Success Rate: " . ($stats['api_calls'] > 0 ? round((($stats['api_calls'] - $stats['api_errors']) / $stats['api_calls']) * 100, 2) : 0) . "%");

_error_log("DOWNLOADS:");
_error_log("  - Total Attempted: {$stats['downloads_attempted']}");
_error_log("  - Successful: {$stats['downloads_successful']}");
_error_log("  - Failed: {$stats['downloads_failed']}");
_error_log("  - Skipped (existing): {$stats['downloads_skipped']}");
_error_log("  - Download Success Rate: " . ($stats['downloads_attempted'] > 0 ? round(($stats['downloads_successful'] / $stats['downloads_attempted']) * 100, 2) : 0) . "%");

_error_log("ENCODER:");
_error_log("  - Encoder Submissions: {$stats['encoder_submissions']}");

_error_log("PERFORMANCE:");
_error_log("  - Total videos imported: $total_imported");
_error_log("  - Final memory usage: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . "MB");
_error_log("  - Peak memory usage: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB");

// Calculate total execution time
static $script_start_time = null;
if ($script_start_time === null) {
    $script_start_time = $_SERVER['REQUEST_TIME'] ?? time();
}
$total_execution_time = time() - $script_start_time;
_error_log("  - Total execution time: " . gmdate("H:i:s", $total_execution_time) . " (" . $total_execution_time . " seconds)");

_error_log("=== END STATISTICS REPORT ===");
_error_log("Script execution completed successfully");

die();
