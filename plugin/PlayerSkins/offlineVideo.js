function replaceVideoSourcesPerOfflineVersion() {
    if (!empty(mediaId)) {
        replaceVideoSourcesPerOfflineVersionFromVideosId(mediaId);
    } else {
        console.error("replaceVideoSourcesPerOfflineVersion: empty mediaId");
    }
}

async function replaceVideoSourcesPerOfflineVersionFromVideosId(videos_id) {
    videos_id = parseInt(videos_id);
    if (empty(videos_id)) {
        //console.error("replaceVideoSourcesPerOfflineVersionFromVideosId: empty videos id");
        return false;
    }
    if (!$("#mainVideo").length) {
        //console.error("replaceVideoSourcesPerOfflineVersionFromVideosId: mainVideo not present");
        return false;
    }
    //videoJSRecreateSources(false);
    $('source.offline-video').remove();
    getAllOfflineVideoPouch(videos_id).then(async function (collection) {
        //console.log("replaceVideoSourcesPerOfflineVersionFromVideosId: ",videos_id, collection);
        var sources = [];
        for (var i in collection.rows) {
            var video = collection.rows[i].doc;
            if (typeof video !== 'object') {
                continue;
            }

            for (var i in video._attachments) {
                if (i == 'poster') {
                    continue;
                }
                //console.log("replaceVideoSourcesPerOfflineVersionFromVideosId video: ",video);
                var fileBlob = await offlineVideoDBPouch.getAttachment(video._id, i);
                var source = createSourceFromBlob(fileBlob, video.video_type, i);
                console.log("replaceVideoSourcesPerOfflineVersionFromVideosId video: ", video, source);
                createSourceElement(source);
                sources.push(source);
            }

        }

        console.log("replaceVideoSourcesPerOfflineVersionFromVideosId sources: ", sources);
        player.src(sources);
        videoJSRecreateSources(sources[0]);
        offlineVideoButtonCheck();
    }).catch(function (e) {
        console.log("replaceVideoSourcesPerOfflineVersionIfExists: Error: " + (e.stack || e));
    });
}

async function updateAllOfflineVideoMetaData() {
    var result = await getAllOfflineVideoPouch(0).then(async function (videoRow) {
        console.log('updateAllOfflineVideoMetaData videoRow', videoRow);
        var videosUpdated = [];
        for (var i in videoRow.rows) {
            var video = videoRow.rows[i].doc;
            if (typeof video !== 'object') {
                continue;
            }
            videosUpdated.push(await updateOfflineVideoMetaData(video.videos_id));
        }

        return videosUpdated;

    }).catch(function (err) {
        console.log('updateAllOfflineVideoMetaData videoRow error', err);
        return err;
    });
    return result;
}

async function updateOfflineVideoMetaData(videos_id) {
    videos_id = parseInt(videos_id);
    if (empty(videos_id)) {
        //console.error("replaceVideoSourcesPerOfflineVersionFromVideosId: empty videos id");
        return false;
    }

    var apiURL = webSiteRootURL + 'plugin/API/get.json.php?APIName=video&videos_id=' + videos_id;

    $.ajax({
        url: apiURL,
        success: async function (response) {
            if (response.error) {
                avideoAlertError(response.message);
                return false;
            }
            if (empty(response.response.rows)) {
                avideoToastError('Empty respose');
                return false;
            }
            var row = response.response.rows[0];
            var result = await getAllOfflineVideoPouch(videos_id).then(async function (videoRow) {
                //console.log('updateOfflineVideoMetaData videoRow', videoRow);
                var videosUpdated = [];
                for (var i in videoRow.rows) {
                    var video = videoRow.rows[i].doc;
                    if (typeof video !== 'object') {
                        continue;
                    }
                    //video = video.doc;
                    video.title = row.title;
                    video.duration = row.duration;
                    video.link = row.link;
                    video.duration_in_seconds = row.duration_in_seconds;
                    video.modified = new Date().getTime();
                    video.meta_data_modified = video.modified;
                    console.log('updateOfflineVideoMetaData video', video);
                    videosUpdated.push(await offlineVideoDBPouch.put(video).then(async function (result) {
                        fetch(row.images.poster).then(res => res.blob()).then(async function (fileBlob) {
                            //console.log('updateOfflineVideoMetaData fetched put', result, fileBlob);
                            var result2 = await offlineVideoDBPouch.putAttachment(result.id, 'poster', result.rev, fileBlob, 'image/jpeg').then(function (result2) {
                                // handle result
                                //console.log('updateOfflineVideoMetaData fetched ', result2);
                                //avideoToastSuccess('Meta data updated');
                                return result2;
                            }).catch(function (err) {
                                //console.log('updateOfflineVideoMetaData fetched ', err);
                                avideoToastError('Meta data Attachment: ' + err.message);
                                return err;
                            });
                            return result2;
                        });
                    }).catch(function (err) {
                        //console.log('storeOfflineVideoPouch put error', err);
                        avideoToastError('Video Info: ' + err.message);
                        return err;
                    }));
                }

                return videosUpdated;

            }).catch(function (err) {
                console.log('getOfflineVideoPouch videoRow error', err);
                avideoToastError('Model creation error: ' + err.message);
                return err;
            });
            return result;
        }
    });


}

function createSourceFromBlob(blob, type, res) {
    if (empty(type)) {
        type = 'video/mp4';
    }
    if (empty(res)) {
        res = 'auto';
    }
    blob = blob.slice(0, blob.size, type);
    var src;
    if (window.webkitURL != null) {
        src = window.webkitURL.createObjectURL(blob);
    } else {
        src = window.URL.createObjectURL(blob);
    }
    var source = {
        src: src,
        type: type,
        res: res,
        class: 'offline-video',
        label: res + 'p <span class="label label-warning" style="padding: 0 2px; font-size: .8em; display: inline;">(OFFLINE)</span>',
    };
    console.log('createSourceFromBlob ', source, blob);
    return source;
}

function createImageSourceFromBlob(blob, type) {
    if (empty(type)) {
        type = 'image/jpeg';
    }
    blob = blob.slice(0, blob.size, type);
    var src;
    if (window.webkitURL != null) {
        src = window.webkitURL.createObjectURL(blob);
    } else {
        src = window.URL.createObjectURL(blob);
    }
    var source = {
        src: src,
        type: type,
    };
    console.log('createImageSourceFromBlob ', source, blob);
    return source;
}


function getOneOfflineVideoSource() {
    var first = false;
    var video480 = false;
    $("#mainVideo source").each(function (index) {
        if (empty(first)) {
            first = $(this);
        }
        var resolution = $(this).attr("res");
        if (resolution == 480) {
            video480 = $(this);
        }
    });
    if (!empty(video480)) {
        console.log('getOneOfflineVideoSource 480p video found', video480);
        return video480;
    }
    console.log('getOneOfflineVideoSource first video found', first);
    return first;
}

function changeProgressBarOfflineVideo(progressBarSelector, value) {
    value = value.toFixed(2);
    $(progressBarSelector).find('.progress-bar')
            .attr('aria-valuenow', value)
            .css('width', value + '%')
            .text(value + '%');
}

async function fetchVideoFromNetwork(src, type, resolution, progressBarSelector) {
    if (src.match(/\.m3u8/) && typeof fetchVideoFromNetworkHLS === 'function') {
        return await fetchVideoFromNetworkHLS(src, type, resolution, progressBarSelector);
    } else {
        return await fetchVideoFromNetworkMP4(src, type, resolution, progressBarSelector);
    }
}

async function fetchVideoFromNetworkMP4(src, type, resolution, progressBarSelector) {
    console.log('fetching videos from network', src, type, resolution, progressBarSelector);

    // Step 1: start the fetch and obtain a reader
    let response = await fetch(src);

    const reader = response.body.getReader();

    // Step 2: get total length
    const contentLength = +response.headers.get('Content-Length');

    // Step 3: read the data
    let receivedLength = 0; // received that many bytes at the moment
    let chunks = []; // array of received binary chunks (comprises the body)
    while (true) {
        const {done, value} = await reader.read();

        if (done) {
            break;
        }

        chunks.push(value);
        receivedLength += value.length;
        var percentageComplete = (receivedLength / contentLength) * 100;
        var percentageCompleteStr = percentageComplete.toFixed(2) + '%';
        if (!empty(progressBarSelector)) {
            changeProgressBarOfflineVideo(progressBarSelector, percentageComplete);
        }
        console.log(`Received ${receivedLength} of ${contentLength}  ${percentageCompleteStr}`);
    }

    let fileBlob = new Blob(chunks, {type: type});
    console.log('fetching videos from network finish', type, fileBlob);
    return await storeOfflineVideoPouch(src, fileBlob, type, mediaId, resolution);
}

async function storeOfflineVideoPouch(src, fileBlob, type, videos_id, resolution) {
    var result = await getOfflineVideoPouch(videos_id).then(async function (videoRow) {
        console.log('getOfflineVideoPouch videoRow', videoRow);
        videoRow._id = getDBOfflineID(videos_id);
        videoRow.src = src;
        videoRow.video_type = type;
        videoRow.videos_id = videos_id;
        videoRow.modified = new Date().getTime();
        var result = await offlineVideoDBPouch.put(videoRow).then(async function (result) {
            console.log('storeOfflineVideoPouch put', result, result.id, resolution, result._rev, fileBlob, type);
            var result2 = await offlineVideoDBPouch.putAttachment(result.id, resolution, result.rev, fileBlob, type).then(function (result2) {
                // handle result
                console.log('offlineVideoDBPouch putAttachment', result2);
                avideoToastSuccess('Video saved');
                updateOfflineVideoMetaData(videos_id);
                return result2;
            }).catch(function (err) {
                console.log('offlineVideoDBPouch putAttachment', err);
                avideoToastError('Attachment: ' + err.message);
                return err;
            });
            return result2;
        }).catch(function (err) {
            console.log('storeOfflineVideoPouch put error', err);
            avideoToastError('Video Info: ' + err.message);
            return err;
        });
        return result;
    }).catch(function (err) {
        console.log('getOfflineVideoPouch videoRow error', err);
        avideoToastError('Model creation error: ' + err.message);
        return err;
    });
    return result;

}

function getDBOfflineID(videos_id) {
    return 'videos_id_' + videos_id;
}

async function getOfflineVideoPouch(videos_id) {
    var result = await offlineVideoDBPouch.get(getDBOfflineID(videos_id)).catch(function (err) {
        //console.log('getOfflineVideoPouch got something ', err);
        if (err.name === 'not_found') {
            var videoRow = {
                _id: getDBOfflineID(videos_id),
                src: '',
                //fileBlob: fileBlob,
                video_type: '',
                contentLength: 0,
                videos_id: videos_id,
                resolution: 0,
                created: new Date().getTime(),
                modified: new Date().getTime(),
                view: {
                    by_videos_id: {
                        map: function (doc) {
                            emit(doc.videos_id);
                        }.toString()
                    }
                }
            };
            return videoRow;
        } else { // hm, some other error
            console.log('getOfflineVideoPouch got error ', err);
            throw err;
        }
    }).then(function (videoRow) {
        // sweet, here is our configDoc
        //console.log('getOfflineVideoPouch got something row ', videoRow);
        return videoRow;
    }).catch(function (err) {
        // handle any errors
        console.log('getOfflineVideoPouch got something ', err);
        throw err;
    });
    return result;
}

async function getAllOfflineVideoPouch(videos_id) {
    videos_id = parseInt(videos_id);
    var options = {include_docs: true};
    if (!empty(videos_id)) {
        options = {key: videos_id, include_docs: true};
    }
    var result = offlineVideoDBPouch.query(mapOfflineVideosId, options).then(function (result) {
        // handle result
        //console.log(result);
        return result;
    }).catch(function (err) {
        // handle errors
        console.log(err);
        throw err;
    });

    return result;

}

async function deleteOfflineVideoPouch(videos_id, resolution) {
    videos_id = parseInt(videos_id);
    offlineVideoDBPouch.get(getDBOfflineID(videos_id)).then(function (doc) {
        if (empty(resolution)) {
            return offlineVideoDBPouch.remove(doc._id, doc._rev);
        } else {
            return offlineVideoDBPouch.removeAttachment(doc._id, resolution, doc._rev).then(function (result) {
                // handle result
                console.log('removeAttachment', result);
                return result;
            }).catch(function (err) {
                console.log('removeAttachment error', err);
            });
        }
    });
}

function createSourceElement(source) {
    if (typeof source !== 'object') {
        return false;
    }
    var sourceElement = $('<source />', source);
    if (!empty(source.class)) {
        $(sourceElement).addClass(source.class);
    }
    //createTestVideo(sourceElement.clone());
    //console.log('displayVideo', source);
    $("video#mainVideo, #mainVideo_html5_api").append(sourceElement);


}

function recreateFromSourceTags() {
    var sources = [];
    $("#mainVideo source").each(function (index) {
        var res = $(this).attr("res");
        var src = $(this).attr("src");
        var type = $(this).attr("type");

        var source = {
            src: src,
            type: type,
            res: 480
        };
        sources.push(source);

    });
    console.log('recreateFromSourceTags', sources);
    player.src(sources);
    videoJSRecreateSources(0);
}

function openDownloadOfflineVideoPage() {
    if (empty(mediaId)) {
        return false;
    }
    var url = webSiteRootURL + 'plugin/PlayerSkins/offlineVideo.php';
    url = addQueryStringParameter(url, 'videos_id', mediaId);
    url = addQueryStringParameter(url, 'socketResourceId', socketResourceId);
    avideoModalIframeSmall(url);
    return true;
}
var offlineVideoButtonCheckTimeout;
var offlineVideoButtonCheckIsActive = false;
function offlineVideoButtonCheck() {
    if (offlineVideoButtonCheckIsActive || empty(mediaId)) {
        return false;
    }
    offlineVideoButtonCheckIsActive = true;
    getAllOfflineVideoPouch(mediaId).then(function (collection) {
        if (!empty(collection.total_rows)) {
            if (isOfflineSourceSelectedToPlay()) {
                setOfflineButton('playingOffline', false);
            } else {
                setOfflineButton('readyToPlayOffline', false);
            }
        } else {
            setOfflineButton('download', false);
        }
        clearTimeout(offlineVideoButtonCheckTimeout);
        offlineVideoButtonCheckTimeout = setTimeout(function () {
            offlineVideoButtonCheck();
        }, 5000);
        offlineVideoButtonCheckIsActive = false;
    }).catch(function (e) {
        console.log("Error offlineVideoButtonCheck 2: ", e);
        offlineVideoButtonCheckIsActive = false;
    });
}

function isOfflineSourceSelectedToPlay() {
    var currSource = player.currentSrc();
    //console.log("isOfflineSourceSelectedToPlay: ", currSource);
    if (currSource.match(/^blob:http/i)) {
        return true;
    } else {
        return false;
    }
}

function setOfflineButton(type, showLoading) {
    if (showLoading) {
        offlineVideoLoading(true);
    }
    $('#mainVideo').addClass('vjs-has-started');
    switch (type) {
        case 'download':
            avideoTooltip(".offline-button", "Download");
            $('.offline-button').removeClass('hasOfflineVideo');
            $('body').removeClass('playingOfflineVideo');
            offlineVideoButtonCheck();
            break;
        case 'readyToPlayOffline':
            avideoTooltip(".offline-button", "Ready to play offline");
            $('body').removeClass('playingOfflineVideo');
            $('.offline-button').addClass('hasOfflineVideo');
            break;
        case 'playingOffline':
            avideoTooltip(".offline-button", "Playing offline");
            $('body').addClass('playingOfflineVideo');
            $('.offline-button').addClass('hasOfflineVideo');
            break;
    }
    if (showLoading) {
        offlineVideoLoading(false);
    }
}

function offlineVideoLoading(active) {
    if (active) {
        $('.offline-button').addClass('loading');
        $('.offline-button').addClass('fa-pulse');
    } else {
        $('.offline-button').removeClass('loading');
        $('.offline-button').removeClass('fa-pulse');
    }
}

function socketUpdateOfflineVideoSource(resourceId) {
    if (avideoSocketIsActive()) {
        sendSocketMessageToResourceId({}, 'replaceVideoSourcesPerOfflineVersion', resourceId)
    }
}

function getFirstOfflineAttachmentResolution(video) {
    if (empty(video._attachments)) {
        return false;
    }
    for (var i in video._attachments) {
        if (i !== 'poster') {
            return i;
        }
    }
    return false;
}

function mapOfflineVideosId(doc) {
    emit(doc.videos_id);
}

var offlineVideoDBPouch;
async function createOfflineDatabase(alsoDelete) {
    if (!empty(alsoDelete)) {
        return await offlineVideoDBPouch.destroy(function (err, response) {
            if (err) {
                return console.log(err);
            } else {
                offlineVideoDBPouch = new PouchDB(offlineVideoDbName);
            }
        });
    } else {
        offlineVideoDBPouch = new PouchDB(offlineVideoDbName);
    }
    return offlineVideoDBPouch;
}

$(document).ready(function () {
    createOfflineDatabase(false);
    replaceVideoSourcesPerOfflineVersion();
});