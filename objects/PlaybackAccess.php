<?php

/** An optional prerequisite, independent of the existing visibility/payment rules. */
class PlaybackAccess
{
    private static $rules = [];

    public static function isInstalled()
    {
        return ObjectYPT::isTableInstalled('playback_access_rules');
    }

    public static function getStoredRule($scope, $id)
    {
        $key = $scope . ':' . intval($id);
        if (!array_key_exists($key, self::$rules)) {
            if (!self::isInstalled()) {
                return null; // Existing installations keep their behavior until the migration runs.
            }
            $res = sqlDAL::readSql('SELECT mode, groups_json FROM playback_access_rules WHERE scope = ? AND scope_id = ?', 'si', [$scope, intval($id)], true);
            if (!$res) {
                throw new RuntimeException('Could not read playback access rules');
            }
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if ($row) {
                if (!in_array($row['mode'], ['require', 'unrestricted'], true)) {
                    throw new RuntimeException('Invalid saved playback access mode');
                }
                $groups = json_decode($row['groups_json'], true);
                // A damaged restriction must never become unrestricted.
                $row['groups'] = is_array($groups) ? self::normalizeGroups($groups) : [];
                $row['scope'] = $scope;
                $row['scope_id'] = intval($id);
            }
            self::$rules[$key] = $row ?: null;
        }
        return self::$rules[$key];
    }

    public static function getCategoryRule($categories_id)
    {
        $visited = [];
        while ($categories_id) {
            $categories_id = intval($categories_id);
            if (isset($visited[$categories_id])) {
                throw new RuntimeException('Cycle in playback access category inheritance');
            }
            $visited[$categories_id] = true;
            $rule = self::getStoredRule('category', $categories_id);
            if ($rule !== null) {
                return $rule;
            }
            $res = sqlDAL::readSql('SELECT parentId FROM categories WHERE id = ?', 'i', [$categories_id]);
            if (!$res) {
                throw new RuntimeException('Could not read playback access category');
            }
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $categories_id = intval($row['parentId'] ?? 0);
        }
        return null;
    }

    public static function getVideoRule($videos_id)
    {
        if (!self::isInstalled()) {
            return null;
        }
        $rule = self::getStoredRule('video', $videos_id);
        if ($rule !== null) {
            return $rule;
        }
        // Do not reuse a persistent video cache: a move to a restricted category applies immediately.
        $res = sqlDAL::readSql('SELECT categories_id FROM videos WHERE id = ?', 'i', [intval($videos_id)]);
        if (!$res) {
            throw new RuntimeException('Could not read playback access video');
        }
        $row = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return self::getCategoryRule(intval($row['categories_id'] ?? 0));
    }

    public static function isAllowed($videos_id, $users_id = null)
    {
        if ($users_id === null) {
            $users_id = User::getId();
        }
        if ($users_id && User::isAdmin($users_id)) {
            return true;
        }
        try {
            $rule = self::getVideoRule($videos_id);
            if ($rule === null || $rule['mode'] === 'unrestricted') {
                return true;
            }
            if (!$users_id) {
                return false;
            }
            $groups = array_column(UserGroups::getUserGroups($users_id), 'id');
            return !empty(array_intersect($rule['groups'], $groups));
        } catch (Throwable $th) {
            _error_log($th->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    public static function normalizeGroups($groups)
    {
        $ids = [];
        foreach ($groups as $group) {
            if (!is_scalar($group) || !ctype_digit((string) $group) || intval($group) < 1) {
                throw new InvalidArgumentException('Invalid user group');
            }
            $ids[] = intval($group);
        }
        return array_values(array_unique($ids));
    }

    public static function saveRule($scope, $id, $mode, $groups)
    {
        if (!User::isAdmin()) {
            forbiddenPage('Only an admin can configure playback access', true);
        }
        if (!in_array($scope, ['category', 'video'], true) || intval($id) < 1
            || !in_array($mode, ['inherit', 'require', 'unrestricted'], true) || !is_array($groups)) {
            throw new InvalidArgumentException('Invalid playback access rule');
        }
        if (!self::isInstalled()) {
            throw new InvalidArgumentException('Update the database before configuring playback access');
        }
        $table = $scope === 'video' ? 'videos' : 'categories';
        $res = sqlDAL::readSql("SELECT id FROM {$table} WHERE id = ?", 'i', [intval($id)], true);
        $target = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!$target) {
            throw new InvalidArgumentException('Video or category not found');
        }
        $groups = $mode === 'require' ? self::normalizeGroups($groups) : [];
        if ($mode === 'require' && !$groups) {
            throw new InvalidArgumentException('Select at least one required user group');
        }
        foreach ($groups as $group) {
            $res = sqlDAL::readSql('SELECT id FROM users_groups WHERE id = ?', 'i', [$group], true);
            $exists = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            if (!$exists) {
                throw new InvalidArgumentException('User group not found');
            }
        }
        if ($mode === 'inherit') {
            $saved = sqlDAL::writeSql('DELETE FROM playback_access_rules WHERE scope = ? AND scope_id = ?', 'si', [$scope, intval($id)]);
        } else {
            $saved = sqlDAL::writeSql('INSERT INTO playback_access_rules (scope, scope_id, mode, groups_json, modified) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE mode = VALUES(mode), groups_json = VALUES(groups_json), modified = NOW()', 'siss', [$scope, intval($id), $mode, json_encode($mode === 'require' ? $groups : [])]);
        }
        if (!$saved) {
            throw new RuntimeException('Could not save playback access rule');
        }
        self::$rules = [];
    }
}
