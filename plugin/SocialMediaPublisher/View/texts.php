<?php
// translated texts for plugin/SocialMediaPublisher/script.js, printed once per page (include_once)
$socialPublisherText = array(
    'connected' => __('Connected', true),
    'reconnectNeeded' => __('Reconnection needed', true),
    'reconnectHelp' => __('This connection expired. Connect the account again to keep publishing.', true),
    'publish' => __('Publish', true),
    'reconnect' => __('Reconnect', true),
    'renew' => __('Renew', true),
    'disconnect' => __('Disconnect', true),
    'disconnectConfirm' => __('Disconnect this account? You can connect it again at any time.', true),
    'noAccounts' => __('No account connected yet. Use the buttons above to connect one.', true),
    'noHistory' => __('This video was not published yet.', true),
    'titleRequired' => __('Please enter a title.', true),
    'publishingNow' => __('Another publication is being sent. Please wait.', true),
    'published' => __('The video was published.', true),
    'publishFailed' => __('The video could not be published. Please try again later.', true),
    'serverUnreachable' => __('We could not reach the server. Check your internet connection and try again.', true),
    'openOnYouTube' => __('Open on YouTube', true),
    'open' => __('Open', true),
    'resume' => __('Resume', true),
    'tryAgain' => __('Try again', true),
    'cancel' => __('Cancel upload', true),
    'cancelConfirm' => __('Cancel this upload? Nothing will be published on YouTube.', true),
    'of' => __('of', true),
    'connectionCanceled' => __('The connection window was closed before the authorization finished. No account was connected.', true),
    'connectionSaved' => __('Account connected.', true),
    'connectionNotSaved' => __('The account could not be connected. Please try again.', true),
    'categoriesUnavailable' => __('The YouTube categories could not be loaded, YouTube will choose one.', true),
    'privacy' => array(
        'private' => __('Private', true),
        'unlisted' => __('Unlisted', true),
        'public' => __('Public', true),
    ),
);
?>
<script>
    var socialPublisherText = <?php echo json_encode($socialPublisherText); ?>;
</script>
