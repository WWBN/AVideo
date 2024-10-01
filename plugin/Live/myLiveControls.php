<?php
$class = '';
if (!empty($live_key)) {
    $bodyClass = "body_live_{$live_servers_id}_{$live_key}";
    $class = "ShowIf_{$live_servers_id}_{$live_key}";
    $hideClass = "HideIf_{$live_servers_id}_{$live_key}";
    echo "<style>.{$class} {display: none;}body.{$bodyClass} .{$class} {display: block !important;}body.{$bodyClass} .{$hideClass} {display: none !important;}</style>";
}

$row = LiveTransmition::keyExists($live_key);
if(empty($row)){
    echo '<!-- Live key not found myLiveControls.php -->';
    return;
}
if(User::getId() != $row['users_id'] && !User::isAdmin()){
    echo '<!-- This live does not belong to you myLiveControls.php -->';
    return;
}
?>
<style>
    .currentUrlTextContainer{
        position: absolute; 
        right: 5px; 
        bottom: 1px; 
        font-size: 0.7em;
    }
    .currentUrlTextTopContainer{
        margin-top: -6px; 
        margin-bottom: 6px;
    }

    #sendViewersControls button{
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
                <div  class="currentUrlTextContainer">
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
            <div class="modal-body">
                <div class="list-group" id="urlList">
                </div>
                <hr>
                <div class="form-group">
                    <input type="text" class="form-control" id="customUrl" placeholder="https://example.com/custom">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-block" id="saveUrlBtn">
                    <i class="fas fa-save"></i> <?php echo __('Save'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var viewerUrl = '';

    $(document).ready(function() {
        // Disable Send Viewers button initially// Load viewerUrl from the cookie if it exists
        if (Cookies.get('viewerUrl')) {
            viewerUrl = Cookies.get('viewerUrl');
            $('#customUrl').val(viewerUrl); // Update the displayed URL
            $('.currentUrlText').text(viewerUrl); // Update the displayed URL
            $('#sendViewersBtn').prop('disabled', false); // Enable Send Viewers button if URL is present
        }
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

            // Determine which URL to use
            if (customUrl !== '') {
                if (!validURL(customUrl)) {
                    avideoAlertError(__("You need to enter a valid URL!"));
                    return;
                }
                viewerUrl = customUrl;
            } else if (selectedUrl !== '') {
                viewerUrl = selectedUrl;
            } else {
                avideoAlertError(__("Please select or enter a URL."));
                return;
            }

            // Save viewerUrl to cookie with expiration of 365 days
            Cookies.set('viewerUrl', viewerUrl, {
                path: '/',
                expires: 365
            });

            // Save and update the UI
            console.log("Viewer URL saved:", viewerUrl);
            $('#sendViewersBtn').prop('disabled', false); // Enable Send Viewers button
            avideoToastSuccess(__("URL saved! Now you can redirect viewers to the specified URL."));
            $('.currentUrlText').text(viewerUrl); // Update the displayed URL

            // Close the modal
            $('#urlModal').modal('hide');
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
        console.log("Sending viewers to:", viewerUrl);
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/sendViewers.json.php',
            type: 'POST',
            data: {
                'viewerUrl': viewerUrl,
                'live_key': '<?php echo $live_key; ?>',
                'live_servers_id': '<?php echo $live_servers_id;?>'
            },
            success: function(response) {
                console.log("Send viewers response:", response);
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoAlertSuccess(__("Viewers sent successfully!"));
                    // Optionally, disable the button after sending
                    $('#sendViewersBtn').prop('disabled', true);
                }
            },
            error: function() {
                avideoAlertError(__("An error occurred while sending viewers."));
            }
        });
    }
</script>