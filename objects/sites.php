<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
class Sites extends ObjectYPT
{
    protected $name;
    protected $url;
    protected $status;
    protected $secret;

    public static function getSearchFieldsNames()
    {
        return ['name', 'url'];
    }

    public static function getTableName()
    {
        return 'sites';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function save()
    {
        if (empty($this->getSecret())) {
            $this->setSecret(md5(uniqid()));
        }

        $siteURL = $this->getUrl();
        if (substr($siteURL, -1) !== '/') {
            $siteURL .= "/";
        }
        $this->setUrl($siteURL);
        return parent::save();
    }

    public static function getFromFileName($fileName)
    {
        $obj = new stdClass();
        $obj->url = '';
        $obj->secret = '';
        $obj->filename = $fileName;
        $video = Video::getVideoFromFileNameLight($fileName);
        if (!empty($video['sites_id'])) {
            $site = new Sites($video['sites_id']);
            $obj->url = $site->getUrl();
            $obj->secret = $site->getSecret();
        }
        return $obj;
    }

    public static function getFromStatus($status)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status = ? ";

        $res = sqlDAL::readSql($sql, 's', [$status]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
}
