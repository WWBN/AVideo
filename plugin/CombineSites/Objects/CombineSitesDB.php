<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGive.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGet.php';

class CombineSitesDB extends ObjectYPT {

    protected $id, $site_url, $status, $channels_label, $playlists_label, $categories_label, $site_label, $get_token, $give_token;

    static function getSearchFieldsNames() {
        return array('site_url');
    }

    static function getTableName() {
        return 'combine_sites';
    }

    function getSite_url() {
        return $this->site_url;
    }

    function getStatus() {
        return $this->status;
    }

    function getChannels_label() {
        return $this->channels_label;
    }

    function getPlaylists_label() {
        return $this->playlists_label;
    }

    function getCategories_label() {
        return $this->categories_label;
    }

    function getSite_label() {
        return $this->site_label;
    }

    function setSite_url($site_url) {
        $this->site_url = $site_url;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setChannels_label($channels_label) {
        $this->channels_label = $channels_label;
    }

    function setPlaylists_label($playlists_label) {
        $this->playlists_label = $playlists_label;
    }

    function setCategories_label($categories_label) {
        $this->categories_label = $categories_label;
    }

    function setSite_label($site_label) {
        $this->site_label = $site_label;
    }

    function getGet_token() {
        return $this->get_token;
    }

    function getGive_token() {
        return $this->give_token;
    }

    function setGet_token($get_token) {
        if (!empty($this->get_token)) {
            return false;
        }
        $this->get_token = preg_replace('/[^a-z0-9.]/i', '', $get_token);
    }

    function setGive_token($give_token) {
        if (!empty($this->give_token)) {
            return false;
        }
        $this->give_token = preg_replace('/[^a-z0-9.]/i', '', $give_token);
    }

    function loadFromSite($site_url) {
        $row = self::getFromSite($site_url);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static function getFromSite($site_url) {
        if (filter_var($site_url, FILTER_VALIDATE_URL) === false) {
            _error_log("CombineSitesDB::getFromSite ({$site_url}) is not a valid URL");
            return false;
        }
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  site_url = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", array($site_url));
        if ($res) {
            $data = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $data;
        }
        _error_log("CombineSitesDB::getFromSite ({$site_url}) on SQL ($sql) return an empty result ");
        return false;
    }

    function getId() {
        return $this->id;
    }

    static function sitesGetGivePermissions() {
        $rows = CombineSitesDB::getAll();
        foreach ($rows as $key => $value) {
            $rows[$key]['give'] = CombineSitesGive::getAllFromSite($value['id'], true);
            $rows[$key]['get'] = CombineSitesGet::getAllFromSite($value['id'], true);
        }
        return $rows;
    }

    static function sitesGetGivePermissionsFromSiteURL($site_url, $getOneOnly = false, $type = false) {
        $site = self::getFromSite($site_url);
        if ($site) {
            $rows = array();
            if (empty($getOneOnly) || $getOneOnly == "give") {
                $row['give'] = CombineSitesGive::getAllFromSite($site['id'], true, $type);
            }
            if (empty($getOneOnly) || $getOneOnly == "get") {
                $row['get'] = CombineSitesGet::getAllFromSite($site['id'], true, $type);
            }
            return $row;
        } else {
            _error_log("CombineSitesDB::sitesGetGivePermissionsFromSiteURL($site_url) return empty result");
        }
        return false;
    }

    static function sitesIsEnable($site_url) {
        $site = self::getFromSite($site_url);
        return $site["status"] === 'a';
    }
    
    
    static function getAllActive() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status = 'a' ";

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
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
