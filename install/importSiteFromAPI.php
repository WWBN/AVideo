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

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

function download($url, $filename, $path, $forceDownload = false) {
    $parts = explode("/{$filename}/", $url);

    if (empty($parts[1])) {
        if (preg_match("/\.mp3$/", $url)) {
            $parts[1] = "{$filename}.mp3";
        }
    }

    if (empty($parts[1])) {
        _error_log("importVideo::download ERROR on download {$url}");
        return false;
    }

    $parts2 = explode('?', $parts[1]);
    $file = $parts2[0];
    $destination = $path . $file;
    if ($forceDownload || !file_exists($destination)) {
        _error_log("importVideo::download [$destination]");
        return wget($url, $destination, true);
    } else {
        _error_log("importVideo::download skipped [$destination]");
    }
    return false;
}

set_time_limit(360000);
ini_set('max_execution_time', 360000);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;

while (empty($siteURL) || !isValidURL($siteURL)) {
    $siteURL = readline('Enter a valid URL: ');
}

while (empty($APISecret)) {
    $APISecret = readline('Enter a valid APISecret: ');
}

if (empty($imported_users_id)) {
    $imported_users_id = (int) readline('Enter a users id to be the video owner or 0 to also import the users: ');
}
if (empty($imported_categories_id)) {
    $imported_categories_id = (int) readline('Enter a category id to be linked to the video or 0 to also import the categories: ');
}
if (empty($total_to_import)) {
    $total_to_import = (int) readline('How many videos do you want to import? type 0 to import all: ');
}

$rowCount = 50;
$current = 1;
$hasNewContent = true;

_error_log("importSite: start {$siteURL} imported_users_id=$imported_users_id imported_categories_id=$imported_categories_id total_to_import=$total_to_import");
//exit;
if ($type !== 'm3u8') {
    if (empty($imported_categories_id) || $imported_categories_id < 0) {
        // get categories
        while ($hasNewContent) {
            $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=category&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}";

            $content = url_get_contents($APIURL, "", 30);

            $hasNewContent = false;
            $current++;

            if (!empty($content)) {
                _error_log("importCategory: SUCCESS {$APIURL}");
                $json = _json_decode($content);
                if (!empty($json) && !empty($json->response) && !empty($json->response->totalRows) && !empty($json->response->rows)) {
                    _error_log("importCategory: JSON SUCCESS totalRows={$json->response->totalRows}");
                    $hasNewContent = true;

                    foreach ($json->response->rows as $key => $value) {


                        $cat = Category::getCategoryByName($value->clean_name);

                        if (!empty($cat)) {
                            _error_log("importCategory: category exists [{$cat['id']}]{$cat['clean_name']}");
                            continue;
                        }

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

                        _error_log("importCategory: Saving ...");
                        $id = $o->save(true);
                        if ($id) {
                            _error_log("importCategory: saved {$id}");
                        } else {
                            _error_log("importCategory: ERROR NOT saved");
                        }
                        //exit;
                    }
                } else {
                    _error_log("importCategory: JSON ERROR {$content} ");
                }
            } else {
                _error_log("importCategory: ERROR {$APIURL} content is empty");
            }
        }
    }
    if (empty($imported_users_id)) {
        $current = 1;
        $hasNewContent = true;
        // get users
        while ($hasNewContent) {
            $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=users_list&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}";

            $content = url_get_contents($APIURL, "", 30);

            $hasNewContent = false;
            $current++;

            if (!empty($content)) {
                _error_log("importUsers: SUCCESS {$APIURL}");
                $json = _json_decode($content);
                if (!empty($json) && !empty($json->response)) {
                    _error_log("importUsers: JSON SUCCESS");
                    $hasNewContent = true;

                    foreach ($json->response as $key => $value) {


                        $user = User::getUserFromEmail($value->email);

                        if (empty($user)) {
                            $user = User::getUserFromChannelName($value->channelName);
                        }

                        if (!empty($user)) {
                            _error_log("importUsers: exists [{$user['id']}]{$user['clean_name']}");
                            continue;
                        }

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

                        _error_log("importUsers: Saving ...");
                        $id = $o->save(false);
                        if ($id) {
                            _error_log("importUsers: saved {$id}");

                            wget($value->photo, "{$global['systemRootPath']}videos/userPhoto/photo{$id}.png", true);
                            //wget($value->background, "{$global['systemRootPath']}videos/userPhoto/photo{$id}.png", true);
                        } else {
                            _error_log("importUsers: ERROR NOT saved");
                            $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                        }
                        //exit;
                    }
                } else {
                    _error_log("importUsers: JSON ERROR " . json_last_error_msg());
                    //exit;
                    _error_log("importUsers: JSON ERROR {$content} ");
                }
            } else {
                _error_log("importUsers: ERROR {$APIURL} content is empty");
            }
        }
    }
}
$current = 1;
$hasNewContent = true;
$total_imported = 0;

// import videos
while ($hasNewContent) {

    $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=video&rowCount={$rowCount}&current={$current}&APISecret={$APISecret}&sort[created]=desc";

    $content = url_get_contents($APIURL, "", 30);

    $hasNewContent = false;
    $current++;

    if (!empty($content)) {
        _error_log("importVideos: SUCCESS {$APIURL}");
        $json = _json_decode($content);
        if (!empty($json) && !empty($json->response) && !empty($json->response->totalRows) && !empty($json->response->rows)) {
            _error_log("importVideo: JSON SUCCESS totalRows={$json->response->totalRows}");
            $hasNewContent = true;
            foreach ($json->response->rows as $key => $value) {

                if ($type == 'm3u8') {
                    if (empty($value->videos->m3u8)) {
                        continue;
                    }
                }

                $videos_id = 0;

                $row = Video::getVideoFromFileNameLight($value->filename);
                if (!empty($row)) {
                    _error_log("importVideo: Video found");
                    $videos_id = $row['id'];
                } else {
                    _error_log("importVideo: Video NOT found");
                }
                _error_log("importVideo: Video {$videos_id} {$value->title} {$value->fileName}");

                if (empty($imported_users_id)) {
                    $users_id = 1;
                    $user = User::getUserFromEmail($value->email);
                    if (empty($user)) {
                        $user = User::getUserFromChannelName($value->channelName);
                    }
                    if (!empty($user)) {
                        $users_id = $user['id'];
                    }
                } else {
                    $users_id = $imported_users_id;
                }

                if (empty($imported_categories_id) || $imported_categories_id < 0) {
                    $cat = Category::getCategoryByName($value->clean_category);
                    $categories_id = $cat['id'];
                } else {
                    $categories_id = $imported_categories_id;
                }

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
                }

                _error_log("importVideo: Saving video");
                $id = $video->save(false, true);
                if ($id) {
                    _error_log("importVideo: Video saved {$id} categories_id=$categories_id ($value->clean_category) created=$value->created");
                    make_path($path);

                    // download images
                    download($value->images->poster, $value->filename, $path);
                    download($value->images->thumbsGif, $value->filename, $path);

                    foreach ($value->videos->mp4 as $key2 => $value2) {
                        _error_log("importVideo MP4: key = {$key} key2 = {$key2} APIURL = $APIURL");
                        download($value2, $value->filename, $path);
                    }

                    if (!empty($value->videos->mp3)) {
                        _error_log("importVideo MP3: {$value->videos->mp3} APIURL = $APIURL");
                        download($value->videos->mp3, $value->filename, $path);
                    }

                    $video->setStatus(Video::STATUS_ACTIVE);
                    if (!empty($value->videos->m3u8)) {
                        if ($size < 10000000) {
                            if(empty($videos_id)){
                                _error_log("importVideo m3u8: {$value->videos->m3u8->url} APIURL = $APIURL ($size) " . humanFileSize($size));
                                sendToEncoder($id, $value->videos->m3u8->url);
                            }

                            if(empty($videos_id)){
                                $video->setStatus(Video::STATUS_ENCODING);
                            }
                        } else {
                            _error_log("importVideo m3u8 NOT SEND: ($size) " . humanFileSize($size));
                        }
                    }
                    if(empty($videos_id)){
                        $total_imported++;
                    }
                    if (!empty($total_to_import) && $total_to_import > 0 && $total_imported >= $total_to_import) {
                        _error_log("importVideo completed: total_imported=$total_imported >= total_to_import=$total_to_import ");
                        $hasNewContent = false;
                        break;
                    }else{
                        _error_log("importVideo continue: total_imported=$total_imported < total_to_import=$total_to_import ");
                    }
                } else {
                    _error_log("importVideo: ERROR Video NOT saved");
                    $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                }
                $video->save(false, true);
                //exit;
            }
        } else {
            _error_log("importVideo: JSON ERROR {$content} ");
        }
    } else {
        _error_log("importVideo: ERROR {$APIURL} content is empty");
    }
}

die();
