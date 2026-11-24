/**
 * ADs editor (plugin/ADs/editor.php): page map, position selection, configured status and the
 * desktop top banner split. The upload/list functions stay in editor.php.
 */
var ADsEditor = (function () {
    // AVideo moves inline scripts to the end of the page, after this file: ADsEditorConfig is read on ready
    var config = {positions: {}, texts: {}};
    var texts = {};
    var selectedType = '';
    var savingSplit = false;

    function getPosition(type) {
        return config.positions[type] || null;
    }

    function getMapSlots() {
        return $('#ADsMap .ADsMapPage button.ADsMapSlot[data-type]');
    }

    function showMapPage(page) {
        $('#ADsMap').attr('data-page', page);
        $('#ADsMap .ADsMapPage').each(function () {
            $(this).toggleClass('hidden', $(this).data('page') !== page);
        });
        $('#ADsMap .ADsMapPageButton').each(function () {
            var pressed = $(this).data('page') === page;
            $(this).toggleClass('btn-primary', pressed)
                .toggleClass('btn-default', !pressed)
                .attr('aria-pressed', pressed ? 'true' : 'false');
        });
        highlight(selectedType, true);
    }

    /**
     * Marks where the selected position is on the map. While the split is on, the top banner is
     * shown by its halves; while it is off, a half is shown at the place of the whole banner.
     */
    function highlight(type, keepMapPage) {
        selectedType = type;
        $('#ADsPositionSelect').val(type);
        var $slots = getMapSlots();
        $slots.removeClass('btn-primary ADsMapSlotSelected').addClass('btn-default').removeAttr('aria-current');
        var position = getPosition(type);
        if (!position) {
            return;
        }
        var $targets = $slots.filter('[data-type="' + type + '"]');
        if (position.parent) {
            if (!config.splits[position.parent]) {
                $targets = $targets.add($slots.filter('[data-type="' + position.parent + '"]'));
            }
        } else if (config.splits[type]) {
            $targets = $targets.add($slots.filter('[data-parent="' + type + '"]'));
        }
        $targets.removeClass('btn-default').addClass('btn-primary ADsMapSlotSelected').attr('aria-current', 'true');
        $('.ADsSplitSwitcherButton').each(function () {
            var current = $(this).data('type') === type;
            $(this).toggleClass('btn-primary', current).toggleClass('btn-default', !current);
            if (current) {
                $(this).attr('aria-current', 'true');
            } else {
                $(this).removeAttr('aria-current');
            }
        });

        var page = $('#ADsMap').attr('data-page');
        if (!keepMapPage && position.map.length && position.map.indexOf(page) === -1) {
            showMapPage(position.map[0]);
        }
        updatePreview();
    }

    /**
     * Opens a position as a click on its list item does, then moves the focus to its settings.
     */
    function selectPosition(type, focusSelector) {
        var $link = $('#adsNav' + type);
        if (!$link.length) {
            return;
        }
        if (!$link.parent().hasClass('active')) {
            // a native click: an error in the inline onclick (restartForm) must not stop the tab change
            $link[0].click();
        }
        setTimeout(function () {
            var $focus = focusSelector ? $(focusSelector) : $('#adsTabs' + type + ' .ADsPositionTitle');
            $focus.first().trigger('focus');
        }, 200);
    }

    function renderStatus(type) {
        var position = getPosition(type);
        if (!position) {
            return;
        }
        var configured = position.images > 0 || !!position.code;
        $('.ADsNavStatus[data-type="' + type + '"]').each(function () {
            var $status = $(this).empty();
            if (position.images > 0) {
                $status.append($('<span class="badge"></span>').attr('title', texts.images)
                    .append('<i class="fas fa-image" aria-hidden="true"></i> ')
                    .append(document.createTextNode(position.images)), ' ');
            }
            if (position.code) {
                $status.append($('<span class="badge"></span>').attr('title', texts.adCode)
                    .append('<i class="fas fa-code" aria-hidden="true"></i>'), ' ');
            }
            if (!configured) {
                $status.append($('<span class="text-muted ADsStatusEmpty"></span>').text(texts.empty));
            }
        });
        var label = position.name + ', ' + position.width + 'x' + position.height + ', '
            + (configured ? texts.configured : texts.empty);
        getMapSlots().filter('[data-type="' + type + '"]')
            .toggleClass('ADsMapSlotConfigured', configured)
            .toggleClass('ADsMapSlotEmpty', !configured)
            .attr('aria-label', label);
    }

    function setImages(type, count, firstImage) {
        var position = getPosition(type);
        if (position) {
            position.images = parseInt(count, 10) || 0;
            $('#SaveOrderMetadata' + type).closest('.ADsImageMetadata').toggleClass('hidden', !position.images);
            position.firstImage = position.images ? (firstImage || '') : '';
            $('.ADsThumb[data-type="' + type + '"]').each(function () {
                if (position.firstImage) {
                    $(this).attr('src', position.firstImage).removeClass('hidden');
                } else {
                    $(this).addClass('hidden').attr('src', '');
                }
            });
            renderStatus(type);
        }
    }

    function setCode(type, hasCode) {
        var position = getPosition(type);
        if (position) {
            position.code = !!hasCode;
            $('.ADsImageCodeNotice[data-type="' + type + '"]').toggleClass('hidden', !position.code);
            renderStatus(type);
        }
    }

    function setSplitInputs(type, enabled) {
        $('.ADsSplitModeInput[data-type="' + type + '"]').each(function () {
            $(this).prop('checked', $(this).val() === (enabled ? 'split' : 'single'));
        });
    }

    function setSplit(type, enabled) {
        config.splits[type] = enabled;
        setSplitInputs(type, enabled);
        $('[data-split-parent="' + type + '"]').toggleClass('ADsSplitOn', enabled);
        updatePositionChoices();
        highlight(selectedType);
    }

    function updatePositionChoices() {
        $.each(config.positions, function (type, position) {
            $('#ADsPositionSelect option[value="' + type + '"]').prop('disabled', !!position.parent && !config.splits[position.parent]);
        });
    }

    function isPreviewOpen() {
        return $('#ADsPreviewBody').length > 0 && !$('#ADsPreviewBody').hasClass('hidden');
    }

    function updatePreview() {
        var position = getPosition(selectedType);
        if (!position || !isPreviewOpen()) {
            return;
        }
        var pages = texts.pages || {};
        var context = [position.name, position.device === 'mobile' ? texts.mobile : texts.desktop];
        if (position.previewURL && pages[position.page]) {
            context.push(pages[position.page]);
        }
        $('#ADsPreviewContext').removeClass('hidden').text(context.join(' \u00b7 '));
        var $frame = $('#ADsPreviewFrame');
        $('#ADsPreviewUnavailable').toggleClass('hidden', !!position.previewURL);
        $frame.toggleClass('hidden', !position.previewURL);
        $('#ADsPreviewOpen').toggleClass('hidden', !position.previewURL).attr('href', position.previewURL || '#');
        $('#ADsPreviewReload').toggleClass('hidden', !position.previewURL);
        if (!position.previewURL) {
            return;
        }
        $frame.toggleClass('ADsPreviewFrameMobile', position.device === 'mobile');
        if ($frame.attr('src') !== position.previewURL) {
            $frame.attr('src', position.previewURL);
        }
    }

    function setPreviewOpen(open) {
        var $button = $('#ADsPreviewLoad');
        $('#ADsPreviewBody').toggleClass('hidden', !open);
        $button.attr('aria-expanded', open ? 'true' : 'false')
            .toggleClass('btn-primary', !open).toggleClass('btn-default', open);
        $button.find('.ADsPreviewLoadText').text(open ? $button.data('hide') : $button.data('show'));
        $button.find('.fas').toggleClass('fa-eye', !open).toggleClass('fa-times', open);
        if (open) {
            updatePreview();
            return;
        }
        // stop the page loaded in the frame
        $('#ADsPreviewFrame').attr('src', 'about:blank');
        $('#ADsPreviewContext, #ADsPreviewReload, #ADsPreviewOpen').addClass('hidden');
    }

    function refreshPreview() {
        var frame = document.getElementById('ADsPreviewFrame');
        if (frame && frame.contentWindow && isPreviewOpen() && frame.getAttribute('src') !== 'about:blank') {
            frame.contentWindow.location.reload();
        }
    }

    function saveCode(type) {
        var $input = $('#ADsCode' + type);
        var $button = $('.ADsSaveCode[data-type="' + type + '"]');
        if ($button.prop('disabled')) {
            return;
        }
        var code = $input.val();
        var data = {pluginName: 'ADs', pluginsList: type, globalToken: config.saveToken};
        data[type] = code;
        $button.prop('disabled', true);
        $input.prop('readonly', true);
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'admin/save.json.php', type: 'POST', dataType: 'json', data: data,
            success: function (response) {
                if (response && !response.error && response.save) {
                    setCode(type, !!code.trim() && !/^(false|null)$/i.test(code.trim()));
                    avideoToastSuccess(texts.saved);
                    refreshPreview();
                } else {
                    avideoAlertError(getErrorMessage(response));
                }
            },
            error: function () { avideoToastError(texts.error); },
            complete: function () {
                $button.prop('disabled', false);
                $input.prop('readonly', false);
                modal.hidePleaseWait();
            }
        });
    }

    function getErrorMessage(response) {
        if (response && response.msg) {
            return response.msg;
        }
        if (response && typeof response.error === 'string') {
            return response.error;
        }
        return texts.error;
    }

    // admin/save.json.php saves only the fields listed in pluginsList
    function saveSplit(type, enabled) {
        var $toggle = $('.ADsSplitModeInput[data-type="' + type + '"]');
        if (savingSplit) {
            setSplitInputs(type, !enabled);
            return;
        }
        if (!!config.splits[type] === enabled) {
            setSplitInputs(type, enabled);
            return;
        }
        savingSplit = true;
        $toggle.prop('disabled', true);
        $toggle.attr('aria-busy', 'true');
        modal.showPleaseWait();
        var data = {pluginName: 'ADs', pluginsList: type + 'Split', globalToken: config.saveToken};
        data[type + 'Split'] = enabled ? 'true' : 'false';
        $.ajax({
            url: webSiteRootURL + 'admin/save.json.php',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                if (response && !response.error && response.save) {
                    setSplit(type, enabled);
                    refreshPreview();
                    avideoToastSuccess(enabled ? texts.splitOn : ((texts.splitOffByType && texts.splitOffByType[type]) || texts.splitOff));
                } else {
                    setSplitInputs(type, !enabled);
                    avideoAlertError(getErrorMessage(response));
                }
            },
            error: function () {
                setSplitInputs(type, !enabled);
                avideoToastError(texts.error);
            },
            complete: function () {
                savingSplit = false;
                $toggle.removeAttr('aria-busy');
                $toggle.prop('disabled', false);
                modal.hidePleaseWait();
            }
        });
    }

    $(function () {
        config = window.ADsEditorConfig || config;
        texts = config.texts || {};
        config.splits = config.splits || {};
        updatePositionChoices();
        $('#ADsPositionSelect').on('change', function () {
            selectPosition($(this).val());
        });
        $('#ADsMap').on('click', '.ADsMapPageButton', function () {
            showMapPage($(this).data('page'));
        });
        $('#ADsMap').on('click', 'button.ADsMapSlot[data-type]', function () {
            selectPosition($(this).data('type'));
        });
        $('#ADsEditor').on('click', '.ADsSelectPosition', function () {
            selectPosition($(this).data('type'), $(this).data('focus'));
        });
        $('#ADsPositionsNav').on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
            // desktop and mobile are separate lists; Bootstrap only clears the active item of its own list
            var $item = $(this).parent();
            $('#ADsPositionsNav li.active').not($item).removeClass('active')
                .find('a[aria-selected]').attr('aria-selected', 'false');
            $(this).attr('aria-selected', 'true');
            restartForm($(this).data('type'));
            if (!window.ADsListsLoaded[$(this).data('type')]) {
                loadAdsList($(this).data('type'));
            }
            highlight($(this).data('type'));
        });
        $('#ADsPositionsNav').on('keydown', 'a[data-toggle="tab"]', function (event) {
            if (event.key === ' ') {
                event.preventDefault();
                this.click();
            }
        });
        $('#ADsEditor').on('change', '.ADsSplitModeInput', function () {
            if ($(this).is(':checked')) {
                saveSplit($(this).data('type'), $(this).val() === 'split');
            }
        });
        $('.ADsSaveCode').on('click', function () { saveCode($(this).data('type')); });
        $('#ADsPreviewLoad').on('click', function () {
            setPreviewOpen(!isPreviewOpen());
        });
        $('#ADsPreviewReload').on('click', refreshPreview);
        var $active = $('#ADsPositionsNav li.active > a');
        $('#ADsPositionsNav a[role="tab"]').attr('aria-selected', 'false');
        $active.attr('aria-selected', 'true');
        highlight($active.data('type'));
    });

    return {
        selectPosition: selectPosition,
        setImages: setImages,
        setCode: setCode,
        refreshPreview: refreshPreview,
        clearCode: function (type) { $('#ADsCode' + type).val(''); setCode(type, false); refreshPreview(); }
    };
})();
