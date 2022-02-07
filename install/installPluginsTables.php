<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

function _rsearch($folder, $pattern)
{
    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
    $fileList = [];
    foreach ($files as $file) {
        foreach ($file as $key => $value) {
            $file[$key] = "{$folder}{$dir}/{$value}";
        }
        $fileList = array_merge($fileList, $file);
    }
    usort(
        $fileList,
        function ($a, $b) {
                return preg_match('/SendRecordedToEncoder/', $a) ? 1 : 0;
            }
    );
    return $fileList;
}

$option = intval(@$argv[1]);

if (empty($option)) {
    echo "1 - Install tables and enable plugins\n";
    echo "2 - Install tables only\n";
    echo "3 - Enable plugins only\n";
    echo "4 - Update plugins only\n";
    echo "Choose an option: ";
    ob_flush();
    $option = trim(readline(""));
}
if ($option == 1 || $option == 2) {
    $files = _rsearch("{$global['systemRootPath']}plugin/", "/install\/install.sql$/i");
    $templine = '';
    $global['mysqli']->begin_transaction();
    $totalFiles = count($files);
    $countFiles = 0;
    foreach ($files as $value) {
        $countFiles++;
        $lines = file($value);
        $totalLines = count($lines);
        $countLines = 0;
        foreach ($lines as $line) {
            $countLines++;
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    if (!$global['mysqli']->query($templine)) {
                        echo($value . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                        die(json_encode($obj));
                    } else {
                        echo "[{$countFiles}/{$totalFiles}][{$countLines}/{$totalLines}] Success performing query from $value\n";
                    }
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                } 

                $templine = '';
            }
        }
    }
    $global['mysqli']->commit();
}
if ($option == 1 || $option == 3) {
    $EnablePlugins = [
        ['1apicbec-91db-4357-bb10-ee08b0913778', 'API', 'API'],
        ['6daca392-7b14-44fb-aa33-51cba620d92e', 'CookieAlert', 'CookieAlert'],
        ['55a4fa56-8a30-48d4-a0fb-8aa6b3f69033', 'CustomizeAdvanced', 'CustomizeAdvanced'],
        ['55a4fa56-8a30-48d4-a0fb-8aa6b3fuser3', 'CustomizeUser', 'CustomizeUser'],
        ['a06505bf-3570-4b1f-977a-fd0e5cab205d', 'Gallery', 'Gallery'],
        ['e06b161c-cbd0-4c1d-a484-71018efa2f35', 'Live', 'Live'],
        ['5310b394-b54f-48ab-9049-995df4d95239', 'NextButton', 'NextButton'],
        ['plist12345-370-4b1f-977a-fd0e5cabtube', 'Programs', 'PlayLists'],
        ['b5e223db-785b-4436-8f7b-f297860c9be0', 'ReportVideo', 'ReportVideo'],
        ['f7596843-51b1-47a0-8bb1-b4ad91f87d6b', 'TheaterButton', 'TheaterButton'],
        ['45432a78-d0c6-47f3-8ac4-8fd05f507386', 'User_Location', 'User_Location'],
        ['4c1f4f76-b336-4ddc-a4de-184efe715c09', 'MobileManager', 'MobileManager'],
        ['52chata2-3f14-49db-958e-15ccb1a07f0e', 'Chat2', 'Chat2'],
        ['cf145581-7d5e-4bb6-8c12-48fc37c0630d', 'LiveUsers', 'LiveUsers'],
        ['996c9afb-b90e-40ca-90cb-934856180bb9', 'MP4ThumbsAndGif', 'MP4ThumbsAndGif'],
        ['eb6e2808-d876-4488-94cb-2448a6b14e0b', 'SendRecordedToEncoder', 'SendRecordedToEncoder'],
        ['f2hls8c6-9359-4cc1-809f-fac32c8a4333', 'VideoHLS', 'VideoHLS'],
        ['4b9142c0-f0c3-42be-8fe5-a4775111239c', 'VideoResolutionSwitcher', 'VideoResolutionSwitcher'],
        ['28e74f9a-a2ef-4644-86f0-40234ae7c1b5', 'VideoThumbnails', 'VideoThumbnails'],
        ['meet225-3807-4167-ba81-0509dd280e06', 'Meet', 'Meet'],
        ['YPTSocket-5ee8405eaaa16', 'YPTSocket', 'YPTSocket'],
        ['Scheduler-5ee8405eaaa16', 'Scheduler', 'Scheduler'],
    ];
    foreach ($EnablePlugins as $value) {
        if ($plugin = Plugin::getOrCreatePluginByName($value[2], 'active')) {
            echo "Success enable plugin ($value[2])  " . $plugin['name'] . "\n";
        } else {
            echo "ERROR enable plugin ($value[2]) \n";
        }
    }
}
if ($option == 4) {
    echo "Searching for {$global['systemRootPath']}plugin/[plugin]/install/install.sql" . PHP_EOL;
    $files = _rsearch("{$global['systemRootPath']}plugin/", "/install\/install.sql$/i");
    $templine = '';
    foreach ($files as $value) {
        if (preg_match("/User_Location/", $value)) {
            continue;
        }
        if (preg_match("/Customize/", $value)) {
            continue;
        }

        echo "Checking tables from {$value}" . PHP_EOL;
        $lines = file($value);
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                if (!$global['mysqli']->query($templine)) {
                    echo($value . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                //die(json_encode($obj));
                } else {
                    echo "Success performing query from $value\n";
                }
                $templine = '';
            }
        }
    }
    $plugins = Plugin::getAvailablePlugins();
    foreach ($plugins as $value) {
        $p = AVideoPlugin::loadPlugin($value->dir);
        if (empty($p)) {
            continue;
        }
        $currentVersion = $p->getPluginVersion();
        if (AVideoPlugin::updatePlugin($value->dir)) {
            $p = AVideoPlugin::loadPlugin($value->dir, true);
            $newVersion = $p->getPluginVersion();
            echo "{$value->dir} updated FROM {$currentVersion} TO {$newVersion}" . PHP_EOL;
        }
    }
}
echo "Option {$option} finished \n";
