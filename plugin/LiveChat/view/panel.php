<?php
$p = YouPHPTubePlugin::loadPlugin("LiveChat");
$canSendMessage = $p->canSendMessage();
?>
<link href="<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/view/style.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/view/script.js" type="text/javascript"></script>
<link href="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-animation.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
<div class="alert alert-warning" id="chatOffline">
    <?php echo __("Trying to establish a chat server connection"); ?>
</div>
<div style="display: none" id="chatOnline">
    <div class="panel panel-default liveChat">
        <div class="panel-heading"><i class="fa fa-comments-o"></i> <?php echo __("Live Chat"); ?> <button class="btn btn-xs btn-default pull-right" id="collapseBtn"><i class="fa fa-minus-square"></i></button></div>
        <div class="colapsibleArea">
            <div class="panel-body">  
                <ul class="messages"></ul>
            </div>

            <div class="panel-footer">
                <?php
                if ($canSendMessage) {
                    ?>
                    <div class="input-group">
                        <input type="text" class="form-control message_input" placeholder="<?php echo __("Type your message..."); ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-secondary send_message" type="button"><i class="fa fa-send"></i> <?php echo __("Send"); ?></button>
                        </span>
                    </div>
                    <?php
                }else{
                ?>
                <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-default"> <?php echo __('Login'); ?></a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="message_template">
        <li class="message">
            <div class="text_wrapper alert alert-info pull-left">
                <div class="name label label-info"><?php echo User::getNameIdentification(); ?></div>
                <div class="text"></div>
            </div>
            <div class="avatar pull-right"><img src="<?php echo User::getPhoto(); ?>" class="img-responsive img-circle photo"></div>
        </li>
    </div>
</div>
<script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script>
    var attempChatConnections = 3;
    var conn;
    function sendJsonMessage(text, message_side) {
        conn.send(text);
        return sendMessage(text, message_side);
    }
    function connect() {
        console.log('Trying to reconnect on <?php echo $p->getWebSocket(); ?>');
        conn = new WebSocket('<?php echo $p->getWebSocket(); ?>');
        conn.onopen = function (e) {
            console.log("Connection established!");
            attempChatConnections = 3;
            $("#chatOffline").slideUp();
            $("#chatOnline").slideDown();
            
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/getChat.json.php',
                data: {
                    "live_stream_code": "<?php echo $chatId; ?>"
                },
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                    for(i=0; i<response.length;i++){
                        if(response[i].users_id == "<?php echo User::getId(); ?>"){
                            message_side = "right";
                        }else{                        
                            message_side = "left";    
                        }
                        createMessage(response[i].text, response[i].identification, response[i].photo, message_side);
                    }
                    $('.messages').animate({scrollTop: $('.messages').prop('scrollHeight')}, 300);
                }
            });
        };
        conn.onmessage = function (e) {
            var messageData = JSON.parse(e.data);
            //this message is not for you
            if (messageData.chatId !== "<?php echo $chatId; ?>") {
                return false;
            }
            var json = getJsonDataObject();
            json.text = e.data;
            sendMessage(e.data, 'left');
        };
        conn.onclose = function (e) {
            console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
            if (attempChatConnections-- === 0) {
                $("#chatOffline").removeClass('alert-warning');
                $("#chatOffline").addClass('alert-danger');
                $("#chatOffline").text('Chat connection fail');
            }
            setTimeout(function () {
                connect();
            }, 1000);
        };

        conn.onerror = function (err) {
            console.error('Socket encountered error: ', err.message, 'Closing socket');
            $("#chatOnline").slideUp();
            $("#chatOffline").slideDown();
            conn.close();
        };
    }

    function alertChat() {
        //var snd = new Audio("<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/view/notification.wav");
        //snd.play();
        $("#chatOnline .fa-comments-o").addClass('faa-ring');
        $("#chatOnline .fa-comments-o").addClass('animated');
        return setTimeout(function () {
            $("#chatOnline .fa-comments-o").removeClass('faa-ring');
            $("#chatOnline .fa-comments-o").removeClass('animated');
        }, 2000);
    }
    function getJsonDataObject() {
        var chatId = "<?php echo $chatId; ?>";
        var photo = "<?php echo User::getPhoto(); ?>";
        var userId = "<?php echo User::getId(); ?>";
        var name = "<?php echo User::getNameIdentification(); ?>";
        var text = getMessageText();
        var json = {"photo": photo, "name": name, "text": text, "chatId": chatId, "userId": userId};
        return json;
    }
    function makeDrag() {
        //$("#chatOnline").draggable('destroy');
        $("#chatOnline").draggable({handle: ".panel-heading"});
    }
    $(function () {
        $('#collapseBtn').click(function (e) {
            $('.colapsibleArea').slideToggle().promise().done(function () {
                //makeDrag();
            });
        });

<?php
if ($canSendMessage) {
    ?>
            $('.send_message').click(function (e) {
                sendJsonMessage(JSON.stringify(getJsonDataObject()), 'right');
            });
            $('.message_input').keyup(function (e) {
                if (e.which === 13) {
                    sendJsonMessage(JSON.stringify(getJsonDataObject()), 'right');
                    $('.message_input').val('');
                }
            });

    <?php
}
?>
        connect();
        makeDrag();
    });
</script>
