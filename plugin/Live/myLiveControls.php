<?php
$class = '';
if (!empty($live_key)) {
    $bodyClass = "body_live_{$live_servers_id}_{$live_key}";
    $class = "ShowIf_{$live_servers_id}_{$live_key}";
    $hideClass = "HideIf_{$live_servers_id}_{$live_key}";
    echo "<style>.{$class} {display: none;}body.{$bodyClass} .{$class} {display: block !important;}body.{$bodyClass} .{$hideClass} {display: none !important;}</style>";
}

$row = LiveTransmition::keyExists($live_key);
if (empty($row)) {
    echo '<!-- Live key not found myLiveControls.php -->';
    return;
}
if (User::getId() != $row['users_id'] && !User::isAdmin()) {
    echo '<!-- This live does not belong to you myLiveControls.php -->';
    return;
}

$custom = User::getRedirectCustomUrl(User::getId());
?>
<style>
    .currentUrlTextContainer {
        position: absolute;
        right: 5px;
        bottom: 1px;
        font-size: 0.7em;
    }

    .currentUrlTextTopContainer {
        margin-top: -6px;
        margin-bottom: 6px;
    }

    #sendViewersControls button {
        position: relative;
    }
</style>
<div class="pull-right" id="sendViewersControls">

    <div class="btn-group pull-right " role="group">
        <?php
        echo getTourHelpButton('plugin/Live/myLiveControls.json', 'btn btn-default', false);
        ?>
        <button id="promptForUrlBtn" onclick="promptForViewerUrl()" class="btn btn-primary">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div id="sendViewersButtons" style="display: inline-block; ;">
            <button id="sendViewersBtn"
                onclick="confirmSendViewers()"
                class="btn btn-success <?php echo $class; ?> " disabled
                style="overflow: hidden;"
                data-toggle="tooltip"
                title="<?php echo __('Send all viewers from your livestream to the specified URL'); ?>">
                <div class="currentUrlTextTopContainer">
                    <?php echo __("Redirect Viewers"); ?>
                    <i class="fa-solid fa-diamond-turn-right"></i>
                </div>
                <!-- Display the current URL -->
                <div class="currentUrlTextContainer">
                    <span class="currentUrlText"><?php echo __("No URL set"); ?></span>
                </div>
            </button>
            <!-- Placeholder content for when the user is not online -->

            <button class="btn btn-primary <?php echo $hideClass; ?> " disabled data-toggle="tooltip" title="<?php echo __("Start a live stream to use the controls"); ?>">
                <div class="currentUrlTextTopContainer">
                    <?php echo __("You are not live right now"); ?>
                </div>
                <!-- Display the current URL -->
                <div class="currentUrlTextContainer">
                    <span class="currentUrlText"><?php echo __("No URL set"); ?></span>
                </div>
            </button>
        </div>
    </div>


</div>

<!-- URL Selection Modal -->
<div class="modal fade" id="urlModal" tabindex="-1" role="dialog" aria-labelledby="urlModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" id="modalBody">
                <div class="list-group" id="urlList" data-step="1" data-intro="This is the list of available live streams. Select a stream to get its URL.">
                </div>
                <hr>
                <div class="form-group" id="autoRedirectGroup" data-step="2" data-intro="Check this box if you want to enable auto-redirect when the livestream ends.">
                    <input type="checkbox" id="autoRedirect" value="1">
                    <label for="autoRedirect"><?php echo __('Auto-redirect at the end of the livestream'); ?></label>
                </div>
                <div class="form-group" id="customUrlGroup" data-step="3" data-intro="Enter the custom URL here to manually redirect your viewers to a specific page.">
                    <input type="text" class="form-control" id="customUrl" placeholder="https://example.com/custom">
                </div>
                <div class="form-group" id="customMessageGroup" data-step="4" data-intro="Write a custom message that viewers will see before they are redirected. If left empty, a default message will be displayed.">
                    <textarea class="form-control" id="customMessage" placeholder="<?php echo __('Enter a custom message for your viewers'); ?>"><?php echo __('I hope you enjoyed the stream! As a bonus, we\'ll be sending you to a special page now'); ?>.</textarea>
                </div>
            </div>
            <div class="modal-footer" data-step="5" data-intro="Click here to save your changes.">
                <div class="row">
                    <div class="col-sm-4">
                        <?php
                        echo getTourHelpButton('plugin/Live/myLiveControls.modal.json', 'btn btn-default btn-block');
                        ?>
                    </div>
                    <div class="col-sm-8" id="modalFooterSave" >
                        <button type="button" class="btn btn-success btn-block" id="saveUrlBtn">
                            <i class="fas fa-save"></i> <?php echo __('Save'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var viewerUrl = <?php echo json_encode($custom['url']); ?>;
    var customMessage = <?php echo json_encode($custom['msg']); ?>;
    var autoRedirect = <?php echo json_encode($custom['autoRedirect']); ?>; // Default is false

    function updateRedirectInfoFromVariables() {
        if (!empty(viewerUrl)) {
            $('#customUrl').val(viewerUrl); // Update the displayed URL
            $('#customMessage').val(customMessage); // Update the displayed URL
            $('.currentUrlText').text(viewerUrl); // Update the displayed URL
            $('#sendViewersBtn').prop('disabled', empty(viewerUrl)); // Enable Send Viewers button if URL is present
            $('#autoRedirect').prop('checked', autoRedirect);
        }
    }

    $(document).ready(function() {
        updateRedirectInfoFromVariables();
    });

    // Function to prompt for the viewer URL and store it
    function promptForViewerUrl() {
        // Clear previous list
        $('#urlList').empty();

        // Get all .stats_app elements and populate the modal
        $('.stats_app').each(function() {
            var url = $(this).find('a').attr('href'); // Get the URL from the anchor tag
            var imgSrc = $(this).find('img').attr('src'); // Get the image source
            var title = $(this).find('.media-heading').text().trim(); // Get the media heading text

            // Create a new list item for each URL
            var listItem = `
            <button type="button" class="list-group-item list-group-item-action">
                <div class="media">
                    <div class="media-left">
                        <img src="${imgSrc}" class="media-object" style="width: 50px; height: auto;">
                    </div>
                    <div class="media-body">
                        <strong class="media-heading">${title}</strong>
                        <p class="media-url">${url}</p>
                    </div>
                </div>
            </button>
        `;
            $('#urlList').append(listItem);
        });

        // Show the modal
        $('#urlModal').modal('show');

        // Handle URL list selection
        $('#urlList .list-group-item').click(function() {
            $('#urlList .list-group-item').removeClass('active');
            $(this).addClass('active');

            // Get the selected URL and set it to the custom URL input field
            var selectedUrl = $(this).find('.media-url').text().trim();
            $('#customUrl').val(selectedUrl); // Fill the custom URL input with the selected URL
        });

        // Handle custom URL input focus
        $('#customUrl').on('focus', function() {
            $('#urlList .list-group-item').removeClass('active'); // Deselect all list items
        });

        // Handle Save URL button click
        $('#saveUrlBtn').click(function() {
            var selectedUrl = $('#urlList .list-group-item.active').find('.media-url').text().trim();
            var customUrl = $('#customUrl').val().trim();
            var _customMessage = $('#customMessage').val().trim();

            // Determine which URL to use
            if (customUrl !== '') {
                if (!validURL(customUrl)) {
                    avideoAlertError(__("You need to enter a valid URL!"));
                    return;
                }
                viewerUrl = customUrl;
                customMessage = _customMessage;
            } else if (selectedUrl !== '') {
                viewerUrl = selectedUrl;
                customMessage = _customMessage;
            } else {
                avideoAlertError(__("Please select or enter a URL."));
                return;
            }
            saveCustomURL(viewerUrl, customMessage);

        });
    }


    function saveCustomURL(viewerUrl, customMessage) {
        autoRedirect = $('#autoRedirect').is(':checked'); // Check if auto-redirect is enabled

        modal.showPleaseWait();
        // Save viewerUrl to database using AJAX
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/myLiveControls.save.json.php',
            type: 'POST',
            data: {
                'customUrl': viewerUrl,
                'customMessage': customMessage,
                'autoRedirect': autoRedirect
            },
            success: function(response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(__("URL saved! Now you can redirect viewers to the specified URL."));
                    $('#urlModal').modal('hide'); // Close the modal
                    viewerUrl = response.redirectCustomUrl;
                    customMessage = response.redirectCustomMessage;
                    autoRedirect = response.autoRedirect;
                    updateRedirectInfoFromVariables();
                }
            },
            error: function() {
                modal.hidePleaseWait();
                avideoAlertError(__("An error occurred while saving the URL."));
            }
        });
    }

    // Function to confirm before sending viewers
    async function confirmSendViewers() {
        if (!validURL(viewerUrl)) {
            avideoAlertError(__("Invalid URL"));
            return;
        }
        var confirmed = await avideoConfirm(__("Are you sure you want to redirect all viewers to the specified URL? This action will also stop the live stream."));
        if (confirmed) {
            sendViewers();
        }
    }


    // Function to send viewers to the stored URL
    function sendViewers() {
        if (empty(my_current_live_key)) {
            avideoAlertError(__("Live key not found. It seems your livestream may not be active anymore. Please make sure to redirect viewers before ending the live stream."));
            return;
        }
        if (!validURL(viewerUrl)) {
            avideoAlertError(__("Invalid URL"));
            return;
        }
        modal.showPleaseWait();
        console.log("Sending viewers to:", viewerUrl);
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/sendViewers.json.php',
            type: 'POST',
            data: {
                'viewerUrl': viewerUrl,
                'customMessage': $('#customMessage').val(),
                'live_key': '<?php echo $live_key; ?>',
                'live_servers_id': '<?php echo $live_servers_id; ?>'
            },
            success: function(response) {
                console.log("Send viewers response:", response);
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(__("Viewers sent successfully!"));
                    // Optionally, disable the button after sending
                    $('#sendViewersBtn').prop('disabled', true);
                }
            },
            error: function() {
                modal.hidePleaseWait();
                avideoAlertError(__("An error occurred while sending viewers."));
            }
        });
    }
</script>