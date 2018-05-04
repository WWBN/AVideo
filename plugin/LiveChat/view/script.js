var getMessageText, sendMessage, Message;
Message = function (arg) {
    this.text = arg.text, this.message_side = arg.message_side;
    this.draw = function (_this) {
        return function () {
            var $message;
            var json = JSON.parse(_this.text);
            alertChat();
            return createMessage(json.text, json.name, json.photo, _this.message_side);
        };
    }(this);
    return this;
};
getMessageText = function () {
    var $message_input;
    $message_input = $('.message_input');
    return $message_input.val();
};
sendMessage = function (text, message_side) {
    var $messages, message;
    if (text.trim() === '') {
        return;
    }
    $messages = $('.messages');
    message = new Message({
        text: text,
        message_side: message_side
    });
    message.draw();
    return $messages.animate({scrollTop: $messages.prop('scrollHeight')}, 300);
};

function createMessage(text, name, photo, message_side){    
    $message = $($('.message_template').clone().html());    
    if(message_side==="left"){
        $message.find('.text_wrapper').removeClass("pull-left").addClass("pull-right");
        $message.find('.text_wrapper').removeClass("alert-info").addClass("alert-warning");
        $message.find('.name').removeClass("label-info").addClass("label-warning");
        $message.find('.avatar').removeClass("pull-right").addClass("pull-left");
    }
    $message.find('.text').html(text);
    $message.find('.name').html(name);
    $message.find('.photo').attr('src', photo);
    $('.messages').append($message);
    $message.addClass('appeared');
}