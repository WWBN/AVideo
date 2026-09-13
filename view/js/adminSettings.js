(function ($) {
    'use strict';

    $(function () {
        function status(form, message, failed) {
            form.find('.admin-save-status').text(message).toggleClass('text-danger', !!failed)
                .toggleClass('text-success', !failed && message === adminSettingsMessages.saved);
        }

        $(document).on('submit.avideoAdmin', '.adminOptionsForm', function (event) {
            event.preventDefault();
            var form = $(this);
            if (form.data('saving')) {
                return;
            }
            var data = form.serialize() + '&globalToken=' + encodeURIComponent(adminSaveToken);
            var controls = form.find(':input:enabled');
            form.data('saving', true).attr('aria-busy', 'true');
            controls.prop('disabled', true);
            status(form, adminSettingsMessages.saving, false);
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'admin/save.json.php',
                data: data,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    var failed = !response || !!response.error || !response.save;
                    status(form, failed ? adminSettingsMessages.error : adminSettingsMessages.saved, failed);
                    if (failed) {
                        avideoToastError(adminSettingsMessages.error);
                    } else {
                        avideoToastSuccess(adminSettingsMessages.saved);
                    }
                },
                error: function () {
                    status(form, adminSettingsMessages.error, true);
                    avideoToastError(adminSettingsMessages.error);
                },
                complete: function () {
                    controls.prop('disabled', false);
                    form.removeData('saving').attr('aria-busy', 'false');
                    modal.hidePleaseWait();
                }
            });
        });

        $(document).on('change.avideoAdmin', '.pluginSwitch', function () {
            var toggle = $(this);
            var enabled = toggle.prop('checked');
            var peers = $('.pluginSwitch').filter(function () {
                return $(this).attr('uuid') === toggle.attr('uuid');
            });
            var controls = peers.filter(':enabled');
            controls.prop('disabled', true);
            modal.showPleaseWait();
            function failed() {
                peers.prop('checked', !enabled);
                avideoToastError(adminSettingsMessages.error);
            }
            $.ajax({
                url: webSiteRootURL + 'objects/pluginSwitch.json.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    uuid: toggle.attr('uuid'),
                    name: toggle.attr('name'),
                    dir: toggle.attr('name'),
                    enable: enabled,
                    globalToken: adminSaveToken
                },
                success: function (response) {
                    if (!response || response.error || !(Number(response.status) > 0)) {
                        failed();
                        return;
                    }
                    peers.prop('checked', enabled);
                    avideoToastSuccess(adminSettingsMessages.saved);
                },
                error: failed,
                complete: function () {
                    controls.prop('disabled', false);
                    modal.hidePleaseWait();
                }
            });
        });

        // Bootstrap 3 handles Enter on anchors; add Space for button-like toggles.
        $(document).on('keydown.avideoAdmin', '.admin-menu-toggle', function (event) {
            if (event.key === ' ') {
                event.preventDefault();
                $(this).trigger('click');
            }
        });
    });
})(jQuery);
