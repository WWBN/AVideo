var yptPopupOpened = false;
var yptConnectionReceived = false;
var yptWin;
var socialPublisherBusy = false;
var socialPublisherHistoryTimer = null;
var socialPublisherCategoriesLoadedFor = null;

function socialPublisherT(key) {
    if (typeof socialPublisherText !== 'undefined' && typeof socialPublisherText[key] === 'string') {
        return socialPublisherText[key];
    }
    return key;
}

function socialPublisherBrokerOrigin() {
    try {
        return new URL(typeof yptURL !== 'undefined' ? yptURL : 'https://restream.ypt.me/').origin;
    } catch (e) {
        return '';
    }
}

// this file is included in the body, before view/js/script.js (footer) defines eventer/messageEvent
if (typeof socialPublisherListenerAdded === 'undefined') {
    var socialPublisherListenerAdded = true;
    window.addEventListener('message', socialPublisherOnMessage, false);
}

function socialPublisherOnMessage(e) {
    if (!e.data || typeof e.data.provider === 'undefined' || typeof e.data.parameters === 'undefined') {
        return;
    }
    // accept the connection only from the broker popup, or forwarded by our parent page
    // (view/js/script.js only forwards messages that came from the broker)
    var fromBroker = e.origin === socialPublisherBrokerOrigin();
    var fromParent = e.origin === window.location.origin && window.parent !== window && e.source === window.parent;
    if (!fromBroker && !fromParent) {
        console.warn('SocialMediaPublisher: message ignored from', e.origin);
        return;
    }
    if (!yptPopupOpened) {
        // this window did not open a connection popup
        return;
    }
    saveYPT(e.data.provider, e.data.name, e.data.parameters);
}

function openYPT(provider) {
    yptPopupOpened = 1;
    yptConnectionReceived = false;
    var url = yptURL + 'confirm/' + provider;
    var name = "theYPTPopUp";
    var params = {
        title: $('#socialUploadtitle').val(),
        description: $('#socialUploaddescription').val()
    };
    var strWindowFeatures = "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,resizable=no,height=600,width=800";
    yptWin = openWindowWithPost(url, name, params, strWindowFeatures);
    if (!yptWin) {
        yptPopupOpened = 0;
        avideoAlertError(__('Your browser blocked the popup. Allow popups for this site and try again.'));
        return;
    }
    modal.showPleaseWait();
    var pollTimer = window.setInterval(function () {
        if (yptWin.closed !== false) { // !== is required for compatibility with Opera
            window.clearInterval(pollTimer);
            modal.hidePleaseWait();
            yptPopupOpened = 0;
            if (!yptConnectionReceived) {
                // closed by the user, or the authorization was denied on the provider page
                avideoToastWarning(socialPublisherT('connectionCanceled'));
            }
        }
    }, 500);
}

function saveYPT(provider, name, parameters) {
    yptPopupOpened = 0;
    yptConnectionReceived = true;
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/add.json.php',
        type: "POST",
        data: {
            provider: provider,
            name: name,
            json: parameters,
        },
        success: function (response) {
            if (response && !response.error) {
                avideoToastSuccess(socialPublisherT('connectionSaved'));
            } else {
                avideoAlertError(response && response.msg ? response.msg : socialPublisherT('connectionNotSaved'));
            }
        },
        error: function (xhr) {
            avideoAlertError(socialPublisherErrorFromXHR(xhr));
        },
        complete: function () {
            modal.hidePleaseWait();
            reloadSocialAccountsTables();
        }
    });
    if (yptWin) {
        yptWin.close();
    }
}

function socialPublisherErrorFromXHR(xhr) {
    if (xhr && xhr.responseJSON && typeof xhr.responseJSON.msg === 'string' && xhr.responseJSON.msg !== '') {
        return xhr.responseJSON.msg;
    }
    return socialPublisherT('serverUnreachable');
}

function checkIfIsConnected(id) {

    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/isConnected.json.php',
        type: "POST",
        data: {
            id: id,
        },
        success: function (response) {
            avideoResponse(response);
        },
        error: function (xhr) {
            avideoAlertError(socialPublisherErrorFromXHR(xhr));
        },
        complete: function () {
            modal.hidePleaseWait();
            reloadSocialAccountsTables();
        }
    });
}

function uploadToSocial(id, videos_id, title, description, visibility, row, confirmDuplicate) {
    if (socialPublisherBusy) {
        avideoToastWarning(socialPublisherT('publishingNow'));
        return;
    }
    if (typeof title === 'string' && $.trim(title) === '') {
        avideoAlertError(socialPublisherT('titleRequired'));
        $('#socialUploadtitle').focus();
        return;
    }
    var isYouTube = row && row.isYouTube;
    socialPublisherBusy = true;
    $('button.upload_Publisher_user_preferences').prop('disabled', true);
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/uploadVideo.json.php',
        type: "POST",
        data: {
            id: id,
            videos_id: videos_id,
            title: title,
            description: description,
            visibility: visibility,
            tags: $('#socialUploadtags').val(),
            categoryId: $('#socialUploadcategory').val(),
            confirmDuplicate: confirmDuplicate ? 1 : 0
        },
        success: async function (response) {
            socialPublisherBusy = false;
            $('button.upload_Publisher_user_preferences').prop('disabled', false);
            modal.hidePleaseWait();
            if (!response) {
                avideoAlertError(socialPublisherT('publishFailed'));
                return;
            }
            if (response.duplicate) {
                if (await avideoConfirm(response.msg)) {
                    uploadToSocial(id, videos_id, title, description, visibility, row, true);
                }
                return;
            }
            if (response.error) {
                avideoAlertError(response.msg ? response.msg : socialPublisherT('publishFailed'));
            } else if (isYouTube) {
                // only "started": success is shown in the history once YouTube confirms it
                avideoToastInfo(response.msg);
            } else {
                avideoToastSuccess(response.msg ? response.msg : socialPublisherT('published'));
            }
            reloadSocialAccountsTables();
        },
        error: function (xhr) {
            socialPublisherBusy = false;
            $('button.upload_Publisher_user_preferences').prop('disabled', false);
            modal.hidePleaseWait();
            avideoAlertError(socialPublisherErrorFromXHR(xhr));
            reloadSocialAccountsTables();
        }
    });
}

function reloadSocialAccountsTables() {
    if (typeof Publisher_user_preferencestableVar !== 'undefined') {
        Publisher_user_preferencestableVar.ajax.reload(null, false);
    }
    if (typeof Publisher_social_mediastableVar !== 'undefined') {
        Publisher_social_mediastableVar.ajax.reload(null, false);
    }
    if (typeof Publisher_video_publisher_logstableVarVID !== 'undefined') {
        Publisher_video_publisher_logstableVarVID.ajax.reload(null, false);
    }

}

/* ---------- connections table ---------- */

function socialPublisherRenderAccount(row) {
    var $div = $('<div class="socialPublisherAccount">');
    if (row.details) {
        $div.append($('<div class="largeSocialIcon">').addClass(row.details.iconClass).html(row.details.ico));
    }
    $div.append($('<span class="socialPublisherAccountName">').text(row.preferred_profile ? row.preferred_profile : (row.details ? row.details.label : row.name)));
    return $('<div>').append($div).html();
}

function socialPublisherRenderConnectionStatus(row) {
    var $wrap = $('<div>');
    if (row.connectionStatus === 'reconnect') {
        $wrap.append($('<span class="label label-danger">').text(socialPublisherT('reconnectNeeded')));
        $wrap.append($('<br>'));
        $wrap.append($('<small class="text-muted">').text(socialPublisherT('reconnectHelp')));
    } else {
        $wrap.append($('<span class="label label-success">').append('<i class="fas fa-check"></i> ').append(document.createTextNode(socialPublisherT('connected'))));
    }
    return $wrap.html();
}

function socialPublisherRenderConnectionActions(row, videosId) {
    var $wrap = $('<div class="socialPublisherActions">');
    if (row.connectionStatus === 'reconnect') {
        $wrap.append($('<button type="button" class="reconnect_Publisher_user_preferences btn btn-warning btn-block btn-sm">').append('<i class="fas fa-plug"></i> ').append(document.createTextNode(socialPublisherT('reconnect'))));
    } else if (!empty(videosId)) {
        $wrap.append($('<button type="button" class="upload_Publisher_user_preferences btn btn-primary btn-block btn-sm">').append('<i class="fa-regular fa-share-from-square"></i> ').append(document.createTextNode(socialPublisherT('publish'))));
    }
    if (row.connectionStatus === 'renewable') {
        $wrap.append($('<button type="button" class="revalidate_Publisher_user_preferences btn btn-default btn-block btn-xs">').append('<i class="fa-solid fa-arrows-rotate"></i> ').append(document.createTextNode(socialPublisherT('renew'))));
    }
    $wrap.append($('<button type="button" class="delete_Publisher_user_preferences btn btn-default btn-block btn-xs">').append('<i class="fas fa-unlink"></i> ').append(document.createTextNode(socialPublisherT('disconnect'))));
    return $('<div>').append($wrap).html();
}

// YouTube-only fields are shown when the user has a YouTube account; categories are loaded once with it
function socialPublisherLoadCategories(rows) {
    var youtube = null;
    for (var i in rows) {
        if (rows[i].isYouTube && rows[i].connectionStatus !== 'reconnect') {
            youtube = rows[i];
            break;
        }
    }
    if (!youtube || !$('#socialUploadcategory').length || socialPublisherCategoriesLoadedFor === youtube.id) {
        return;
    }
    socialPublisherCategoriesLoadedFor = youtube.id;
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/youtubeCategories.json.php',
        data: { id: youtube.id },
        success: function (response) {
            if (!response || response.error || empty(response.categories)) {
                $('#socialUploadcategoryGroup').addClass('hidden');
                console.warn('SocialMediaPublisher categories:', response ? response.msg : '');
                return;
            }
            var $select = $('#socialUploadcategory');
            $select.find('option:not(:first)').remove();
            for (var i in response.categories) {
                $select.append($('<option>').val(response.categories[i].id).text(response.categories[i].title));
            }
            $('#socialUploadcategoryGroup').removeClass('hidden');
        },
        error: function () {
            $('#socialUploadcategoryGroup').addClass('hidden');
        }
    });
}

/* ---------- publishing history ---------- */

function socialPublisherJobIcon(job) {
    switch (job.state) {
        case 'published':
            return '<i class="fas fa-check-circle text-success"></i> ';
        case 'processing':
        case 'queued':
        case 'preparing':
        case 'uploading':
            return '<i class="fas fa-circle-notch fa-spin"></i> ';
        case 'canceled':
            return '<i class="fas fa-ban text-muted"></i> ';
        case 'interrupted':
            return '<i class="fas fa-pause-circle text-warning"></i> ';
    }
    return '<i class="fas fa-exclamation-triangle text-danger"></i> ';
}

function socialPublisherRenderJob(row) {
    var job = row.job;
    var $job = $('<div class="socialPublisherJob">').addClass('socialPublisherJob-' + job.state);
    if (job.title) {
        $job.append($('<div class="socialPublisherJobTitle">').text(job.title));
    }
    var meta = [];
    if (job.accountName) {
        meta.push(job.accountName);
    }
    if (job.privacy && socialPublisherText && socialPublisherText.privacy && socialPublisherText.privacy[job.privacy]) {
        meta.push(socialPublisherText.privacy[job.privacy]);
    }
    if (meta.length) {
        $job.append($('<small class="text-muted">').text(meta.join(' - ')));
    }
    var $status = $('<div class="socialPublisherJobStatus">').addClass(job.isError ? 'text-danger' : '');
    $status.append(socialPublisherJobIcon(job)).append(document.createTextNode(job.message));
    $job.append($status);

    if (job.isActive || job.state === 'interrupted') {
        var percent = parseInt(job.percent) || 0;
        var $bar = $('<div class="progress-bar" role="progressbar">')
            .attr('aria-valuenow', percent).attr('aria-valuemin', 0).attr('aria-valuemax', 100)
            .css('width', percent + '%').text(percent + '%');
        if (job.isActive) {
            $bar.addClass('progress-bar-striped active');
        } else {
            $bar.addClass('progress-bar-warning');
        }
        $job.append($('<div class="progress">').append($bar));
        if (job.totalBytes) {
            $job.append($('<small class="text-muted">').text(humanFileSize(job.uploadedBytes) + ' ' + socialPublisherT('of') + ' ' + humanFileSize(job.totalBytes)));
        }
    }

    var $buttons = $('<div class="socialPublisherJobButtons">');
    if (job.url) {
        $buttons.append($('<a target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-xs">').attr('href', job.url)
            .append('<i class="fab fa-youtube"></i> ').append(document.createTextNode(socialPublisherT('openOnYouTube'))));
    }
    if (job.isMine) {
        if (job.needsReconnect) {
            $buttons.append($('<button type="button" class="btn btn-warning btn-xs socialPublisherJobReconnect">').append('<i class="fas fa-plug"></i> ').append(document.createTextNode(socialPublisherT('reconnect'))));
        }
        if (job.canResume) {
            $buttons.append($('<button type="button" class="btn btn-primary btn-xs socialPublisherJobAction" data-action="resume">')
                .append('<i class="fas fa-redo"></i> ').append(document.createTextNode(socialPublisherT(job.state === 'interrupted' ? 'resume' : 'tryAgain'))));
        }
        if (job.canCancel) {
            $buttons.append($('<button type="button" class="btn btn-default btn-xs socialPublisherJobAction" data-action="cancel">')
                .append('<i class="fas fa-times"></i> ').append(document.createTextNode(socialPublisherT('cancel'))));
        }
    }
    if ($buttons.children().length) {
        $job.append($buttons);
    }
    return $('<div>').append($job).html();
}

// keeps the history up to date while an upload runs or YouTube is processing it
function socialPublisherScheduleHistoryReload(rows) {
    clearTimeout(socialPublisherHistoryTimer);
    var delay = 0;
    for (var i in rows) {
        var job = rows[i].job;
        if (!job) {
            continue;
        }
        if (job.isActive) {
            delay = 4000;
            break;
        }
        if (job.state === 'processing') {
            delay = 15000;
        }
    }
    if (delay && typeof Publisher_video_publisher_logstableVarVID !== 'undefined') {
        socialPublisherHistoryTimer = setTimeout(function () {
            Publisher_video_publisher_logstableVarVID.ajax.reload(null, false);
        }, delay);
    }
}

async function socialPublisherJobAction(logs_id, action, $btn) {
    if (action === 'cancel' && !await avideoConfirm(socialPublisherT('cancelConfirm'))) {
        return;
    }
    $btn.prop('disabled', true);
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/SocialMediaPublisher/youtubeJob.json.php',
        type: 'POST',
        data: {
            logs_id: logs_id,
            action: action
        },
        success: function (response) {
            if (!response || response.error) {
                avideoAlertError(response && response.msg ? response.msg : socialPublisherT('publishFailed'));
            } else {
                avideoToastSuccess(response.msg);
            }
        },
        error: function (xhr) {
            avideoAlertError(socialPublisherErrorFromXHR(xhr));
        },
        complete: function () {
            modal.hidePleaseWait();
            reloadSocialAccountsTables();
        }
    });
}

function checkInstagram(accessToken, containerId, instagramAccountId) {
    var url = webSiteRootURL + 'plugin/SocialMediaPublisher/publishInstagram.json.php';
    url = addQueryStringParameter(url, 'accessToken', accessToken);
    url = addQueryStringParameter(url, 'containerId', containerId);
    url = addQueryStringParameter(url, 'instagramAccountId', instagramAccountId);
    modal.showPleaseWait();
    $.ajax({
        url: url,
        complete: function(response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                reloadSocialAccountsTables();
            }
        }
    });
}
