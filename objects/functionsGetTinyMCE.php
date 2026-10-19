<?php
global $tinyMCELibs;
$tinyMCEuid = uniqid();

// Basic configurations
$basicValidElements = 'a[role|href|target|data-toggle|data-parent|data-dismiss|aria-expanded|aria-controls|class|title],' .
    'div[class|role|data-toggle|aria-labelledby|aria-hidden|aria-expanded|data-target|data-parent|style],' .
    'button[class|data-toggle|data-target|data-dismiss|type|aria-expanded],' .
    'span[class|aria-hidden|style],' .
    'ul[class],' .
    'li[class],' .
    'i[class],' .
    'img[class|src|alt|data-src|style],' .
    'nav[class],' .
    'input[class|type|data-toggle|placeholder|aria-describedby],' .
    'label[for|class|data-toggle],' .
    'textarea[class|rows|placeholder],' .
    'h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],' .
    'p[class|style],' .
    'br,' .
    'hr[class],' .
    'ol[class],' .
    'blockquote[class],' .
    'abbr[title],' .
    'code,'.
    'pre[class]';

// Allowing all attributes and tags
$extendedValidElements = '*[*]';


// Toolbar and Plugins based on simpleMode or full feature
if ($simpleMode) {
    // Conditional assignment based on PHP variables
    $validElements = $basicValidElements;

    $tinyMCEplugins = 'code preview autolink fullscreen link lists pagebreak nonbreaking anchor wordcount help ';
    $tinyMCEtoolbar = 'fullscreen | styles align bold italic strikethrough underline | link | numlist bullist | removeformat | code';
    $tinyMCEmenubar = false;
    if (!empty($allowImageAndMedia)) {
        // Images and media by URL only: simple mode has no upload endpoint
        $tinyMCEplugins .= 'image media ';
        $tinyMCEtoolbar = str_replace('| link |', '| link image media |', $tinyMCEtoolbar);
        // extended_valid_elements replaces TinyMCE's img rule, so keep the image dialog dimensions
        $validElements = str_replace('img[class|src|alt|data-src|style]', 'img[class|src|alt|data-src|style|width|height]', $validElements);
        // The media dialog only accepts what purifyChannelAboutHTML() keeps on the channel page
        require_once $global['systemRootPath'] . 'objects/channelAboutHTML.php';
        $tinyMCEsafeIframePattern = getChannelAboutSafeIframePattern($global['webSiteRootURL']);
        // TinyMCE's default list plus this site, so its own embed previews are not sandboxed in the editor
        $tinyMCEsandboxExclusions = ['youtube.com', 'youtu.be', 'vimeo.com', 'player.vimeo.com', 'dailymotion.com', 'embed.music.apple.com', 'open.spotify.com', 'giphy.com', 'dai.ly', 'codepen.io', 'youtube-nocookie.com'];
        $tinyMCEsiteHost = preg_replace('/^www\./i', '', (string) parse_url($global['webSiteRootURL'], PHP_URL_HOST));
        if (!empty($tinyMCEsiteHost)) {
            $tinyMCEsandboxExclusions[] = $tinyMCEsiteHost;
        }
    }
} else {

    // Conditional assignment based on PHP variables
    $validElements = $allowAllTags ? $extendedValidElements : $basicValidElements;

    $tinyMCEplugins = 'code preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help';
    $tinyMCEtoolbar = 'fullscreen | blocks | align bold italic strikethrough underline | forecolor backcolor | link image media pageembed | numlist bullist | removeformat | code';
    $tinyMCEmenubar = 'edit insert view format table tools help';
}



if (empty($tinyMCELibs)) {
    $tinyMCELibs = 1;
?>
    <script type="text/javascript" src="<?php echo getURL('node_modules/tinymce/tinymce.min.js'); ?>"></script>
    <style>
        .tox-statusbar__branding,
        .tox-promotion {
            display: none !important;
        }
    </style>
<?php
} else {
    $tinyMCELibs++;
}
?>
<script>
    <?php
    if ($simpleMode) {
    ?>

        function image_upload_handler<?php echo $tinyMCEuid; ?>(blobInfo, progress) {
            avideoToastError('Image upload disabled');
            // Other simple editors keep a pasted image, as they always did; the media editor removes it
            return Promise.reject({
                message: 'Image upload disabled',
                remove: <?php echo empty($allowImageAndMedia) ? 'false' : 'true'; ?>
            });
        }
    <?php
    } else {
    ?>
        if (typeof videos_id === 'undefined') {
            videos_id = -1;
        }
        const image_upload_handler<?php echo $tinyMCEuid; ?> = (blobInfo, progress) => new Promise((resolve, reject) => {

            if (!videos_id) {
                console.log('images_upload_handler !videos_id', videos_id);
                $('#inputTitle').val("Article automatically booked");
                saveVideo(false);
            }

            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', webSiteRootURL + 'objects/uploadArticleImage.php?video_id=' + videos_id);

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({
                        message: 'HTTP Error: ' + xhr.status,
                        remove: true
                    });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }

                const json = JSON.parse(xhr.responseText);

                if (!json || typeof json.url != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.url);
            };

            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            const formData = new FormData();
            formData.append('file_data', blobInfo.blob(), blobInfo.filename());

            xhr.send(formData);
        });

    <?php
    }

    $language = ($_SESSION['language'] == 'en_US') ? 'us' : $_SESSION['language'];
    $langFile = 'node_modules/tinymce-langs/langs/' . $language . '.js';

    if (file_exists($global['systemRootPath'] . $langFile)) {
        $language = "'{$language}'";
        $language_url = "'" . getURL($langFile) . "'";
    } else {
        $language = 'null';
        $language_url = 'null';
    }
    ?>
    setTimeout(function() {
        <?php if (!empty($tinyMCEsafeIframePattern)) { ?>
        function getShortsEmbedURL(url) {
            var shorts = String(url || '').trim().match(/^(?:https?:\/\/)?(?:www\.|m\.)?youtube\.com\/shorts\/([\w-]+)/i);
            return shorts ? 'https://www.youtube.com/embed/' + shorts[1] : '';
        }
        <?php } ?>
        tinymce.init({
            language: <?php echo $language; ?>,
            language_url: <?php echo $language_url; ?>,
            selector: '#<?php echo $id; ?>', // change this value according to your HTML
            plugins: '<?php echo $tinyMCEplugins; ?>',
            toolbar: '<?php echo $tinyMCEtoolbar; ?>',
            menubar: '<?php echo $tinyMCEmenubar; ?>',
            license_key: 'gpl',
            height: 400,
            convert_urls: false,
            mobile: {
                theme: 'silver'
            },
            images_upload_handler: image_upload_handler<?php echo $tinyMCEuid; ?>,
            <?php
            if (!empty($tinyMCEsafeIframePattern)) {
                // No upload tab and no pasted/dropped files: images are inserted by URL
                echo "image_uploadtab: false, paste_data_images: false,";
            ?>
            sandbox_iframes_exclusions: <?php echo json_encode($tinyMCEsandboxExclusions, JSON_HEX_TAG | JSON_HEX_AMP); ?>,
            // The media dialog converts source again when dimensions change; keep Shorts embeddable.
            urlconverter_callback: function(url, node, onSave, name) {
                if (name === 'source' || (name === 'src' && (node === 'iframe' || (node && node.nodeName === 'IFRAME')))) {
                    return getShortsEmbedURL(url) || url;
                }
                return url;
            },
            media_url_resolver: function(data) {
                var url = String(data.url || '').trim();
                if (new RegExp(<?php echo json_encode($tinyMCEsafeIframePattern, JSON_HEX_TAG | JSON_HEX_AMP); ?>, 'i').test(url)) {
                    return Promise.resolve({
                        html: '<iframe src="' + tinymce.DOM.encode(url) + '" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"></iframe>'
                    });
                }
                var shortsEmbedURL = getShortsEmbedURL(url);
                if (shortsEmbedURL) {
                    return Promise.resolve({
                        html: '<iframe src="' + shortsEmbedURL + '" width="315" height="560" frameborder="0" allowfullscreen="allowfullscreen"></iframe>'
                    });
                }
                // Links TinyMCE itself turns into YouTube, Vimeo or Dailymotion players
                if (/youtu\.be\/[\w\-?&=.]+|youtube\.com.+v=[^&]+|youtube\.com\/embed\/[\w\-?&=]+|vimeo\.com\/(?:.*\/)?[0-9]+|dailymotion\.com\/video\/[^_]+|dai\.ly\/[^_]+/i.test(url)) {
                    return Promise.resolve({
                        html: ''
                    });
                }
                return Promise.reject({
                    msg: <?php echo json_encode(__('Use a YouTube, Vimeo or Dailymotion link, or an embed link from this site'), JSON_HEX_TAG | JSON_HEX_AMP); ?>
                });
            },
            <?php
            }
            if (!empty($validElements)) {
                echo "extended_valid_elements: '{$validElements}',";
            }
            // valid_styles is intentionally not set: the editor never limited inline CSS (a typo kept it
            // off since 2024 and existing content relies on it); styles are filtered where the HTML is shown
            ?>
        });
    }, <?php echo $tinyMCELibs * 500; ?>);
</script>
