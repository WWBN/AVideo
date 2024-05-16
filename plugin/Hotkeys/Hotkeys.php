<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Hotkeys extends PluginAbstract
{

    public function getTags()
    {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }
    public function getDescription()
    {
        global $global;
        $desc = "Enable hotkeys for videos";
        $desc .= "<ul>
        <li>Space bar toggles play/pause.</li>
        <li>Right and Left Arrow keys seek the video forwards and back.</li>
        <li>Up and Down Arrow keys increase and decrease the volume.</li>
        <li>M key toggles mute/unmute.</li>
        <li>F key toggles fullscreen off and on. (Does not work in Internet Explorer, it seems to be a limitation where scripts
        cannot request fullscreen without a mouse click)</li>
        <li>Double-clicking with the mouse toggles fullscreen off and on.</li>
        <li>Number keys from 0-9 skip to a percentage of the video. 0 is 0% and 9 is 90%.</li>
        </ul>";
        return $desc;
    }

    public function getName()
    {
        return "Hotkeys";
    }

    public function getPluginVersion()
    {
        return "1.1";
    }

    public function getUUID()
    {
        return "11355314-1b30-ff15-afb-67516fcccff7";
    }

    public function getHelp()
    {
        $obj = $this->getDataObject();
        $html = "<h2 id='Hotkeys help' >" . __('Hotkeys') . "</h2><p>" . __("When you are watching media, you can use these keyboard-shortcuts.") . "</p><table class='table'><tbody>";
        $html .= "<tr><td>" . __("Seek") . "</td><td>" . __("Left") . "/" . __("right") . "-" . __("arrow") . "</td></tr><tr><td>";
        if ($obj->ReplaceVolumeWithPlusMinus) {
            $html .= __("Volume") . "</td><td>+/-</td></tr>";
        } else {
            $html .= __("Volume") . "Volume</td><td>" . __("Up") . "/" . __("Down") . "-" . __("Arrow") . "</td></tr>";
        }
        if ($obj->Fullscreen) {
            $html .= "<tr><td>" . __("Fullscreen") . "</td><td>" . $obj->FullscreenKey . "</td></tr>";
        }
        if ($obj->PlayPauseKey == " ") {
            $html .= "<tr><td>" . __("Play") . "/" . __("pause") . "</td><td>" . __("space") . "</td></tr>";
        } else {
            $html .= "<tr><td>" . __("Play") . "/" . __("pause") . "</td><td>" . $obj->PlayPauseKey . "</td></tr>";
        }
        return $html . "</tbody></table>";
    }
    public function getJSFiles()
    {
        if (isVideo()) {
            return array("node_modules/videojs-hotkeys/videojs.hotkeys.min.js");
        }
        return array();
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        return $obj;
    }

    

    public function getFooterCode()
    {
        global $global;
        $obj = $this->getDataObject();

        if (isVideo()) {

            $tmp = file_get_contents("{$global['systemRootPath']}plugin/Hotkeys/hotkeys.js");

            PlayerSkins::getStartPlayerJS($tmp);
        }
        $js = '<script src="' . getURL('plugin/Hotkeys/listener.js') . '" type="text/javascript"></script>';

        return $js;
    }
}
