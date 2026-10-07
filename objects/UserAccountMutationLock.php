<?php

// Account promotion and deletion must share a lock, including while video hooks run.
// Named locks survive commits made by plugins; a surrounding transaction would not.
class UserAccountMutationLock
{
    private static $held = [];
    private $name;
    private $isAdmin;

    private function __construct($name)
    {
        $this->name = $name;
    }

    public static function acquire($users_id)
    {
        global $mysqlDatabase;
        $users_id = intval($users_id);
        if ($users_id <= 0) {
            return false;
        }
        $name = 'avideo_user_' . sha1($mysqlDatabase) . '_' . $users_id;
        if (isset(self::$held[$name])) {
            self::$held[$name]++;
            $lock = new self($name);
        } else {
            try {
                $res = sqlDAL::readSql('SELECT GET_LOCK(?, ?) AS locked', 'si', [$name, 10], true);
                if ($res === false) {
                    return false;
                }
                $row = sqlDAL::fetchAssoc($res);
                sqlDAL::close($res);
                if (intval($row['locked'] ?? 0) !== 1) {
                    return false;
                }
                self::$held[$name] = 1;
                $lock = new self($name);
            } catch (\Throwable $th) {
                _error_log('Account mutation lock failed: ' . $th->getMessage(), AVideoLog::$ERROR);
                return false;
            }
        }
        try {
            // A locking read sees committed changes even inside an existing snapshot,
            // and waits for a promotion whose surrounding transaction has not committed.
            $res = sqlDAL::readSql('SELECT isAdmin FROM users WHERE id = ? FOR UPDATE', 'i', [$users_id], true);
            if ($res === false) {
                return false;
            }
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!is_array($row) || !array_key_exists('isAdmin', $row)) {
                return false;
            }
            $lock->isAdmin = intval($row['isAdmin']);
            return $lock;
        } catch (\Throwable $th) {
            _error_log('Account mutation target read failed: ' . $th->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    public function __destruct()
    {
        if (--self::$held[$this->name] > 0) {
            return;
        }
        unset(self::$held[$this->name]);
        try {
            $res = sqlDAL::readSql('SELECT RELEASE_LOCK(?) AS released', 's', [$this->name], true);
            if ($res !== false) {
                sqlDAL::close($res);
            } else {
                _error_log('Account mutation lock release failed', AVideoLog::$ERROR);
            }
        } catch (\Throwable $th) {
            _error_log('Account mutation lock release failed: ' . $th->getMessage(), AVideoLog::$ERROR);
        }
    }
}
