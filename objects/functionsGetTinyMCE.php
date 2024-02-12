<?php
global $tinyMCELibs;
$tinyMCEuid = uniqid();
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

        function image_upload_handler<?php echo $tinyMCEuid; ?>(blobInfo, success, failure) {
            avideoToastError('Image upload disabled');
        }
        var tinyMCEplugins<?php echo $tinyMCEuid; ?> = 'code preview autolink fullscreen link pagebreak nonbreaking anchor wordcount help ';
        var tinyMCEtoolbar<?php echo $tinyMCEuid; ?> = 'fullscreen | styleselect align bold italic strikethrough underline | link | numlist bullist | removeformat | code';
        var tinyMCEmenubar<?php echo $tinyMCEuid; ?> = '';
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

        //var tinyMCEplugins = 'code print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help ';
        var tinyMCEplugins<?php echo $tinyMCEuid; ?> = 'code preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help ';
        var tinyMCEtoolbar<?php echo $tinyMCEuid; ?> = 'fullscreen | formatselect | bold italic strikethrough | link image media pageembed | numlist bullist | removeformat | addcomment';
        var tinyMCEmenubar<?php echo $tinyMCEuid; ?> = 'edit insert view format table tools help';
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
        tinymce.init({
            language: <?php echo $language; ?>,
            language_url: <?php echo $language_url; ?>,
            selector: '#<?php echo $id; ?>', // change this value according to your HTML
            plugins: tinyMCEplugins<?php echo $tinyMCEuid; ?>,
            //toolbar: 'fullscreen | formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',
            toolbar: tinyMCEtoolbar<?php echo $tinyMCEuid; ?>,
            menubar: tinyMCEmenubar<?php echo $tinyMCEuid; ?>, // remove 'file' menu as it's useless in our context
            height: 400,
            convert_urls: false,
            mobile: {
                theme: 'silver'
            },
            images_upload_handler: image_upload_handler<?php echo $tinyMCEuid; ?>,
            valid_styles: {
                '*': 'color,font-size,font-family'
            },
            extended_valid_elements: (
                'a[role|href|target|data-toggle|data-parent|data-dismiss|aria-expanded|aria-controls|class|title],' +
                'div[class|role|data-toggle|aria-labelledby|aria-hidden|aria-expanded|data-target|data-parent],' +
                'button[class|data-toggle|data-target|data-dismiss|type|aria-expanded],' +
                'span[class|aria-hidden|style],' +
                'ul[class],' +
                'li[class],' +
                'i[class],' +
                'img[class|src|alt|data-src],' +
                'nav[class],' +
                'input[class|type|data-toggle|placeholder|aria-describedby],' +
                'label[for|class|data-toggle],' +
                'textarea[class|rows|placeholder],' +
                'h1[class],h2[class],h3[class],h4[class],h5[class],h6[class],' +
                'p[class],' +
                'br,' +
                'hr[class],' +
                'ol[class],' +
                'blockquote[class],' +
                'abbr[title],' +
                'code,' +
                'pre[class]'
            ),
        });
    }, <?php echo $tinyMCELibs * 500; ?>);
</script>