<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
// get the encoder
$_REQUEST['site'] = get_domain($global['webSiteRootURL']);
$json = PlayList::getEPG();
$generated = $json->generated;

$precision = 1; // minutes
$timeSteps = 1440 / $precision; // how many cells each row

$time = time();

function secondsToDayTime($seconds) {
    $duration = secondsToDuration($seconds);
    $parts = explode(":", $duration);
    unset($parts[2]);
    return implode(":", $parts);
}
?>
<style>
    td.hasVideo{
        background-color: #EFE;
    }
    td.playingNow{
        background-color: #CFC;
    }
    td.finished, td.finished a{
        background-color: #EEE;
        color: #AAA;
    }
    #epgDiv{
        position: relative;
    }
    #epgTable > thead > tr > th{
        font-size: 0.7em;
    }
    #epgTable th, 
    #epgTable td{
        padding: 2px 4px;
    }
    #epgTable td:empty{
        padding: 0;
        border: none;
        width: 2px;
    }
    #timeline{
        background-color: red;
        border: solid 1px #A00;
        width: 2px;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0.3;
    }
    #epgTable th{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .hasVideo{
        overflow: hidden;
    }
    .hasVideo > a {
        font-size: 0.7em;
        max-height: 26px;
        display: block;
    }
</style>
<a class="btn btn-default pull-right" href="#timeline"> <i class="far fa-clock"></i> <?php echo __('Now'); ?> </a>
<hr>
<div class="table-responsive" id="epgDiv">
    <table class="table table-hover table-bordered  table-striped header-fixed" id="epgTable">
        <thead>
            <tr>
                <th scope="col"><?php echo __("Program"); ?></th>
                <?php
                for ($i = 0; $i < $timeSteps; $i++) {
                    $duration = secondsToDayTime($i * $precision * 60);
                    $currentCellTime = strtotime("today " . $duration);
                    $durationNext = secondsToDuration(($i + 1) * $precision * 60);
                    $currentCellTimeNext = strtotime("today " . $duration);
                    $class = "";
                    if ($time >= $currentCellTime && $time <= $currentCellTimeNext) {
                        $class .= " now ";
                    }
                    if ($i % 15 === 0) {
                        echo '<th scope="col" class="' . $class . '" colspan="15">' . $duration . '</th>';
                    }
                }
                ?>
                <th scope="col">00:00</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($json->sites as $key => $value) {
                if ($key == $_REQUEST['site']) {
                    $site = $value;
                    foreach ($site->channels as $users_id => $channel) {
                        $identification = User::getNameIdentificationById($users_id);
                        foreach ($channel->playlists as $playlist) {
                            if (!PlayLists::showOnTV($playlist->playlists_id)) {
                                continue;
                            }
                            echo "<tr>";
                            if (!empty($playlist->programme)) {

                                $pl = new PlayList($playlist->playlists_id);
                                ?>
                            <th class="programColumn">
                                <?php echo $playlist->playlists_id, " ", $pl->getName(); ?><br>
                                <small><?php echo $identification; ?></small>
                            </th>
                            <?php
                            for ($i = 0; $i < $timeSteps + 1; $i++) {
                                $tooltip = "";
                                $currentCellTime = strtotime("today " . secondsToDuration($i * $precision * 60));
                                $durationNext = secondsToDuration(($i + 1) * $precision * 60);
                                $currentCellTimeNext = strtotime("today " . $durationNext);
                                $currentCellDate = date("Y/m/d H:i:s", $currentCellTime);
                                $currentCellTimeNextDate = date("Y/m/d H:i:s", $currentCellTimeNext);
                                $content = "";
                                $colspan = 0;
                                $class = "";
                                $firstColumns = true;
                                foreach ($playlist->programme as $plItem) {
                                    //$content = "-Next:{$currentCellTimeNextDate}<br>-Stop: {$plItem->stop_date}<br>-$currentCellTimeNext >= $plItem->stop<br>" ;
                                    if ($currentCellTime >= $plItem->start && $currentCellTimeNext <= $plItem->stop) {
                                        $images = Video::getImageFromFilename($plItem->filename);
                                        $img = "<img src='{$images->thumbsJpg}' class='img img-responsive' style='height: 60px; padding: 2px;'><br>";
                                        $title = addcslashes("{$img} {$plItem->title}<br>{$plItem->start_date}", '"');
                                        $tooltip = "data-toggle=\"tooltip\" data-html=\"true\" data-original-title=\"{$title}\" data-placement=\"bottom\"  ";
                                        $colspan++;
                                        $class .= " hasVideo ";
                                        $link = PlayLists::getLinkToLive($playlist->playlists_id);
                                        $content = " <span class='label label-primary'>" . $plItem->duration . "</span><br><a href='{$link}'>".$plItem->title."</a>";
                                        //$content = "<br>Stop: {$plItem->stop_date}<br>Next:{$currentCellTimeNextDate}<br>$currentCellTimeNext >= $plItem->stop<br>" . $plItem->title;
                                        if ($time >= $plItem->start && $time <= $plItem->stop) {
                                            $class .= " playingNow ";
                                        } else if ($time >= $plItem->stop) {
                                            $class .= " finished ";
                                        }
                                        for (; $i < $timeSteps + 1; $i++) {
                                            $currentCellTime = strtotime("today " . secondsToDuration($i * $precision * 60));
                                            $currentCellDate = date("Y/m/d H:i:s", $currentCellTime);
                                            if ($currentCellTime <= $plItem->stop) {
                                                $colspan++;
                                                continue;
                                            } else {
                                                break;
                                            }
                                        }
                                        break;
                                    } else if ($currentCellTime >= $plItem->start && $currentCellTime <= $plItem->stop) {
                                        $images = Video::getImageFromFilename($plItem->filename);
                                        $img = "<img src='{$images->thumbsJpg}' class='img img-responsive' style='height: 60px; padding: 2px;'><br>";
                                        $title = addcslashes("{$img} {$plItem->title}<br>{$plItem->start_date}", '"');
                                        $tooltip = "data-toggle=\"tooltip\" data-html=\"true\" data-original-title=\"{$title}\" data-placement=\"bottom\"  ";
                                        $colspan++;
                                        $class .= " hasVideo ";
                                        $link = PlayLists::getLinkToLive($playlist->playlists_id);
                                        $content = " <span class='label label-primary'>" . $plItem->duration . "</span><br><a href='{$link}'>".$plItem->title."</i></a>";
                                        //$content = "<br>Stop: {$plItem->stop_date}<br>Next:{$currentCellTimeNextDate}<br>$currentCellTimeNext >= $plItem->stop<br>" . $plItem->title;
                                        if ($time >= $plItem->start && $time <= $plItem->stop) {
                                            $class .= " playingNow ";
                                        } else if ($time >= $plItem->stop) {
                                            $class .= " finished ";
                                        }
                                        break;
                                    }
                                }

                                if ($colspan) {
                                    $colspan = " colspan='{$colspan}' ";
                                } else {
                                    $colspan = "";
                                }
                                
                                $id = "";
                                if($firstColumns){
                                    $firstColumns = false;
                                   $id = "id=\"col{$currentCellTime}\""; 
                                }
                                 
                                
                                echo '<td scope"col" ' . $colspan . ' class="' . $class . '" ' . $tooltip . ' '.$id.'>' . $content . '</td>';
                            }
                        }
                        echo "</tr>";
                    }
                }
            }
        }
        ?>
        </tbody>
    </table>
    <div id="timeline" style="display: none;"></div>
</div>
<script>
    var timeNow = <?php echo strtotime(date("Y-m-d H:i:00", $time)); ?>;
    var timeNowPositionIncrement = 0;
    var timeNowPositionLeft = 0;
    var timeNowPositionWidth = 0;
    var stepDetails = 5;
    $(document).ready(function () {
        /*
         $('#epgTable th').each(function (i) {
         var remove = 0;
         
         var tds = $(this).parents('table').find('tr td:nth-child(' + (i + 1) + ')')
         tds.each(function (j) {
         if (this.innerHTML == '' || this.innerHTML == '&nbsp;')
         remove++;
         });
         if (remove == ($('#epgTable tr').length - 1)) {
         $(this).hide();
         tds.hide();
         }
         });
         * 
         */
        if ($('#col' + timeNow).length) {
            timeNowPositionLeft = $('#col' + timeNow).position().left;
            timeNowPositionWidth = $('#col' + timeNow).outerWidth();
            $('#timeline').css({'left': timeNowPositionLeft + 'px'});
            var left = timeNowPositionLeft-(35*5);
            if(left<0){
                left = 0;
            }
            $('#timeline').fadeIn('slow');
            $('#epgDiv').animate({scrollLeft: left}, 500);
        }

        setInterval(function () {
            timeNow += 1;
            timeNowPositionIncrement++;
            if ($('#col' + timeNow).length) {
                timeNowPositionIncrement = 0;
                timeNowPositionLeft = $('#col' + timeNow).position().left;
                timeNowPositionWidth = $('#col' + timeNow).outerWidth();
            }
            var timeNowPositionStep = (timeNowPositionWidth / (60)) * timeNowPositionIncrement;
            if(timeNowPositionWidth===0){
                timeNowPositionStep = 0.38 * timeNowPositionIncrement;//35/(60*15);//35px / 15 min
            }
            $('#timeline').css({'left': (timeNowPositionLeft + timeNowPositionStep) + 'px'});
        }, 1000);
    });
</script>