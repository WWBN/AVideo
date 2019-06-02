<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class MonetizeUsers extends PluginAbstract {

    public function getDescription() {
        $txt = "This plugin will reward your users based on their videos view, each view will affect the user's walled balance";

        $txt .= $this->isReadyLabel(array('YPTWallet'));
        return $txt;
    }

    public function getName() {
        return "MonetizeUsers";
    }

    public function getUUID() {
        return "10573335-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->rewardPerView = 0.1;
        $obj->rewardOnlyLoggedUsersView = true;
        return $obj;
    }

    public function getTags() {
        return array('free', 'monetize', 'wallet');
    }

    public function addView($videos_id, $total) {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->rewardOnlyLoggedUsersView && !User::isLogged()) {
            return false;
        }

        // Check ownership to prevent the uploader from farming money from their own video content
        if (User::isLogged()) {
            $user_id = User::getId();
            if (Video::isOwner($videos_id, $user_id)) {
                return false; // Prevent exploitation of free money; Don't award money if viewer is uploader
            }
        }

        $wallet = YouPHPTubePlugin::loadPlugin("YPTWallet");
        $video = new Video("", "", $videos_id);
        return $wallet->transferBalanceFromSiteOwner($video->getUsers_id(), $obj->rewardPerView, "Reward from video <a href='{$global['webSiteRootURL']}v/{$videos_id}'>" . $video->getTitle() . "</a>", true);
    }

}
