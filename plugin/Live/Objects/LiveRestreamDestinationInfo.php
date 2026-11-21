<?php

/**
 * Plain helpers that tell a restream owner, before and after a live, which channel a destination
 * sends to and what happened the last time it was used. Pure functions (no database, network or
 * session): lives can start automatically from a schedule, so nothing here asks for a
 * confirmation; it only prepares information for the Restream panel and Active Lives.
 */
class LiveRestreamDestinationInfo
{
    const PROVIDERS = array('youtube' => 'YouTube', 'facebook' => 'Facebook', 'twitch' => 'Twitch');

    // Raw translation: every text is escaped when it is turned into HTML, and plain-text messages
    // (alerts, logs) must not show HTML entities.
    private static function t($text)
    {
        return function_exists('__') ? __($text, true) : $text;
    }

    private static function e($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }

    private static function toArray($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        } elseif (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }
        return is_array($value) ? $value : array();
    }

    /** 'youtube', 'facebook', 'twitch' for a destination connected with a button, otherwise ''. */
    public static function getConnectedProvider($parameters)
    {
        $parameters = self::toArray($parameters);
        if (empty($parameters['restream.ypt.me']) || !is_array($parameters['restream.ypt.me'])) {
            return '';
        }
        $connected = $parameters['restream.ypt.me'];
        $provider = is_string($connected['provider'] ?? null) ? strtolower($connected['provider']) : '';
        if (isset(self::PROVIDERS[$provider])) {
            return $provider;
        }
        foreach (array_keys(self::PROVIDERS) as $name) {
            if (!empty($connected[$name])) {
                return $name;
            }
        }
        return '';
    }

    /** Id format and picture hosts accepted for each provider. */
    private static function rules($provider)
    {
        $rules = array(
            'youtube' => array('id' => '/^[A-Za-z0-9_-]{10,64}\z/', 'images' => '(ggpht\.com|googleusercontent\.com|ytimg\.com)'),
            'facebook' => array('id' => '/^[0-9]{5,30}\z/', 'images' => '(fbcdn\.net|fbsbx\.com)'),
            'twitch' => array('id' => '/^[0-9]{1,20}\z/', 'images' => '(jtvnw\.net)'),
        );
        return $rules[$provider] ?? null;
    }

    /**
     * Validated summary of the YouTube channel, Facebook page/profile or Twitch channel a
     * destination sends to. The parameters are posted by the browser, so every field is checked
     * again here; anything unexpected is dropped instead of being displayed.
     * $provider defaults to the channel's own 'provider' (records saved before it existed are YouTube).
     */
    public static function normalizeChannel($channel, $provider = null)
    {
        $channel = self::toArray($channel);
        if ($provider === null) {
            $provider = is_string($channel['provider'] ?? null) ? $channel['provider'] : 'youtube';
        }
        $rules = self::rules($provider);
        if (empty($rules) || (isset($channel['provider']) && $channel['provider'] !== $provider)) {
            return null;
        }
        $id = is_scalar($channel['id'] ?? null) ? trim((string) $channel['id']) : '';
        if (!preg_match($rules['id'], $id)) {
            return null;
        }
        $title = is_string($channel['title'] ?? null) ? trim((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $channel['title'])) : '';
        if (function_exists('mb_substr')) {
            $title = mb_substr($title, 0, 150, 'UTF-8');
        } else {
            $title = substr($title, 0, 150);
        }
        $handle = is_string($channel['handle'] ?? null) ? trim($channel['handle']) : '';
        if ($provider === 'youtube') {
            if ($handle !== '' && $handle[0] !== '@') {
                $handle = '@' . $handle;
            }
            $handle = preg_match('/^@[\p{L}\p{N}._-]{1,100}\z/u', $handle) ? $handle : '';
        } elseif ($provider === 'twitch') {
            $handle = preg_match('/^[A-Za-z0-9_]{1,25}\z/', $handle) ? strtolower($handle) : '';
        } else {
            $handle = '';
        }
        $thumbnail = is_string($channel['thumbnail'] ?? null) ? trim($channel['thumbnail']) : '';
        $thumbnailHost = strtolower((string) parse_url($thumbnail, PHP_URL_HOST));
        if (parse_url($thumbnail, PHP_URL_SCHEME) !== 'https'
            || !preg_match('/(^|\.)' . $rules['images'] . '\z/', $thumbnailHost)) {
            $thumbnail = '';
        }
        $kind = 'channel';
        $url = '';
        if ($provider === 'youtube') {
            $url = 'https://www.youtube.com/channel/' . $id;
        } elseif ($provider === 'twitch') {
            $url = $handle !== '' ? 'https://www.twitch.tv/' . $handle : '';
        } else {
            $kind = ($channel['kind'] ?? '') === 'profile' ? 'profile' : 'page';
            $link = is_string($channel['url'] ?? null) ? trim($channel['url']) : '';
            $linkHost = strtolower((string) parse_url($link, PHP_URL_HOST));
            if ($kind === 'page') {
                $url = (parse_url($link, PHP_URL_SCHEME) === 'https' && preg_match('/(^|\.)facebook\.com\z/', $linkHost))
                    ? $link : 'https://www.facebook.com/' . $id;
            }
        }
        return array(
            'provider' => $provider,
            'kind' => $kind,
            'id' => $id,
            'title' => $title,
            'handle' => $handle,
            'thumbnail' => $thumbnail,
            'url' => $url,
            'live_streaming' => ($provider === 'youtube' && ($channel['live_streaming'] ?? '') === 'disabled') ? 'disabled' : 'unknown',
            'checked_at' => max(0, intval($channel['checked_at'] ?? 0)),
        );
    }

    /** The channel/page restream.ypt.me saved with a YouTube, Facebook or Twitch connection, or null. */
    public static function getChannelFromParameters($parameters)
    {
        $parameters = self::toArray($parameters);
        $provider = self::getConnectedProvider($parameters);
        if ($provider === '') {
            return null;
        }
        return self::normalizeChannel($parameters['restream.ypt.me']['channel'] ?? null, $provider);
    }

    /** "page" for Facebook pages, "profile" for a Facebook profile, otherwise "channel". */
    public static function getChannelNoun($provider, $kind = 'channel')
    {
        if ($provider === 'facebook') {
            return $kind === 'profile' ? self::t('profile') : self::t('page');
        }
        return self::t('channel');
    }

    public static function getChannelLabel($channel)
    {
        $channel = self::normalizeChannel($channel);
        if (empty($channel)) {
            return '';
        }
        $label = $channel['title'] !== '' ? $channel['title'] : ($channel['handle'] !== '' ? $channel['handle'] : $channel['id']);
        if ($channel['handle'] !== '' && strcasecmp($channel['handle'], $label) !== 0) {
            $label .= " ({$channel['handle']})";
        }
        if ($channel['kind'] === 'profile') {
            $label .= ' (' . self::t('profile') . ')';
        }
        return $label;
    }

    /** "YouTube channel X", "Facebook page X", "Twitch channel X". */
    public static function getChannelDescription($channel)
    {
        $channel = self::normalizeChannel($channel);
        if (empty($channel)) {
            return '';
        }
        return self::PROVIDERS[$channel['provider']] . ' ' . self::getChannelNoun($channel['provider'], $channel['kind']) . ' ' . self::getChannelLabel($channel);
    }

    /** Small card for the destination name: picture, name, handle, link. */
    public static function getChannelHtml($channel)
    {
        $channel = self::normalizeChannel($channel);
        if (empty($channel)) {
            return '';
        }
        $icons = array('youtube' => 'fa-youtube', 'facebook' => 'fa-facebook', 'twitch' => 'fa-twitch');
        $html = '<div class="restream-channel">';
        if ($channel['thumbnail'] !== '') {
            $html .= '<img src="' . self::e($channel['thumbnail']) . '" alt="" width="24" height="24" class="img-circle" loading="lazy" referrerpolicy="no-referrer"> ';
        }
        $label = '<i class="fab ' . $icons[$channel['provider']] . '" aria-hidden="true"></i> ' . self::e(self::getChannelLabel($channel));
        if ($channel['url'] !== '') {
            $html .= '<a href="' . self::e($channel['url']) . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
        } else {
            $html .= '<span>' . $label . '</span>';
        }
        return $html . '</div>';
    }

    /** Plain text from a provider message that may be a string, an object or an array. */
    public static function getPlainMessage($message, $max = 300)
    {
        if (is_object($message) || is_array($message)) {
            $array = self::toArray($message);
            if (is_string($array['error']['message'] ?? null)) {
                $message = $array['error']['message'];
            } elseif (is_string($array['message'] ?? null)) {
                $message = $array['message'];
            } else {
                $message = json_encode($message);
            }
        }
        $message = strip_tags(str_ireplace(array('<br>', '<br/>', '<br />'), ' ', (string) $message));
        $message = trim((string) preg_replace('/\s+/', ' ', $message));
        $length = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
        if ($length > $max) {
            $message = (function_exists('mb_substr') ? mb_substr($message, 0, $max - 3, 'UTF-8') : substr($message, 0, $max - 3)) . '...';
        }
        return $message;
    }

    /**
     * Recognizes "this YouTube channel cannot go live" (403 liveStreamingNotEnabled) in a
     * restream.ypt.me answer and returns an actionable message, or null.
     */
    public static function getLiveStreamingDisabledInfo($response, $destinationName)
    {
        if (!is_object($response) || empty($response->error)) {
            return null;
        }
        $details = json_encode(array($response->msg ?? '', $response->rawData ?? ''));
        if (!preg_match('/liveStreamingNotEnabled|not enabled for live streaming/i', (string) $details)) {
            return null;
        }
        $info = new stdClass();
        $info->reason = 'live_disabled';
        $info->msg = sprintf(self::t('YouTube says the channel connected to "%s" cannot go live yet.'), html_entity_decode((string) $destinationName, ENT_QUOTES, 'UTF-8'))
            . "\n" . self::t('Turn on live streaming for that channel in YouTube Studio (the first activation can take up to 24 hours), or connect a channel that can go live.');
        return $info;
    }

    /**
     * What to remember about one key request: success with its live link, or the failure reason.
     * $reason comes from the reconnect/live-streaming checks ('expired', 'permission',
     * 'live_disabled'); other failures keep the provider's message as plain text.
     */
    public static function getLastResult($response, $reason = '', $now = null)
    {
        $now = $now === null ? time() : intval($now);
        if (is_object($response) && isset($response->error) && $response->error === false) {
            $liveUrl = is_string($response->live_url ?? null) ? $response->live_url : '';
            if (!preg_match('#^https://(www\.)?(youtube\.com|facebook\.com|twitch\.tv)/#i', $liveUrl)) {
                $liveUrl = '';
            }
            return array('state' => 'ok', 'reason' => '', 'msg' => '', 'live_url' => $liveUrl, 'at' => $now);
        }
        if (!is_object($response)) {
            return array('state' => 'error', 'reason' => 'no_answer', 'msg' => self::t('The connection service did not answer.'), 'live_url' => '', 'at' => $now);
        }
        $message = '';
        foreach (array($response->msg ?? '', $response->rawData ?? '') as $candidate) {
            $message = self::getPlainMessage($candidate);
            if ($message !== '') {
                break;
            }
        }
        return array(
            'state' => 'error',
            'reason' => in_array($reason, array('expired', 'permission', 'live_disabled'), true) ? $reason : 'other',
            'msg' => $message,
            'live_url' => '',
            'at' => $now,
        );
    }

    private static function hint($level, $icon, $text)
    {
        return array('level' => $level, 'icon' => $icon, 'text' => $text);
    }

    private static function formatTime($time)
    {
        $time = intval($time);
        if ($time <= 0) {
            return '';
        }
        return function_exists('humanTimingAgo') ? humanTimingAgo($time, 0, false) : date('Y-m-d H:i', $time);
    }

    /**
     * Status hints for one destination, most important first.
     * $row: destination row (status, stream_url, parameters); $tokenExpired: needToRevalidate;
     * $lastResult: getLastResult() of the most recent key request; $sameChannelAs: ids of other
     * destinations sending to the same channel; $channelLookupFailed: the channel could not be read.
     */
    public static function getHints($row, $channel, $lastResult, $tokenExpired, $sameChannelAs = array(), $channelLookupFailed = false)
    {
        $row = self::toArray($row);
        $provider = self::getConnectedProvider($row['parameters'] ?? '');
        $label = $provider !== '' ? self::PROVIDERS[$provider] : '';
        $channel = self::normalizeChannel($channel, $provider !== '' ? $provider : null);
        $noun = self::getChannelNoun($provider, $channel['kind'] ?? 'page');
        $lastResult = self::toArray($lastResult);
        $hints = array();

        if (($row['status'] ?? 'a') !== 'a') {
            $hints[] = self::hint('muted', 'fa-pause-circle', self::t('Inactive: lives are not sent here.'));
        }
        if ($tokenExpired) {
            $hints[] = self::hint('danger', 'fa-plug', sprintf(self::t('The %s connection expired. Click Revalidate before the next live.'), $label !== '' ? $label : self::t('account')));
        }

        $state = $lastResult['state'] ?? '';
        $reason = $lastResult['reason'] ?? '';
        $when = self::formatTime($lastResult['at'] ?? 0);
        if ($state === 'error' && in_array($reason, array('expired', 'permission'), true) && !$tokenExpired) {
            $hints[] = self::hint('danger', 'fa-plug', sprintf(self::t('The last live could not use this %s connection (%s). Connect the account again with the %s button.'), $label, $when, $label));
        }
        if (($state === 'error' && $reason === 'live_disabled') || (!empty($channel) && $channel['live_streaming'] === 'disabled')) {
            $hints[] = self::hint('danger', 'fa-video-slash', self::t('YouTube says this channel cannot go live yet. Turn on live streaming in YouTube Studio; it can take up to 24 hours.'));
        }
        if ($state === 'error' && !in_array($reason, array('expired', 'permission', 'live_disabled'), true)) {
            $text = sprintf(self::t('The last live could not start here (%s).'), $when);
            if (!empty($lastResult['msg'])) {
                $text .= ' ' . $lastResult['msg'];
            }
            $hints[] = self::hint('warning', 'fa-exclamation-triangle', $text);
        }

        if (!empty($sameChannelAs)) {
            $ids = implode(', ', array_map(function ($id) {
                return '#' . intval($id);
            }, $sameChannelAs));
            $hints[] = self::hint('warning', 'fa-clone', sprintf(self::t('Same %s as destination %s: each one sends its own copy of the live. Delete the one you do not need.'), $noun, $ids));
        }

        if ($provider !== '' && empty($channel)) {
            $hints[] = $channelLookupFailed
                ? self::hint('warning', 'fa-question-circle', sprintf(self::t('We could not see which %s %s this connection uses. Connect it again with the %s button to check.'), $label, $noun, $label))
                : self::hint('info', 'fa-circle-notch fa-spin', sprintf(self::t('Checking which %s %s this connection uses...'), $label, $noun));
        }
        if ($provider === '' && preg_match('#(^|[/.])youtube\.com[:/]#i', (string) ($row['stream_url'] ?? ''))) {
            $hints[] = self::hint('info', 'fa-key', self::t('Filled in by hand: the stream key decides which YouTube channel receives the live.'));
        }

        if ($state === 'ok') {
            $text = sprintf(self::t('The last live connected here %s.'), $when);
            $hints[] = self::hint('success', 'fa-check-circle', $text) + array('live_url' => $lastResult['live_url'] ?? '');
        } elseif (empty($hints)) {
            $hints[] = self::hint('success', 'fa-check', self::t('Ready for the next live.'));
        }
        return $hints;
    }

    public static function getHintsHtml($hints, $extraHtml = '')
    {
        $html = '<div class="restream-destination-hints">';
        foreach ((array) $hints as $hint) {
            if (!is_array($hint) || empty($hint['text'])) {
                continue;
            }
            $level = in_array($hint['level'] ?? '', array('success', 'info', 'warning', 'danger', 'muted'), true) ? $hint['level'] : 'info';
            $icon = is_string($hint['icon'] ?? null) && preg_match('/^fa-[a-z0-9-]+( fa-[a-z0-9-]+)*\z/', $hint['icon']) ? $hint['icon'] : 'fa-info-circle';
            $html .= '<div class="text-' . $level . '"><i class="fas ' . $icon . '" aria-hidden="true"></i> ' . self::e($hint['text']);
            if (!empty($hint['live_url']) && preg_match('#^https://#i', $hint['live_url'])) {
                $html .= ' <a href="' . self::e($hint['live_url']) . '" target="_blank" rel="noopener noreferrer">' . self::e(self::t('Open')) . '</a>';
            }
            $html .= '</div>';
        }
        return $html . $extraHtml . '</div>';
    }

    /**
     * For each destination id, the other destinations of the same list connected to the same
     * channel or page (same provider and id). $channelsById: id => normalized channel or null.
     */
    public static function getSameChannelDestinations($channelsById)
    {
        $byChannel = array();
        foreach ((array) $channelsById as $id => $channel) {
            if (!empty($channel['id'])) {
                $byChannel[($channel['provider'] ?? 'youtube') . ':' . $channel['id']][] = intval($id);
            }
        }
        $result = array();
        foreach ($byChannel as $ids) {
            if (count($ids) < 2) {
                continue;
            }
            foreach ($ids as $id) {
                $result[$id] = array_values(array_diff($ids, array($id)));
            }
        }
        return $result;
    }
}
