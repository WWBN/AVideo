<?php
require_once __DIR__ . '/../../../videos/configuration.php';
$video = Video::getVideoLight($videos_id);

$lis = array();

if ((!empty($video['description'])) && !empty($obj->Description)) {
    $desc = nl2br(trim($video['description']));
    if (!isHTMLEmpty($desc)) {
        $duid = uniqid();
        $titleAlert = str_replace(array('"', "'"), array('``', "`"), $video['title']);
        $descTitle = __("Description");
        $lis[] = "
            <button type=\button\" class=\"btn btn-dark\" 
                onclick='avideoAlert(\"$titleAlert\", \"<div style=\\\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\\\" 
                id=\\\"videoDescriptionAlertContent$duid\\\" ></div>\", \"\");$(\"#videoDescriptionAlertContent$duid\").html($(\"#videoDescription$duid\").html());return false;' 
                data-toggle=\"tooltip\" title=\"$descTitle\"><i class=\"far fa-file-alt\"></i> 
                <span  class=\"hidden-md hidden-sm hidden-xs\">$descTitle</span></a>
                <div id=\"videoDescription$duid\" style=\"display: none;\">$desc</div>
            </button>
        ";
    }
}
if (Video::canEdit($video['id'])) {
    $descTitle = __('Edit Video');
    $lis[] = "
            <button type=\"button\" 
                class=\"btn btn-dark\" onclick=\"avideoModalIframe(webSiteRootURL + 'view/managerVideosLight.php?avideoIframe=1&videos_id={$video['id']}');return false;\" 
                data-toggle=\"tooltip\" title=\"$descTitle\">
                <i class=\"fa fa-edit\"></i>
                <span class=\"hidden-md hidden-sm hidden-xs\">$descTitle</span>
            </button>
        ";
    $suggestedBTN = Layout::getSuggestedButton($video['id'], 'btn btn-dark');
    if (!empty($suggestedBTN)) {
        $lis[] = $suggestedBTN;
    }
}
if (empty($lis)) {
    return false;
}
?>
<div class="dropdown text-center" >
    <button class="btn btn-dark btn-xs dropdown-toggle" type="button" data-toggle="dropdown" title=<?php printJSString('More Options'); ?>>
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu btn-dark ">
        <li>
            <?php
            echo implode('</li><li>', $lis)
            ?>
        </li>
    </ul>
</div>
