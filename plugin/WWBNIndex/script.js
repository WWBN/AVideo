var expireInterval, expireDownInterval, resendInterval, resendCountDownInterval;
   
$(document).on("submit", "#verifyEmailForm", function (e) {
    e.preventDefault();
    var code = $(this).find("input[name=code]").val().trim();
    var expire = parseInt($(this).find("input[name=expireTime]").val());
    if (code == "") {
        swal("Required Field", "Code is required.", "info");
        return false;
    }

    if (expire == 0) {
        swal("Code Expired", "Please try another code for verification", "info");
    } else {
        $.ajax({
            url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
            type: "POST",
            data: {action: "submitVerificationCode", otp: code},
            dataType : 'json',
            beforeSend: function() {
                modal.showPleaseWait();
            },
            success: function (response) {
                // console.log("submitVerificationCode")
                // console.log(response)
                modal.hidePleaseWait();
                if (response) {
                    if (response.error) {
                        swal(response.title, response.message, "error");
                    } else {
                        // 
                        clearInterval(resendInterval);
                        clearInterval(resendCountDownInterval);
                        clearInterval(expireInterval);
                        clearInterval(expireDownInterval);

                        $("#wwbnIndexAuthBtn").css("display", "none");
                        $("#wwbnIndexVerifyBtn").css("display", "none");
                        $("#wwbnIndexAcctStatusBtn").css("display", "block");

                        swal(response.title, response.message, "success");
                        $("#verifyEmailModal").modal("hide");
                    }
                } else {
                    swal("Error", "Ops! Something went wrong", "error");
                }
            }, 
            error: function(error) {
                console.log(error)
            }
        });
    }
});

grid.find("#wwbnIndexAcctStatusBtn").on("click", function (e) {
    e.preventDefault();
    swal("Pending", "Your account still pending, Please wait until your account become active", "info")
});

$(document).on("click", "#resendCode", function (e) {
    e.preventDefault();
    resendCode();
});

grid.find("#wwbnIndexVerifyBtn").on("click", function (e) {
    e.preventDefault();
    resendCode(false);
});

function resendCode(vefify = true) {
    if (!vefify) {
        var expireTime = $("#verifyEmailForm").find("input[name=expireTime]");
        if (expireTime.val() != 0) {
            $("#verifyEmailModal").modal({ backdrop: "static", keyboard: false });
        } else {
            resendCodeAjax();
        }
    } else {
        resendCodeAjax(true);
    }
}

function resendCodeAjax(resend = false) {
    $.ajax({
        url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
        type: "POST",
        data: {action: "resendVerificationCode"},
        dataType : 'json',
        beforeSend: function() {
            modal.showPleaseWait();
        },
        success: function (response) {
            // console.log("resendVerificationCode")
            // console.log(response)
            modal.hidePleaseWait();
            if (response) {
                if (response.error) {
                    swal(response.title, response.message, "error");
                } else {
                    clearInterval(resendInterval);
                    clearInterval(expireInterval);
                    $("#verifyEmailModal").modal({ backdrop: "static", keyboard: false });
                    var resendTime = $("#verifyEmailForm").find("input[name=resendTime]");
                    var expireTime = $("#verifyEmailForm").find("input[name=expireTime]");
                    if (resend) {
                        $("#resendCodeDisplay").css("display", "block");
                        $("#resendCode").attr("disabled", true);
                        resendTime.val(120); // 2mins
                    } else {
                        resendTime.val(0); 
                    }
                    expireTime.val(300); // 5mins
                    var expireTimeVal = 300, resendTimeVal = 120;
                    resendInterval = setInterval(() => {
                        if (expireTimeVal != 0) {
                            expireTime.val(expireTimeVal);
                            expireTimeVal--;
                        } else {
                            expireTime.val(0);
                        }
                        if (resend) {
                            if (resendTimeVal == 0) {
                                $("#resendCodeDisplay").css("display", "none");
                                $("#resendCode").removeAttr("disabled");
                                resendTime.val(0)
                            } else {
                                resendTime.val(resendTimeVal)
                                resendTimeVal--;
                            }
                        }
                    }, 1000);
                    expireCountDown(expireTimeVal, $("#verifyEmailForm").find("#expireTimer"));
                    if (resend) {
                        resendCountDown(resendTimeVal, $("#verifyEmailForm").find("#resendTimer"));
                    }
                }
            } else {
                swal("Error", "Ops! Something went wrong", "error");
            }
        }, 
        error: function(error) {
            console.log(error)
        }
    });
}

grid.find("#wwbnIndexAuthBtn").on("click", function (e) {
    $.ajax({
        url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
        type: "POST",
        data: {action: "authAccount"},
        dataType : 'json',
        beforeSend: function() {
            modal.showPleaseWait();
        },
        success: function (response) {
            modal.hidePleaseWait();
            // console.log("authAccount")
            // console.log(response)
            if (response) {
                if (response.error) {
                    swal(response.title, response.message, "error");
                } else {
                    $("#wwbnIndexVerifyBtn").css("display", "block");
                    $("#wwbnIndexAuthBtn").css("display", "none");
                    if ($("#wwbnIndexOrganicIndexedBtn").length > 0) {
                        $("#wwbnIndexOrganicIndexedBtn").css("display", "none");
                    }
                    var expireTime = $("#verifyEmailForm").find("input[name=expireTime]");
                    var resendTime = $("#verifyEmailForm").find("input[name=resendTime]");
                    expireTime.val(300); // 5mins
                    var expireTimeVal = 300;
                    expireInterval = setInterval(() => {
                        if (resendTime.val() == "" || resendTime.val() == "0" || resendTime.val() == 0) {
                            if (expireTimeVal != 0) {
                                expireTime.val(expireTimeVal);
                                expireTimeVal--;
                            } else {
                                expireTime.val(0);
                            }
                        }
                    }, 1000);
                    expireCountDown(expireTimeVal, $("#verifyEmailForm").find("#expireTimer"));
                    $("#verifyEmailModal").modal({ backdrop: "static", keyboard: false });
                }
            } else {
                swal("Error", "Ops! Something went wrong", "error");
            }
        }, 
        error: function(error) {
            console.log(error)
        }
    });
});


function resendCountDown(duration, display) {
    clearInterval(resendCountDownInterval);
    var timer = duration, minutes, seconds;
    resendCountDownInterval = setInterval(() => {
        if (timer >= 0) { // && $("#resendTimer").text() != "00:00"
            if (parseInt($("input[name=resendTime]").val()) != 0) {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                if (--timer < 0) {
                    timer = duration;
                }
                display.text(minutes + ":" + seconds);
            } else {
                display.text("00:00");
            }
        }
    }, 1000);
}

function expireCountDown(duration, display) {
    clearInterval(expireDownInterval);
    var timer = duration, minutes, seconds;
    expireDownInterval = setInterval(() => {
        if (timer >= 0) { //$("#expireTimer").text() != "00:00"
            if (parseInt($("input[name=expireTime]").val()) != 0) {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                if (--timer < 0) {
                    timer = duration;
                }
                display.text(minutes + ":" + seconds);
            } else {
                display.text("00:00");
            }
        }
    }, 1000);
}

grid.find("#wwbnIndexSubmitIndexBtn").on("click", function (e) {
    e.preventDefault();
    wwbnIndexSubmit();
});

function wwbnIndexSubmit(engine_name = "") {
    swal({
        title: "Submit Index",
        text: "Are you sure to index this platform?",
        icon: "info",
        buttons: {
            cancel : "Cancel",
            confirm : {text: 'Confirm'}
        },
    })
    .then((submit) => {
        if (submit) {
            $.ajax({
                url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
                type: "POST",
                data: {action: "submitIndex", "engine_name": engine_name},
                dataType : 'json',
                beforeSend: function() {
                    modal.showPleaseWait();
                },
                success: function (response) {
                    modal.hidePleaseWait();
                    // console.log("submitIndex")
                    // console.log(response)
                    if (response) {
                        if (response.error) {
                            swal(response.title, response.message, "error");
                            if (response.message == "Feed Name already exist!") {
                                // SHOW FORM TO INPUT NEW ENGINE NAME / FEED NAME
                                $("#engine_name_exist").text(response.engine_name)
                                $("#feedIndexModal").modal({ backdrop: "static", keyboard: false });
                            }
                        } else {
                            $("#wwbnIndexSubmitIndexBtn").css("display", "none");
                            $("#wwbnIndexIndexInReviewBtn").css("display", "block");
                            $("#feedIndexModal").modal("hide");
                            swal(response.title, response.message, "success");
                        }
                    } else {
                        swal("Error", "Ops! Something went wrong", "error");
                    }
                }, 
                error: function(error) {
                    console.log(error)
                }
            });
        }
    });
}

$(document).on("submit", "#feedIndexForm", function (e) {
    e.preventDefault();
    var engine_name = $(this).find("input[name=engine_name]").val();
    swal({
        title: "Submit Index",
        text: "You can't edit this name once submitted. Are you sure to submit already?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((submit) => {
        if (submit) {
            wwbnIndexSubmit(engine_name);
        } else {
            // swal("");
        }
    });
});

grid.find("#wwbnIndexIndexInReviewBtn").on("click", function (e) {
    e.preventDefault();
    swal("Pending", "Your index is under review.", "info");
});

grid.find("#wwbnIndexIndexActiveBtn").on("click", function (e) {
    e.preventDefault();
    // GET TERMS AND CONDITION API
    $.ajax({
        url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
        type: "POST",
        data: {action: "getIndexTermsAndConditions"},
        dataType : 'json',
        beforeSend: function() {
            // modal.showPleaseWait();
        },
        success: function (response) {
            // modal.hidePleaseWait();
            // console.log("getIndexTermsAndConditions")
            // console.log(response)
            if (response) {
                if (response.error) {
                    // swal(response.title, response.message, "error");
                    $("input[name=wwbnIndexTaCTitle]").val(response.title);
                    $("input[name=wwbnIndexTaCDescription]").val(response.message);
                    $("#wwbnIndexTaCDisplay").html("");
                } else {
                    var html = "";
                    if (response.data.item_name != "" && response.data.item_name != null && response.data.item_name != undefined) {
                        html += '<h3>'+response.data.item_name+'</h3>';
                        html += '<p>'+response.data.description+'</p>';
                    }
                    $("#wwbnIndexTaCDisplay").html(html);
                    $("#inactiveIndexModal").modal({ backdrop: "static", keyboard: false });
                }
            } else {
                swal("Error", "Ops! Something went wrong - getIndexTermsAndConditions", "error");
            }
        },
        error: function (error) {
            console.log(error)
        }
    });
});

$(document).on("click", "#wwbnIndexTaCBtn", function (e) {
    e.preventDefault();
    if ( $("#wwbnIndexTaCDisplay").text() != "" ) {
        $("#inactiveIndexModal").modal("hide");
        $("#indexTaCModal").modal({ backdrop: "static", keyboard: false });
    } else {
        var title = $("input[name=wwbnIndexTaCTitle]").val();
        var message = $("input[name=wwbnIndexTaCDescription]").val();
        swal(title, message, "error");
    }
});

$(document).on("hide.bs.modal", "#indexTaCModal", function() {
    $("#inactiveIndexModal").modal({ backdrop: "static", keyboard: false });
});

grid.find("#wwbnIndexApproveButNotIndexYetBtn").on("click", function (e) {
    e.preventDefault();
    swal("Pending", "Your index has been approved but not yet indexed. Please wait your platform is almost there.", "info");
});

$(document).on("click", "#wwbnIndexReIndexBtn", function (e) {
    e.preventDefault();
    swal({
        title: "Re-Index",
        text: "Are you sure to re-index this platform?",
        icon: "info",
        buttons: {
            cancel : "Cancel",
            confirm : {text: 'Confirm'}
        },
        // dangerMode: true,
    })
    .then((submit) => {
        if (submit) {
            $.ajax({
                url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
                type: "POST",
                data: {action: "reIndex"},
                dataType : 'json',
                beforeSend: function() {
                    modal.showPleaseWait();
                },
                success: function (response) {
                    modal.hidePleaseWait();
                    // console.log("reIndex")
                    // console.log(response)
                    if (response) {
                        if (response.error) {
                            swal(response.title, response.message, "error");
                        } else {
                            $("#wwbnIndexIndexActiveBtn").css("display", "none");
                            $("#wwbnIndexIndexInReviewBtn").css("display", "block");
                            $("#inactiveIndexModal").modal("hide");
                            swal(response.title, response.message, "success");
                        }
                    } else {
                        swal("Error", "Ops! Something went wrong", "error");
                    }
                },
                error: function (error) {
                    console.log(error)
                }
            });
        } 
    });
});

grid.find("#wwbnIndexIndexUnindexBtn").on("click", function (e) {
    e.preventDefault();
    swal({
        title: "UnIndex",
        text: "Are you sure to unindex this platform?",
        icon: "warning",
        buttons: {
            cancel : "Cancel",
            confirm : {text: 'Confirm'}
        },
        dangerMode: true
    })
    .then((submit) => {
        if (submit) {
            $.ajax({
                url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
                type: "POST",
                data: {action: "unIndex"},
                dataType : 'json',
                beforeSend: function() {
                    modal.showPleaseWait();
                },
                success: function (response) {
                    modal.hidePleaseWait();
                    // console.log("unIndex")
                    // console.log(response)
                    if (response) {
                        if (response.error) {
                            swal(response.title, response.message, "error");
                        } else {
                            $("#wwbnIndexIndexUnindexBtn").css("display", "none");
                            $("#wwbnIndexIndexActiveBtn").css("display", "block");
                            swal(response.title, response.message, "success");
                        }
                    } else {
                        swal("Error", "Ops! Something went wrong", "error");
                    }
                },
                error: function (error) {
                    console.log(error)
                }
            });
        }
    });
});


grid.find("#wwbnIndexAuthenticatedBtn").on("click", function (e) {
    swal("Authenticated", "Your account is already authenticated!", "info");
});


grid.find("#wwbnIndexErrorBtn").on("click", function (e) {
    e.preventDefault();
    var title = $(this).data("title");
    var message = $(this).data("message");
    swal(title, message, "error");
});

grid.find("#wwbnIndexOrganicIndexedBtn").on("click", function (e) {
    e.preventDefault();
    swal("Organic Indexed", "Platform was indexed by default. Please authenticate and submit index to update platform changes.", "info");
});

grid.find("#wwbnIndexRequestResetBtn").on("click", function() {
    $.ajax({
        url: webSiteRootURL+"plugin/WWBNIndex/ajax.php",
        type: "POST",
        data: {action: "requestResetKeys"},
        dataType : 'json',
        beforeSend: function() {
            modal.showPleaseWait();
        },
        success: function (response) {
            modal.hidePleaseWait();
            // console.log(response)
            if (response) {
                if (response.error) {
                    swal(response.title, response.message, response.type ? response.type : "error");
                } else {
                    swal(response.title, response.message, "success");
                }
            } else {
                swal("Error", "Ops! Something went wrong", "error");
            }
        },
        error: function (error) {
            console.log(error)
        }
    });
});