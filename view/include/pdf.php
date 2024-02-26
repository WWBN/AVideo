<?php
$sources = getVideosURLPDF($video['filename']);
//var_dump($sources);exit;
?>
<div class="row main-video" style="padding: 10px;" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">

        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <video id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <?php
                $url = $sources["pdf"]['url'];
                ?>
                <iframe id="pdfIframe" class="embed-responsive-item" scrolling="no" <?php echo Video::$iframeAllowAttributes; ?> type="application/pdf" src="<?php echo $url; ?>"></iframe>
                <script>
                    $(document).ready(function () {
                        addCloseButtonInPage();
                        addView(<?php echo $video['id']; ?>, 0);
                        
                        $('#pdfIframe').on('load', function(){
                            checkIfPDFWasConvertedIntoImage();
                        });
                    });
                    function checkIfPDFWasConvertedIntoImage(){
                        var iframe = $('#pdfIframe');
                        var iframeContent = iframe.contents();
                        if($('body > img', iframeContent).length){
                            var oldurl = iframe.attr('src');
                            /*
                            var parts = oldurl.split('?');
                            if(!empty(parts[0])){
                                oldurl = parts[0];
                            }
                            */
                            var newurl = 'https://docs.google.com/viewerng/viewer?embedded=true';
                            newurl = addQueryStringParameter(newurl, 'url', oldurl);
                            console.log('checkIfPDFWasConvertedIntoImage load new URL',newurl, oldurl);
                            iframe.attr('oldsrc', oldurl);
                            iframe.attr('src', newurl);
                        }
                    }
                </script>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {

        });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
