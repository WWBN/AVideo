<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!Permissions::canSeeLogs()) {
    forbiddenPage("You cannot see the logs");
}

if (!empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage("This page is disabled");
}

$printedarray = array();
AVideoLog::$ERROR;

$collapsibleOpen = "<div class='collapsible-container terminal-alert'>
    <button class=\"btn btn-default btn-ghost collapsibleOpen collapsibleBtn\">+</button>
  <button class=\"lbl-toggle btn btn-default btn-ghost collapsibleClose collapsibleBtn\" style='display:none;'>-</button>
<div class=\"collapsible-content-closed \">...</div>
<div class=\"collapsible-content\" style=\"display:none;\">";
$collapsibleClose = "</div></div>";

$outputText = "";
$outputTextErrors = array();
$outputTextWarnings = array();
$linesAdded = 0;
$isCollapsed = true;

function e($text) {
    if (empty($text)) {
        return false;
    }
    global $printedarray, $global, $mysqlPass,
    $outputText, $outputTextErrors, $outputTextWarnings,
    $collapsibleOpen, $collapsibleClose, $linesAdded, $isCollapsed;

    $uniqid = "";
    $class = "";
    $collapsible = true;
    $uniqid = uniqid();
    if (
            preg_match("/(AVideoLog::SECURITY)/", $text, $matches) ||
            preg_match("/(Prepare failed)/i", $text, $matches) ||
            preg_match("/(AVideoLog::ERROR)/", $text, $matches) ||
            preg_match("/(fatal)/i", $text, $matches)) {
        $outputTextErrors[] = array($uniqid, $text, $matches[1]);
        $class = "terminal-alert terminal-alert-error ";
        $style = "background-color: #A00; color:#FFF;font-weight: bold;";
        $collapsible = false;
    } else
    if (preg_match("/(PHP Warning)/i", $text, $matches)) {
        $class = "terminal-alert terminal-alert-primary";
        $outputTextWarnings[] = array($uniqid, $text, $matches[1]);
        $collapsible = false;
    } else if (preg_match("/PHP Notice/", $text)) {
        $class = "logNotice";
    } else if (preg_match("/(AVideoLog::WARNING)/", $text, $matches) || preg_match("/AVideoLog::DEBUG/", $text)) {
        $class = "logDebug";
    }

    if ($collapsible) {
        $log = preg_replace("/^[^\]]+/", "", $text);
        if (!empty($log)) {
            if (in_array($log, $printedarray)) {
                $class = "logIgnore";
            }
            $printedarray[] = $log;
        }
    }

    $removeLinesWithWords = array('password', 'recoverPass', 'pass');
    foreach ($removeLinesWithWords as $value) {
        if (preg_match("/{$value}/i", $text)) {
            $text = preg_replace("/[^ ]/", "*", $text) . " - {$value}";
            break;
        }
    }

    $text = preg_replace("/[^ ]+@[^ ]+/i", "myemail@mydomain.com", $text);

    $text = htmlentities($text);
    if ($collapsible && !$isCollapsed) {
        $outputText .= $collapsibleOpen;
        $isCollapsed = true;
    }
    if (!$collapsible && $isCollapsed) {
        if ($linesAdded === 0) {
            $outputText = "";
        } else {
            $outputText .= $collapsibleClose;
        }
        $isCollapsed = false;
    }
    if (!$collapsible && !$isCollapsed) {
        
    }
    $linesAdded++;
    $outputText .= "<div class='logLine {$class}' id='{$uniqid}'>"
            . "<span class='lineCount'>#{$linesAdded}</span> {$text}"
            . "</div>" . PHP_EOL;
    return true;
}

if (empty($global['logfile'])) {
    die("Log variable does not exists");
}

if (!file_exists($global['logfile'])) {
    die("you may have problems on the write permission for the log file");
}

$lines = tail($global['logfile'], 5000, true, true);
$lines = array_reverse($lines);
foreach ($lines as $key => $line) {
    if (empty($line[0])) {
        unset($line[$key]);
    } else {
        break;
    }
}
$outputText .= $collapsibleOpen;
foreach ($lines as $line) {
    e($line[0]);
}
$outputText .= $collapsibleClose;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Logs <?php echo date("Y-M-d H:i:s"); ?></title>
        <link rel="stylesheet" href="<?php echo $global['webSiteRootURL']; ?>view/css/terminal.min.css" />
        <style>
            html {
                scroll-behavior: smooth;
            }
            input[type='checkbox'] { display: none; } 
            :root {
                --global-font-size: 15px;
                --global-line-height: 1.4em;
                --global-space: 10px;
                --font-stack: Menlo, Monaco, Lucida Console, Liberation Mono,
                    DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace,
                    serif;
                --mono-font-stack: Menlo, Monaco, Lucida Console, Liberation Mono,
                    DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace,
                    serif;
                --background-color: #222225;
                --page-width: 60em;
                --font-color: #e8e9ed;
                --invert-font-color: #222225;
                --secondary-color: #a3abba;
                --tertiary-color: #a3abba;
                --primary-color: #62c4ff;
                --error-color: #ff3c74;
                --progress-bar-background: #3f3f44;
                --progress-bar-fill: #62c4ff;
                --code-bg-color: #3f3f44;
                --input-style: solid;
                --display-h1-decoration: none;
            }
            .logDebug{

            }
            .logNotice{
                color: #62c4ff;
            }
            .logIgnore{

            }
            .logCode{
                padding-left: 47px;
            }
            .collapsibleBtn{
                position: absolute;
                top: 0;
                left: -40px;
                width: 30px;
                height: 30px;
                padding: 5px;
            }
            .collapsible-container{
                display: flex;
                position: relative;
            }
            .collapsible-container.active{

            }
            .logLine{
                margin: 0;
            }
            .collapsible-content .logLine{
                line-height: 1em;
                display: inline;
            }
            .lineCount{
                color: #777;
                -moz-user-select: none;
                -khtml-user-select: none;
                -webkit-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            .terminal-card .btn{
                padding: 2px 15px;
                border: none;
                font-size: 0.8em;
            }
        </style>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js"></script>
    </head>

    <body  class="terminal">
        <div class="container-fluid">
            <div class="terminal-nav">
                <header class="terminal-logo">
                    <div class="logo terminal-prompt">
                        Log Date <?php echo date("Y-M-d H:i:s"); ?>
                    </div>
                </header>
            </div>
            If you have a problem with your installation, feel free to share this log information on GitHub, 
            on this log we hide all the sensitive information from your log. but it still very helpfull to find issues.
            <?php
            if (count($outputTextErrors)) {
                ?>
                <hr>
                <div class="terminal-card">
                    <header>
                        <?php echo "Total: " . count($outputTextErrors) . " errors needs your attention"; ?>
                    </header>
                    <div>
                        <div class="btn-group">
                            <?php
                            $count = 0;
                            foreach ($outputTextErrors as $value) {
                                $count++;
                                ?>
                                <?php echo "<a href='#{$value[0]}'  class='btn btn-default btn-ghost scrollToError'>#{$count} - {$value[2]}</a>"; ?>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            if (count($outputTextWarnings)) {
                ?>
                <hr>
                <div class="terminal-card">
                    <header>
                        <?php echo "Total: " . count($outputTextWarnings) . " warnings this maybe mean something"; ?>

                    </header>
                    <div>
                        <div class="btn-group">
                            <?php
                            $count = 0;
                            foreach ($outputTextWarnings as $value) {
                                $count++;
                                echo "<a href='#{$value[0]}'  class='btn btn-default btn-ghost scrollToError'>#{$count} - {$value[2]}</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
            <hr>
            <div class="btn-group">
                <button class="btn btn-default btn-ghost expandAll">Expand All</button>
                <button class="btn btn-default btn-ghost collapseAll">Collapse All</button>
            </div>
            <pre class="logCode">
                <?php
                $outputText = str_replace(array(parse_url($global['webSiteRootURL'], PHP_URL_HOST), $global['systemRootPath'], $global['salt'], $mysqlPass, $mysqlUser, $mysqlHost, $mysqlDatabase), array("www.mysite.com", "path/to/my/streamer/site/", "mySalt", "myMySQLPass", "myMySQLUser", "myMySQLHost", "myMySQLDatabase"), $outputText);

                echo $outputText;
                ?>
            </pre>
        </div>
        <script>
            $(function () {
                $(".collapsibleBtn").click(function () {
                    var parent = $(this).parent(".collapsible-container");
                    var content = $(parent).find(".collapsible-content");
                    var contentClosed = $(parent).find(".collapsible-content-closed");
                    var open = $(parent).find(".collapsibleOpen");
                    var close = $(parent).find(".collapsibleClose");
                    if ($(content).is(":visible")) {
                        $(content).slideUp({
                            complete: function () {
                                $(parent).removeClass('active');
                                $(contentClosed).show();
                                $(open).show();
                                $(close).hide();
                            }
                        });
                    } else {
                        $(contentClosed).hide();
                        $(content).slideDown({
                            complete: function () {
                                $(parent).addClass('active');
                                $(open).hide();
                                $(close).show();
                            }
                        });
                    }
                });

                $(".expandAll").click(function () {
                    $(".collapsible-content-closed").hide();
                    $(".collapsible-content").show();
                    $(".collapsible-container").addClass('active');
                    $(".collapsibleOpen").hide();
                    $(".collapsibleClose").show();
                });

                $(".collapseAll").click(function () {
                    $(".collapsible-content-closed").show();
                    $(".collapsible-content").hide();
                    $(".collapsible-container").removeClass('active');
                    $(".collapsibleOpen").show();
                    $(".collapsibleClose").hide();
                });

                $(".scrollToError").click(function () {
                    $('html, body').animate({scrollTop: $($(this).attr('href')).position().top}, 'fast');
                });
            });
        </script>
    </body>
</html>