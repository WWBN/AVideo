
var downloadURLOrAlertErrorInterval;
var downloadURLOrAlertModal = getPleaseWait();
function downloadURLOrAlertError(jsonURL, data, filename, FFMpegProgress) {
    if (empty(jsonURL)) {
        console.log('downloadURLOrAlertError error empty jsonURL', jsonURL, data, filename, FFMpegProgress);
        return false;
    }
    downloadURLOrAlertModal.showPleaseWait();
    avideoToastInfo('Converting');
    console.log('downloadURLOrAlertError 1', jsonURL, FFMpegProgress);
    checkFFMPEGProgress(FFMpegProgress);
    $.ajax({
        url: jsonURL,
        method: 'POST',
        data: data,
        success: function (response) {
            clearInterval(downloadURLOrAlertErrorInterval);
            if (response.error) {
                avideoAlertError(response.msg);
                downloadURLOrAlertModal.hidePleaseWait();
            } else if (response.url) {
                if (response.msg) {
                    avideoAlertInfo(response.msg);
                }
                if (
                    isMobile()
                    //|| /cdn.ypt.me/.test(response.url)
                ) {
                    console.log('downloadURLOrAlertError 2', response.url);
                    window.open(response.url, '_blank');
                    avideoToastInfo('Opening file');
                    //document.location = response.url
                } else {
                    console.log('downloadURLOrAlertError 3', response.url, filename);
                    downloadURL(response.url, filename);
                }
            } else {
                console.log('downloadURLOrAlertError 4', response);
                avideoResponse(response);
            }

            //downloadURLOrAlertModal.hidePleaseWait();
        }
    });
}

function checkFFMPEGProgress(FFMpegProgress) {
    if (empty(FFMpegProgress)) {
        return false;
    }
    $.ajax({
        url: FFMpegProgress,
        success: function (response) {
            console.log('checkFFMPEGProgress',response);
            if (typeof response.progress.progress !== 'undefined') {
                var text = 'Converting ...';
                if (typeof response.progress.progress !== 'undefined') {
                    text += response.progress.progress + '% ';
                    downloadURLOrAlertModal.setProgress(response.progress.progress);
                }
                downloadURLOrAlertModal.setText(text);
                if (response.progress.progress !== 100) {
                    setTimeout(function () {
                        checkFFMPEGProgress(FFMpegProgress);
                    }, 10000);
                }else if(response.progress.progress >= 100){
                    downloadURLOrAlertModal.hidePleaseWait();
                }
            }
        }
    });
}
