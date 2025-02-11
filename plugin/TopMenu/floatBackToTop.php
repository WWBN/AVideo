<!-- Back to Top Button -->
<div class="floatingRightBottom hideOnPlayShorts">
  <button id="back-to-top" class="btn btn-primary circle-menu" title="Back to Top">
    <i class="fa fa-arrow-up fa-2x"></i>
  </button>
</div>
<script>
  // jQuery to Show/Hide Back to Top Button
  $(document).ready(function () {
    // Show the button when scrolling down 100px
    $(window).scroll(function () {
      if ($(this).scrollTop() > 100) {
        $('#back-to-top').fadeIn();
        $('body').addClass('back-to-top-on');
      } else {
        $('#back-to-top').fadeOut();
        $('body').removeClass('back-to-top-on');
      }
    });

    // Scroll to top when the button is clicked
    $('#back-to-top').click(function () {
      $('html, body').animate({ scrollTop: 0 }, 500); // 500ms smooth scroll
      return false;
    });
  });
</script>
