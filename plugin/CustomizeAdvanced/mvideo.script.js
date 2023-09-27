function updateDiskUsage(videos_id) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/CustomizeAdvanced/updateDiskUsage.php',
        data: { "videos_id": videos_id },
        type: 'post',
        success: function (response) {
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                $("#grid").bootgrid('reload');
            }
            console.log(response);
            modal.hidePleaseWait();
        }
    });
}
function removeThumbs(videos_id) {
    modal.showPleaseWait();
    $.ajax({
        url: webSiteRootURL + 'plugin/CustomizeAdvanced/deleteThumbs.php',
        data: { "videos_id": videos_id },
        type: 'post',
        success: function (response) {
            avideoResponse(response);
            console.log(response);
            modal.hidePleaseWait();
        }
    });
}