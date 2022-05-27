<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    global $tinyMCELibs;
    if (empty($tinyMCELibs)) {
        $tinyMCELibs = 1;
        ?>
        <script type="text/javascript" src="<?php echo getURL('node_modules/tinymce/tinymce.min.js'); ?>"></script>
        <style>.tox-statusbar__branding{
                display:none !important;
            }</style>
        <?php }
        ?>
    <script>
    <?php
    if ($simpleMode) {
        ?>
            function image_upload_handler(blobInfo, success, failure) {
                avideoToastError('Image upload disabled');
            }
            var tinyMCEplugins = 'code print preview autolink fullscreen link hr pagebreak nonbreaking anchor wordcount help ';
            var tinyMCEtoolbar = 'fullscreen | styleselect align bold italic strikethrough underline | link | numlist bullist | removeformat | code';
            var tinyMCEmenubar = '';
        <?php
    } else {
        ?>

            const image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {

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
                            reject({message: 'HTTP Error: ' + xhr.status, remove: true});
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


            function images_upload_handler_old(blobInfo, success, failure) {
                console.log('images_upload_handler start', blobInfo, success, failure);
                var xhr, formData;
                var timeOuttime = 0;
                if (!videos_id) {
                    console.log('images_upload_handler !videos_id', videos_id);
                    $('#inputTitle').val("Article automatically booked");
                    saveVideo(false);
                    timeOuttime = 5;
                }
                setTimeout(function () {
                    console.log('images_upload_handler uploadArticleImage', videos_id);
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', webSiteRootURL + 'objects/uploadArticleImage.php?video_id=' + videos_id);
                    xhr.onload = function () {
                        console.log('images_upload_handler xhr.onload', xhr);
                        var json;
                        if (xhr.status != 200) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }

                        json = xhr.responseText;
                        json = JSON.parse(json);
                        if (json.error === false && json.url) {
                            success(json.url);
                        } else if (json.msg) {
                            avideoAlertError(json.msg);
                        } else {
                            avideoAlertError("<?php echo __("Unknown Error!"); ?>");
                        }

                    };
                    console.log('images_upload_handler formData', blobInfo.filename());
                    formData = new FormData();
                    formData.append('file_data', blobInfo.blob(), blobInfo.filename());
                    console.log('images_upload_handler formData 2 ', xhr);
                    xhr.send(formData);
                    console.log('images_upload_handler formData 3 ', xhr);
                }, timeOuttime);
                console.log('images_upload_handler end');
            }
            //var tinyMCEplugins = 'code print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help ';
            var tinyMCEplugins = 'code preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help ';
            var tinyMCEtoolbar = 'fullscreen | formatselect | bold italic strikethrough | link image media pageembed | numlist bullist | removeformat | addcomment';
            var tinyMCEmenubar = 'edit insert view format table tools help';
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
        tinymce.init({
            language: <?php echo $language; ?>,
            language_url: <?php echo $language_url; ?>,
            selector: '#<?php echo $id; ?>', // change this value according to your HTML
            plugins: tinyMCEplugins,
            //toolbar: 'fullscreen | formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',
            toolbar: tinyMCEtoolbar,
            menubar: tinyMCEmenubar, // remove 'file' menu as it's useless in our context
            height: 400,
            convert_urls: false,
            mobile: {
                theme: 'silver'
            },
            images_upload_handler: image_upload_handler
        });
    </script>
    <?php
}
