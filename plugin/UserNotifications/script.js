function getTemplateFromArray(itemsArray) {
    if(typeof user_notification_template == 'undefined'){
        return false;
    }
    var template = user_notification_template;
    //console.log('getTemplateFromArray', itemsArray);
    template = template.replace(new RegExp('{placeholder}', 'g'), user_notification_template_placeholder_image);
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
    } else if (search == 'created' && typeof _serverSystemTimezone !== 'undefined' && typeof moment != 'undefined') {
            try {
                m = moment.tz(itemsArray.created, _serverSystemTimezone).local();
                replace = m.fromNow();
            } catch (error) {
                console.warn('m = moment.tz(created, "'+_serverSystemTimezone+'").local()', itemsArray.created, _serverSystemTimezone, error);
            }
        } else if (search == 'href' && !empty(replace) && !isValidURL(replace)) {
            replace = webSiteRootURL + replace;
        }
        template = template.replace(new RegExp('{' + search + '}', 'g'), replace);
    }
    template = cleanUpTemplate(template);
    return template;
}

function addTemplateFromArray(itemsArray, prepend) {
    if (typeof itemsArray === 'function') {
        return false;
    }
    //console.log('addTemplateFromArray', itemsArray);
    //console.trace('processApplication addTemplateFromArray ', itemsArray);
    if (!empty(itemsArray.element_id) && $('#' + (itemsArray.element_id)).length) {
        var selector = '#' + (itemsArray.element_id);
        $(selector).removeClass('notificationLiveItemRemoveThis');
        return false;
    }
    var template = getTemplateFromArray(itemsArray);

    var priority = 6;
    if (!isNaN(itemsArray.priority)) {
        priority = itemsArray.priority;
    }
    if (empty(priority)) {
        priority = 6;
    }
    var selector = '#topMenuUserNotifications ul .list-group .priority' + priority;
    //console.log('addTemplateFromArray prepend', selector);
    try {
        if(prepend){
            $(selector).prepend(template);
        }else{
            $(selector).append(template);
        }
        updateUserNotificationCount();
    } catch (e) {
        //console.log('addTemplateFromArray prepend error', selector, e);
    }
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
    console.log('UserNotification::userNotification', itemsArray, toast, customTitle);
    addTemplateFromArray(itemsArray, true);

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
}

function socketUserNotificationCallback(json) {
    console.log('socketUserNotificationCallback 1', json);
    var toast = false;
    var customTitle = false;
    if (!empty(json.toast)) {
        toast = json.toast;
    }
    if (!empty(json.customTitle)) {
        customTitle = json.customTitle;
    }

    console.log('socketUserNotificationCallback 2', toast, customTitle);
    userNotification(json, toast, customTitle);
}

var _updateUserNotificationCountTimeout;
function updateUserNotificationCount() {
    clearTimeout(_updateUserNotificationCountTimeout);
    _updateUserNotificationCountTimeout = setTimeout(function () {
        var valueNow = parseInt($('#topMenuUserNotifications  a > span.badge-notify').text());
        var total = $('#topMenuUserNotifications > ul .list-group a').length;
        //console.log('updateUserNotificationCount', total);
        if (total <= 0) {
            $('#topMenuUserNotifications').addClass('hasNothingToShow');
            $('#topMenuUserNotifications').removeClass('hasSomethingToShow');
            $('#topMenuUserNotifications > a > span.badge').removeClass('badge-notify');
            $('#topMenuUserNotifications > a > span.badge').text(0);
        } else if (total != valueNow) {
            $('#topMenuUserNotifications').removeClass('hasNothingToShow');
            $('#topMenuUserNotifications').addClass('hasSomethingToShow');
            $('#topMenuUserNotifications > a > span.badge').addClass('badge-notify');
            animateChilds('#topMenuUserNotifications .dropdown-menu .list-group .priority', 'animate__bounceInRight', 0.05);
            $('#topMenuUserNotifications > a > span.badge').hide();
            setTimeout(function () {
                var selector = '#topMenuUserNotifications > a > span.badge';
                countToOrRevesrse(selector, total);
                $(selector).show();
            }, 1);
            createFilterButtons();
            checkIfCanDeleteNotifications();
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
                    if (typeof itemsArray === 'function') {
                        continue;
                    }
                    addTemplateFromArray(itemsArray, true);
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
                setTimeout(function () {
                    $(t).parent().remove();
                    updateUserNotificationCount();
                }, 500);
            }
        }
    });
}

function getCountNotificationIcons() {
    var selector = '#topMenuUserNotifications > ul .list-group .icon i';
    var iconsCountList = [];
    $(selector).each(function (index) {
        var className = $(this).attr('class');
        var classNameType = $(this).parent().attr('class');

        classNameType = classNameType.replace("icon bg-", "");

        var id = $(this).closest('div.userNotifications').attr('id');
        var listIndex = className + classNameType;
        //console.log('getCountNotificationIcons class', listIndex);
        if (empty(iconsCountList[listIndex])) {
            iconsCountList[listIndex] = [];
        }
        iconsCountList[listIndex].push([id, classNameType, className]);
    });
    //console.log('getCountNotificationIcons finish', iconsCountList);
    return iconsCountList;
}

function createFilterButtons() {

    var icons = getCountNotificationIcons();
    var buttons = '<div class="btn-group btn-group-justified">';

    var count = 0;
    for (var i in icons) {
        var icon = icons[i];
        if (typeof icon == 'function') {
            continue;
        }
        count++;
        var id = 'uNotfFilter_' + count;
        //buttons += '<button class="btn btn-'+icon[0][1]+' btn-sm" onclick=""><i class="'+icon[0][2]+'"></i> <span class="badge">'+icon.length+'</span></button>';
        buttons += '<input type="checkbox" value="' + icon[0][2] + '" id="' + id + '" class="hidden check-with-label" checked><label for="' + id + '" class="btn btn-' + icon[0][1] + ' btn-xs label-for-check"><i class="' + icon[0][2] + '"></i> <span class="badge">' + icon.length + '</span></label>';
    }
    buttons += '</div>';

    $('#userNotificationsFilterButtons').empty();
    if (count > 1) {
        $('#userNotificationsFilterButtons').append(buttons);
        setCheckboxOnChange();
    }
}

function getCheckedFilterButtons() {
    var iconsList = {};
    var selector = '#userNotificationsFilterButtons .check-with-label:checked';
    $(selector).each(function (index) {
        var val = $(this).val();
        iconsList[val] = val;
    });
    return iconsList;
}

function checkIfCanDeleteNotifications() {
    var selector = '#topMenuUserNotifications .userNotifications.canDelete';
    if ($(selector).length) {
        $('#topMenuUserNotifications').removeClass('hasNothingToDelete');
        $('#topMenuUserNotifications').addClass('hasSomethingToDelete');
    } else {
        $('#topMenuUserNotifications').removeClass('hasSomethingToDelete');
        $('#topMenuUserNotifications').addClass('hasNothingToDelete');
    }
}

function setCheckboxOnChange() {
    $('.check-with-label').on('change', function () {
        var iconsList = getCheckedFilterButtons();
        var selector = '#topMenuUserNotifications > ul .list-group .icon i';
        $(selector).each(function (index) {
            var parent = $(this).closest('div.userNotifications');
            var className = $(this).attr('class');
            if (empty(iconsList[className])) {
                $(parent).slideUp();
            } else {
                $(parent).slideDown();
            }
        });
    });
}

function deleteAllNotifications() {
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
                setTimeout(function () {
                    $('#topMenuUserNotifications .dropdown-menu .list-group .canDelete').remove();
                    updateUserNotificationCount();
                }, 500);
            }
        }
    });
}

$(document).ready(function () {
    //Avoid dropdown menu close on click inside
    $(document).on('click', '#topMenuUserNotifications .dropdown-menu', function (e) {
        e.stopPropagation();
    });
    $('#topMenuUserNotifications').on('click', function (e) {
        $(this).find('.lazyload').lazy({
            effect: 'fadeIn',
            afterLoad: function (element) {
                element.removeClass('lazyload');
                element.addClass('lazyloadLoaded');
            }
        });
    });
    getUserNotification();
});