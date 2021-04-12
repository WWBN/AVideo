<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    global $tinyMCELibs;
    if(empty($tinyMCELibs)){
        $tinyMCELibs = 1;
        ?>
            <script type="text/javascript" src="<?php echo getCDN(); ?>view/js/tinymce/tinymce.min.js"></script>
        <?php
    }
    ?>
    <script>
        tinymce.init({
            language: "<?php echo $_SESSION['language']; ?>",
            selector: '#<?php echo $id; ?>', // change this value according to your HTML
            plugins: 'code print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help ',
            //toolbar: 'fullscreen | formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',
            toolbar: 'fullscreen | formatselect | bold italic strikethrough | link image media pageembed | numlist bullist | removeformat | addcomment',
            menubar: 'edit insert view format table tools help', // remove 'file' menu as it's useless in our context
            height: 400,
            convert_urls: false,
            mobile: {
              theme: 'silver'
            },
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                if (!videos_id) {
                    $('#inputTitle').val("Article automatically booked");
                    saveVideo(false);
                }
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '<?php echo $global['webSiteRootURL']; ?>objects/uploadArticleImage.php?video_id=' + videos_id);
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
            }
        });
    </script>
    <?php
}