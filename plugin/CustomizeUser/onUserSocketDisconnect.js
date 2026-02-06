if (response?.msg?.users_id) {
    $('#caller'+response.msg.users_id).hide();
    $('.users_id_'+response.msg.users_id).removeClass('online');
}
