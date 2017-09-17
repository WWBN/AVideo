<link href="<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/view/style.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/LiveChat/view/script.js" type="text/javascript"></script>
<div class="alert alert-warning" id="chatOffline">
    Trying to establish a chat server connection
</div>
<div style="display: none" id="chatOnline">
    <div class="panel panel-default liveChat">
        <div class="panel-heading">Live Chat</div>
        <div class="panel-body">  
            <ul class="messages"></ul>
        </div>
        <div class="panel-footer">
            <div class="input-group">
                <input type="text" class="form-control message_input" placeholder="Type your message...">
                <span class="input-group-btn">
                    <button class="btn btn-secondary send_message" type="button"><i class="fa fa-send"></i> Send</button>
                </span>
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
<script>
<?php
$server = parse_url($global['webSiteRootURL']);
?>
    var attempChatConnections = 3;
    var conn;
    function sendJsonMessage(text, message_side) {
        conn.send(text);
        return sendMessage(text, message_side);
    }
    function connect() {
        console.log('Trying to reconnect on <?php echo $server['host'] ?>');
        conn = new WebSocket('wss://<?php echo $server['host'] ?>/wss/');
        conn.onopen = function (e) {
            console.log("Connection established!");
            attempChatConnections = 3;
            $("#chatOffline").slideUp();
            $("#chatOnline").slideDown();
        };
        conn.onmessage = function (e) {
            var messageData = JSON.parse(e.data);
            //this message is not for you
            if (messageData.chatId !== "<?php echo $chatId; ?>") {
                console.log("<?php echo $chatId; ?>");
                console.log(messageData);
                return false;
            }
            var json = getJsonDataObject();
            json.text = e.data;
            sendMessage(e.data, 'left');
        };
        conn.onclose = function (e) {
            console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
            if(attempChatConnections--===0){
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

    function getJsonDataObject() {
        var chatId = "<?php echo $chatId; ?>";
        var photo = "<?php echo User::getPhoto(); ?>";
        var name = "<?php echo User::getNameIdentification(); ?>";
        var text = getMessageText();
        var json = {"photo": photo, "name": name, "text": text, "chatId": chatId};
        return json;
    }
    $(function () {
        $('.send_message').click(function (e) {
            sendJsonMessage(JSON.stringify(getJsonDataObject()), 'right');
        });
        $('.message_input').keyup(function (e) {
            if (e.which === 13) {
                sendJsonMessage(JSON.stringify(getJsonDataObject()), 'right');
            }
        });
        connect();
        
    });
</script>