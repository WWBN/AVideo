<section class="ai-settings" aria-label="<?php echo __('Video chat settings'); ?>">
    <div id="companionChatPanelBody">
        <div id="companionMarketplaceCreditNotice" class="alert alert-info hidden" role="note">
            <i class="fa-solid fa-wallet" aria-hidden="true"></i>
            <?php echo __('Companion is connected automatically through AVideo. Creating your organization and site requires a valid Marketplace AccessToken and a positive available wallet balance, excluding reserved funds.'); ?>
            <?php echo __('Save the token in Admin > Plugins > AI. Video processing and chat require enough available credits for each operation.'); ?>
            <a href="https://streamphp.com/marketplace/" target="_blank" rel="noopener noreferrer" class="alert-link"><?php echo __('Open Marketplace to add credits'); ?></a>
            <button type="button" class="btn btn-default btn-sm" onclick="loadCompanionChat()"><i class="fa-solid fa-rotate" aria-hidden="true"></i> <?php echo __('Check connection again'); ?></button>
        </div>
        <div id="companionChatNotConfigured" style="display:none;" class="alert alert-warning">
            <?php echo __("The Companion Chat service is not available right now. Ask your site administrator to check the AI plugin connection."); ?>
        </div>
        <div id="companionChatLoading">
            <i class="fas fa-spinner fa-spin"></i> <?php echo __('Loading...'); ?>
        </div>
        <div id="companionOrganizationDisabled" style="display:none;" class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> <span id="companionOrganizationDisabledText"></span>
        </div>
        <div id="companionChatContent" style="display:none;">
            <div class="ai-settings-header">
                <div class="ai-settings-title">
                    <span class="ai-settings-icon text-primary"><i class="fa-regular fa-comments" aria-hidden="true"></i></span>
                    <div>
                        <h3><?php echo __('Video chat'); ?></h3>
                        <p class="text-muted"><?php echo __('Answers from your video, right in the player.'); ?></p>
                    </div>
                </div>
                <div id="companionVideoReady" class="ai-settings-control" style="display:none;">
                    <label for="companionChatToggle"><?php echo __('Enable on player'); ?></label>
                    <div class="material-switch ai-settings-switch">
                        <input type="checkbox" id="companionChatToggle" onchange="companionToggleChat(this.checked)">
                        <label for="companionChatToggle" class="label-primary"><span class="sr-only"><?php echo __('Enable chat on the video player'); ?></span></label>
                    </div>
                </div>
            </div>
            <div class="ai-price-grid">
                <div class="ai-price-item">
                    <span class="ai-price-caption text-muted"><?php echo __('Video processing'); ?></span>
                    <strong class="ai-price-amount" id="companionVideoEstimate">&mdash;</strong>
                    <span class="ai-price-note text-muted"><?php echo __('Estimated per processing run'); ?></span>
                </div>
                <div class="ai-price-item">
                    <span class="ai-price-caption text-muted"><?php echo __('Chat answers'); ?></span>
                    <strong class="ai-price-amount" id="companionQuestionPrice">&mdash;</strong>
                    <span class="ai-price-note text-muted"><?php echo __('Per answered question'); ?></span>
                </div>
                <div class="ai-price-item ai-wallet-item">
                    <span class="ai-price-caption text-muted"><i class="fa-solid fa-wallet" aria-hidden="true"></i> <?php echo __('Marketplace wallet'); ?></span>
                    <span id="companionMarketplaceStatus">&mdash;</span>
                    <span class="ai-price-note text-muted"><?php echo __('Shared across your AI services'); ?></span>
                </div>
            </div>
            <div class="ai-settings-meta">
                <span id="companionAlreadyProcessedPrice" class="text-success" style="display:none;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <?php echo __('Processed'); ?> <span class="text-muted">&middot; <?php echo __('No processing charge to reuse'); ?></span></span>
                <?php if (User::isAdmin()) { ?>
                <button type="button" class="btn btn-default btn-sm" id="companionOpenDashboardBtn" onclick="companionOpenDashboard()" title="<?php echo __('Open this site in Companion (videos, chat history, settings and spend). Only this site is visible there.'); ?>">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> <?php echo __('Open Companion dashboard'); ?>
                </button>
                <?php } ?>
            </div>
            <details class="ai-price-details">
                <summary><?php echo __('Pricing details'); ?> <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
                <div class="ai-price-details-body small">
                    <p class="text-muted" id="companionVideoDuration"></p>
                    <p><strong><?php echo __('Processing rate'); ?>:</strong> <span id="companionVideoPrice">&mdash;</span></p>
                    <p><?php echo __('The final charge uses the duration measured during processing. A connected Marketplace wallet is required.'); ?></p>
                    <p><?php echo __('Reusing a processed video has no additional processing charge. Requesting a new processing run is charged again.'); ?></p>
                    <p class="text-muted"><?php echo __('All prices are in USD. Wallet balance is shared with your other AI services.'); ?></p>
                </div>
            </details>

            <div id="companionNeedsMarketplaceLink" style="display:none;" class="alert alert-warning">
                <?php echo __('Connect a Marketplace wallet with available credits to process videos and use chat. Save your AccessToken in Admin > Plugins > AI, then connect below.'); ?>
                <br>
                <button class="btn btn-warning btn-sm" onclick="companionLinkMarketplace()">
                    <i class="fa-solid fa-link"></i> <?php echo __('Connect marketplace wallet now'); ?>
                </button>
            </div>

            <div id="companionVideoNotSubmitted">
                <button class="btn btn-primary btn-sm" onclick="companionSubmitVideo()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> <?php echo __('Process this video for Chat'); ?>
                </button>
            </div>

            <div id="companionVideoProcessing" style="display:none;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" id="companionProgressBar" style="width:0%">0%</div>
                </div>
                <p class="text-muted"><?php echo __('Companion is processing this video (transcription + visual understanding). This can take a few minutes for longer videos.'); ?></p>
            </div>

            <div id="companionVideoError" style="display:none;" class="alert alert-danger"></div>
        </div>
    </div>
</section>

<script>
    var companionActionBusy = false;
    var companionPollTimer = null;

    // A per-question price is routinely a fraction of a cent, so a fixed
    // 2-decimal format prints it as "0.00" - which reads as "free". Keep the
    // usual 2 decimals for ordinary amounts and extend up to the value's own
    // precision, unless the caller asks for an exact number of decimals.
    function companionPriceDecimals(value, decimals) {
        if (decimals !== null && decimals !== undefined && Number.isFinite(Number(decimals))) {
            return Math.min(Math.max(Math.trunc(Number(decimals)), 2), 8);
        }
        var text = Math.abs(Number(value)).toFixed(8).replace(/0+$/, '');
        var dot = text.indexOf('.');
        return Math.min(Math.max(dot === -1 ? 0 : text.length - dot - 1, 2), 8);
    }

    function companionPrice(amount, decimals) {
        if (amount === null || amount === undefined || amount === '' || !Number.isFinite(Number(amount)) || Number(amount) < 0) {
            return <?php echo json_encode(__('Unavailable')); ?>;
        }
        var value = Number(amount);
        return value.toLocaleString(document.documentElement.lang || undefined, {minimumFractionDigits: 2, maximumFractionDigits: companionPriceDecimals(value, decimals)}) + ' USD';
    }

    function companionProcessingEstimate(flatFee, perMinute, durationSeconds) {
        if (durationSeconds === null || durationSeconds === undefined || !Number.isFinite(Number(durationSeconds)) || Number(durationSeconds) <= 0) {
            return null;
        }
        if ([flatFee, perMinute].some(function(value) { return value === null || value === undefined || value === '' || !Number.isFinite(Number(value)) || Number(value) < 0; })) {
            return null;
        }
        return Number(flatFee) + Number(perMinute) * Number(durationSeconds) / 60;
    }

    function companionAjax(action, extraData, onSuccess) {
        var mutation = action !== 'status';
        if (mutation && companionActionBusy) {
            return;
        }
        if (mutation) {
            companionActionBusy = true;
        }
        var data = Object.assign({videos_id: <?php echo (int) $videos_id; ?>, action: action}, extraData || {});
        var url = webSiteRootURL + 'plugin/AI/tabs/companionChat.json.php';
        function complete() {
            if (mutation) {
                companionActionBusy = false;
                modal.hidePleaseWait();
            }
        }
        function failed(xhr) {
            var message = xhr && xhr.status === 403
                ? <?php echo json_encode(__('Your session expired or you no longer have permission. Reload the page and try again.')); ?>
                : <?php echo json_encode(__('Could not load the Chat settings. Please try again.')); ?>;
            $('#companionChatLoading').hide();
            $('#companionChatContent').hide();
            $('#companionChatNotConfigured').show().text(message);
            avideoToastError(message);
            complete();
        }
        function send() {
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if (typeof onSuccess === 'function') {
                        onSuccess(response);
                    }
                },
                error: failed,
                complete: complete
            });
        }
        if (!mutation) {
            send();
            return;
        }
        // The token rendered in the page head expires after five minutes.
        // Fetch a fresh, video-scoped token without weakening server validation.
        $.ajax({
            url: url,
            data: {videos_id: data.videos_id, action: 'token'},
            type: 'post',
            dataType: 'json',
            success: function(response) {
                if (!response || response.error || !response.globalToken) {
                    failed();
                    return;
                }
                data.globalToken = response.globalToken;
                send();
            },
            error: failed
        });
    }

    function companionRenderStatus(response) {
        $('#companionChatLoading').hide();
        var status = response.status || {};
        var hasLowBalance = status.balance !== null && status.balance !== undefined && status.balance !== ''
            && Number.isFinite(Number(status.balance)) && Number(status.balance) < 5;
        $('#companionMarketplaceCreditNotice').toggleClass('hidden', !hasLowBalance);
        if (response.notConfigured || response.videoStatusError) {
            $('#companionChatContent').hide();
            $('#companionChatNotConfigured').show();
            return;
        }
        $('#companionChatNotConfigured').hide();
        $('#companionChatContent').show();

        if (status.organization_status && status.organization_status !== 'active') {
            var reason = status.marketplace_auto_disabled_at
                ? <?php echo json_encode(__('This account is disabled because the connected marketplace wallet balance ran out. Add credits to your marketplace wallet to reactivate automatically - content is at risk of deletion after 24 hours.')); ?>
                : <?php echo json_encode(__('This account has been disabled. Contact support for details.')); ?>;
            $('#companionOrganizationDisabledText').text(reason);
            $('#companionOrganizationDisabled').show();
        } else {
            $('#companionOrganizationDisabled').hide();
        }
        if (status.marketplace_connected) {
            var balance = (status.balance !== null && status.balance !== undefined) ? status.balance : '?';
            $('#companionMarketplaceStatus').empty()
                .append($('<span>', {class: 'label label-success', text: <?php echo json_encode(__('Connected')); ?>}))
                .append(document.createTextNode(' ' + companionPrice(balance)));
            $('#companionNeedsMarketplaceLink').hide();
        } else {
            $('#companionMarketplaceStatus').empty()
                .append($('<span>', {class: 'label label-default', text: <?php echo json_encode(__('Not connected')); ?>}));
        }
        $('#companionVideoPrice').text(companionPrice(status.video_processing_flat_fee) + ' ' + <?php echo json_encode(__('per video')); ?> + ' + ' + companionPrice(status.video_processing_per_minute) + '/min');
        $('#companionQuestionPrice').text(companionPrice(status.question_price));

        var video = response.videoStatus;
        var duration = video && Number(video.duration_seconds) > 0 ? video.duration_seconds : response.videoDurationSeconds;
        var estimate = companionProcessingEstimate(status.video_processing_flat_fee, status.video_processing_per_minute, duration);
        $('#companionVideoEstimate').text(estimate === null
            ? <?php echo json_encode(__('Estimate unavailable: video duration is not known yet.')); ?>
            : companionPrice(estimate, 4));
        $('#companionVideoDuration').text(estimate === null ? '' : (Number(duration) / 60).toLocaleString(document.documentElement.lang || undefined, {maximumFractionDigits: 2}) + ' ' + <?php echo json_encode(__('minutes of video')); ?>);
        $('#companionAlreadyProcessedPrice').toggle(!!video && video.status === 'ready');
        $('#companionVideoNotSubmitted, #companionVideoProcessing, #companionVideoReady, #companionVideoError, #companionNeedsMarketplaceLink').hide();

        if (!video) {
            $('#companionVideoNotSubmitted').show();
            if (!status.marketplace_connected) {
                $('#companionNeedsMarketplaceLink').show();
            }
            return;
        }
        if (video.status === 'ready') {
            $('#companionVideoReady').show();
            var chat = response.chatConfig;
            var enabled = !!(chat && chat.enabled);
            $('#companionChatToggle').prop('checked', enabled);
        } else if (video.status === 'failed' || video.status === 'cancelled') {
            $('#companionVideoError').show().text(video.error_message || <?php echo json_encode(__('Processing failed.')); ?>);
            $('#companionVideoNotSubmitted').show();
        } else {
            $('#companionVideoProcessing').show();
            $('#companionProgressBar').css('width', (video.progress_percent || 0) + '%').text((video.progress_percent || 0) + '%');
            // One poll chain only: a submit/toggle while a poll is pending
            // would otherwise leave two timers hitting the endpoint forever.
            clearTimeout(companionPollTimer);
            companionPollTimer = setTimeout(loadCompanionChat, 5000);
        }
    }

    function loadCompanionChat() {
        companionAjax('status', {}, function(response) {
            if (response.error) {
                $('#companionChatLoading').hide();
                $('#companionChatNotConfigured').show().text(response.msg);
                $('#companionChatContent').hide();
                avideoToastError(response.msg);
                return;
            }
            companionRenderStatus(response);
        });
    }

    // Admin-only: asks AVideo (server-to-server) for a one-time Companion
    // sign-in link for the current admin and opens it. The link is bound to
    // this installation's Site and expires in a couple of minutes.
    function companionOpenDashboard() {
        var btn = document.getElementById('companionOpenDashboardBtn');
        if (btn) btn.disabled = true;
        // Open the tab synchronously (popup blockers) and navigate it later.
        var target = window.open('', '_blank');
        var url = webSiteRootURL + 'plugin/AI/companionAdminLogin.json.php';
        var failed = function (msg) {
            if (btn) btn.disabled = false;
            if (target) target.close();
            avideoAlertError(msg || '<?php echo __('Could not open Companion'); ?>');
        };
        $.post(url, { action: 'token' }, function (tokenResponse) {
            if (!tokenResponse || tokenResponse.error || !tokenResponse.globalToken) {
                failed(tokenResponse && tokenResponse.msg);
                return;
            }
            $.post(url, { action: 'login', globalToken: tokenResponse.globalToken }, function (response) {
                if (!response || response.error || !response.login_url) {
                    failed(response && response.msg);
                    return;
                }
                if (btn) btn.disabled = false;
                if (target) {
                    target.location = response.login_url;
                } else {
                    window.location = response.login_url;
                }
            }, 'json').fail(function () { failed(); });
        }, 'json').fail(function () { failed(); });
    }

    function companionSubmitVideo() {
        modal.showPleaseWait();
        companionAjax('submit', {}, function(response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
                return;
            }
            companionRenderStatus(response);
        });
    }

    function companionToggleChat(checked) {
        modal.showPleaseWait();
        companionAjax(checked ? 'enable_chat' : 'disable_chat', {}, function(response) {
            modal.hidePleaseWait();
            if (response.error) {
                // The server state did not change, so the switch must not keep
                // showing what the user asked for. Fall back to the previous
                // position, then let the payload correct it when it carries a
                // usable status.
                $('#companionChatToggle').prop('checked', !checked);
                avideoAlertError(response.msg);
                if (response.status) {
                    companionRenderStatus(response);
                }
                return;
            }
            companionRenderStatus(response);
        });
    }

    function companionLinkMarketplace() {
        modal.showPleaseWait();
        companionAjax('link_marketplace', {}, function(response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
                return;
            }
            avideoToastSuccess(<?php echo json_encode(__('Marketplace wallet connected')); ?>);
            companionRenderStatus(response);
        });
    }

    $(document).ready(function() {
        loadCompanionChat();
    });
</script>
