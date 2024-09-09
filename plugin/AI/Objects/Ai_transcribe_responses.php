<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_transcribe_responses extends ObjectYPT
{

    protected $id, $vtt, $language, $duration, $text, $total_price, $size_in_bytes, $mp3_url, $ai_responses_id;

    static function getSearchFieldsNames()
    {
        return array('vtt', 'language', 'text', 'mp3_url');
    }

    static function getTableName()
    {
        return 'ai_transcribe_responses';
    }

    static function getAllAi_responses()
    {
        global $global;
        $table = "ai_responses";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }


    function setId($id)
    {
        $this->id = intval($id);
    }

    function setVtt($vtt)
    {
        $this->vtt = $vtt;
    }

    function setLanguage($language)
    {
        $this->language = $language;
    }

    function setDuration($duration)
    {
        $this->duration = $duration;
    }

    function setText($text)
    {
        $this->text = $text;
    }

    function setTotal_price($total_price)
    {
        $this->total_price = $total_price;
    }

    function setSize_in_bytes($size_in_bytes)
    {
        $this->size_in_bytes = intval($size_in_bytes);
    }

    function setMp3_url($mp3_url)
    {
        $this->mp3_url = $mp3_url;
    }

    function setAi_responses_id($ai_responses_id)
    {
        $this->ai_responses_id = intval($ai_responses_id);
    }


    function getId()
    {
        return intval($this->id);
    }

    function getVtt()
    {
        return $this->vtt;
    }

    function getLanguage()
    {
        return $this->language;
    }

    function getDuration()
    {
        return $this->duration;
    }

    function getText()
    {
        return $this->text;
    }

    function getTotal_price()
    {
        return $this->total_price;
    }

    function getSize_in_bytes()
    {
        return intval($this->size_in_bytes);
    }

    function getMp3_url()
    {
        return $this->mp3_url;
    }

    function getAi_responses_id()
    {
        return intval($this->ai_responses_id);
    }

    static function getVTTPaths($videos_id, $lang = '')
    {
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        if (empty($filename)) {
            return array('path' => '', 'relative' => '', 'url' => '');
        }
        $videos_dir = getVideosDir();
        if (!empty($lang)) {
            $lang = ".{$lang}";
        }
        $relativePathVTT = $filename . DIRECTORY_SEPARATOR . $filename . $lang . '.vtt';
        $vtt = $videos_dir . $relativePathVTT;
        $subtitle = false;
        if (file_exists($vtt)) {
            $subtitle = getURL("videos/{$relativePathVTT}");
        }
        return array('path' => $vtt, 'relative' => $relativePathVTT, 'url' => $subtitle);
    }

    public function save()
    {
        if (empty($this->size_in_bytes)) {
            $this->size_in_bytes = strlen($this->vtt);
            if (empty($this->size_in_bytes)) {
                $this->size_in_bytes = strlen($this->text);
            }
        }
        return parent::save();
    }

    static function saveVTT($vtt, $language, $duration, $text, $total_price, $size_in_bytes, $mp3, $jsonDecoded)
    {
        _error_log('AI: saveVTT line=' . __LINE__);
        $token = $jsonDecoded->token;
        $o = new Ai_transcribe_responses(0);
        $o->setVtt($vtt);
        $o->setLanguage($language);
        $o->setDuration($duration);
        $o->setText($text);
        $o->setTotal_price($total_price);
        $o->setSize_in_bytes($size_in_bytes);
        $o->setMp3_url($mp3);
        $o->setAi_responses_id($token->ai_responses_id);
        $jsonDecoded->Ai_transcribe_responses = $o->save();

        $jsonDecoded->vttsaved = false;
        if (!empty($vtt) && !empty($jsonDecoded->Ai_transcribe_responses)) {
            _error_log('AI: ' . basename(__FILE__) . ' line=' . __LINE__);
            //$jsonDecoded->lines[] = __LINE__;
            $paths = Ai_transcribe_responses::getVTTPaths($token->videos_id, $language);
            if (!empty($paths['path'])) {
                $jsonDecoded->vttsaved = file_put_contents($paths['path'], $vtt);
                _error_log("VTTFile saveVTT success videos_id={$token->videos_id}, language={$language} " . json_encode($paths));
            } else {
                _error_log("VTTFile Path is empty videos_id={$token->videos_id}, language={$language} " . json_encode($paths));
            }
        }
        $jsonDecoded->error = false;
        return  $jsonDecoded;
    }
}
