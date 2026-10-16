<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (empty(CustomizeAdvanced::getLocalEncoderURL())) {
    http_response_code(404);
    exit;
}

$global['doNotLoadPlayer'] = 1;
$_page = new Page(array('Local Encoder'));
$_page->setExtraScripts(array('plugin/CustomizeAdvanced/localEncoderLauncher.js'));
$metaDescription = __('Prepare videos on your computer and upload them to your AVideo site.');
$downloads = array(
    array(__('Windows 10/11 (64-bit)'), 'LocalEncoder-windows-x64.zip', 'fab fa-windows'),
    array(__('macOS - Apple Silicon (M-series)'), 'LocalEncoder-macos-arm64.zip', 'fab fa-apple'),
    array(__('macOS - Intel'), 'LocalEncoder-macos-x64.zip', 'fab fa-apple'),
    array(__('Linux - Intel / AMD (x64)'), 'LocalEncoder-linux-x64.tar.gz', 'fab fa-linux'),
    array(__('Linux - ARM64'), 'LocalEncoder-linux-arm64.tar.gz', 'fab fa-linux'),
);
?>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-desktop" aria-hidden="true"></i> Local Encoder</h1>
        <p class="lead"><?php echo __('Prepare videos on your computer and upload them to your AVideo site.'); ?></p>
    </div>
    <div class="panel panel-primary" id="local-encoder-launcher">
        <div class="panel-heading"><h2 class="panel-title"><?php echo __('Open Local Encoder'); ?></h2></div>
        <div class="panel-body">
            <p data-status role="status" aria-live="polite"><?php echo __('Checking whether Local Encoder is running on this computer...'); ?></p>
            <p><a data-control class="hidden"><?php echo __('Open the running control page'); ?></a></p>
            <p>
                <a data-open class="btn btn-primary disabled" aria-disabled="true"><?php echo __('Open Local Encoder'); ?></a>
                <button data-check type="button" class="btn btn-default"><?php echo __('Check again'); ?></button>
                <a class="btn btn-default" href="#local-encoder-downloads"><i class="fas fa-download" aria-hidden="true"></i> <?php echo __('Download Local Encoder'); ?></a>
            </p>
            <p class="help-block"><?php echo __('Already downloaded it? Extract the whole package and open LocalEncoder once to enable this button. Accept your browser opening prompt if it appears.'); ?></p>
            <p class="help-block"><?php echo __('First time using it? Download the program below. If your browser cannot confirm that it is running, you can still open it from your computer.'); ?></p>
        </div>
    </div>
    <script type="application/json" id="local-encoder-config"><?php echo json_encode(array(
        'site' => $global['webSiteRootURL'],
        'text' => array(
            'invalid' => __('This video link is not valid. Open this page again from your AVideo site.'),
            'ready' => __('Local Encoder is running on this computer. You can open it now.'),
            'open' => __('Open Local Encoder'),
            'opening' => __('Opening Local Encoder... Accept the browser prompt. The application may open in another tab.'),
            'checking' => __('Checking whether Local Encoder is running on this computer...'),
            'unknown' => __('We could not confirm that Local Encoder is running. If you already have it, click Open Local Encoder or open the program manually, then Check again. Otherwise, download it below. Your browser may also be blocking the check.'),
            'computer' => __('Local Encoder runs on Windows, macOS and Linux. Open this page on the computer where you want to process videos.'),
        ),
    ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
    <div class="alert alert-info" role="note">
        <strong><?php echo __('Local Encoder complements your Web Encoder.'); ?></strong>
        <?php echo __('Keep a working Web Encoder configured. AVideo still uses it for live previews, video and timeline thumbnails, recordings, playlist-to-live processing and other server-side media services.'); ?>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="panel panel-default" id="local-encoder-downloads">
                <div class="panel-heading"><h2 class="panel-title"><?php echo __('Download for your computer'); ?></h2></div>
                <div class="panel-body">
                    <p><?php echo __('Choose your system below. These ready-to-use packages include the tools needed to process videos; a GitHub account, Python and a separate FFmpeg installation are not required.'); ?></p>
                    <div class="list-group">
                        <?php foreach ($downloads as $download) { ?>
                            <a class="list-group-item" href="<?php echo htmlspecialchars('https://github.com/YPT-ME/Local-Encoder/releases/latest/download/' . $download[1], ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="<?php echo htmlspecialchars($download[2], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                                <?php echo __('Download'); ?> — <?php echo $download[0]; ?>
                                <span class="badge"><i class="fas fa-download" aria-hidden="true"></i></span>
                            </a>
                        <?php } ?>
                    </div>
                    <p><?php echo __('On a Mac, open About This Mac: an Apple M-series chip means Apple Silicon; an Intel processor means Intel.'); ?></p>
                    <p><a href="https://github.com/YPT-ME/Local-Encoder/releases" target="_blank" rel="noopener noreferrer"><?php echo __('Release notes and checksums'); ?></a> · <a href="https://github.com/YPT-ME/Local-Encoder" target="_blank" rel="noopener noreferrer"><?php echo __('Project and installation help'); ?></a></p>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading"><h2 class="panel-title"><?php echo __('Why use Local Encoder?'); ?></h2></div>
                <div class="panel-body">
                    <ul>
                        <li><?php echo __('Encoding can be faster with your processor or a supported graphics card, without waiting in a shared server queue.'); ?></li>
                        <li><?php echo __('Conversion runs on your computer, reducing the work on the Encoder server.'); ?></li>
                        <li><?php echo __('YouTube and other supported video links download through your own connection, which can help when a provider blocks the Encoder server.'); ?></li>
                    </ul>
                    <p><?php echo __('Actual speed depends on your hardware and connection. Provider restrictions can still prevent downloads. Only import media you are allowed to publish.'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h2 class="panel-title"><?php echo __('Your first video'); ?></h2></div>
        <div class="panel-body">
            <ol>
                <li><?php echo __('Extract the whole download, keep its files together and open LocalEncoder (LocalEncoder.exe on Windows). Its control page opens in your browser.'); ?></li>
                <li><?php echo __('Connect with this site address and the same username and password you use on this AVideo site. Your account needs upload permission.'); ?><br><strong><?php echo htmlspecialchars($global['webSiteRootURL'], ENT_QUOTES, 'UTF-8'); ?></strong></li>
                <li><?php echo __('Select video files, choose a folder or paste an individual video link. Leave advanced options at their defaults and click Add to queue.'); ?></li>
                <li><?php echo __('Keep the computer and Local Encoder running until processing and upload finish, then check your video in My videos.'); ?></li>
            </ol>
            <p><?php echo __('Keep Local Encoder updated for changes to YouTube and other providers. For playlists, channels, audio-only input, WebM output and other unsupported workflows, continue using the Web Encoder.'); ?></p>
            <p><strong><?php echo __('Do not replace the site Encoder URL with the Local Encoder page address.'); ?></strong></p>
        </div>
    </div>
</div>
<?php $_page->print(); ?>
