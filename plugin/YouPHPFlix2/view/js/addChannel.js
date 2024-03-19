

function channelToYouPHPFlix2(users_id, add) {
    $.ajax({
        url: webSiteRootURL + 'plugin/YouPHPFlix2/channelToYouPHPFlix2.json.php',
        method: 'POST',
        data: {'users_id': users_id, 'add': add},
        success: function (response) {
            avideoResponse(response);
            if(!response.error){
                if(response.add){
                    $('.ChannelToYouPHPFlix2'+response.users_id).addClass('isChannelToYouPHPFlix2');
                }else{
                    $('.ChannelToYouPHPFlix2'+response.users_id).removeClass('isChannelToYouPHPFlix2');
                }
            }
        }
    });
}