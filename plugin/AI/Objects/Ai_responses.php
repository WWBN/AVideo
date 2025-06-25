<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_responses extends ObjectYPT
{

    protected $id, $elapsedTime, $videos_id, $price;

    static function getSearchFieldsNames()
    {
        return array();
    }

    static function getTableName()
    {
        return 'ai_responses';
    }

    static function getAllVideos()
    {
        global $global;
        $table = "videos";
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

    function setElapsedTime($elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;
    }

    function setVideos_id($videos_id)
    {
        $this->videos_id = intval($videos_id);
    }


    function getId()
    {
        return intval($this->id);
    }

    function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    function getVideos_id()
    {
        return intval($this->videos_id);
    }

    function setPrice($price)
    {
        $this->price = floatval($price);
    }

    function getPrice()
    {
        return floatval($this->price);
    }

    static function getAllImageFromVideo($videos_id)
    {
        global $global;
        $sql = "SELECT *
        FROM ai_responses_json as arj
        LEFT JOIN  ai_responses as ar ON ar.id = arj.ai_responses_id
        WHERE ar.videos_id = ? AND arj.ai_type = ?";

        $sql .= self::getSqlFromPost('arj.');
        // var_dump($sql, [$videos_id, AI::$typeImage]);
        $res = sqlDAL::readSql($sql, 'is', [$videos_id, AI::$typeImage]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        //var_dump($sql, $fullData);exit;
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                if (!empty($row['response'])) {
                    $row['response'] = _json_decode($row['response']);
                }
                //var_dump($row['response']->data[0]->url);
                $row['url'] = '';
                if (!empty($row['response']) && !empty($row['response']->data[0]->url)) {
                    $row['url'] = $row['response']->data[0]->url;
                }

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

    static function getAllBasicFromVideo($videos_id)
    {
        global $global;
        $sql = "SELECT amr.*,amr.id as ai_metatags_responses_id, ar.created as sortDate
        FROM ai_metatags_responses amr
        JOIN ai_responses ar ON amr.ai_responses_id = ar.id
        WHERE ar.videos_id = ?";

        $sql .= self::getSqlFromPost();
        //var_dump($sql);
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        //var_dump($sql, $fullData);exit;
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                if (empty($row['videoTitles'])) {
                    $row['videoTitles'] = array();
                } else if (is_string($row['videoTitles'])) {
                    $row['videoTitles'] = json_decode($row['videoTitles']);
                }
                if (empty($row['keywords'])) {
                    $row['keywords'] = array();
                } else if (is_string($row['keywords'])) {
                    $row['keywords'] = json_decode($row['keywords']);
                }
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
    static function getAllTranscriptionFromVideo($videos_id)
    {
        global $global;
        $sql = "SELECT atr.*, atr.id as ai_transcribe_responses_id, ar.created as sortDate
        FROM ai_transcribe_responses atr
        JOIN ai_responses ar ON atr.ai_responses_id = ar.id
        WHERE ar.videos_id = ? ";

        $sql .= self::getSqlFromPost();
        //var_dump($sql);
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $rows = array();
        if ($res != false) {
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            return $fullData;
        } else {
            sqlDAL::close($res);
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getAllFromVideo($videos_id)
    {
        global $global;
        $sql = "SELECT
                j.*,
                m.*,
                m.id as ai_metatags_responses_id,
                t.*,
                t.id as ai_transcribe_responses_id,
                r.*,
                r.created as sortDate
            FROM
                ai_responses AS r
            LEFT JOIN
                ai_metatags_responses AS m ON r.id = m.ai_responses_id
            LEFT JOIN
                ai_transcribe_responses AS t ON r.id = t.ai_responses_id
            LEFT JOIN
                ai_responses_json AS j ON r.id = j.ai_responses_id
            WHERE
                r.videos_id = ?
            UNION
            SELECT
                j.*,
                m.*,
                m.id as ai_metatags_responses_id,
                t.*,
                t.id as ai_transcribe_responses_id,
                r.*,
                r.created as sortDate
            FROM
                ai_responses AS r
            LEFT JOIN
                ai_metatags_responses AS m ON r.id = m.ai_responses_id
            RIGHT JOIN
                ai_transcribe_responses AS t ON r.id = t.ai_responses_id
            RIGHT JOIN
                ai_responses_json AS j ON r.id = j.ai_responses_id
            WHERE
                r.videos_id = ?";

        $sql .= self::getSqlFromPost();
        //var_dump($sql);
        $res = sqlDAL::readSql($sql, 'ii', [$videos_id, $videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            $cleanKeys = array();
            /*
            $cleanKeys = array(
                'professionalDescription',
                'casualDescription',
                'shortSummary',
                'metaDescription',
                'rrating',
                'rratingJustification',
                'vtt',
                'text');
            */
            foreach ($fullData as $row) {
                if (empty($row['videoTitles'])) {
                    $row['videoTitles'] = array();
                } else if (is_string($row['videoTitles'])) {
                    $row['videoTitles'] = json_decode($row['videoTitles']);
                }
                if (empty($row['keywords'])) {
                    $row['keywords'] = array();
                } else if (is_string($row['keywords'])) {
                    $row['keywords'] = json_decode($row['keywords']);
                }
                foreach ($cleanKeys as $value) {
                    $row[$value] = "Replaced {$value}";
                }
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

    static function getHistory($videos_id)
    {
        global $global;
        $sql = "SELECT
            ar.*,
            ar.created as sortDate,
            COALESCE(amr.price_prompt_tokens, 0) as price_prompt_tokens,
            COALESCE(amr.price_completion_tokens, 0) as price_completion_tokens,
            COALESCE(atr.total_price, 0) as total_price,
            (COALESCE(amr.price_prompt_tokens, 0) + COALESCE(amr.price_completion_tokens, 0)) AS total_metadata ,
            COALESCE(atr.total_price, 0) AS total_transcription ,
            (COALESCE(amr.price_prompt_tokens, 0) + COALESCE(amr.price_completion_tokens, 0) + COALESCE(atr.total_price, 0)) AS total
        FROM
            ai_responses ar
        LEFT JOIN
            ai_metatags_responses amr ON ar.id = amr.ai_responses_id
        LEFT JOIN
            ai_transcribe_responses atr ON ar.id = atr.ai_responses_id
        WHERE
        ar.videos_id = ?
        ORDER BY sortDate DESC";

        //$sql .= self::getSqlFromPost();
        //var_dump($sql);
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    static function getValidTranscriptions($videos_id)
    {
        $rows = self::getTranscriptions($videos_id);
        foreach ($rows as $row) {
            if (!empty($row['text'])) {
                return $row;
            }
        }
        return false;
    }


    static function getTranscriptions($videos_id)
    {
        global $global;
        $sql = "SELECT atr.*, ar.created as sortDate, atr.id as ai_transcribe_responses_id, 0 as ai_metatags_responses_id
        FROM `ai_transcribe_responses` atr
        JOIN `ai_responses` ar ON atr.ai_responses_id = ar.id
        WHERE ar.videos_id = ?";

        $sql .= self::getSqlFromPost();
        //var_dump($videos_id, $sql);
        $res = sqlDAL::readSql($sql, 'i', [$videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);

        return $fullData;
    }

    static function hasTranscriptions($videos_id)
    {
        $rows = self::getValidTranscriptions($videos_id);
        return !empty($rows);
    }

    static function getTranscriptionText($videos_id)
    {
        $rows = self::getValidTranscriptions($videos_id);
        if (!empty($rows)) {
            return $rows['text'];
        }
        return '';
    }

    static function getTranscriptionVtt($videos_id)
    {
        $rows = self::getValidTranscriptions($videos_id);
        if (!empty($rows)) {
            //_error_log("AI::getTranscriptionVtt($videos_id) ".json_encode($rows['vtt']));
            return $rows['vtt'];
        }
        return '';
    }

    static function getLatest($videos_id)
    {
        global $global;
        $sql = "SELECT tr.*, mr.*, r.* FROM ai_responses r
        LEFT JOIN ai_transcribe_responses tr ON tr.ai_responses_id = r.id
        LEFT JOIN ai_metatags_responses mr ON mr.ai_responses_id = r.id
        WHERE videos_id = ? ORDER BY r.id DESC LIMIT 1";
        $formats = 'i';
        $values = [$videos_id];
        //var_dump($sql, $values);
        $res = sqlDAL::readSql($sql, $formats, $values);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $data;
    }
}
