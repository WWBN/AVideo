<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/WWBNIndex/Objects/WWBNIndexModel.php';

class WWBNIndex extends PluginAbstract 
{

    public function getTags() 
    {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        );
    }

    public function getDescription() 
    {
        // global $global;
        $desc = "Index platform into <a href='https://searchtube.com/'>Searchtube</a><br>";
        $desc .= "<b>Note:</b> Please refresh the page if buttons seems not working.";
        return $desc;
    }

    static function getToken()
    {
        global $global;
        $obj = new stdClass();
        $obj->plugin = "WWBNIndex";
        $obj->webSiteRootURL = $global['webSiteRootURL'];
        $obj->time = time();
        return encryptString($obj);
    }

    public function getName() 
    {
        return "WWBNIndex";
    }

    public function getUUID() 
    {
        return "WWBNIndex";
    }

    public function getPluginVersion() 
    {
        return "1.0";
    }

    // public function getEmptyDataObject() 
    // {
    //     global $global;
    //     $obj = new stdClass();
    //     return $obj;
    // }

    public function getPluginMenu() 
    {
        global $global;
        // HAS ACCOUNT
        $authenticated_btn = '<button type="button" class="btn btn-success btn-sm btn-xs btn-block" id="wwbnIndexAuthenticatedBtn"><i class="fas fa-user-check"></i>&nbsp; Authenticated</button>';

        $WWBNIndexModel = new WWBNIndexModel();
        $object_data = $WWBNIndexModel->getPluginData()[0]['object_data'];
        if ($object_data != "" && $object_data != null) {
            $object_data = json_decode($object_data);  // convert string to object
            $has_account = $object_data->username;
            $email = $object_data->email;
            $engine_name = $object_data->engine_name;
            $verified = $object_data->verified;
            $organic = $object_data->organic;
        }

        if (isset($has_account)) {
            if (isset($engine_name)) {
                if (isset($organic)) {
                    if (isset($verified)) {
                        $account = $this->getYouPortalUser($email);
                        if ($account->false == false && $account->data->status == 0) { // ACCOUNT NOT ACTIVE
                            $plugin_menu = '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAcctStatusBtn"><i class="fas fa-user"></i>&nbsp; Pending Account</button>';
                        } else {
                            $plugin_menu = $authenticated_btn;
                            $plugin_menu .= '<button type="button" class="btn btn-primary btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexSubmitIndexBtn"><i class="fas fa-paper-plane"></i>&nbsp; Submit Index</button>';
                            $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                        }
                    } else {
                        $plugin_menu = '<button type="button" class="btn btn-success btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexVerifyBtn"><i class="fas fa-envelope"></i>&nbsp; Verify Email</button>'; 
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAcctStatusBtn" style="display:none;"><i class="fas fa-user"></i>&nbsp; Pending Account</button>';
                    }
                    return $plugin_menu;
                }
                // CHECK INDEX STATUS
                $getFeedStatus = $this->getFeedStatus(parse_url($global['webSiteRootURL'])['host']);
                if ($getFeedStatus->error == true) {
                    if ($getFeedStatus->message == "URL not match") {
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                        $plugin_menu .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexActiveBtn"><i class="fas fa-video"></i>&nbsp; Index Inactive </button>';
                        return $plugin_menu;
                    }
                    return '<button class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexErrorBtn" data-title="'.$getFeedStatus->title.'" data-message="'.$getFeedStatus->message.'">Error</button>';
                }
                if ($getFeedStatus->indexed) { // INDEX - ALREADY ADDED IN PUBLISHER
                    if ($getFeedStatus->status == "active") {
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexActiveBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; Index Inactive </button>';
                        $plugin_menu .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexUnindexBtn"><i class="fas fa-video"></i>&nbsp; Unindex</button>';
                    } else if ($getFeedStatus->status == "review") { // PENDING / In REVIEW
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                    } else if ($getFeedStatus->status == "inactive") { // INACTIVE / REJECT
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                        $plugin_menu .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexActiveBtn"><i class="fas fa-video"></i>&nbsp; Index Inactive </button>';
                    }
                } else { 
                    if ($getFeedStatus->status == "active") {
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-success btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexApproveButNotIndexYetBtn"><i class="fas fa-video"></i>&nbsp; Approved </button>';
                    } else if ($getFeedStatus->status == "review") { // PENDING / In REVIEW
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                    } else if ($getFeedStatus->status == "inactive") { // INACTIVE / REJECT
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                        $plugin_menu .= '<button type="button" class="btn btn-danger btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexActiveBtn"><i class="fas fa-video"></i>&nbsp; Index Inactive </button>';
                    } 
                }
            } else {
                if (isset($verified)) {
                    $account = $this->getYouPortalUser($email);
                    if ($account->false == false && $account->data->status == 0) { // ACCOUNT NOT ACTIVE
                        $plugin_menu = '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAcctStatusBtn"><i class="fas fa-user"></i>&nbsp; Pending Account</button>';
                    } else {
                        $plugin_menu = $authenticated_btn;
                        $plugin_menu .= '<button type="button" class="btn btn-primary btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexSubmitIndexBtn"><i class="fas fa-paper-plane"></i>&nbsp; Submit Index</button>';
                        $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexIndexInReviewBtn" style="display: none;"><i class="fas fa-video"></i>&nbsp; In Review</button>';
                    }
                } else {
                    $plugin_menu = '<button type="button" class="btn btn-success btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexVerifyBtn"><i class="fas fa-envelope"></i>&nbsp; Verify Email</button>'; 
                    $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAcctStatusBtn" style="display:none;"><i class="fas fa-user"></i>&nbsp; Pending Account</button>';
                }
            }
        } else {
            $plugin_menu = '<button type="button" class="btn btn-primary btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAuthBtn"><i class="fas fa-user-unlock"></i>&nbsp; Authenticate</button>';
            $plugin_menu .= '<button type="button" class="btn btn-success btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexVerifyBtn" style="display:none;"><i class="fas fa-envelope"></i>&nbsp; Verify Email</button>';
            $plugin_menu .= '<button type="button" class="btn btn-warning btn-sm btn-xs btn-block wwbn-index-btn" id="wwbnIndexAcctStatusBtn" style="display:none;"><i class="fas fa-user"></i>&nbsp; Pending Account</button>';
        }
        $plugin_menu .= '<input type="hidden" name="abcdefghijklmnop">'; //wwbnCode
        return $plugin_menu;
    }

    public function getFooterCode() 
    {
        global $global;
        ob_start();
        include $global['systemRootPath'] . 'plugin/WWBNIndex/modal.php';
        $content = ob_get_clean();
        return $content;
    }

    public function getYouPortalUser($email = "")
    {
        $configuration = new Configuration();
        $data = array(
            "apiName"   => "getUser",
            "email"     => ($email != "") ? $email : $configuration->getContactEmail()
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            return (object) array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with get user api.");
        }
        curl_close($ch);
        return $response;
    }

    private function getFeedStatus($host) 
    {
        $configuration = new Configuration();
        $data = array(
            "apiName"       => "getFeedStatus",
            "avideo_id"     => getPlatformId(),
            "engine_name"   => $configuration->getWebSiteTitle(),
            "host"          => $host,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            return (object) array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with get feed api.");
        }
        curl_close($ch);
        return $response;
    }
}
