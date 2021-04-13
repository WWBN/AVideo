
var avideoNotifications = [];

$(document).ready(function () {
    var avideoNotificationsCookie = Cookies.get('avideoNotifications');
    if (typeof avideoNotificationsCookie !== 'undefined') {
        //avideoNotifications = avideoNotificationsCookie;
    }
    processNotifications();
});

function processNotifications() {
    console.log('processNotifications', avideoNotifications);
    $('.LayoutNotificationCount').text(avideoNotifications.length);
    $('#LayoutNotificationItems').empty();
    for (var item in avideoNotifications) {
        if (typeof avideoNotifications[item] == 'function') {
            continue;
        }
        
        var html = '<li>';
        html += '<a href="'+avideoNotifications[item].link+'" class="notificationLink">';
        html += '<img src="'+avideoNotifications[item].image+'" class="img img-circle img-responsive">';
        html += '<div>'+avideoNotifications[item].text+'</div>';
        html += '</a></li>';
        
        $('#LayoutNotificationItems').prepend(html);
        console.log('processNotifications item', item);
        console.log('processNotifications', avideoNotifications[item]);
    }
}

function addNotification(title, text, image, link) {
    var obj = {title: title, text: text, image: image, link:link};
    console.log('addNotification type', typeof avideoNotifications);
    console.log('addNotification=', avideoNotifications);
    console.log('addNotification', obj);
    avideoNotifications.push(obj);
    /*
    Cookies.set('avideoNotifications', avideoNotifications, {
        path: '/',
        expires: 365
    });
     * 
     */
}

function removeNotification(index) {
    avideoNotifications.splice(index, 1);
    /*
    Cookies.set('avideoNotifications', avideoNotifications, {
        path: '/',
        expires: 365
    });
     * 
     */
}