<?php
$videos_id = getVideos_id();
?>
<button class="btn btn-primary">
  <span class="glyphicon glyphicon-phone"></span> <?php echo __('Open in the app');  ?>
</button>

<script>
/* Attempt deep-link first, fallback keeps user on the web page.
   Works on iOS & Android mobile browsers that allow custom-scheme redirect. */

$('#open-in-app').on('click', function (e) {
  e.preventDefault();                         // stop immediate navigation
  var webURL   = this.href;                   // original link
  var deepLink = 'ypt://video?videos_id=<?php echo $videos_id; ?>'; // build dynamically if preciso
  var timeout  = 1200;                        // ms to decide fallback

  /* Create hidden iframe (Android < 5) or set window.location (iOS/modern) */
  var clickedAt = +new Date();
  window.location = deepLink;

  /* If after timeout the page hasn't switched, assume failure and stay */
  setTimeout(function () {
    if (+new Date() - clickedAt < timeout + 100) {
      window.location = webURL;               // graceful fallback
    }
  }, timeout);
});
</script>
