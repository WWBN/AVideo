<?php


/*
 * $users_id="" or 0 means send messages to all users
 * $users_id="-1" means send to no one
 */

 function sendSocketMessage($msg, $callbackJSFunction = "", $users_id = "-1", $send_to_uri_pattern = "", $try = 0)
 {
     if (AVideoPlugin::isEnabledByName('YPTSocket')) {
         if (!is_string($msg)) {
             $msg = json_encode($msg);
         }
         try {
             $obj = YPTSocket::send($msg, $callbackJSFunction, $users_id, $send_to_uri_pattern);
             //_error_log("sendSocketMessage YPTSocket::send ");
         } catch (Exception $exc) {
             if ($try < 3) {
                 sleep(1);
                 _error_log("sendSocketMessage try agaion [$try]" . $exc->getMessage());
                 $obj = sendSocketMessage($msg, $callbackJSFunction, $users_id, $send_to_uri_pattern, $try + 1);
             } else {
                 $obj = new stdClass();
                 $obj->error = true;
                 $obj->msg = $exc->getMessage();
             }
         }
         if ($obj->error && !empty($obj->msg)) {
             _error_log("sendSocketMessage " . $obj->msg);
         }
         return $obj;
     }
     return false;
 }

 function sendSocketMessageToUsers_id($msg, $users_id, $callbackJSFunction = "")
 {
     if (empty($users_id)) {
         return false;
     }
     _error_log("sendSocketMessageToUsers_id start " . json_encode($users_id));
     if (!is_array($users_id)) {
         $users_id = [$users_id];
     }

     $resp = [];
     foreach ($users_id as $value) {
         $resp[] = sendSocketMessage($msg, $callbackJSFunction, $value);
     }

     return $resp;
 }

 function sendSocketErrorMessageToUsers_id($msg, $users_id, $callbackJSFunction = "avideoResponse")
 {
     $newMessage = new stdClass();
     $newMessage->error = true;
     $newMessage->msg = $msg;
     return sendSocketMessageToUsers_id($newMessage, $users_id, $callbackJSFunction);
 }

 function sendSocketSuccessMessageToUsers_id($msg, $users_id, $callbackJSFunction = "avideoResponse")
 {
     $newMessage = new stdClass();
     $newMessage->error = false;
     $newMessage->msg = $msg;
     return sendSocketMessageToUsers_id($newMessage, $users_id, $callbackJSFunction);
 }

 function sendSocketMessageToAll($msg, $callbackJSFunction = "", $send_to_uri_pattern = "")
 {
     return sendSocketMessage($msg, $callbackJSFunction, "", $send_to_uri_pattern);
 }

 function sendSocketMessageToNone($msg, $callbackJSFunction = "")
 {
     return sendSocketMessage($msg, $callbackJSFunction, -1);
 }

function getSocketConnectionLabel()
{
    $html = '<span class="socketStatus">
            <span class="socket_icon socket_loading_icon">
                <i class="fas fa-sync fa-spin"></i>
            </span>
            <span class="socket_icon socket_not_loading socket_disconnected_icon">
                <span class="fa-stack">
                    <i class="fas fa-slash fa-stack-1x"></i>
                    <i class="fas fa-plug fa-stack-1x"></i>
                </span> ' . __('Disconnected') . '
            </span>
            <span class="socket_icon socket_not_loading socket_connected_icon">
                <span class="fa-stack">
                    <i class="fas fa-plug fa-stack-1x"></i>
                </span>  ' . __('Connected') . '
                <span class="total_users_online" style="margin-left: 10px;"><i class="fas fa-spinner fa-spin"></i></span>
            </span>
        </span>';
    return $html;
}

function getSocketVideoClassName($videos_id)
{
    return 'total_on_videos_id_' . $videos_id;
}

function getSocketLiveClassName($key, $live_servers_id)
{
    return 'total_on_live_' . $key . '_' . intval($live_servers_id);
}

function getSocketLiveLinksClassName($live_links_id)
{
    return 'total_on_live_links_id_' . $live_links_id;
}
