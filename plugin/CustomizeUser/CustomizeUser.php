<?php

global $global;
if (empty($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Categories_has_users_groups.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_extra_info.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_affiliations.php';

class CustomizeUser extends PluginAbstract
{


    private static function _getSocialMedia()
    {
        return [
            'website' => [
                'class' => 'icoCopy',
                'icon' => 'fas fa-globe',
                'label' => __("Website"),
                'placeholder' => __("Website URL"),
                'isActive' => true,
            ],
            'facebook' => [
                'class' => 'icoFacebook',
                'icon' => 'fab fa-facebook-f',
                'label' => __("Facebook"),
                'placeholder' => __("Facebook URL"),
                'isActive' => true,
            ],
            'youtube' => [
                'class' => 'icoYoutube',
                'icon' => 'fab fa-youtube',
                'label' => __("Youtube"),
                'placeholder' => __("Youtube URL"),
                'isActive' => true,
            ],
            'instagram' => [
                'class' => 'icoInstagram',
                'icon' => 'fab fa-instagram',
                'label' => __("Instagram"),
                'placeholder' => __("Instagram URL"),
                'isActive' => true,
            ],
            'whatsapp' => [
                'class' => 'icoWhatsapp',
                'icon' => 'fab fa-whatsapp',
                'label' => __("Whatsapp"),
                'placeholder' => __("Whatsapp URL"),
                'isActive' => true,
            ],
            'threads' => [
                'class' => 'icoThreads ',
                'icon' => 'fa-brands fa-square-threads',
                'label' => __("Threads"),
                'placeholder' => __("Threads URL"),
                'isActive' => true,
            ],
            'twitter' => [
                'class' => 'icoXTwitter',
                'icon' => 'fa-brands fa-x-twitter',
                'label' => __("Twitter"),
                'placeholder' => __("Twitter URL"),
                'isActive' => true,
            ],
            'linkedin' => [
                'class' => 'icoLinkedIn',
                'icon' => 'fab fa-linkedin-in',
                'label' => __("LinkedIn"),
                'placeholder' => __("LinkedIn URL"),
                'isActive' => true,
            ],
            'patreon' => [
                'class' => 'icoPatreon',
                'icon' => 'fab fa-patreon',
                'label' => __("Patreon"),
                'placeholder' => __("Patreon URL"),
                'isActive' => true,
            ],
            'amazon' => [
                'class' => 'icoAmazon',
                'icon' => 'fab fa-amazon',
                'label' => __("Amazon"),
                'placeholder' => __("Amazon URL"),
                'isActive' => true,
            ],
            'goodreads' => [
                'class' => 'icoGoodreads',
                'icon' => 'fab fa-goodreads',
                'label' => __("Goodreads"),
                'placeholder' => __("Goodreads URL"),
                'isActive' => true,
            ],
            'tiktok' => [
                'class' => 'icoTikTok',
                'icon' => 'fab fa-tiktok',
                'label' => __("TikTok"),
                'placeholder' => __("TikTok URL"),
                'isActive' => true,
            ],
            'pinterest' => [
                'class' => 'icoPinterest',
                'icon' => 'fab fa-pinterest',
                'label' => __("Pinterest"),
                'placeholder' => __("Pinterest URL"),
                'isActive' => true,
            ],
            'reddit' => [
                'class' => 'icoReddit',
                'icon' => 'fab fa-reddit',
                'label' => __("Reddit"),
                'placeholder' => __("Reddit URL"),
                'isActive' => true,
            ],
            /*
            'telegram' => [
                'class' => 'icoTelegram',
                'icon' => 'fab fa-telegram',
                'label' => __("Telegram"),
                'placeholder' => __("Telegram URL"),
                'isActive' => true,
            ],
            'google' => [
                'class' => 'icoGoogle',
                'icon' => 'fab fa-google',
                'label' => __("Google"),
                'placeholder' => __("Google URL"),
                'isActive' => true,
            ],
            */
            'vimeo' => [
                'class' => 'icoVimeo',
                'icon' => 'fab fa-vimeo',
                'label' => __("Vimeo"),
                'placeholder' => __("Vimeo URL"),
                'isActive' => true,
            ],
            'tumblr' => [
                'class' => 'icoTumblr',
                'icon' => 'fab fa-tumblr',
                'label' => __("Tumblr"),
                'placeholder' => __("Tumblr URL"),
                'isActive' => true,
            ],
            'spreaker' => [
                'class' => 'icoSpreaker',
                'icon' => 'fas fa-heart',
                'label' => __("Spreaker"),
                'placeholder' => __("Spreaker URL"),
                'isActive' => true,
            ],
            /*
            'rss' => [
                'class' => 'icoRss',
                'icon' => 'fas fa-rss',
                'label' => __("RSS"),
                'placeholder' => __("RSS URL"),
                'isActive' => true,
            ],
            'gab' => [
                'class' => 'icoGab',
                'icon' => 'fab fa-gab',
                'label' => __("Gab"),
                'placeholder' => __("Gab URL"),
                'isActive' => true,
            ],
            */
            'clouthub' => [
                'class' => 'icoCloutHub',
                'icon' => 'fas fa-cloud',
                'label' => __("CloutHub"),
                'placeholder' => __("CloutHub URL"),
                'isActive' => true,
            ],
            'pinboard' => [
                'class' => 'icoPinboard',
                'icon' => 'fas fa-thumbtack',
                'label' => __("Pinboard"),
                'placeholder' => __("Pinboard URL"),
                'isActive' => true,
            ],
            'apple' => [
                'class' => 'icoApple',
                'icon' => 'fab fa-apple',
                'label' => __("Apple"),
                'placeholder' => __("Apple URL"),
            ],
            'imdb' => [
                'class' => 'icoIMDB',
                'icon' => 'fab fa-imdb',
                'label' => __("IMDB"),
                'placeholder' => __("IMDB URL"),
            ],
            'podcast' => [
                'class' => 'icoPodcast',
                'icon' => 'fas fa-podcast',
                'label' => __("Podcast"),
                'placeholder' => __("Podcast URL"),
            ],
            'shopping' => [
                'class' => 'icoShopping',
                'icon' => 'fas fa-shopping-cart',
                'label' => __("Shopping Website"),
                'placeholder' => __("Shopping Website URL"),
            ],
        ];
    }

    public static function getSocialMedia()
    {
        $obj = AVideoPlugin::getDataObject('CustomizeUser');
        $socialMedias = self::_getSocialMedia();
        foreach ($socialMedias as $key => $value) {
            $param = "socialMedia_{$key}";
            $socialMedias[$key]['isActive'] = $obj->$param;
        }
        return  $socialMedias;
    }

    public function getTags()
    {
        return [
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        ];
    }

    public function getDescription()
    {
        $txt = "Fine Tuning User Profile";
        return $txt;
    }

    public function getName()
    {
        return "CustomizeUser";
    }

    public function getUUID()
    {
        return "55a4fa56-8a30-48d4-a0fb-8aa6b3fuser3";
    }

    public function getPluginVersion()
    {
        return "4.1";
    }

    public function getEmptyDataObject()
    {
        global $advancedCustom, $advancedCustomUser;
        $obj = new stdClass();

        $socialMedias = self::_getSocialMedia();
        foreach ($socialMedias as $key => $value) {
            $param = "socialMedia_{$key}";
            $obj->$param = true;
            self::addDataObjectHelper($param, "<i class=\"{$value['icon']}\"></i> Enable {$value['placeholder']}", "The user can add his {$value['placeholder']}, and it will appear on his channel");
        }

        $obj->nonAdminCannotDownload = false;
        $obj->nonAdminCannotDeleteVideo = false;
        $obj->userCanAllowFilesDownload = false;
        $obj->userCanAllowFilesShare = false;
        $obj->userCanAllowFilesDownloadSelectPerVideo = false;
        $obj->userCanAllowFilesShareSelectPerVideo = false;
        $obj->blockEmbedFromSharedVideos = true;
        $obj->userCanProtectVideosWithPassword = true;
        $obj->userCanChangeVideoOwner = false;

        $obj->usersCanCreateNewCategories = !isset($advancedCustom->usersCanCreateNewCategories) ? false : $advancedCustom->usersCanCreateNewCategories;
        $obj->userCanNotChangeCategory = !isset($advancedCustom->userCanNotChangeCategory) ? false : $advancedCustom->userCanNotChangeCategory;
        $obj->userCanNotChangeUserGroup = false;

        $o = new stdClass();
        $o->type = array_merge(array('Default'), UserGroups::getAllUsersGroupsArray());
        $o->value = 0;
        $obj->userDefaultUserGroup = $o;
        $obj->userMustBeLoggedIn = !isset($advancedCustom->userMustBeLoggedIn) ? false : $advancedCustom->userMustBeLoggedIn;
        $obj->userMustBeLoggedInCloseButtonURL = "";
        $obj->onlyVerifiedEmailCanUpload = !isset($advancedCustom->onlyVerifiedEmailCanUpload) ? false : $advancedCustom->onlyVerifiedEmailCanUpload;
        $obj->sendVerificationMailAutomatic = !isset($advancedCustom->sendVerificationMailAutomatic) ? false : $advancedCustom->sendVerificationMailAutomatic;

        $obj->verificationMailTextLine1 = "Just a quick note to say a big welcome and an even bigger thank you for registering";
        $obj->verificationMailTextLine2 = "Cheers, %s Team.";
        $obj->verificationMailTextLine3 = "You are just one click away from starting your journey with %s!";
        $obj->verificationMailTextLine4 = "All you need to do is to verify your e-mail by clicking the link below";
        $obj->verificationMailButtonLabel = "Verify";

        $obj->recoverPassSubject = "Recover Pass from";
        $obj->recoverPassText = "You asked for a recover link, click on the provided link";

        $obj->unverifiedEmailsCanNOTLogin = !isset($advancedCustom->unverifiedEmailsCanNOTLogin) ? false : $advancedCustom->unverifiedEmailsCanNOTLogin;
        $obj->unverifiedEmailsCanNOTComment = false;
        $obj->unverifiedEmailsCanNOTLiveStream = true;
        $obj->newUsersCanStream = !isset($advancedCustom->newUsersCanStream) ? false : $advancedCustom->newUsersCanStream;
        $obj->doNotIdentifyByName = !isset($advancedCustomUser->doNotIndentifyByName) ? false : $advancedCustomUser->doNotIndentifyByName;
        self::addDataObjectHelper('doNotIdentifyByName', 'Do NOT identify user by Name', 'The identification order will be: <br>1. Name<br>2. email<br>3. Username<br>4. Channel Name');
        $obj->doNotIdentifyByEmail = !isset($advancedCustomUser->doNotIndentifyByEmail) ? false : $advancedCustomUser->doNotIndentifyByEmail;
        self::addDataObjectHelper('doNotIdentifyByEmail', 'Do NOT identify user by Email', 'The identification order will be: <br>1. Name<br>2. email<br>3. Username<br>4. Channel Name');
        $obj->doNotIdentifyByUserName = !isset($advancedCustomUser->doNotIndentifyByUserName) ? false : $advancedCustomUser->doNotIndentifyByUserName;
        self::addDataObjectHelper('doNotIdentifyByUserName', 'Do NOT identify user by Username', 'The identification order will be: <br>1. Name<br>2. email<br>3. Username<br>4. Channel Name');
        $obj->hideRemoveChannelFromModeYoutube = !isset($advancedCustom->hideRemoveChannelFromModeYoutube) ? false : $advancedCustom->hideRemoveChannelFromModeYoutube;
        $obj->showChannelBannerOnModeYoutube = !isset($advancedCustom->showChannelBannerOnModeYoutube) ? false : $advancedCustom->showChannelBannerOnModeYoutube;
        $obj->showChannelLiveTab = true;
        $obj->showChannelHomeTab = true;
        $obj->showChannelVideosTab = true;
        $obj->showChannelFeed = true;
        $obj->showArticlesTab = true;
        $obj->showAudioTab = true;
        $obj->showImageTab = true;
        $obj->showChannelProgramsTab = true;
        $obj->showBigVideoOnChannelVideosTab = true;
        $obj->encryptPasswordsWithSalt = !isset($advancedCustom->encryptPasswordsWithSalt) ? false : $advancedCustom->encryptPasswordsWithSalt;
        $obj->requestCaptchaAfterLoginsAttempts = !isset($advancedCustom->requestCaptchaAfterLoginsAttempts) ? 0 : $advancedCustom->requestCaptchaAfterLoginsAttempts;
        $obj->disableSignOutButton = false;
        $obj->disableNativeSignUp = !isset($advancedCustom->disableNativeSignUp) ? false : $advancedCustom->disableNativeSignUp;
        $obj->disableCompanySignUp = true;
        self::addDataObjectHelper('disableCompanySignUp', 'Disable Company SignUp', 'Company SignUp will enable a form with sone extra fields specific for companies');
        $obj->disableNativeSignIn = !isset($advancedCustom->disableNativeSignIn) ? false : $advancedCustom->disableNativeSignIn;
        $obj->disablePersonalInfo = !isset($advancedCustom->disablePersonalInfo) ? true : $advancedCustom->disablePersonalInfo;

        $o = new stdClass();
        $o->type = [0 => '--  None', 1 => '-- Random'] + self::getBGAnimationArray();
        $o->value = 1;
        $obj->loginBackgroundAnimation = $o;

        $obj->userCanChangeUsername = true;

        $obj->signInOnRight = false;
        $obj->doNotShowRightProfile = false;
        $obj->doNotShowLeftProfile = false;

        $obj->forceLoginToBeTheEmail = false;
        $obj->emailMustBeUnique = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->messageReplaceWelcomeBackLoginBox = $o;

        // added on 2019-02-11
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->messageToAppearBelowLoginBox = $o;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->messageToAppearAboveSignUpBox = $o;

        $obj->keepViewerOnChannel = false;
        $obj->showLeaveChannelButton = false;
        $obj->addChannelNameOnLinks = true;

        $obj->doNotShowTopBannerOnChannel = false;

        $obj->doNotShowMyChannelNameOnBasicInfo = false;
        $obj->doNotShowMyAnalyticsCodeOnBasicInfo = false;
        $obj->doNotShowMyAboutOnBasicInfo = false;

        $obj->MyChannelLabel = "My Channel";
        $obj->showAllAboutTextOnChannel = false;
        self::addDataObjectHelper('showAllAboutTextOnChannel', 'Show All About Text On Channel', 'Instead of show the [Show more] button, it will show the whole text');

        $obj->afterLoginGoToMyChannel = false;
        $obj->afterLoginGoToURL = "";
        $obj->afterLogoffGoToMyChannel = false;
        $obj->afterLogoffGoToURL = "";
        $obj->afterSignUpGoToURL = "";
        $obj->signupWelcomeAlert = "You are welcome and an even bigger thank you for registering";

        $obj->allowDonationLink = false;
        $obj->donationButtonLabel = ('Donation');
        $obj->allowWalletDirectTransferDonation = false;
        $obj->UsersCanCustomizeWalletDirectTransferDonation = false;
        $obj->donationWalletButtonLabel = ('Donate from your wallet');
        $obj->disableCaptchaOnWalletDirectTransferDonation = false;

        $obj->showEmailVerifiedMark = true;

        $obj->Checkmark1Enabled = true;
        $obj->Checkmark1HTML = '<i class="fas fa-check" data-toggle="tooltip" data-placement="bottom" title="Trustable User"></i>';
        $obj->Checkmark2Enabled = true;
        $obj->Checkmark2HTML = '<i class="fas fa-shield-alt" data-toggle="tooltip" data-placement="bottom" title="Official User"></i>';
        $obj->Checkmark3Enabled = true;
        $obj->Checkmark3HTML = '<i class="fas fa-certificate fa-spin" data-toggle="tooltip" data-placement="bottom" title="Premium User"></i>';

        $obj->autoSaveUsersOnCategorySelectedGroups = false;
        self::addDataObjectHelper('autoSaveUsersOnCategorySelectedGroups', 'Auto save new videos on category selected User Groups', 'Edit this plugin to select the user groups per category');
        $obj->enableExtraInfo = false;
        self::addDataObjectHelper('enableExtraInfo', 'Enable user extra info', 'You can add custom fields on user´s profile, Edit this plugin to tell what fields should be saved');
        $obj->videosSearchAlsoSearchesOnChannelName = false;
        self::addDataObjectHelper('videosSearchAlsoSearchesOnChannelName', 'Videos search also searches on ChannelName', 'With this checked when you searc a video we will also return the results that matches with the channel name');

        $obj->doNotShowPhoneMyAccount = true;
        $obj->doNotShowPhoneOnSignup = true;

        $obj->enableAffiliation = false;
        self::addDataObjectHelper('enableAffiliation', 'Enable user affiliation', 'Users that are marked as company can select other users to be afiliated to him');

        $obj->enableChannelCalls = true;
        self::addDataObjectHelper('enableChannelCalls', 'Enable Meeting Calls from channels', 'This feature requires the meet plugin enabled');

        $obj->onSignUpSubscribeToChannelsIds = '[]';
        self::addDataObjectHelper('onSignUpSubscribeToChannelsIds', 'On Sign Up Subscribe To Channels Ids', 'This field is an json array with ids of users that will be auto-subscribed');

        $o = new stdClass();
        $o->type = [0 => 'Do not Delete', 1 => '1 Day'];
        for ($i = 2; $i <= 7; $i++) {
            $o->type[$i] = "{$i} Days";
        }
        for ($i = 10; $i < 30; $i += 5) {
            $o->type[$i] = "{$i} Days";
        }
        for ($i = 30; $i <= 90; $i += 10) {
            $o->type[$i] = "{$i} Days";
        }
        $o->value = 0;
        $obj->autoDeleteUnverifiedUsersAfterDays = $o;
        return $obj;
    }

    function onUserSocketConnect()
    {
        global $global;
        echo file_get_contents($global['systemRootPath'] . 'plugin/CustomizeUser/onUserSocketConnect.js');
    }

    function onUserSocketDisconnect()
    {
        global $global;
        echo file_get_contents($global['systemRootPath'] . 'plugin/CustomizeUser/onUserSocketDisconnect.js');
    }

    static function getCallerButton($users_id, $class = '')
    {
        global $global;
        $users_id = intval($users_id);
        $varsArray = array('users_id' => $users_id, 'class' => $class);
        $filePath = $global['systemRootPath'] . 'plugin/CustomizeUser/View/channelCall.php';
        return getIncludeFileContent($filePath, $varsArray);
    }

    public static function isChannelCallEnabled()
    {
        $obj = AVideoPlugin::getDataObjectIfEnabled('CustomizeUser');
        if (!empty($obj->enableChannelCalls)) {
            $objSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
            return $objSocket->enableCalls;
        }
        return false;
    }

    public static function autoIncludeBGAnimationFile()
    {
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        $obj = AVideoPlugin::getObjectData('CustomizeUser');
        Layout::includeBGAnimationFile($obj->loginBackgroundAnimation->value);
        //Layout::includeBGAnimationFile('Animated3');
    }

    public function getUserOptions()
    {
        $obj = $this->getDataObject();
        $userOptions = [];

        if ($obj->Checkmark1Enabled) {
            $userOptions["Checkmark 1"] = "checkmark1";
        }
        if ($obj->Checkmark2Enabled) {
            $userOptions["Checkmark 2"] = "checkmark2";
        }
        if ($obj->Checkmark3Enabled) {
            $userOptions["Checkmark 3"] = "checkmark3";
        }
        return $userOptions;
    }

    public static function getBGAnimationArray()
    {
        if (!class_exists('Layout')) {
            $avideoLayout = AVideoPlugin::getObjectData('Layout');
        }
        $files = Layout::getBGAnimationFiles();
        $response = [];
        foreach ($files as $key => $value) {
            $response[$value['name']] = ucfirst($value['name']);
        }
        return $response;
    }

    public static function canDownloadVideosFromUser($users_id)
    {
        global $config, $canDownloadVideosFromUserReason;
        $canDownloadVideosFromUserReason = '';
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (!empty($obj->nonAdminCannotDownload) && !User::isAdmin()) {
            $canDownloadVideosFromUserReason = 'you selected nonAdminCannotDownload and you are not admin';
            return false;
        }
        if (empty($obj) || empty($obj->userCanAllowFilesDownload)) {
            $can = self::canDownloadVideos();
            if(!$can){
                $canDownloadVideosFromUserReason = 'your site configuration can download option is not checked';
            }
            return $can;
        }
        $user = new User($users_id);
        $can = !empty($user->getExternalOption('userCanAllowFilesDownload'));
        if(!$can){
            $canDownloadVideosFromUserReason = "you selected userCanAllowFilesDownload but the user {$users_id} the option 'Allow Download My Videos' on user profile is not checked";
        }
        return $can;
    }

    public static function canDownloadVideos()
    {
        global $config;
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (!empty($obj->nonAdminCannotDownload) && !User::isAdmin()) {
            return false;
        }
        return !empty($config->getAllow_download());
    }

    public static function setCanDownloadVideosFromUser($users_id, $value = true)
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj) || empty($obj->userCanAllowFilesDownload)) {
            return false;
        }
        $user = new User($users_id);
        return $user->addExternalOptions('userCanAllowFilesDownload', $value);
    }

    public static function canShareVideosFromUser($users_id)
    {
        global $advancedCustom;

        if (!isShareEnabled()) {
            //_error_log("NOT isShareEnabled()");
            return false;
        }

        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj) || empty($obj->userCanAllowFilesShare)) {
            return true;
        }
        $user = new User($users_id);
        return !empty($user->getExternalOption('userCanAllowFilesShare'));
    }

    public static function setCanShareVideosFromUser($users_id, $value = true)
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj) || empty($obj->userCanAllowFilesShare)) {
            return false;
        }
        $user = new User($users_id);
        return $user->addExternalOptions('userCanAllowFilesShare', $value);
    }

    public static function getSwitchUserCanAllowFilesDownload($users_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/CustomizeUser/switchUserCanAllowFilesDownload.php';
    }

    public static function getSwitchUserCanAllowFilesShare($users_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/CustomizeUser/switchUserCanAllowFilesShare.php';
    }

    public function getMyAccount($users_id)
    {
        global $global;
        $objcu = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");

        if (!empty($objcu) && !empty($objcu->userCanAllowFilesDownload)) {
            echo '<div class="form-group">
    <label class="col-md-4 control-label">' . __("Allow Download My Videos") . '</label>
    <div class="col-md-8 inputGroupContainer">';
            self::getSwitchUserCanAllowFilesDownload($users_id);
            echo '</div></div>';
        }
        if (!empty($objcu) && !empty($objcu->userCanAllowFilesShare)) {
            echo '<div class="form-group">
    <label class="col-md-4 control-label">' . __("Allow Share My Videos") . '</label>
    <div class="col-md-8 inputGroupContainer">';
            self::getSwitchUserCanAllowFilesShare($users_id);
            echo '</div></div>';
        }

        include $global['systemRootPath'] . 'plugin/CustomizeUser/getMyAccount.php';
    }

    public function getChannelButton()
    {
        global $global, $isMyChannel;
        if (!$isMyChannel) {
            return "";
        }
        $objcu = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        echo "<div class=\"clearfix\"></div><div style=\"float:right\">";
        if (!empty($objcu) && !empty($objcu->userCanAllowFilesDownload)) {
            echo '<div style=" margin:0 20px 10px 0;  height: 15px;">';
            echo '<div class="" style="max-width: 100px; float:right;"> ';
            self::getSwitchUserCanAllowFilesDownload(User::getId());
            echo '</div>';
            echo '<label class="control-label" style="float:right; margin:0 10px;">' . __("Allow Download My Videos") . '</label>';
            echo '</div>';
        }
        if (!empty($objcu) && !empty($objcu->userCanAllowFilesShare)) {
            echo '<div style=" margin:0 20px 10px 0; height: 15px;">';
            echo '<div class="" style="max-width: 100px; float:right;"> ';
            self::getSwitchUserCanAllowFilesShare(User::getId());
            echo '</div>';
            echo '<label class="control-label" style="float:right; margin:0 10px;">' . __("Allow Share My Videos") . '</label>';
            echo '</div>';
        }
        echo "</div>";
    }

    public function getVideoManagerButton()
    {
        global $isMyChannel;
        $isMyChannel = true;
        return self::getChannelButton();
    }

    public static function canDownloadVideosFromVideo($videos_id)
    {
        global $_lastCanDownloadVideosFromVideoReason, $canDownloadVideosFromUserReason;
        $_lastCanDownloadVideosFromVideoReason = '';
        if (!CustomizeUser::canDownloadVideos()) {
            $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
            if (!empty($obj->nonAdminCannotDownload)) {
                $_lastCanDownloadVideosFromVideoReason = 'CustomizeUser::canDownloadVideos nonAdminCannotDownload';
            } else {
                $_lastCanDownloadVideosFromVideoReason = 'CustomizeUser::canDownloadVideos site Configuration';
            }
            return false;
        }
        $video = new Video("", "", $videos_id);
        if (empty($video)) {
            $_lastCanDownloadVideosFromVideoReason = 'Empty video for video id ' . $videos_id;
            return false;
        }
        $users_id = $video->getUsers_id();
        if (!CustomizeUser::canDownloadVideosFromUser($users_id)) {
            $_lastCanDownloadVideosFromVideoReason = 'CustomizeUser::canDownloadVideosFromUser - '.$canDownloadVideosFromUserReason;
            return false;
        }
        $category = new Category($video->getCategories_id());
        if (is_object($category) && !$category->getAllow_download()) {
            $_lastCanDownloadVideosFromVideoReason = 'Category does not allow download';
            return false;
        }
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (!empty($obj->userCanAllowFilesDownloadSelectPerVideo)) {
            if (empty($video->getCan_download())) {
                $_lastCanDownloadVideosFromVideoReason = 'userCanAllowFilesDownloadSelectPerVideo';
                return false;
            }
        }
        return true;
    }

    public static function canShareVideosFromVideo($videos_id)
    {
        $video = new Video("", "", $videos_id);
        if (empty($video)) {
            _error_log("CustomizeUser::canShareVideosFromVideo video not found");
            return false;
        }
        $users_id = $video->getUsers_id();
        if (!self::canShareVideosFromUser($users_id)) {
            //_error_log("CustomizeUser::canShareVideosFromVideo canShareVideosFromUser($users_id) = false");
            return false;
        }
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (!empty($obj->userCanAllowFilesShareSelectPerVideo) && !empty($obj->blockEmbedFromSharedVideos)) {
            if (empty($video->getCan_share())) {
                _error_log("CustomizeUser::canShareVideosFromVideo video->getCan_share() = false");
                return false;
            }
        }
        return true;
    }

    public function onUserSignup($users_id)
    {
        global $global;
        $obj = $this->getDataObject();
        if (!empty($obj->onSignUpSubscribeToChannelsIds)) {
            $json = _json_decode($obj->onSignUpSubscribeToChannelsIds);
            if (!empty($json) && is_array($json)) {
                foreach ($json as $channel_owner_users_id) {
                    $subsc = new Subscribe(0, '', $channel_owner_users_id, $users_id);
                    $subsc->setNotify(1);
                    $subsc->save();
                }
            }
        }
    }

    public function getWatchActionButton($videos_id)
    {
        global $global, $video;
        if (!empty($videos_id) && empty($video)) {
            $video = Video::getVideo($videos_id);
        }
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/CustomizeUser/actionButton.php';
    }

    public function getHTMLMenuRight()
    {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->keepViewerOnChannel) {
            include $global['systemRootPath'] . 'plugin/CustomizeUser/channelMenuRight.php';
        }
    }

    public function getModeYouTube($videos_id)
    {
        global $global, $config;
        if (empty($videos_id)) {
            return false;
        }

        $svd = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
        if (!empty($svd)) {
            if ($svd->isWhiteListed()) {
                //_error_log("CustomizeUser::getModeYouTube this video is embed and whitelisted, we will by pass the security check");
                return true;
            } else {
                _error_log("CustomizeUser::getModeYouTube this video is NOT whitelisted, [" . (@$_SERVER['HTTP_REFERER']) . "]");
            }
        }
        $cansee = User::canWatchVideoWithAds($videos_id);
        $obj = $this->getDataObject();
        if (!$cansee) {
            $resp = Video::canVideoBePurchased($videos_id);
            if (!empty($resp) && $resp->canVideoBePurchased && isValidURL($resp->buyURL)) {
                header("Location: {$resp->buyURL}");
                exit;
            } else {
                forbiddenPage(__("Sorry, this video is private"));
            }
            /*
              if (!AVideoPlugin::isEnabledByName('Gallery') && !AVideoPlugin::isEnabledByName('YouPHPFlix2') && !AVideoPlugin::isEnabledByName('YouTube')) {
              header("Location: {$global['webSiteRootURL']}user?msg=" . urlencode(__("Sorry, this video is private")));
              } else {
              header("Location: {$global['webSiteRootURL']}?msg=" . urlencode(__("Sorry, this video is private")));
              }
              exit;
             *
             */
        } elseif ($obj->userCanProtectVideosWithPassword) {
            if (!$this->videoPasswordIsGood($videos_id)) {
                $video = Video::getVideoLight($videos_id);
                include "{$global['systemRootPath']}plugin/CustomizeUser/confirmVideoPassword.php";
                exit;
            }
        }
    }

    public static function videoPasswordIsGood($videos_id)
    {
        $video = new Video("", "", $videos_id);
        $videoPassword = $video->getVideo_password();
        if (empty($videoPassword)) {
            return true;
        }
        //var_dump($_REQUEST['video_password'], $videoPassword);exit;
        if (empty($_SESSION['video_password'][$videos_id]) || $videoPassword !== $_SESSION['video_password'][$videos_id]) {
            if (!empty($_REQUEST['video_password']) && $_REQUEST['video_password'] == $videoPassword) {
                _session_start();
                $_SESSION['video_password'][$videos_id] = $_REQUEST['video_password'];
                return true;
            }
            return false;
        }
        return true;
    }

    public function getEmbed($videos_id)
    {
        $this->getModeYouTube($videos_id);
    }

    public function getStart()
    {
        global $global;
        $obj = $this->getDataObject();
        $thisScriptFile = pathinfo($_SERVER["SCRIPT_FILENAME"]);
        useVideoHashOrLogin();
        if (
            empty($global['ignoreUserMustBeLoggedIn']) && !isBot() && !empty($obj->userMustBeLoggedIn) &&
            ((!isEmbed() && isVideo()) ||
                $thisScriptFile["basename"] === 'index.php' ||
                $thisScriptFile["basename"] === "channel.php" ||
                $thisScriptFile["basename"] === "channels.php" ||
                $thisScriptFile["basename"] === "trending.php" ||
                $thisScriptFile["basename"] === "player.php") &&
            !User::isLogged()
        ) {
            _error_log("CustomizeUser::userMustBeLoggedIn basename: {$thisScriptFile["basename"]}");
            gotToLoginAndComeBackHere('');
            //$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            //header("Location: {$global['webSiteRootURL']}user?redirectUri=" . urlencode($actual_link));
            exit;
        }
    }

    public function getPluginMenu()
    {
        global $global;
        $filePath = $global['systemRootPath'] . 'plugin/CustomizeUser/pluginMenu.php';
        return getIncludeFileContent($filePath);
    }

    public function getVideosManagerListButton()
    {
        global $global;
        $filePath = $global['systemRootPath'] . 'plugin/CustomizeUser/videosManagerListButton.php';
        return trim(preg_replace('/\s+/', ' ', getIncludeFileContent($filePath)));
    }

    public static function profileTabName($users_id)
    {
        $p = AVideoPlugin::loadPlugin("CustomizeUser");
        $obj = $p->getDataObject();
        $btn = '';
        if (!empty(self::showExtraInfo())) {
            $btn .= '<li><a data-toggle="tab" href="#tabExtraInfo' . $p->getUUID() . '">' . __('Extra Info') . '</a></li>';
        }
        if ($obj->allowWalletDirectTransferDonation && $obj->UsersCanCustomizeWalletDirectTransferDonation) {
            $btn .= '<li><a data-toggle="tab" href="#tabWalletDonation' . $p->getUUID() . '">' . __('Donations Options') . '</a></li>';
        }
        if ($obj->enableAffiliation) {
            $notifications = self::getAffiliationNotifications();
            $totalNotifications = count($notifications);

            if (!empty($totalNotifications)) {
                $totalNotifications = '<span class="badge badge-danger">' . count($notifications) . '</span>';
            } else {
                $totalNotifications = '';
            }

            $btn .= '<li><a data-toggle="tab" href="#tabAffiliation">' . __('Affiliations') . ' ' . $totalNotifications . '</a></li>';
        }
        $btn .= '<li><a data-toggle="tab" href="#tabSubscriptions"><i class="fas fa-bell"></i> ' . __('Following') . '</a></li>';
        $btn .= '<li><a onclick="avideoModalIframeFullScreen(webSiteRootURL+\'plugin/CustomizeUser/confirmDeleteUser.php?users_id=' . $users_id . '\');return false;" style="cursor: pointer;"><i class="fas fa-trash"></i> ' . __('Delete my account') . '</a></li>';
        return $btn;
    }

    public static function showExtraInfo()
    {
        global $_showExtraInfo;
        if (!isset($_showExtraInfo)) {
            $p = AVideoPlugin::loadPlugin("CustomizeUser");
            $obj = $p->getDataObject();
            $_showExtraInfo = false;
            if ($obj->enableExtraInfo) {
                $_showExtraInfo = true;
            }
            if (!$_showExtraInfo && !$obj->disableCompanySignUp) {
                $rows = Users_extra_info::getAll();
                $_showExtraInfo = !empty($rows);
            }
            if (!$_showExtraInfo && $obj->enableAffiliation) {
                $_showExtraInfo = true;
            }
        }
        return $_showExtraInfo;
    }

    public static function profileTabContent($users_id)
    {
        global $global;
        $p = AVideoPlugin::loadPlugin("CustomizeUser");
        $obj = $p->getDataObject();
        $btn = '';
        if (!empty(self::showExtraInfo())) {
            $tabId = 'tabExtraInfo' . $p->getUUID();
            include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabExtraInfo.php';
        }
        if ($obj->allowWalletDirectTransferDonation && $obj->UsersCanCustomizeWalletDirectTransferDonation) {
            $tabId = 'tabWalletDonation' . $p->getUUID();
            include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabDonation.php';
        }

        if ($obj->enableAffiliation) {
            $tabId = 'tabAffiliation';
            include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabAffiliation.php';
        }

        $tabId = 'tabSubscriptions';
        include $global['systemRootPath'] . 'plugin/CustomizeUser/View/tabSubscriptions.php';
        return $btn;
    }

    public function getUsersManagerListButton()
    {
        global $global;
        $p = AVideoPlugin::loadPlugin("CustomizeUser");
        $obj = $p->getDataObject();
        $btn = '';
        if (Permissions::canAdminUsers()) {
            $btn .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block" onclick="avideoModalIframeFullScreen(webSiteRootURL+\\\'plugin/CustomizeUser/confirmDeleteUser.php?users_id=\'+ row.id + \'\\\');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('Delete User') . '"><i class="fas fa-trash"></i> ' . __('Delete') . '</button>';
            if (self::showExtraInfo()) {
                $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block" onclick="avideoAlertAJAXHTML(webSiteRootURL+\\\'plugin/CustomizeUser/View/extraInfo.php?users_id=\'+ row.id + \'\\\');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('Show Extra Info') . '"><i class="fas fa-info"></i> ' . __('Extra Info') . '</button>';
            }
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block" onclick="avideoModalIframeSmall(webSiteRootURL+\\\'plugin/CustomizeUser/setSubscribers.php?users_id=\'+ row.id + \'\\\');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('This will add a fake number of subscribers on the user subscribe button') . '"><i class="fas fa-plus"></i> ' . __('Subscribers') . '</button>';
            if (AVideoPlugin::isEnabledByName('LoginControl')) {
                $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block" onclick="avideoModalIframe(webSiteRootURL+\\\'plugin/LoginControl/loginHistory.php?users_id=\'+ row.id + \'\\\');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('Login History') . '"><i class="fas fa-history"></i> ' . __('Login History') . '</button>';
            }
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block" onclick="avideoModalIframeSmall(webSiteRootURL+\\\'plugin/CustomizeUser/setPassword.php?users_id=\'+ row.id + \'\\\');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('Channel Password') . '"><i class="fas fa-lock"></i> ' . __('Password') . '</button>';
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block" onclick="swapUser(\'+ row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __('Use this user') . '"><i class="fas fa-user-friends"></i> ' . __('Use this user') . '</button>';
        }

        return $btn;
    }

    public function afterNewVideo($videos_id)
    {
        $obj = $this->getDataObject();
        if (!empty($obj->autoSaveUsersOnCategorySelectedGroups)) {
            $video = new Video("", "", $videos_id);
            $categories_id = $video->getCategories_id();
            $rows = Categories_has_users_groups::getAllFromCategory($categories_id);
            $userGroups = [];
            foreach ($rows as $value) {
                $userGroups[] = $value['users_groups_id'];
            }
            $userGroups = array_unique($userGroups);

            if (!empty($userGroups)) {
                _error_log("CustomizeUser::afterNewVideo: set user groups " . json_encode($userGroups));
                $video->setVideoGroups($userGroups);
                return $video->save(true, true);
            }
        }

        return false;
    }

    static function getAffiliateCompanies($users_id_affiliate, $activeOnly = true)
    {
        $obj = AVideoPlugin::getObjectData('CustomizeUser');
        if ($obj->enableAffiliation) {
            return Users_affiliations::getAll(0, $users_id_affiliate, $activeOnly);
        }
        return array();
    }

    static function getCompanyAffiliates($users_id_company, $activeOnly = true)
    {
        $obj = AVideoPlugin::getObjectData('CustomizeUser');
        if ($obj->enableAffiliation) {
            return Users_affiliations::getAll($users_id_company, 0, $activeOnly);
        }
        return array();
    }

    /*
      static function getNotifications() {
      global $global, $customUser_getNotifications;
      $return = array();

      $affiliation = self::getAffiliationNotifications();
      $return = array_merge($return, $affiliation);

      return $return;
      }
     *
     */

    static function getAffiliationNotifications()
    {
        global $global, $customUser_getAffiliationNotifications;
        $return = array();
        if (User::isLogged()) {
            $users_id = User::getId();
            if (!isset($customUser_getAffiliationNotifications)) {
                $customUser_getAffiliationNotifications = array();
            }
            if (!isset($customUser_getAffiliationNotifications[$users_id])) {
                $obj = AVideoPlugin::getObjectData('CustomizeUser');
                if ($obj->enableAffiliation) {
                    $isACompany = User::isACompany();
                    if ($isACompany) {
                        $rows = self::getCompanyAffiliates($users_id, false);
                    } else {
                        $rows = self::getAffiliateCompanies($users_id, false);
                    }

                    foreach ($rows as $value) {
                        if ($value['status'] == 'i') {
                            $users_id = $isACompany ? $value['users_id_affiliate'] : $value['users_id_company'];
                            $value['users_id'] = $users_id;

                            $value['js'] = 'avideoAlertOnce('
                                . '"' . __('You have a new affiliation request') . '",'
                                . "\"<a href='{$global['webSiteRootURL']}user?tab=tabAffiliation'>" . __('Please click here') . '</a>", "info", "' . $value['id'] . $value['modified'] . '");';

                            $return[] = $value;
                        }
                    }
                }
                $customUser_getAffiliationNotifications[$users_id] = $return;
            } else {
                $return = $customUser_getAffiliationNotifications[$users_id];
            }
        }
        return $return;
    }

    public function getFooterCode(): string
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/CustomizeUser/View/footer.php';
        return '';
    }

    public function getMobileInfo()
    {
        $obj = $this->getDataObject();
        $return = new stdClass();
        $return->userCanChangeVideoOwner = $obj->userCanChangeVideoOwner;
        $return->usersCanCreateNewCategories = $obj->usersCanCreateNewCategories;
        $return->userCanNotChangeCategory = $obj->userCanNotChangeCategory;
        $return->userCanNotChangeUserGroup = $obj->userCanNotChangeUserGroup;

        return $return;
    }

    function executeEveryDay()
    {
        $obj = AVideoPlugin::getDataObject('CustomizeUser');
        if (!empty($obj->autoDeleteUnverifiedUsersAfterDays->value)) {
            self::deleteOldUsers($obj->autoDeleteUnverifiedUsersAfterDays->value);
        }
    }

    public static function deleteOldUsers($days)
    {
        global $global;

        // Calculate the date before which records should be selected
        $dateThreshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Construct the SQL query to select users older than the threshold date
        $sql = "SELECT id, user, name FROM users WHERE created < ? AND (emailVerified is NULL OR emailVerified = 0)";
        $global['lastQuery'] = $sql;

        // Fetch users that match the criteria
        $res = sqlDAL::readSql($sql, "s", [$dateThreshold]);
        $users = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        if ($users !== false) {
            // Loop through each user and attempt to delete
            foreach ($users as $user) {
                $deleteSql = "DELETE FROM users WHERE id = ?";
                $deleteResult = sqlDAL::writeSql($deleteSql, "i", [$user['id']]);

                // Check if deletion was successful
                if (!$deleteResult) {
                    // If deletion fails, echo user ID, username, and name
                    echo "Failed to delete user: ID={$user['id']}, Username={$user['user']}, Name={$user['name']}".PHP_EOL;
                }
            }
        }
    }


    function getChannelPageButtons($users_id)
    {
        global $advancedCustomUser;
        $r = '';
        if (!empty($advancedCustomUser->showChannelFeed)) {
            $urlChannel = addLastSlash(User::getChannelLink($users_id));
            $rss = "{$urlChannel}rss";
            $mrss = "{$urlChannel}mrss";
            $roku = "{$urlChannel}roku.json";
            $r .= getFeedButton($rss, $mrss, $roku);
        }

        $r .= User::getAddChannelToGalleryButton($users_id);
        $r .= User::getBlockUserButton($users_id);
        $r .= Subscribe::getButton($users_id);
        return $r;
    }
}
