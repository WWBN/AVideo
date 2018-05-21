<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Channel{
    
    static function getChannels(){        
        global $global;
        $sql = "SELECT u.*, "
                . " (SELECT count(v.id) FROM videos v where v.users_id = u.id) as total_videos "
                . " FROM users u "
                . " HAVING total_videos > 0 ";
        $sql .= BootGrid::getSqlFromPost(array('user', 'about'));
        $res = sqlDAL::readSql($sql); 
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $subscribe = array();
        if ($res!=false) {
            foreach ($fullResult as $row) {
                unset($row['password']);
                $subscribe[] = $row;
            }
        } else {
            $subscribe = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }
    
}

