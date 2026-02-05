<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';
if (!User::isLogged()) {
    gotToLoginAndComeBackHere();
}

$activation = getActivationCode();
$expiresIn = $activation['expires'] - time();

// Full layout
$_page = new Page(array('Activate'));
?>
<style>
.activate-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.activate-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 20px;
    padding: 50px;
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    color: #fff;
}

.activate-icon {
    font-size: 4em;
    margin-bottom: 20px;
    color: #e74c3c;
}

.activate-title {
    font-size: 1.8em;
    margin-bottom: 10px;
    font-weight: 300;
}

.activate-subtitle {
    color: #888;
    margin-bottom: 30px;
    font-size: 1.1em;
}

.activate-code {
    font-size: 4em;
    font-weight: bold;
    letter-spacing: 0.15em;
    color: #fff;
    background: rgba(255,255,255,0.1);
    border-radius: 15px;
    padding: 30px 40px;
    margin: 30px 0;
    font-family: 'Courier New', monospace;
    display: inline-block;
    border: 2px dashed rgba(255,255,255,0.2);
    user-select: all;
    cursor: pointer;
}

.activate-code:hover {
    background: rgba(255,255,255,0.15);
    border-color: #e74c3c;
}

.activate-timer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #888;
    font-size: 1.1em;
    margin-bottom: 30px;
}

.activate-timer i {
    color: #e74c3c;
}

.activate-timer #countdown {
    font-weight: bold;
    color: #fff;
    min-width: 50px;
}

.activate-steps {
    text-align: left;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 25px 30px;
    margin-top: 30px;
}

.activate-steps h4 {
    margin: 0 0 20px 0;
    color: #e74c3c;
    font-weight: 500;
}

.activate-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 15px;
    color: #ccc;
}

.activate-step:last-child {
    margin-bottom: 0;
}

.activate-step-number {
    background: #e74c3c;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9em;
    flex-shrink: 0;
}

.activate-step-text {
    padding-top: 3px;
}

.activate-user {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    color: #888;
}

.activate-user img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.activate-user strong {
    color: #fff;
}

.activate-refresh {
    margin-top: 20px;
}

.activate-refresh .btn {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: #888;
    padding: 10px 25px;
    border-radius: 8px;
    transition: all 0.3s;
}

.activate-refresh .btn:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-color: rgba(255,255,255,0.3);
}

.activate-expired {
    display: none;
    padding: 20px;
    background: rgba(231, 76, 60, 0.2);
    border-radius: 10px;
    color: #e74c3c;
    margin-bottom: 20px;
}

.activate-expired.show {
    display: block;
}

.activate-code.expired {
    opacity: 0.5;
    text-decoration: line-through;
}

@media (max-width: 600px) {
    .activate-card {
        padding: 30px 20px;
    }
    .activate-code {
        font-size: 2.5em;
        padding: 20px 25px;
        letter-spacing: 0.1em;
    }
    .activate-title {
        font-size: 1.4em;
    }
}
</style>

<div class="activate-container">
    <div class="activate-card">
        <div class="activate-icon">
            <i class="fas fa-tv"></i>
        </div>

        <h1 class="activate-title"><?php echo __("Link Your TV"); ?></h1>
        <p class="activate-subtitle"><?php echo __("Use this code to sign in on your TV or streaming device"); ?></p>

        <div class="activate-expired" id="expiredMessage">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo __("Code expired! Click the button below to generate a new one."); ?>
        </div>

        <div class="activate-code" id="activationCode" title="<?php echo __("Click to copy"); ?>" onclick="copyCode()">
            <?php echo $activation['code']; ?>
        </div>

        <div class="activate-timer">
            <i class="fas fa-clock"></i>
            <span><?php echo __("Code expires in"); ?></span>
            <span id="countdown"><?php echo gmdate("i:s", $expiresIn); ?></span>
        </div>

        <div class="activate-steps">
            <h4><i class="fas fa-list-ol"></i> <?php echo __("How to connect"); ?></h4>

            <div class="activate-step">
                <span class="activate-step-number">1</span>
                <span class="activate-step-text"><?php echo __("Open the AVideo app on your TV or streaming device"); ?></span>
            </div>

            <div class="activate-step">
                <span class="activate-step-number">2</span>
                <span class="activate-step-text"><?php echo __("Select \"Sign in\" and choose \"Sign in with code\""); ?></span>
            </div>

            <div class="activate-step">
                <span class="activate-step-number">3</span>
                <span class="activate-step-text"><?php echo __("Enter the code shown above using your TV remote"); ?></span>
            </div>

            <div class="activate-step">
                <span class="activate-step-number">4</span>
                <span class="activate-step-text"><?php echo __("Your TV will be linked automatically"); ?></span>
            </div>
        </div>

        <div class="activate-user">
            <img src="<?php echo User::getPhoto(); ?>" alt="">
            <span><?php echo __("Signing in as"); ?></span>
            <strong><?php echo User::getNameIdentification(); ?></strong>
        </div>

        <div class="activate-refresh">
            <button class="btn" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> <?php echo __("Generate new code"); ?>
            </button>
        </div>
    </div>
</div>

<script>
var expiresIn = <?php echo $expiresIn; ?>;

function updateCountdown() {
    if (expiresIn <= 0) {
        document.getElementById('countdown').textContent = '00:00';
        document.getElementById('expiredMessage').classList.add('show');
        document.getElementById('activationCode').classList.add('expired');
        return;
    }

    var minutes = Math.floor(expiresIn / 60);
    var seconds = expiresIn % 60;
    document.getElementById('countdown').textContent =
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

    expiresIn--;
    setTimeout(updateCountdown, 1000);
}

function copyCode() {
    var code = '<?php echo $activation['code']; ?>';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(function() {
            avideoToast('<?php echo __("Code copied to clipboard!"); ?>');
        });
    }
}

$(document).ready(function() {
    updateCountdown();
});
</script>

<?php
$_page->print();
?>
