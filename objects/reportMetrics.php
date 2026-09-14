<?php

/** Numeric report queries. Callers must authorize the requested owner scope. */
class ReportMetrics
{
    private static function rows($sql, $formats, $values)
    {
        $result = sqlDAL::readSql($sql, $formats, $values);
        if ($result === false) {
            throw new RuntimeException('Unable to load report metrics');
        }
        $rows = sqlDAL::fetchAllAssoc($result);
        sqlDAL::close($result);
        return $rows;
    }

    private static function filters($owner, $dateColumn, $from, $to, &$formats, &$values)
    {
        $sql = '';
        $formats = '';
        $values = [];
        if ($owner > 0) {
            $sql .= ' AND v.users_id = ?';
            $formats .= 'i';
            $values[] = (int) $owner;
        }
        if ($from !== '') {
            $sql .= " AND {$dateColumn} >= ?";
            $formats .= 's';
            $values[] = $from;
        }
        if ($to !== '') {
            $sql .= " AND {$dateColumn} <= ?";
            $formats .= 's';
            $values[] = $to;
        }
        return $sql;
    }

    public static function videoActivity($owner = 0, $from = '', $to = '', $byChannel = false)
    {
        $where = self::filters($owner, 's.`when`', $from, $to, $formats, $values);
        // Both views reports include all existing videos, regardless of publication status.
        $group = $byChannel ? 'v.users_id' : 'v.id';
        $sql = "SELECT {$group} AS id, COUNT(*) AS total_views,
                COALESCE(SUM(s.seconds_watching_video), 0) AS seconds_watching_video
                FROM videos_statistics s JOIN videos v ON v.id = s.videos_id
                WHERE 1=1 {$where} GROUP BY {$group}";
        if (!$byChannel) {
            $sql = "SELECT a.id AS videos_id, v.users_id, v.title, v.filename, v.type, v.externalOptions,
                    a.total_views, a.seconds_watching_video
                    FROM ({$sql}) a JOIN videos v ON v.id = a.id";
        }
        $sql .= ' ORDER BY total_views DESC, ' . ($byChannel ? 'id' : 'videos_id') . ' DESC';
        return self::rows($sql, $formats, $values);
    }

    public static function reactions($owner = 0, $comments = false, $from = '', $to = '')
    {
        // These are current votes, not an immutable history of every change of vote.
        $table = $comments ? 'comments_likes' : 'likes';
        $join = $comments ? 'comments v ON v.id = r.comments_id' : 'videos v ON v.id = r.videos_id';
        $where = self::filters($owner, 'COALESCE(r.modified, r.created)', $from, $to, $formats, $values);
        return self::rows("SELECT v.users_id AS id,
                SUM(CASE WHEN r.`like` = 1 THEN 1 ELSE 0 END) AS thumbsUp,
                SUM(CASE WHEN r.`like` = -1 THEN 1 ELSE 0 END) AS thumbsDown
                FROM {$table} r JOIN {$join}
                WHERE r.`like` IN (1, -1) {$where} GROUP BY v.users_id ORDER BY v.users_id",
            $formats, $values);
    }

    public static function subscriptions($owner = 0)
    {
        $sql = "SELECT COUNT(*) AS total FROM subscribes WHERE status = 'a' AND users_id > 0";
        $rows = self::rows($sql . ($owner > 0 ? ' AND users_id = ?' : ''),
            $owner > 0 ? 'i' : '', $owner > 0 ? [(int) $owner] : []);
        return (int) $rows[0]['total'];
    }
}
