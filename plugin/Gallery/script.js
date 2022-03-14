function channelToGallery(users_id, add) {
    $.ajax({
        url: webSiteRootURL + 'plugin/Gallery/channelToGallery.json.php',
        method: 'POST',
        data: {'users_id': users_id, 'add': add},
        success: function (response) {
            avideoResponse(response);
            if(!response.error){
                if(response.add){
                    $('.ChannelToGallery'+response.users_id).addClass('isChannelToGallery');
                }else{
                    $('.ChannelToGallery'+response.users_id).removeClass('isChannelToGallery');
                }
            }
        }
    });
}