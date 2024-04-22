<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class AdBlockerDetector extends PluginAbstract
{

    public function getTags()
    {
        return array(
            PluginTags::$FREE,
        );
    }

    public function getDescription()
    {
        global $global;
        $desc = "This plugin detects and stops nasty ad blockers, ensuring ad revenue by prompting users to disable them on your AVideo site";
        return $desc;
    }

    public function getName()
    {
        return "AdBlockerDetector";
    }

    public function getUUID()
    {
        return "6dmaa392-7ad-blocker-aa33-detector";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function getStart()
    {
        global $global;
        if (!empty($_REQUEST["adBDetec"]) && empty($global['ignoreAdBlocker'])) {
            $global['ignoreAdBlocker'] = 1;
            require_once "{$global['systemRootPath']}plugin/AdBlockerDetector/index.php";
            exit;
        }
    }

    public function getHeadCode()
    {
        global $global, $config;
        $js = '';
        if(empty($global['ignoreAdBlocker'])){
            if (empty($_REQUEST["adBDetec"])) {
                $js .= '<script src="' . getURL('node_modules/blockadblock/blockadblock.js') . '" type="text/javascript"></script>';
                $js .= '<script src="' . getURL('plugin/AdBlockerDetector/script.js') . '" type="text/javascript"></script>';
                
                $js .= "<script>
                function reloadWithadBDetec() {
                    var currentUrl = window.location.href;
                    if (currentUrl.indexOf('?') === -1) {
                        currentUrl += '?adBDetec=1';
                    } else {
                        currentUrl += '&adBDetec=1';
                    }
                    window.location.href = currentUrl;
                }
                function adBlockDetected() {
                    modal.showPleaseWait();
                    avideoToastError('Ad Blocker detected');
                    $('.container, .container-fluid').html('');
                    reloadWithadBDetec();
                }
                if(typeof blockAdBlock === 'undefined') {
                    avideoToastError('Ad Blocker file not found');
                    adBlockDetected();
                }
                if(typeof checkScriptBlocking === 'undefined') {
                    avideoToastError('Ad Blocker JS checkScriptBlocking not found');
                    adBlockDetected();
                }
                
                </script>";
            }else{
                $js .= '<script>history.replaceState(null, null, "'.getRedirectUri().'");</script>';
            }
        }

        return $js;
    }



    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        return $obj;
    }
}
