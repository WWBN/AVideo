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


//streamer config
require_once '../videos/configuration.php';

// make sure display all errors
error_reporting(E_ALL);


// Log script start with parameters
_error_log("=== SCRIPT START ===");
_error_log("Parameters: siteURL='$siteURL', APISecret='" . substr($APISecret, 0, 5) . "...', imported_users_id=$imported_users_id, imported_categories_id=$imported_categories_id, total_to_import=$total_to_import, type='$type'");
_error_log("PHP Version: " . phpversion() . ", Memory Limit: " . ini_get('memory_limit'));

_error_log("Configuration loaded successfully");

# Initialize statistics tracking
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

// Configuration: Auto-create missing users
$auto_create_missing_users = true; // Set to false if you don't want to auto-create users
_error_log("Statistics tracking initialized");
_error_log("Auto-create missing users: " . ($auto_create_missing_users ? 'ENABLED' : 'DISABLED'));

if (!isCommandLineInterface()) {
    _error_log("ERROR: Not running in command line interface");
    return die('Command Line only');
}

ob_end_flush();
_error_log("Output buffering flushed");

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

// Safe wrapper for video operations that might trigger plugin events
function safeVideoOperation($operation, $video, ...$args) {
    try {
        $result = call_user_func_array(array($video, $operation), $args);
        _error_log("Safe video operation '$operation' completed successfully");
        return $result;
    } catch (TypeError $e) {
        _error_log("TypeError in video operation '$operation': " . $e->getMessage());
        _error_log("This is likely due to plugin issues with user external options");
        _error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    } catch (Exception $e) {
        _error_log("Exception in video operation '$operation': " . $e->getMessage());
        _error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

// Function to create a basic user when video owner is missing
function createBasicUserFromVideoData($email, $channelName) {
    global $stats;

    if (empty($email) || empty($channelName)) {
        _error_log("Cannot create user - missing email or channel name");
        return false;
    }

    _error_log("Creating basic user from video data: email=$email, channel=$channelName");

    try {
        $o = new User(0);
        $o->setUser($channelName); // Use channel name as username
        $o->setPassword($channelName); // Temporary password (should be changed)
        $o->setName($channelName); // Use channel name as display name
        $o->setEmail($email);
        $o->setIsAdmin(0);
        $o->setStatus('a'); // Active status
        $o->setCanStream(1);
        $o->setCanUpload(1);
        $o->setCanCreateMeet(0);
        $o->setCanViewChart(0);
        $o->setChannelName($channelName);
        $o->setEmailVerified(1); // Assume verified

        $id = $o->save(false);
        if ($id) {
            _error_log("Successfully created basic user with ID: $id");
            $stats['users_created']++;
            return $id;
        } else {
            _error_log("Failed to create basic user");
            return false;
        }
    } catch (Exception $e) {
        _error_log("Exception creating basic user: " . $e->getMessage());
        return false;
    }
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

// Log the final values that will be used
_error_log("=== FINAL CONFIGURATION ===");
_error_log("Site URL: $siteURL");
_error_log("API Secret length: " . strlen($APISecret));
_error_log("Import users ID: $imported_users_id (0 means import users)");
_error_log("Import categories ID: $imported_categories_id (0 means import categories)");
_error_log("Total to import: $total_to_import (0 means import all)");
_error_log("Type filter: '$type' (empty means all types)");

$rowCount = 50;
$current = 1;
$hasNewContent = true;

// Add memory management
$processedCount = 0;
$memoryCleanupInterval = 10; // Clean up every 10 items
$batchSize = 5; // Process in smaller batches for better memory management

_error_log("importSite: start {$siteURL} imported_users_id=$imported_users_id imported_categories_id=$imported_categories_id total_to_import=$total_to_import");

//exit;
_error_log("Checking type parameter: type='$type'");
if ($type !== 'm3u8') {
    _error_log("Type is not m3u8, proceeding with normal import");
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
                _error_log("Breaking out of categories loop due to empty response");
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
                            // Note: No video status to set here, this is user import
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
                _error_log("Breaking out of users loop due to empty response");
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
} else {
    _error_log("Type is m3u8, skipping categories and users import");
}

_error_log("Proceeding to videos import section");
_error_log("=== STARTING VIDEOS IMPORT ===");
$current = 1;
$hasNewContent = true;
$total_imported = 0;

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

                // Debug: Log available user fields
                if (isset($value->email)) {
                    _error_log("Video owner email: {$value->email}");
                } else {
                    _error_log("WARNING: No email field in video data");
                }

                if (isset($value->channelName)) {
                    _error_log("Video owner channel: {$value->channelName}");
                } else {
                    _error_log("WARNING: No channelName field in video data");
                }

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

                    // Check current owner of existing video
                    if (isset($row['users_id'])) {
                        _error_log("Current video owner: users_id={$row['users_id']}");
                    }
                } else {
                    _error_log("importVideo: Video NOT found in database - will create new");
                }

                // Determine user ID
                if (empty($imported_users_id)) {
                    // We want to preserve the original owner from the source site
                    $users_id = 1; // Default fallback

                    // First, try to find the user by email
                    $user = User::getUserFromEmail($value->email);
                    if (empty($user) && !empty($value->channelName)) {
                        // If not found by email, try by channel name
                        $user = User::getUserFromChannelName($value->channelName);
                    }

                    if (!empty($user)) {
                        $users_id = $user['id'];
                        _error_log("Found existing user ID $users_id for email/channel: {$value->email}/{$value->channelName}");
                    } else {
                        // User doesn't exist locally
                        _error_log("Original video owner not found locally!");
                        _error_log("  - Original email: {$value->email}");
                        _error_log("  - Original channel: {$value->channelName}");

                        // Option 1: Try to create the user if we have enough info and auto-creation is enabled
                        if ($auto_create_missing_users && !empty($value->email) && !empty($value->channelName)) {
                            _error_log("Auto-creation enabled - Attempting to create missing user...");
                            $created_user_id = createBasicUserFromVideoData($value->email, $value->channelName);
                            if ($created_user_id) {
                                $users_id = $created_user_id;
                                _error_log("SUCCESS: Created user with ID $users_id for video owner");
                            } else {
                                _error_log("FAILED to create user, falling back to admin (ID: 1)");
                                $users_id = 1;
                            }
                        } else {
                            if (!$auto_create_missing_users) {
                                _error_log("Auto-creation disabled, using admin (ID: 1)");
                            } else {
                                _error_log("Insufficient user data to create user, using admin (ID: 1)");
                            }
                            $users_id = 1;
                        }
                    }
                } else {
                    $users_id = $imported_users_id;
                    _error_log("Using provided user ID: $users_id (overriding original owner)");
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

                _error_log("Creating/updating video object with users_id=$users_id, categories_id=$categories_id");
                $video = new Video($value->title, $value->filename, $videos_id);

                $video->setCreated("'$value->created'");
                $video->setDuration($value->duration);
                $video->setType($value->type);
                $video->setVideoDownloadedLink($value->videoDownloadedLink);
                $video->setDuration_in_seconds($value->duration_in_seconds);
                $video->setDescription($value->description);

                // Set the owner - this will update existing videos too
                $video->setUsers_id($users_id);
                if ($is_new_video) {
                    _error_log("Setting owner for NEW video to users_id=$users_id");
                } else {
                    _error_log("UPDATING owner for EXISTING video (ID: $videos_id) to users_id=$users_id");
                }

                $video->setCategories_id($categories_id);

                // Debug: Show what values are set on the video object before saving
                _error_log("DEBUG: Video object values before save:");
                _error_log("  - Title: {$value->title}");
                _error_log("  - Filename: {$value->filename}");
                _error_log("  - Video ID (for existing): $videos_id");
                _error_log("  - Users ID to set: $users_id");
                _error_log("  - Categories ID: $categories_id");
                _error_log("  - Is new video: " . ($is_new_video ? 'YES' : 'NO'));

                $path = getVideosDir() . $value->filename . DIRECTORY_SEPARATOR;
                $size = getDirSize($path);
                _error_log("Video path: $path, current size: " . humanFileSize($size) . " ($size bytes)");

                if ($size < 10000000) {
                    if(empty($videos_id)){
                        try {
                            $video->setStatus(Video::STATUS_TRANFERING);
                            _error_log("importVideo status: transfering ($size) " . humanFileSize($size));
                        } catch (Exception $e) {
                            _error_log("ERROR setting video status to TRANSFERING: " . $e->getMessage());
                        } catch (TypeError $e) {
                            _error_log("TypeError setting video status to TRANSFERING: " . $e->getMessage());
                        }
                    }else{
                        if ($size > 1000000) {
                            try {
                                $video->setStatus(Video::STATUS_ACTIVE);
                                _error_log("importVideo status: set to ACTIVE for existing video");
                            } catch (Exception $e) {
                                _error_log("ERROR setting existing video status to ACTIVE: " . $e->getMessage());
                            } catch (TypeError $e) {
                                _error_log("TypeError setting existing video status to ACTIVE: " . $e->getMessage());
                            }
                        }
                        _error_log("importVideo status: else ($size) " . humanFileSize($size));
                    }
                }
                if(empty($videos_id)){
                    try {
                        $video->setStatus(Video::STATUS_TRANFERING);
                        _error_log("Setting new video status to TRANSFERRING");
                    } catch (Exception $e) {
                        _error_log("ERROR setting new video status to TRANSFERRING: " . $e->getMessage());
                    } catch (TypeError $e) {
                        _error_log("TypeError setting new video status to TRANSFERRING: " . $e->getMessage());
                    }
                } else {
                    _error_log("Updating existing video ID: $videos_id");
                }

                _error_log("importVideo: Saving video object...");

                // For existing videos, let's try a more direct approach first
                if (!$is_new_video && $videos_id > 0) {
                    _error_log("Using direct database update for existing video ID: $videos_id");

                    // Try direct update first for existing videos
                    $sql = "UPDATE videos SET
                            title = ?,
                            description = ?,
                            users_id = ?,
                            categories_id = ?,
                            duration = ?,
                            type = ?,
                            videoDownloadedLink = ?,
                            duration_in_seconds = ?,
                            created = ?
                            WHERE id = ?";

                    $result = sqlDAL::writeSql($sql, "ssiiississ", [
                        $value->title,
                        $value->description,
                        $users_id,
                        $categories_id,
                        $value->duration,
                        $value->type,
                        $value->videoDownloadedLink,
                        $value->duration_in_seconds,
                        $value->created,
                        $videos_id
                    ]);

                    if ($result) {
                        _error_log("Direct database update SUCCESS for existing video ID: $videos_id");
                        $id = $videos_id;
                    } else {
                        _error_log("Direct database update FAILED, trying object save method...");
                        $id = safeVideoOperation('save', $video, false, true);
                    }
                } else {
                    // For new videos, use the regular save method
                    $id = safeVideoOperation('save', $video, false, true);
                }

                if ($id) {
                    _error_log("importVideo: SUCCESS - Video saved with ID: {$id} categories_id=$categories_id ($value->clean_category) created=$value->created");

                    // Verify that the users_id was actually updated
                    $savedVideo = Video::getVideoLight($id);
                    if ($savedVideo && isset($savedVideo['users_id'])) {
                        if ($savedVideo['users_id'] == $users_id) {
                            _error_log("VERIFICATION SUCCESS: Video owner correctly set to users_id={$users_id}");
                        } else {
                            _error_log("VERIFICATION FAILED: Expected users_id={$users_id}, but database shows users_id={$savedVideo['users_id']}");
                            _error_log("Attempting direct database update...");

                            // Try direct database update as fallback
                            $sql = "UPDATE videos SET users_id = ? WHERE id = ?";
                            $result = sqlDAL::writeSql($sql, "ii", [$users_id, $id]);
                            if ($result) {
                                _error_log("Direct database update SUCCESS: Set users_id={$users_id} for video ID {$id}");
                            } else {
                                _error_log("Direct database update FAILED for video ID {$id}");
                            }
                        }
                    } else {
                        _error_log("WARNING: Could not verify video save - unable to retrieve saved video data");
                    }

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

                    // Set video status with error handling
                    try {
                        $video->setStatus(Video::STATUS_ACTIVE);
                        _error_log("Video status set to ACTIVE successfully");
                    } catch (Exception $e) {
                        _error_log("ERROR setting video status to ACTIVE: " . $e->getMessage());
                        _error_log("Stack trace: " . $e->getTraceAsString());
                    } catch (TypeError $e) {
                        _error_log("TypeError setting video status to ACTIVE: " . $e->getMessage());
                        _error_log("Stack trace: " . $e->getTraceAsString());
                    }

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
                                try {
                                    $video->setStatus(Video::STATUS_ENCODING);
                                    _error_log("Setting video status to ENCODING - SUCCESS");
                                } catch (Exception $e) {
                                    _error_log("ERROR setting video status to ENCODING: " . $e->getMessage());
                                    _error_log("Stack trace: " . $e->getTraceAsString());
                                } catch (TypeError $e) {
                                    _error_log("TypeError setting video status to ENCODING: " . $e->getMessage());
                                    _error_log("Stack trace: " . $e->getTraceAsString());
                                }
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
                    try {
                        $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                        _error_log("Set video status to BROKEN_MISSING_FILES");
                    } catch (Exception $e) {
                        _error_log("ERROR setting video status to BROKEN_MISSING_FILES: " . $e->getMessage());
                    } catch (TypeError $e) {
                        _error_log("TypeError setting video status to BROKEN_MISSING_FILES: " . $e->getMessage());
                    }
                }

                _error_log("Final save of video object...");
                try {
                    $finalSaveResult = $video->save(false, true);
                    _error_log("Final save result: " . ($finalSaveResult ? 'SUCCESS' : 'FAILED'));
                } catch (Exception $e) {
                    _error_log("ERROR during final video save: " . $e->getMessage());
                    _error_log("Stack trace: " . $e->getTraceAsString());
                    $finalSaveResult = false;
                } catch (TypeError $e) {
                    _error_log("TypeError during final video save: " . $e->getMessage());
                    _error_log("Stack trace: " . $e->getTraceAsString());
                    $finalSaveResult = false;
                }

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
        _error_log("Breaking out of videos loop due to empty response");
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
_error_log("  - Created New: {$stats['users_created']} (includes auto-created from video data)");
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
