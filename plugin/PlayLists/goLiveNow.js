$(document).ready(function () {
    swal({
        title: "Your playlist is NOT live, do you want to go live now?",
        icon: "warning",
        dangerMode: true,
        buttons: {
            goLive: true
        }
    }).then(function (value) {
        switch (value) {
            case "goLive":

                //modal.showPleaseWait();
                $.ajax({
                    url: liveLink,
                    success: function (response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                            //modal.hidePleaseWait();
                        } else {
                            avideoToast(response.msg);
                            setTimeout(function () {
                                tryToPlay(0);
                                //location.reload();
                            }, 2000);
                        }
                    }
                });
                break;
        }
    });
});