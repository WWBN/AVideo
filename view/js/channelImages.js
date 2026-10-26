/* global $, modal, avideoToastError, avideoResponse, webSiteRootURL */
function initializeChannelImageEditor(options) {
    var editor = $('#' + options.id);
    var crop = $('#' + options.cropID);
    var save = editor.find('.image-editor-save');
    var reset = editor.find('.image-editor-reset');
    var status = editor.find('.image-editor-status');
    var originalURL = options.url;
    var dirty = false;
    var ready = false;
    var pending = false;
    var lastPoints = '';
    var library = editor.find('.image-editor-library');
    var libraryButton = editor.find('[id^="library-btn"]');
    var libraryPending = false;
    library.attr('id', options.id + 'Library');
    libraryButton.attr({'aria-controls': options.id + 'Library', 'aria-expanded': 'false'});

    function update(message, error) {
        save.prop('disabled', pending || !ready || !dirty);
        reset.prop('disabled', pending || !dirty);
        editor.attr('aria-busy', pending ? 'true' : 'false');
        editor.find('.croppieDiv').toggleClass('image-editor-disabled', pending);
        editor.find('.croppieDiv a').attr('aria-disabled', pending ? 'true' : 'false').attr('tabindex', pending ? '-1' : '0');
        editor.find('.cr-slider, .image-editor-library button').prop('disabled', pending);
        status.text(message).toggleClass('text-danger', !!error).toggleClass('text-success', message === options.messages.saved).toggleClass('text-muted', !error && message !== options.messages.saved);
    }
    crop.on('image-editor:loading', function (event, changed) { ready = false; dirty = dirty || changed; update(options.messages.loading); });
    crop.on('image-editor:ready', function (event, changed) {
        ready = true;
        dirty = dirty || changed;
        lastPoints = JSON.stringify(crop.croppie('get').points);
        if (options.type === 'art') {
            var viewport = crop.find('.cr-viewport');
            if (!viewport.find('.image-editor-safe-area').length) {
                $('<span>', {'class': 'image-editor-safe-area', 'aria-hidden': 'true'})
                    .css({width: options.safeWidth / options.width * 100 + '%', height: options.safeHeight / options.height * 100 + '%'})
                    .appendTo(viewport);
            }
        }
        update(dirty ? options.messages.changed : options.messages.ready);
    });
    crop.on('image-editor:invalid', function () { update(options.messages.invalid, true); avideoToastError(options.messages.invalid); });
    crop.on('image-editor:error', function () { ready = false; dirty = true; update(options.messages.loadError, true); avideoToastError(options.messages.loadError); });
    crop.on('update.croppie', function () {
        var points = JSON.stringify(crop.croppie('get').points);
        if (ready && !pending && points !== lastPoints) {
            lastPoints = points;
            dirty = true;
            update(options.messages.changed);
        }
    });
    function closeLibrary() {
        library.addClass('hidden');
        libraryButton.attr('aria-expanded', 'false');
    }
    editor.find('.image-editor-library-close').on('click', function () { closeLibrary(); libraryButton.trigger('focus'); });
    crop.on('image-editor:library', function () {
        if (pending || libraryPending) { return; }
        if (!library.hasClass('hidden')) { closeLibrary(); return; }
        library.removeClass('hidden');
        libraryButton.attr('aria-expanded', 'true');
        var grid = library.find('.image-editor-library-grid').empty();
        var label = library.find('.image-editor-library-status').text(options.messages.loading);
        libraryPending = true;
        modal.showPleaseWait();
        $.ajax({
            url: options.libraryURL, dataType: 'json', timeout: 30000,
            success: function (images) {
                if (!Array.isArray(images)) { label.text(options.messages.loadError); return; }
                label.text(images.length ? options.messages.selectImage : options.messages.emptyLibrary);
                images.forEach(function (image) {
                    $('<button>', {type: 'button', 'class': 'btn btn-default image-editor-library-item', 'aria-label': image.filename})
                        .append($('<img>', {src: image.url, alt: image.filename, loading: 'lazy'}))
                        .on('click', function () {
                            closeLibrary();
                            window[options.restart](image.url);
                        }).appendTo(grid);
                });
            },
            error: function () { label.text(options.messages.loadError); },
            complete: function () { libraryPending = false; modal.hidePleaseWait(); }
        });
    });
    reset.on('click', function () {
        if (pending) { return; }
        dirty = false;
        window[options.restart](originalURL, false);
    });
    save.on('click', function () {
        if (pending || !ready || !dirty) { return; }
        pending = true;
        update(options.messages.saving);
        modal.showPleaseWait();
        function finish() { pending = false; update(status.text(), status.hasClass('text-danger')); modal.hidePleaseWait(); }
        var resultOptions = {type: 'base64', size: {width: options.width, height: options.height}, format: 'png'};
        if (options.type === 'art') {
            resultOptions.format = 'jpeg';
            resultOptions.quality = 0.95;
            resultOptions.backgroundColor = '#fff';
        }
        crop.croppie('result', resultOptions).then(function (image) {
            if (options.maxPostBytes > 0 && $.param({image: image}).length > options.maxPostBytes) {
                update(options.messages.tooLarge, true);
                avideoToastError(options.messages.tooLarge);
                finish();
                return;
            }
            $.ajax({
                url: options.endpoint,
                type: 'POST',
                dataType: 'json',
                timeout: 120000,
                data: {image: image},
                success: function (response) {
                    avideoResponse(response);
                    if (response && response.error === false) {
                        dirty = false;
                        crop.trigger('image-editor:saved');
                        // The photo endpoint returns a site-relative path; resolve it from the site root, not the page.
                        originalURL = response.url ? new URL(response.url, webSiteRootURL).href : originalURL;
                        originalURL = new URL(originalURL, window.location.href);
                        originalURL.searchParams.set('updated', Date.now());
                        originalURL = originalURL.href;
                        update(options.messages.saved);
                        if (options.type === 'photo' && response.previewURL) {
                            var photoPath = new URL(response.url, webSiteRootURL).pathname;
                            $('img').filter(function () {
                                // Croppie's own image must keep the uncropped source or the next save crops the crop.
                                if ($.contains(editor[0], this)) { return false; }
                                return new URL(this.src, window.location.href).pathname === photoPath || $(this).closest('#rightProfileButton, .rightProfileBtnGroup').length;
                            }).attr('src', response.previewURL + '&updated=' + Date.now());
                        }
                    } else {
                        update(response && response.msg ? response.msg : options.messages.error, true);
                    }
                },
                error: function () { update(options.messages.error, true); avideoToastError(options.messages.error); },
                complete: finish
            });
        }).catch(function () { update(options.messages.error, true); avideoToastError(options.messages.error); finish(); });
    });
    window[options.restart](originalURL, false);
}
