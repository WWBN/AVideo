<?php
require_once __DIR__ . '/../../videos/configuration.php';

$isCanceled = !empty($_GET['cancel']);
$type = $isCanceled ? 'cancel' : 'success';

$messages = [
    'success' => [
        'title' => 'Payment Successful',
        'icon' => 'fa-check-circle',
        'alert' => 'alert-success',
        'button' => 'btn-success',
        'progress' => 'progress-bar-success',
        'text' => 'Your payment has been processed successfully. This window will close automatically.'
    ],
    'cancel' => [
        'title' => 'Payment Cancelled',
        'icon' => 'fa-times-circle',
        'alert' => 'alert-warning',
        'button' => 'btn-warning',
        'progress' => 'progress-bar-warning',
        'text' => 'Your payment was not completed. You can try again or contact support if needed.'
    ]
];

$_page = new Page([$messages[$type]['title']]);
$_page->setIncludeNavbar(false);
$_page->setIncludeFooter(false);
?>
<style>
    .countdown {
        font-size: 16px;
        margin-top: 10px;
        color: #333;
    }
    .alert {
        font-size: 18px;
        padding: 30px;
    }
    .btn-close-now {
        margin-top: 15px;
    }
    .progress {
        margin-top: 15px;
        height: 20px;
    }
</style>
<div class="container" style="margin-top: 50px;">
    <div class="alert text-center <?php echo $messages[$type]['alert']; ?>">
        <h3>
            <i class="fa <?php echo $messages[$type]['icon']; ?>"></i>
            <?php echo $messages[$type]['title']; ?>
        </h3>

        <p><?php echo $messages[$type]['text']; ?></p>

        <p class="countdown">
            <i class="fa fa-clock-o"></i> Closing in <span id="countdown">10</span> seconds...
        </p>

        <div class="progress">
            <div id="progressBar" class="progress-bar <?php echo $messages[$type]['progress']; ?>" role="progressbar" style="width: 100%;">
            </div>
        </div>

        <button class="btn <?php echo $messages[$type]['button']; ?> btn-close-now" onclick="window.close();">
            <i class="fa fa-sign-out"></i> Close Now
        </button>
    </div>
</div>
<script>
    var seconds = 10;
    var total = seconds;

    function updateCountdown() {
        document.getElementById('countdown').innerText = seconds;
        var percent = (seconds / total) * 100;
        document.getElementById('progressBar').style.width = percent + '%';

        if (seconds <= 0) {
            window.close();
        }
        seconds--;
    }

    setInterval(updateCountdown, 1000);
    window.onload = updateCountdown;
</script>
<?php
$_page->print();
