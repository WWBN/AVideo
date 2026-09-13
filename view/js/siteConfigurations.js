/* Site configuration editing; keep image processing out of ordinary saves. */
$(function () {
    'use strict';
    var form = $('#updateConfigForm');
    if (!form.length) return;
    var saving = false;
    var crops = [];
    var status = $('#configurationSaveStatus');
    // Snapshot persisted fields before widgets add their own inputs (e.g. crop zoom).
    var trackedInputs = form.find('input[id]:not([type=file]):not([type=button]):not([type=submit]), select[id], textarea[id]');
    function fieldValues() {
        return JSON.stringify(trackedInputs.toArray().map(function (input) {
            return input.type === 'checkbox' || input.type === 'radio' ? input.checked : $(input).val();
        }));
    }
    var savedValues = fieldValues();
    function updateDirtyStatus() {
        if (saving) return;
        var dirty = fieldValues() !== savedValues || crops.some(function (crop) { return crop.dirty; });
        if (dirty) {
            message(__('Unsaved changes'), false);
            status.data('unsaved', true);
        } else if (status.data('unsaved')) {
            message('', false);
            status.removeData('unsaved');
        }
    }
    form.on('input change', ':input', function () {
        if (trackedInputs.is(this)) updateDirtyStatus();
    });
    function message(text, error) {
        status.text(text).toggleClass('text-danger', !!error).toggleClass('text-success', !error);
    }
    function failure() {
        message(__('Your configurations has NOT been updated!'), true);
        avideoAlertError(__('Your configurations has NOT been updated!'));
    }
    function release() {
        saving = false;
        form.attr('aria-busy', 'false');
        form.find('.configuration-save').prop('disabled', false).find('i').attr('class', 'fas fa-save');
        modal.hidePleaseWait();
    }
    // Associate existing visible labels without changing plugin/field identifiers.
    form.find('.form-group').each(function () {
        var label = $(this).children('label').first();
        var input = $(this).find('input:not([type=hidden]), select, textarea').first();
        if (input.attr('id')) label.attr('for', input.attr('id'));
    });
    // Croppie's generic upload helper saves immediately. This form must retain drafts
    // and export only changed images together with the other configuration fields.
    function crop(id, inputId, key, width, height, outputWidth, outputHeight) {
        var element = $(id);
        var state = {element: element, dirty: false, ready: false, pending: false, key: key,
            size: {width: outputWidth, height: outputHeight}};
        var revision = 0;
        var source = element.attr('data-image-url');
        var queue = Promise.resolve();
        var imageStatus = $(inputId + '-status');
        var fitButton = $(inputId + '-fit');
        crops.push(state);
        $(inputId + '-btn').on('click', function () { $(inputId).trigger('click'); });
        function visible() {
            return new Promise(function (resolve) {
                function check() {
                    if (!element.is(':visible')) {
                        form.one('shown.bs.tab', check);
                        return;
                    }
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            if (element.is(':visible')) resolve();
                            else check();
                        });
                    });
                }
                check();
            });
        }
        function fit(sourceURL, changed) {
            var current = ++revision;
            state.pending = true;
            state.ready = false;
            if (changed) state.dirty = true;
            if (changed) updateDirtyStatus();
            fitButton.prop('disabled', true);
            imageStatus.text(__('Loading...')).removeClass('text-danger');
            // Decode first and contain the WHOLE image in a transparent output canvas.
            // This also avoids Croppie's default cover zoom and EXIF double rotation.
            var decoded = new Promise(function (resolve, reject) {
                var img = new Image();
                var timer = setTimeout(function () { reject(new Error('Image load timeout')); }, 30000);
                img.crossOrigin = 'anonymous';
                img.onerror = function () { clearTimeout(timer); reject(new Error('Image load failed')); };
                img.onload = function () {
                    clearTimeout(timer);
                    try {
                        var canvas = document.createElement('canvas');
                        canvas.width = outputWidth;
                        canvas.height = outputHeight;
                        var scale = Math.min(outputWidth / img.naturalWidth, outputHeight / img.naturalHeight);
                        var drawWidth = img.naturalWidth * scale;
                        var drawHeight = img.naturalHeight * scale;
                        canvas.getContext('2d').drawImage(img, (outputWidth - drawWidth) / 2,
                            (outputHeight - drawHeight) / 2, drawWidth, drawHeight);
                        resolve(canvas.toDataURL('image/png'));
                    } catch (error) { reject(error); }
                };
                img.src = sourceURL;
            });
            // Handle load failures immediately, even when an older bind is still pending.
            decoded = decoded.catch(function (error) { return {error: error}; });
            queue = queue.then(function () { return decoded; }).then(function (url) {
                if (url.error) throw url.error;
                if (current !== revision) return;
                return visible().then(function () {
                    if (current !== revision) return;
                    if (!element.hasClass('croppie-container')) {
                        element.croppie({enforceBoundary: false, mouseWheelZoom: false,
                            viewport: {width: width, height: height}, boundary: {width: width, height: height}});
                    }
                    return element.croppie('bind', {url: url, points: [0, 0, outputWidth, outputHeight], zoom: width / outputWidth});
                });
            }).then(function () {
                if (current !== revision) return;
                state.ready = true;
                state.pending = false;
                imageStatus.text(__('Drag or zoom to adjust. Fit image restores the entire image.'));
                fitButton.prop('disabled', false);
                element.removeClass('configuration-image-error');
            }).catch(function () {
                if (current !== revision) return;
                state.pending = false;
                state.ready = false;
                imageStatus.text(__('Could not load image. Choose another file or retry.')).addClass('text-danger');
                fitButton.prop('disabled', false);
                element.addClass('configuration-image-error');
            });
        }
        // Native gestures only: programmatic Croppie zoom changes must not dirty a draft.
        function markChanged() {
            state.dirty = true;
            updateDirtyStatus();
        }
        element.on('input change', '.cr-slider', function (event) {
            if (state.ready && event.originalEvent && event.originalEvent.isTrusted) markChanged();
        });
        var pointerStart;
        element.on('pointerdown', '.cr-boundary', function (event) {
            pointerStart = {x: event.clientX, y: event.clientY};
        }).on('pointermove', '.cr-boundary', function (event) {
            if (state.ready && pointerStart && (event.buttons || (event.originalEvent && event.originalEvent.pointerType === 'touch')) &&
                (Math.abs(event.clientX - pointerStart.x) > 2 || Math.abs(event.clientY - pointerStart.y) > 2)) markChanged();
        }).on('pointerup pointercancel pointerleave', function () { pointerStart = null; });
        element.on('keydown', '.cr-viewport', function (event) {
            if (state.ready && ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].indexOf(event.key) !== -1) markChanged();
        });
        fitButton.on('click', function () { if (!saving) fit(source, true); });
        $(inputId).on('change', function () {
            var file = this.files && this.files[0];
            if (!file || saving) return;
            if (source.indexOf('blob:') === 0) URL.revokeObjectURL(source);
            source = URL.createObjectURL(file);
            fit(source, true);
            this.value = '';
        });
        fit(source, false);
    }
    crop('#croppieLogo', '#logo', 'logoImgBase64', 250, 70, 500, 140);
    crop('#croppieFavicon', '#favicon', 'faviconBase64', 180, 180, 512, 512);

    var health = $('#configurationHealthCheck');
    var healthLoading = false;
    var healthLoaded = false;
    function loadHealth() {
        if (!health.length || healthLoading || healthLoaded) return;
        healthLoading = true;
        health.attr('aria-busy', 'true');
        health.find('.health-check-status').text(__('Running diagnostics. You can continue editing in the other tabs.'));
        health.find('button').prop('disabled', true);
        $.ajax({url: health.attr('data-url'), dataType: 'html', timeout: 180000}).done(function (html) {
            // The fragment is the same admin-only PHP component used by the admin page.
            if (html.indexOf('id="healthCheck"') === -1) {
                health.find('.health-check-status').text(__('Could not load health check. Please try again.'));
                return;
            }
            health.html(html);
            healthLoaded = true;
        }).fail(function () {
            health.find('.health-check-status').text(__('Could not load health check. Please try again.'));
        }).always(function () {
            healthLoading = false;
            health.attr('aria-busy', 'false');
            health.find('button').prop('disabled', false);
        });
    }
    form.on('shown.bs.tab', 'a[href="#tabCompatibility"]', loadHealth);
    health.on('click', '.health-check-retry', loadHealth);

    $('#smtpSecure').on('change', function () {
        var port = $('#smtpPort');
        if (['', '0', '25', '465', '587'].indexOf(String(port.val())) !== -1) {
            port.val($(this).val() === 'ssl' ? '465' : ($(this).val() === 'tls' ? '587' : '25'));
        }
    });
    $('#testEmail').on('click', function () {
        var button = $(this);
        if (button.prop('disabled') || saving) return;
        button.prop('disabled', true);
        modal.showPleaseWait();
        $.ajax({url: webSiteRootURL + 'objects/sendEmail.json.php', type: 'post', dataType: 'json', timeout: 60000,
            data: {captcha: $('#captchaText').val(), first_name: 'Your Site test',
                email: form.attr('data-saved-email'), website: 'www.avideo.com', comment: 'Teste of comment', isTest: 1},
            success: function (response) {
                if (response && !response.error) avideoAlertSuccess(__('Your message has been sent!'));
                else avideoAlertError(__('Your message could not be sent!'));
            },
            error: function () { avideoAlertError(__('Your message could not be sent!')); },
            complete: function () { button.prop('disabled', false); modal.hidePleaseWait(); }
        });
    });
    form.on('submit', function (event) {
        event.preventDefault();
        if (saving) return;
        var invalid = form.find(':invalid').first();
        if (invalid.length) {
            form.find('a[href="#' + invalid.closest('.tab-pane').attr('id') + '"]').tab('show');
            invalid[0].reportValidity();
            return;
        }
        if (crops.some(function (state) { return state.dirty && (state.pending || !state.ready); })) {
            $('#tabRegularLink').tab('show');
            message(__('Wait for the image to finish loading, or choose another file if it failed.'), true);
            return;
        }
        var data = {};
        var fields = {webSiteTitle: 'inputWebSiteTitle', description: 'inputWebSiteDescription', language: 'inputLanguage',
            contactEmail: 'inputEmail', authCanUploadVideos: 'authCanUploadVideos', authCanViewChart: 'authCanViewChart',
            authCanComment: 'authCanComment', head: 'head', adsense: 'adsense', session_timeout: 'session_timeout',
            smtpSecure: 'smtpSecure', smtpHost: 'smtpHost', smtpUsername: 'smtpUsername', smtpPassword: 'smtpPassword',
            smtpPort: 'smtpPort', encoder_url: 'encoder_url'};
        Object.keys(fields).forEach(function (key) {
            var input = $('#' + fields[key]);
            if (input.length) data[key] = input.val();
        });
        var checks = {disable_analytics: 'disable_analytics', allow_download: 'allow_download', autoplay: 'autoplaySwitch',
            smtp: 'enableSmtp', smtpAuth: 'enableSmtpAuth'};
        Object.keys(checks).forEach(function (key) {
            var input = $('#' + checks[key]);
            if (input.length) data[key] = input.prop('checked');
        });
        saving = true;
        form.attr('aria-busy', 'true');
        var enabled = form.find(':input:enabled');
        enabled.prop('disabled', true);
        form.find('.configuration-save i').attr('class', 'fas fa-spinner fa-spin');
        message(__('Saving'), false);
        var changed = crops.filter(function (state) { return state.dirty; });
        var previousTab = form.find('.nav-tabs li.active a');
        if (changed.length) $('#tabRegularLink').tab('show');
        Promise.resolve().then(function () {
            return new Promise(function (resolve) { requestAnimationFrame(function () { requestAnimationFrame(resolve); }); });
        }).then(function () { return Promise.all(changed.map(function (state) {
            return state.element.croppie('result', {type: 'canvas', size: state.size}).then(function (result) {
                data[state.key] = result;
            });
        })); }).then(function () {
            return $.ajax({url: webSiteRootURL + 'objects/configurationUpdate.json.php', type: 'post',
                dataType: 'json', timeout: 120000, data: data});
        }).then(function (response) {
            if (!response || String(response.status) !== '1' || response.error) {
                if (response && String(response.status) === '1' && response.error) {
                    var partial = __('Settings saved, but an image could not be saved. Please try again.');
                    message(partial, true);
                    avideoAlertError(partial);
                } else failure();
                return;
            }
            changed.forEach(function (state) { state.dirty = false; });
            savedValues = fieldValues();
            status.removeData('unsaved');
            form.attr('data-saved-email', data.contactEmail);
            message(response.warning || __('Your configurations has been updated!'), !!response.warning);
            if (response.warning) avideoAlertError(response.warning);
            else avideoAlertSuccess(__('Your configurations has been updated!'));
        }).catch(failure).then(function () {
            enabled.prop('disabled', false);
            if (changed.length) previousTab.tab('show');
            release();
        });
    });
});
