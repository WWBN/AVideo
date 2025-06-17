Object.values(response.users_id_online).forEach(element => {
    if (!element || typeof element.users_id === 'undefined') return;

    $('#caller' + element.users_id).show();
    $('.users_id_' + element.users_id).addClass('online');
});
