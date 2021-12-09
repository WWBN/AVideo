<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    global $tinyMCELibs;
    if (empty($tinyMCELibs)) {
        $tinyMCELibs = 1;
        ?>
        <script type="text/javascript" src="<?php echo getURL('node_modules/tinymce/tinymce.min.js'); ?>"></script>
        <?php
    }
    ?>
    <script>
    <?php
    if ($simpleMode) {
        ?>
            function images_upload_handler(blobInfo, success, failure) {
                avideoToastError('Image upload disabled');
            }
            var tinyMCEplugins = 'code print preview searchreplace autolink directionality visualblocks visualchars fullscreen link codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount textpattern help ';
            var tinyMCEtoolbar = 'fullscreen | formatselect | bold italic strikethrough | link pageembed | numlist bullist | removeformat | addcomment';
        <?php
    } else {
        ?>
            function images_upload_handler(blobInfo, success, failure) {
                var xhr, formData;
                var timeOuttime = 0;
                if (!videos_id) {
                    $('#inputTitle').val("Article automatically booked");
                    saveVideo(false);
                    timeOuttime = 5;
                }
                setTimeout(function () {
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', webSiteRootURL + 'objects/uploadArticleImage.php?video_id=' + videos_id);
                    xhr.onload = function () {
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
                    formData = new FormData();
                    formData.append('file_data', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                }, timeOuttime);
            }
            var tinyMCEplugins = 'code print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help ';
            var tinyMCEtoolbar = 'fullscreen | formatselect | bold italic strikethrough | link image media pageembed | numlist bullist | removeformat | addcomment';
        <?php
    }
    ?>
        tinymce.init({
            language: "<?php echo ($_SESSION['language'] == 'en_US') ? 'us' : $_SESSION['language']; ?>",
            language_url: '<?php echo getURL('node_modules/tinymce-langs/langs/' . (($_SESSION['language'] == 'en_US') ? 'us' : $_SESSION['language']) . '.js'); ?>',
            selector: '#<?php echo $id; ?>', // change this value according to your HTML
            plugins: tinyMCEplugins,
            //toolbar: 'fullscreen | formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',
            toolbar: tinyMCEtoolbar,
            menubar: 'edit insert view format table tools help', // remove 'file' menu as it's useless in our context
            height: 400,
            convert_urls: false,
            mobile: {
                theme: 'silver'
            },
            images_upload_handler: images_upload_handler
        });
    </script>
    <?php
}
