<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['ai_metatags_responses_id']) && empty($_REQUEST['ai_transcribe_responses_id'])) {
    forbiddenPage('ID is required');
}

$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$video = new Video('', '', $videos_id);
if(!empty($_REQUEST['ai_metatags_responses_id'])){
    $ai = new Ai_metatags_responses($_REQUEST['id']);
    
    if (empty($ai->getcompletion_tokens())) {
        forbiddenPage('AI Response not found');
    }
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->videos_id = $videos_id;

switch ($_REQUEST['label']) {
    case 'videoTitles':
        $videoTitles = $ai->getVideoTitles();
        $json = json_decode($videoTitles);
        if (!empty($json)) {
            if (isset($_REQUEST['index'])) {
                $title = $json[$_REQUEST['index']];
                if (!empty($title)) {
                    $video->setTitle($title);
                    $video->setClean_title($title);
                    $id = $video->save();
                    $obj->error = empty($id);
                } else {
                    $obj->msg = 'This title is empty';
                }
            } else {
                $obj->msg = 'Index for title is not set';
            }
        } else {
            $obj->msg = 'Could not get the titles options';
        }
        break;
    case 'keywords':
        $VideoTags = AVideoPlugin::isEnabledByName('VideoTags');
        if (!empty($VideoTags)) {
            $keywords = $ai->getKeywords();
            $json = json_decode($keywords);
            if (!empty($json)) {
                if (isset($_REQUEST['index'])) {
                    $keyword = $json[$_REQUEST['index']];
                    if (!empty($keyword)) {
                        $tags_types_id = AI::getTagTypeId();
                        $id = VideoTags::add($keyword, $tags_types_id, $obj->videos_id);
                        $obj->error = empty($id);
                    } else {
                        $obj->msg = 'This keyword is empty';
                    }
                } else {
                    $obj->msg = 'Index for keyword is not set';
                }
            } else {
                $obj->msg = 'Could not get the keywords options';
            }
        } else {
            $obj->msg = 'VideoTags plugin is disabled';
        }
        break;
    case 'professionalDescription':
        $value = $ai->getProfessionalDescription();
        if (!empty($value)) {
            $video->setDescription($value);
            $id = $video->save();
            $obj->error = empty($id);
        } else {
            $obj->msg = 'Professional Description is empty';
        }
        break;
    case 'casualDescription':
        $value = $ai->getCasualDescription();
        if (!empty($value)) {
            $video->setDescription($value);
            $id = $video->save();
            $obj->error = empty($id);
        } else {
            $obj->msg = 'Casual Description is empty';
        }
        break;
    case 'shortSummary':
        $externalOptions = _json_decode($video->getExternalOptions());
        $SEO = @$externalOptions->SEO;
        $ShortSummary = '';
        $MetaDescription = '';
        if (!empty($SEO)) {
            $ShortSummary = $SEO->ShortSummary;
            $MetaDescription = $SEO->MetaDescription;
        }
        $ShortSummary = $ai->getShortSummary();
        $id = CustomizeAdvanced::setShortSummaryAndMetaDescriptionVideo($obj->videos_id, $ShortSummary, $MetaDescription);
        $obj->error = empty($id);
        break;
    case 'metaDescription':
        $externalOptions = _json_decode($video->getExternalOptions());
        $SEO = @$externalOptions->SEO;
        $ShortSummary = '';
        $MetaDescription = '';
        if (!empty($SEO)) {
            $ShortSummary = $SEO->ShortSummary;
            $MetaDescription = $SEO->MetaDescription;
        }
        $MetaDescription = $ai->getMetaDescription();
        $id = CustomizeAdvanced::setShortSummaryAndMetaDescriptionVideo($obj->videos_id, $ShortSummary, $MetaDescription);
        $obj->error = empty($id);
        break;
    case 'rrating':
        $value = $ai->getRrating();
        if (!empty($value)) {
            $video->setRrating($value);
            $id = $video->save();
            $obj->error = empty($id);
        } else {
            $obj->msg = 'Rating is empty';
        }
        break;
    case 'text':
        if(!empty($_REQUEST['ai_transcribe_responses_id'])){
            $ait = new Ai_transcribe_responses($_REQUEST['id']);
            $value = $ait->getVtt();
            //var_dump($value);exit;
            if (!empty($value)) {
                $paths = Ai_transcribe_responses::getVTTPaths($obj->videos_id, $ait->getLanguage());
                $file = $paths['path'];
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8');
                }
                $utf8_bom = "\xEF\xBB\xBF";
                $id = file_put_contents($file, $utf8_bom . $value);
                $obj->error = !file_exists($file);
                if($obj->error){
                    $obj->msg = 'Transcription file does not exists '.$file;
                }
            } else {
                $obj->msg = 'Transcription is empty';
            }
        }else{
            $obj->msg = 'Transcription ID is empty';
        }
        break;
    default:
        # code...
        break;
}

echo json_encode($obj);
