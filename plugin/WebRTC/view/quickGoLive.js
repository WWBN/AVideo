$(document).ready(function() {
    var quickGoLiveButton = $("#quickGoLiveButton");

    if (quickGoLiveButton.length === 0) {
        return;
    }

    var originalButtonHtml = quickGoLiveButton.html();

    quickGoLiveButton.on("click", function(e) {
        e.preventDefault();
        var btn = $(this);

        // Prevent double-click
        if (btn.prop("disabled")) {
            return false;
        }

        btn.prop("disabled", true);
        btn.html('<i class="fa fa-spinner fa-spin"></i> <span class="hidden-md hidden-sm hidden-mdx">' + __('Starting...') + '</span>');

        $.ajax({
            url: webSiteRootURL + "plugin/WebRTC/quickGoLive.json.php",
            type: "POST",
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    avideoToastSuccess(__('Opening live studio...'));
                    // Add autoStart parameter to trigger automatic streaming
                    var url = response.liveUrl + (response.liveUrl.indexOf('?') === -1 ? '?' : '&') + 'autoStart=1';
                    // Open in fullscreen modal instead of redirecting
                    avideoModalIframeFull(url);
                    // Reset button after modal opens
                    btn.prop("disabled", false);
                    btn.html(originalButtonHtml);
                } else {
                    avideoToastError(response.message || __('Failed to start live session'));
                    btn.prop("disabled", false);
                    btn.html(originalButtonHtml);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = __('Error starting live session');
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {}
                avideoToastError(errorMsg);
                btn.prop("disabled", false);
                btn.html(originalButtonHtml);
            }
        });

        return false;
    });
});
