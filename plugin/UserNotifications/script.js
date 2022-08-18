function getTemplateFromArray(itemsArray) {
    var template = user_notification_template;
    for (var search in itemsArray) {
        var replace = itemsArray[search];
        if (typeof replace == 'function') {
            continue;
        }
        if (search == 'icon') {
            replace = '<i class="' + replace + '"></i>';
        } else if (search == 'image' && !empty(replace) && !isValidURL(replace)) {
            replace = webSiteRootURL + replace;
        } else if (search == 'element_class' && !empty(itemsArray.id)) {
            replace += " UserNotificationsJS_" + itemsArray.id;
        } else if (search == 'created') {
            m = moment.tz(itemsArray.created, itemsArray.timezone).local();
            replace = m.fromNow();
        }
        template = template.replace(new RegExp('{' + search + '}', 'g'), replace);
    }
    template = cleanUpTemplate(template);
    return template;
}

function addTemplateFromArray(itemsArray) {
    if(typeof itemsArray === 'function'){
        return false;
    }
    //console.log('addTemplateFromArray', itemsArray);
    if (!empty(itemsArray.element_id) && $('#' + (itemsArray.element_id)).length) {
        return false;
    }
    var template = getTemplateFromArray(itemsArray);
    
    var priority = 6;
    if(!isNaN(itemsArray.priority)){
        priority = itemsArray.priority;
    }
    if(empty(priority)){
        priority = 6;
    }
    var selector = '#topMenuUserNotifications ul .list-group .priority'+priority;
    console.log('addTemplateFromArray prepend', selector);
    $(selector).prepend(template);
    return true;
}

function cleanUpTemplate(template) {
    for (var index in requiredUserNotificationTemplateFields) {
        var search = requiredUserNotificationTemplateFields[index];
        if (typeof search !== 'string') {
            continue;
        }
        //console.log('cleanUpTemplate', search);
        template = template.replace(new RegExp('\{' + search + '\}', 'g'), '');
    }
    template = template.replace(/<img src="" class="media-object">/g, "");
    return template;
}

function userNotification(itemsArray, toast, customTitle) {
    addTemplateFromArray(itemsArray);

    var title = itemsArray.title;
    if (!empty(customTitle)) {
        title = customTitle;
    }
    if (empty(title)) {
        title = itemsArray.msg;
    }

    if (!empty(toast) && !empty(title)) {
        switch (itemsArray.type) {
            case 'success':
                avideoToastSuccess(title);
                break;
            case 'warning':
                avideoToastWarning(title);
                break;
            case 'danger':
                avideoToastError(title);
                break;
            case 'info':
                avideoToastInfo(title);
                break;
            default:
                avideoToast(title);
                break;
        }
    }
    updateUserNotificationCount();
}

function socketUserNotificationCallback(json) {

    var toast = false;
    var customTitle = false;
    if (!empty(json.toast)) {
        toast = json.toast;
    }
    if (!empty(json.customTitle)) {
        customTitle = json.customTitle;
    }

    console.log('socketUserNotificationCallback', json, toast, customTitle);
    userNotification(json, toast, customTitle);
}

var _updateUserNotificationCountTimeout;
function updateUserNotificationCount() {
    clearTimeout(_updateUserNotificationCountTimeout);
    _updateUserNotificationCountTimeout = setTimeout(function () {
        var valueNow = parseInt($('#topMenuUserNotifications  a > span.badge-notify').text());
        var total = $('#topMenuUserNotifications > ul .list-group a').length;
        if (total != valueNow) {
            //Avoid dropdown menu close on click inside
            $(document).on('click', '#topMenuUserNotifications .dropdown-menu', function (e) {
                e.stopPropagation();
            });
            animateChilds('#topMenuUserNotifications .dropdown-menu .list-group .priority', 'animate__bounceInRight', 0.05);
            $('#topMenuUserNotifications  a > span.badge-notify').hide();
            setTimeout(function () {
                var selector = '#topMenuUserNotifications  a > span.badge-notify';
                countToOrRevesrse(selector, total);
                $(selector).show();
            }, 1);
        }
    }, 500);
}

async function getUserNotification() {
    var url = webSiteRootURL + 'plugin/UserNotifications/getNotifications.json.php';
    $.ajax({
        url: url,
        success: function (response) {
            modal.hidePleaseWait();
            if (response.error) {
                avideoToastError(response.msg);
            } else {
                for (var item in response.notifications) {
                    var itemsArray = response.notifications[item];
                    if(typeof itemsArray === 'function'){
                        continue;
                    }
                    addTemplateFromArray(itemsArray);
                }
                updateUserNotificationCount();
            }
        }
    });
}

function deleteUserNotification(id, t) {
    //modal.showPleaseWait();
    $(t).parent().removeClass('animate__bounceInRight');
    $(t).parent().addClass('animate__flipOutX');
    var url = webSiteRootURL + 'plugin/UserNotifications/View/User_notifications/delete.json.php';
    $.ajax({
        url: url,
        data: {id: id},
        type: 'post',
        success: function (response) {
            //modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                //avideoToastSuccess(response.msg);
                setTimeout(function(){
                    $(t).parent().remove();
                    updateUserNotificationCount();
                },500);
            }
        }
    });
}

function deleteAllNotifications(){
    animateChilds('#topMenuUserNotifications .dropdown-menu .list-group .canDelete', 'animate__flipOutX', 0.05);
    var url = webSiteRootURL + 'plugin/UserNotifications/View/User_notifications/delete.json.php';
    $.ajax({
        url: url,
        success: function (response) {
            //modal.hidePleaseWait();
            if (response.error) {
                avideoAlertError(response.msg);
            } else {
                //avideoToastSuccess(response.msg);
                setTimeout(function(){
                    $('#topMenuUserNotifications .dropdown-menu .list-group .canDelete').remove();
                    updateUserNotificationCount();
                },500);
            }
        }
    });
}

$(document).ready(function () {
    getUserNotification();
});