<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/UserNotifications/Objects/User_notifications.php';

class UserNotifications extends PluginAbstract {

    const type_success = 'success';
    const type_warning = 'warning';
    const type_info = 'info';
    const type_danger = 'danger';
    const types = array(self::type_success, self::type_warning, self::type_info, self::type_danger);
    const requiredUserNotificationTemplateFields = array('href', 'onclick', 'status', 'element_class', 'element_id', 'type', 'image', 'icon', 'title', 'msg', 'html', 'id', 'created');

    public function getDescription() {
        $desc = "This plugin will handle all your user's notification on the top bell bar";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    public function getName() {
        return "UserNotifications";
    }

    public function getUUID() {
        return "UserNotifications-5ee8405eaaa16";
    }

    public function getPluginVersion() {
        return "2.0";
    }

    public function updateScript() {
        global $global;
        /*
          if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
          sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
          }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/UserNotifications/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    public function getHeadCode() {
        global $global;
        $css = '<link href="' . getURL('plugin/UserNotifications/style.css') . '" rel="stylesheet" type="text/css"/>';

        $js = '<script>var user_notification_template = ' . json_encode(self::getTemplate()) . '</script>';
        $js .= '<script>var requiredUserNotificationTemplateFields = ' . json_encode(self::requiredUserNotificationTemplateFields) . '</script>';
        return $css . $js;
    }

    public function getFooterCode() {
        global $global;
        include $global['systemRootPath'] . 'plugin/UserNotifications/footer.php';
    }

    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/UserNotifications/HTMLMenuRight.php';
    }

    static function getTemplate() {
        global $_user_notification_template, $global;
        if (empty($_user_notification_template)) {
            $file = $global['systemRootPath'] . 'plugin/UserNotifications/template.html';
            $_user_notification_template = file_get_contents($file);
        }
        return $_user_notification_template;
    }

    static function createTemplateFromArray($itemsArray) {
        global $global;
        $template = self::getTemplate();
        foreach ($itemsArray as $search => $replace) {
            if ($search == 'icon') {
                $replace = '<i class="' . $replace . '"></i>';
            } else if ($search == 'image' && !empty($replace) && !isValidURL($replace)) {
                $replace = $global['webSiteRootURL'] . $replace;
            } else if ($search == 'element_class') {
                $replace .= " UserNotifications_{$itemsArray['id']}";
            }
            $template = str_replace("{{$search}}", $replace, $template);
        }
        $template = self::cleanUpTemplate($template);
        return $template;
    }

    static function cleanUpTemplate($template) {
        foreach (self::requiredUserNotificationTemplateFields as $search) {
            $template = str_replace("{{$search}}", '', $template);
        }
        $template = str_replace('<img src="" class="media-object">', '', $template);
        return $template;
    }

    public static function notifySocket($array, $to_users_id = 0) {
        if (!empty($to_users_id)) {
            $socketObj = sendSocketMessageToUsers_id($array, $to_users_id, 'socketUserNotificationCallback');
        } else {
            $socketObj = sendSocketMessageToAll($array, 'socketUserNotificationCallback');
        }
        return $socketObj;
    }

    public function createNotificationFromVideosAndUsers_id($videos_id, $users_id, $title, $msg, $element_id, $type, $icon='') {
        global $global;
        $identification = User::getNameIdentificationById($users_id);
        $video = new Video('', '', $videos_id);
        $to_users_id = $video->getUsers_id();
        $image = User::getPhoto($users_id, false, true);
        $href = Video::getLinkToVideo($videos_id);
        $videoTitle = safeString($video->getTitle());
        $msg = "<strong>{$identification}</strong> " . $msg . ': ' . $videoTitle;
        $element_id = "{$element_id}_{$videos_id}_{$users_id}";

        return self::createNotification($title, $msg, $to_users_id, $image, $href, $type, $element_id, $icon);
    }

    public function onVideoLikeDislike($videos_id, $users_id, $isLike) {
        global $global;
        if ($isLike) {
            $title = __('You have a new like');
            $type = self::type_success;
            $msg = __('liked your video');
            $element_id = "UserNotificationLike";
            $icon = 'fas fa-thumbs-up';
        } else {
            $title = __('You have a new dislike');
            $type = self::type_warning;
            $msg = __('disliked your video');
            $element_id = "UserNotificationDisLike";
            $icon = 'fas fa-thumbs-down';
        }
        return self::createNotificationFromVideosAndUsers_id($videos_id, $users_id, $title, $msg, $element_id, $type, $icon);
    }

    public function afterNewComment($comments_id) {
        global $global;
        $c = new Comment('', 0, $comments_id);
        $users_id = $c->getUsers_id();
        $videos_id = $c->getVideos_id();
        $title = __('You have a new comment');
        $type = self::type_success;
        $msg = __('comment your video');
        $element_id = "UserNotificationComment";
        $icon = 'far fa-comment';
        return self::createNotificationFromVideosAndUsers_id($videos_id, $users_id, $title, $msg, $element_id, $type, $icon);
    }

    public function afterNewResponse($comments_id) {
        global $global;
        $c = new Comment('', 0, $comments_id);
        $users_id = $c->getUsers_id();
        $videos_id = $c->getVideos_id();
        $comments_id_parent = $c->getComments_id_pai();
        $cp = new Comment('', 0, $comments_id_parent);
        $to_users_id = $cp->getUsers_id();
        $video = new Video('', '', $videos_id);
        $videoTitle = safeString($video->getTitle());
        $title = __('You have a new response');
        $identification = User::getNameIdentificationById($users_id);
        $msg = '<strong>'.$identification. '</strong> '.__('respond your comment on video on video').': '.$videoTitle;
        $type = self::type_success;
        $element_id = "UserNotificationResponse_{$comments_id}_{$to_users_id}_{$users_id}_{$videos_id}";
        $image = User::getPhoto($users_id, false, true);
        $href = Video::getLinkToVideo($videos_id);
        $icon = 'far fa-comments';
        return self::createNotification($title, $msg, $to_users_id, $image, $href, $type, $element_id, $icon);
    }
    
    public function onNewSubscription($users_id, $subscriber_users_id) {
        global $global;
        $title = __('You have a new subscription');
        $type = self::type_success;
        $element_id = "UserNotificationSubscription_{$users_id}_{$subscriber_users_id}";
        $identification = User::getNameIdentificationById($subscriber_users_id);
        $msg = '<strong>'.$identification. '</strong> '.__('subscribed to your channel');
        $image = User::getPhoto($subscriber_users_id, false, true);
        $href = User::getChannelLink($subscriber_users_id);
        $icon = 'fas fa-user-check';
        return self::createNotification($title, $msg, $users_id, $image, $href, $type, $element_id, $icon);
    }
    
    public static function createNotification($title, $msg = '', $to_users_id = 0, $image = '', $href = '', $type = '', $element_id = '', $icon = '', $element_class = '', $onclick = '', $priority = '') {
        if($to_users_id == User::getId()){
            return false;
        }
        $element_class .= ' canDelete';
        $o = new User_notifications();
        $o->setMsg($msg);
        $o->setTitle($title);
        $o->setStatus('a');
        $o->setUsers_id($to_users_id);
        $o->setType($type);
        $o->setImage($image);
        $o->setIcon($icon);
        $o->setHref($href);
        $o->setOnclick($onclick);
        $o->setElement_class($element_class);
        $o->setElement_id($element_id);
        $o->setPriority($priority);
        if ($id = $o->save()) {
            $array = array(
                'id' => $id,
                'title' => $title,
                'msg' => $msg,
                'image' => $image,
                'href' => $href,
                'type' => $type,
                'element_class' => $element_class,
                'element_id' => $element_id,
                'icon' => $icon,
                'onclick' => $onclick,
                'priority' => $priority,
                'toast' => true,
            );
            self::notifySocket($array, $to_users_id);
        }
    }
    
    public function getUserNotificationButton() {
        ?>
            <button class="btn btn-default btn-sm hideWhenHasNothingToDelete" onclick="deleteAllNotifications();" data-toggle="tooltip" title="<?php echo __('Delete All Notifications') ?>" >
                <i class="fas fa-trash"></i> <span class="hidden-sm hidden-xs"><?php echo __('Delete All'); ?></span>
            </button>
        <?php
    }

}
