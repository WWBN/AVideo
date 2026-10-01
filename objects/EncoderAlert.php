<?php

/**
 * Builds the e-mails requested by the Encoder monitor cron (Encoder: install/cron.php and
 * objects/EncoderMonitor.php) through objects/aVideoEncoderAlert.json.php.
 *
 * Every request value is whitelisted or cast here. The recipient is never taken from the
 * request: the endpoint resolves it from the video owner or the authenticated account.
 */
class EncoderAlert
{
    const OWNER_TYPES = ['waiting', 'waiting_reminder', 'processing', 'processing_reminder', 'error', 'error_reminder'];
    const SYSTEM_CHECKS = ['disk_low', 'queue_stalled', 'error_spike'];
    const QUEUE_STATUSES = ['queue', 'downloaded', 'downloading', 'encoding', 'packing', 'fixing', 'transferring', 'error'];
    const REASONS = ['download_failed', 'conversion_failed', 'transfer_failed', 'worker_stopped', 'video_not_found', 'unknown'];

    /**
     * @return array|false normalized owner alert, false when the type is not supported
     */
    public static function readOwnerAlert(array $request)
    {
        $type = isset($request['type']) ? (string) $request['type'] : '';
        if (!in_array($type, self::OWNER_TYPES, true)) {
            return false;
        }
        $status = isset($request['queue_status']) ? (string) $request['queue_status'] : '';
        $reason = isset($request['reason']) ? (string) $request['reason'] : '';
        return [
            'type' => $type,
            'videos_id' => max(0, intval(@$request['videos_id'])),
            'queue_id' => max(0, intval(@$request['queue_id'])),
            'queue_status' => in_array($status, self::QUEUE_STATUSES, true) ? $status : '',
            'minutes' => max(0, intval(@$request['minutes'])),
            'queue_position' => max(0, intval(@$request['queue_position'])),
            'reason' => in_array($reason, self::REASONS, true) ? $reason : 'unknown',
            'retention_days' => max(0, intval(@$request['retention_days'])),
        ];
    }

    /**
     * @return array|false normalized system alert, false when the check is not supported
     */
    public static function readSystemAlert(array $request)
    {
        $check = isset($request['check']) ? (string) $request['check'] : '';
        if (!in_array($check, self::SYSTEM_CHECKS, true)) {
            return false;
        }
        $encoderURL = isset($request['encoder_url']) ? trim((string) $request['encoder_url']) : '';
        if (!filter_var($encoderURL, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $encoderURL)) {
            $encoderURL = '';
        }
        $lastError = isset($request['last_error']) ? strip_tags((string) $request['last_error']) : '';
        return [
            'check' => $check,
            'disk_free' => max(0, floatval(@$request['disk_free'])),
            'disk_total' => max(0, floatval(@$request['disk_total'])),
            'waiting' => max(0, intval(@$request['waiting'])),
            'processing' => max(0, intval(@$request['processing'])),
            'oldest_waiting_minutes' => max(0, intval(@$request['oldest_waiting_minutes'])),
            'errors_last_hour' => max(0, intval(@$request['errors_last_hour'])),
            'last_error' => function_exists('mb_substr') ? mb_substr($lastError, 0, 200) : substr($lastError, 0, 200),
            'encoder_url' => $encoderURL,
        ];
    }

    public static function getStatusLabel($status)
    {
        $labels = [
            'queue' => __('Waiting in the queue'),
            'downloaded' => __('Waiting in the queue'),
            'downloading' => __('Downloading the original file'),
            'encoding' => __('Encoding'),
            'packing' => __('Packaging'),
            'fixing' => __('Repairing the file'),
            'transferring' => __('Sending to the site'),
            'error' => __('Error'),
        ];
        return isset($labels[$status]) ? $labels[$status] : '';
    }

    public static function getReasonText($reason)
    {
        $texts = [
            'download_failed' => __('The encoder could not download the original file.'),
            'conversion_failed' => __('The file could not be converted. It may be damaged or use a format the encoder does not support.'),
            'transfer_failed' => __('The converted video could not be sent back to this site.'),
            'worker_stopped' => __('The encoding process stopped unexpectedly, and the automatic retries also failed.'),
            'video_not_found' => __('The encoder could not find this video on the site.'),
            'unknown' => __('The encoder reported an unexpected error.'),
        ];
        return isset($texts[$reason]) ? $texts[$reason] : $texts['unknown'];
    }

    public static function formatMinutes($minutes)
    {
        return secondsToHumanTiming(max(1, intval($minutes)) * 60, 1);
    }

    /**
     * @return array ['subject' => plain text, 'body' => HTML]
     */
    public static function buildOwnerEmail(array $alert, $videoTitle, $ownerName, $manageURL)
    {
        $plainTitle = trim(preg_replace('/\s+/', ' ', strip_tags((string) $videoTitle)));
        $title = '<strong>' . htmlspecialchars($plainTitle, ENT_QUOTES, 'UTF-8') . '</strong>';
        $time = htmlspecialchars(self::formatMinutes($alert['minutes']), ENT_QUOTES, 'UTF-8');
        $isError = strpos($alert['type'], 'error') === 0;

        switch ($alert['type']) {
            case 'waiting':
                $subject = __('Your video "%s" is waiting to be processed', true);
                $lead = sprintf(__('Your video %s has been waiting in the encoding queue for %s.'), $title, $time);
                break;
            case 'waiting_reminder':
                $subject = __('Reminder: your video "%s" is still waiting to be processed', true);
                $lead = sprintf(__('Your video %s is still waiting in the encoding queue after %s.'), $title, $time);
                break;
            case 'processing':
                $subject = __('Your video "%s" is taking longer than usual to process', true);
                $lead = sprintf(__('Your video %s has been processing for %s, which is longer than usual.'), $title, $time);
                break;
            case 'processing_reminder':
                $subject = __('Reminder: your video "%s" is still processing', true);
                $lead = sprintf(__('Your video %s is still processing after %s.'), $title, $time);
                break;
            case 'error':
                $subject = __('Your video "%s" could not be processed', true);
                $lead = sprintf(__('Your video %s could not be processed by the encoder.'), $title);
                break;
            default: // error_reminder
                $subject = __('Your video "%s" still could not be processed', true);
                $lead = sprintf(__('Your video %s has been in error for %s and was not processed.'), $title, $time);
                break;
        }

        $paragraphs = [];
        $name = trim(strip_tags((string) $ownerName));
        $paragraphs[] = empty($name) ? __('Hello,') : sprintf(__('Hello %s,'), htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        $paragraphs[] = $lead;
        if ($isError) {
            $paragraphs[] = self::getReasonText($alert['reason']);
            if ($alert['reason'] === 'transfer_failed') {
                // The encoded files are kept, so the administrator can retry without a new upload.
                $action = __('The converted files were kept. Contact the site administrator to retry the transfer; you do not need to upload the video again.');
            } else {
                $action = __('Please upload the video again. If it fails again, contact the site administrator.');
            }
            if (!empty($alert['queue_id'])) {
                $action .= ' ' . sprintf(__('Reference: encoder job #%d.'), $alert['queue_id']);
            }
            $paragraphs[] = $action;
        } else {
            $stage = self::getStatusLabel($alert['queue_status']);
            if (!empty($stage)) {
                $paragraphs[] = sprintf(__('Current stage: %s.'), $stage);
            }
            if (strpos($alert['type'], 'waiting') === 0 && !empty($alert['queue_position'])) {
                $paragraphs[] = sprintf(__('Position in the queue: %d.'), $alert['queue_position']);
            }
            $paragraphs[] = __('Large or long videos can take more time. You do not need to upload the video again.');
        }
        if (!empty($manageURL)) {
            $url = htmlspecialchars($manageURL, ENT_QUOTES, 'UTF-8');
            $paragraphs[] = __('Manage your video') . ': <a href="' . $url . '">' . $url . '</a>';
        }
        $footer = __('We will send at most one reminder per day while the status does not change.');
        if (!empty($alert['retention_days'])) {
            $footer .= ' ' . sprintf(__('Reminders stop after %d days.'), $alert['retention_days']);
        }
        $paragraphs[] = '<small>' . $footer . '</small>';

        return [
            'subject' => sprintf($subject, $plainTitle),
            'body' => '<p>' . implode('</p><p>', $paragraphs) . '</p>',
        ];
    }

    /**
     * @return array ['subject' => plain text, 'body' => HTML]
     */
    public static function buildSystemEmail(array $alert)
    {
        switch ($alert['check']) {
            case 'disk_low':
                $subject = __('Encoder alert: low disk space', true);
                $percent = empty($alert['disk_total']) ? 0 : round($alert['disk_free'] * 100 / $alert['disk_total'], 1);
                $details = sprintf(
                    __('Only %s of %s is free (%s%%). Encodings fail when the disk is full. Delete old files or add storage.'),
                    htmlspecialchars(humanFileSize($alert['disk_free']), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(humanFileSize($alert['disk_total']), ENT_QUOTES, 'UTF-8'),
                    $percent
                );
                break;
            case 'queue_stalled':
                $subject = __('Encoder alert: the encoding queue is stalled', true);
                $details = sprintf(
                    __('%d videos are waiting, the oldest for %s, and nothing is processing. The cron tried to restart the queue. Check the encoder log and the queue page.'),
                    $alert['waiting'],
                    htmlspecialchars(self::formatMinutes($alert['oldest_waiting_minutes']), ENT_QUOTES, 'UTF-8')
                );
                break;
            default: // error_spike
                $subject = __('Encoder alert: many videos failed in the last hour', true);
                $details = sprintf(__('%d videos failed in the last hour.'), $alert['errors_last_hour']);
                if (!empty($alert['last_error'])) {
                    $details .= ' ' . __('Last error') . ': <code>' . htmlspecialchars($alert['last_error'], ENT_QUOTES, 'UTF-8') . '</code>';
                }
                break;
        }
        $paragraphs = [];
        if (!empty($alert['encoder_url'])) {
            $url = htmlspecialchars($alert['encoder_url'], ENT_QUOTES, 'UTF-8');
            $paragraphs[] = __('Encoder') . ': <a href="' . $url . '">' . $url . '</a>';
        }
        $paragraphs[] = $details;
        $paragraphs[] = '<small>' . __('You receive this because your account is an administrator of this encoder. The same alert is repeated at most every few hours while the problem continues.') . '</small>';
        return [
            'subject' => $subject,
            'body' => '<p>' . implode('</p><p>', $paragraphs) . '</p>',
        ];
    }
}
