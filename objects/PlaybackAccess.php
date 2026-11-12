<?php

/** An optional prerequisite, independent of the existing visibility/payment rules. */
class PlaybackAccess
{
    private static $rules = [];

    public static function isInstalled()
    {
        // Unlike an absent migration, a failed schema lookup must not grant legacy access.
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'playback_access_rules'");
        if ($res === false) {
            throw new RuntimeException('Could not check playback access schema');
        }
        $installed = sqlDAL::num_rows($res) > 0;
        sqlDAL::close($res);
        return $installed;
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
            if (!empty(array_intersect($rule['groups'], $groups))) {
                return true;
            }
            return self::purchaseOfTitleSatisfiesRule($videos_id, $users_id);
        } catch (Throwable $th) {
            _error_log($th->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    /**
     * PayPerView option allowRentalOfGroupRestrictedVideos: the user's own rental/purchase of this very title also
     * satisfies its rule. Older PayPerView versions do not have it and keep the group-only behavior.
     */
    private static function purchaseOfTitleSatisfiesRule($videos_id, $users_id)
    {
        if (!AVideoPlugin::loadPluginIfEnabled('PayPerView') || !method_exists('PayPerView', 'purchaseSatisfiesPlaybackGroupRule')) {
            return false;
        }
        return PayPerView::purchaseSatisfiesPlaybackGroupRule($users_id, $videos_id);
    }

    /**
     * True when a rule restricts the title and the user passes it only through their purchase of the title (no
     * allowed group). The watch page then resolves the title like in free-with-ads mode: its visibility groups are
     * typically the membership groups that a renter does not have.
     */
    public static function isAllowedByTitlePurchase($videos_id, $users_id = null)
    {
        if ($users_id === null) {
            $users_id = User::getId();
        }
        try {
            return !empty($users_id) && self::isRestricted($videos_id) && self::purchaseOfTitleSatisfiesRule($videos_id, $users_id);
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::isAllowedByTitlePurchase ' . $th->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    /** True when the refused title can still be rented one by one (PayPerView option and a rental plan of the title). */
    public static function canRentTitle($videos_id)
    {
        try {
            return AVideoPlugin::loadPluginIfEnabled('PayPerView') && method_exists('PayPerView', 'canRentGroupRestrictedVideo')
                && PayPerView::canRentGroupRestrictedVideo($videos_id);
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::canRentTitle ' . $th->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    /** Groups listed by the video's mandatory rule; empty when no rule applies or it cannot be read. */
    public static function getRequiredGroups($videos_id)
    {
        try {
            $rule = self::getVideoRule($videos_id);
        } catch (Throwable $th) {
            _error_log($th->getMessage(), AVideoLog::$ERROR);
            return [];
        }
        if ($rule === null || $rule['mode'] !== 'require') {
            return [];
        }
        return $rule['groups'];
    }

    /**
     * Plans whose purchase adds the buyer to a required group: Subscription plans and group-based
     * PPV plans. A rental of the title alone is offered only when the PayPerView option
     * allowRentalOfGroupRestrictedVideos lets it satisfy the requirement.
     */
    public static function getUnlockOffers($videos_id)
    {
        global $global;
        $groups = self::getRequiredGroups($videos_id);
        $offers = [];
        if (empty($groups)) {
            return $offers;
        }
        $listingRequest = [$_GET, $_POST, $_REQUEST];
        try {
            $video = Video::getVideoLight($videos_id);
            if (empty($video) || $video['status'] === 'i') {
                return $offers;
            }
            $owner = User::getUserFromID($video['users_id']);
            if (empty($owner) || $owner['status'] === 'i') {
                return $offers;
            }
            $visibilityGroups = array_map('intval', array_column(UserGroups::getVideosAndCategoriesUserGroups($videos_id), 'id'));
            $users_id = User::getId();
            $userGroups = $users_id ? array_map('intval', array_column(UserGroups::getUserGroups($users_id), 'id')) : [];
            // Evaluate the existing payment rules without the mandatory prerequisite. A paid-only
            // title with no visibility groups still needs a plugin grant after buying a membership.
            $pluginAccess = AVideoPlugin::userCanWatchVideo($users_id, $videos_id);
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::getUnlockOffers groups ' . $th->getMessage(), AVideoLog::$ERROR);
            return $offers;
        } finally {
            [$_GET, $_POST, $_REQUEST] = $listingRequest;
        }
        $unlocksVideo = function ($planGroups, $planGrantsPlayback = false) use ($groups, $visibilityGroups, $userGroups, $pluginAccess) {
            if (empty(array_intersect($groups, $planGroups))) {
                return false;
            }
            return $pluginAccess || $planGrantsPlayback
                || (!empty($visibilityGroups) && !empty(array_intersect($visibilityGroups, array_merge($userGroups, $planGroups))));
        };
        try {
            if (AVideoPlugin::loadPluginIfEnabled('Subscription') && class_exists('SubscriptionPlansTable')) {
                // getAllActive() applies the request's listing filters; read it like Subscription::getPlansFromVideo().
                $get = $_GET;
                $post = $_POST;
                $request = $_REQUEST;
                $_GET = [];
                $_POST = [];
                $_REQUEST = ['current' => 1, 'rowCount' => 1000];
                try {
                    $plans = SubscriptionPlansTable::getAllActive();
                } finally {
                    $_GET = $get;
                    $_POST = $post;
                    $_REQUEST = $request;
                }
                foreach ($plans as $plan) {
                    if (!$unlocksVideo(array_map('intval', array_column($plan['groups'], 'users_groups_id')))) {
                        continue;
                    }
                    $offers[] = [
                        'type' => 'subscription',
                        'id' => intval($plan['id']),
                        'name' => $plan['name'],
                        'description' => $plan['description'],
                        'price' => floatval($plan['price']),
                        'days' => intval($plan['how_many_days']),
                        'hours' => 0,
                        'featured' => !empty($plan['is_featured']),
                        'url' => addQueryStringParameter(Subscription::getBuyURL($videos_id), 'plans_id', intval($plan['id'])),
                    ];
                }
            }
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::getUnlockOffers subscription ' . $th->getMessage(), AVideoLog::$ERROR);
        }
        try {
            if (AVideoPlugin::loadPluginIfEnabled('PayPerView') && class_exists('Ppv_plans_has_users_groups')
                && ObjectYPT::isTableInstalled('ppv_plans_has_users_groups')) {
                $ppvObject = AVideoPlugin::getDataObject('PayPerView');
                $videoPlanIds = [];
                // A whole-plan purchase also permits every title linked to that PPV plan, even
                // when the title's visibility groups do not match (the normal PPV playback rule).
                foreach (PayPerView::getAllPlansFromVideo($videos_id) as $videoPlan) {
                    if ($videoPlan['status'] === 'a') {
                        $videoPlanIds[] = intval($videoPlan['ppv_plans_id']);
                    }
                }
                foreach (Ppv_plans_has_users_groups::getAllPlansFromUserGroups($groups) as $link) {
                    $plan = PPV_Plans::getFromDb(intval($link['ppv_plans_id']));
                    if (empty($plan) || $plan['status'] !== 'a' || floatval($plan['value']) <= 0 || intval($plan['hours_valid']) < 1) {
                        continue;
                    }
                    // A purchase grants every active group of the plan (PayPerView::getGroupsFromUser()).
                    $planGroups = [];
                    foreach ((array) Ppv_plans_has_users_groups::getAllFromPPV($plan['id']) as $planGroup) {
                        if ($planGroup['status'] === 'a') {
                            $planGroups[] = intval($planGroup['users_groups_id']);
                        }
                    }
                    if (!$unlocksVideo($planGroups, in_array(intval($plan['id']), $videoPlanIds, true))) {
                        continue;
                    }
                    if (!empty($ppvObject->ifNeedToPayRedirectURL)) {
                        $url = PayPerView::getBuyURL($videos_id);
                    } else {
                        // Without videos_id: this checkout sells the plan (its groups), not a rental of the title.
                        $url = addQueryStringParameter($global['webSiteRootURL'] . 'plugin/PayPerView/subscribePlanPerUser.php', 'plans_id', intval($plan['id']));
                        $url = addQueryStringParameter($url, 'redirectUri', getRedirectToVideo($videos_id));
                    }
                    $offers[] = [
                        'type' => 'ppv',
                        'id' => intval($plan['id']),
                        'name' => $plan['name'],
                        'description' => $plan['description'],
                        'price' => floatval($plan['value']),
                        'days' => 0,
                        'hours' => intval($plan['hours_valid']),
                        'featured' => false,
                        'url' => $url,
                    ];
                }
            }
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::getUnlockOffers ppv ' . $th->getMessage(), AVideoLog::$ERROR);
        }
        try {
            // With the PayPerView option, renting the title also unlocks it: PayPerView grants playback of a
            // rented title (AVideoPlugin::userCanWatchVideo) and isAllowed() accepts the rental.
            if (self::canRentTitle($videos_id)) {
                $ppvObject = AVideoPlugin::getDataObject('PayPerView');
                $wholePlans = [];
                foreach ($offers as $offer) {
                    if ($offer['type'] === 'ppv') {
                        $wholePlans[] = $offer['id'];
                    }
                }
                foreach (PayPerView::getRentalPlansOfVideo($videos_id) as $plan) {
                    // The same plan sold whole (all its titles) is already offered.
                    if (in_array(intval($plan['id']), $wholePlans, true)) {
                        continue;
                    }
                    if (!empty($ppvObject->ifNeedToPayRedirectURL)) {
                        $url = PayPerView::getBuyURL($videos_id);
                    } else {
                        $url = addQueryStringParameter($global['webSiteRootURL'] . 'plugin/PayPerView/subscribePlanPerUser.php', 'plans_id', intval($plan['id']));
                        $url = addQueryStringParameter($url, 'videos_id', intval($videos_id));
                    }
                    $offers[] = [
                        'type' => 'rental',
                        'id' => intval($plan['id']),
                        'name' => $plan['name'],
                        'description' => $plan['description'],
                        'price' => floatval($plan['value']),
                        'days' => 0,
                        'hours' => intval($plan['hours_valid']),
                        'featured' => false,
                        'url' => $url,
                    ];
                }
            }
        } catch (Throwable $th) {
            _error_log('PlaybackAccess::getUnlockOffers rental ' . $th->getMessage(), AVideoLog::$ERROR);
        }
        return $offers;
    }

    /** Refuse playback with the title's unlock page (the offers that grant a required group), then stop. */
    public static function showUnlockPage($videos_id)
    {
        global $global, $config;
        $video = Video::getVideoLight($videos_id);
        // Bots, JSON clients and titles that are not published keep the plain refusal.
        if (empty($video) || isBot() || isContentTypeJson() || !in_array($video['status'], Video::getViewableStatus(true), true)) {
            forbiddenPage(__('Playback requires membership in an authorized user group'), true);
        }
        $offers = self::getUnlockOffers($videos_id);
        http_response_code(403);
        include $global['systemRootPath'] . 'view/playbackAccessUnlock.php';
        exit;
    }

    /** True when a mandatory group rule applies to the video, for any viewer. Errors count as restricted. */
    public static function isRestricted($videos_id)
    {
        try {
            $rule = self::getVideoRule($videos_id);
            return $rule !== null && $rule['mode'] === 'require';
        } catch (Throwable $th) {
            _error_log($th->getMessage(), AVideoLog::$ERROR);
            return true;
        }
    }

    /** Keep listing images, but drop every playable (non-image) file of a denied video from a getVideosURL() list. */
    public static function filterPlayableFiles($videos_id, $files)
    {
        if ((!is_array($files) && !is_object($files)) || self::isAllowed($videos_id)) {
            return $files;
        }
        $wasObject = is_object($files);
        $files = (array) $files;
        foreach ($files as $key => $file) {
            $entry = is_object($file) ? (array) $file : $file;
            if (!is_array($entry) || ($entry['type'] ?? '') !== 'image') {
                unset($files[$key]);
            }
        }
        return $wasObject ? (object) $files : $files;
    }

    /** Filter response rows after shared caches are read; retain catalog metadata and field shapes. */
    public static function filterVideoRows($rows)
    {
        if (!is_array($rows) && !is_object($rows)) {
            return $rows;
        }
        $wasObject = is_object($rows);
        $rows = (array) $rows;
        foreach ($rows as &$row) {
            $wasObjectRow = is_object($row);
            $row = (array) $row;
            $videos_id = $row['id'] ?? $row['videos_id'] ?? 0;
            if (!self::isAllowed($videos_id)) {
                // 'videos' is a media map (a JSON object); 'sources' is a list.
                if (isset($row['videos'])) {
                    $row['videos'] = new stdClass();
                }
                if (isset($row['sources'])) {
                    $row['sources'] = is_object($row['sources']) ? new stdClass() : [];
                }
                foreach (['VideoUrl', 'videosURL'] as $field) {
                    if (isset($row[$field])) {
                        $row[$field] = self::filterPlayableFiles($videos_id, $row[$field]);
                    }
                }
                foreach (['videoLink', 'videoDownloadedLink', 'firstVideo'] as $field) {
                    if (isset($row[$field])) {
                        $row[$field] = '';
                    }
                }
                if (isset($row['mp3'])) {
                    $row['mp3'] = false;
                }
            }
            foreach (['playlist', 'relatedVideos'] as $field) {
                if (!empty($row[$field])) {
                    $row[$field] = self::filterVideoRows($row[$field]);
                }
            }
            if (!empty($row['next_video'])) {
                $next = self::filterVideoRows([$row['next_video']]);
                $row['next_video'] = $next[0];
            }
            if ($wasObjectRow) {
                $row = (object) $row;
            }
        }
        unset($row);
        return $wasObject ? (object) $rows : $rows;
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
        // Shared feed/catalog output caches hold media URLs; drop them like a category save does.
        ObjectYPT::deleteALLCache();
    }
}
