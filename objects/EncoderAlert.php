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
            // Older Encoders do not send it; they repeat once per day.
            'reminder_minutes' => isset($request['reminder_minutes']) ? max(0, intval($request['reminder_minutes'])) : 1440,
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
            // Older Encoders do not send it; the footer then stays generic.
            'reminder_minutes' => isset($request['reminder_minutes']) ? max(0, intval($request['reminder_minutes'])) : 0,
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
        $safeTitle = htmlspecialchars($plainTitle, ENT_QUOTES, 'UTF-8');
        $title = '<strong>' . $safeTitle . '</strong>';
        $time = htmlspecialchars(self::formatMinutes($alert['minutes']), ENT_QUOTES, 'UTF-8');
        $isError = strpos($alert['type'], 'error') === 0;
        $isWaiting = strpos($alert['type'], 'waiting') === 0;

        switch ($alert['type']) {
            case 'waiting':
                $subject = __('Your video "%s" is waiting to be processed', true);
                $heading = __('Your video is waiting in the queue');
                $lead = sprintf(__('Your video %s has been waiting in the encoding queue for %s.'), $title, $time);
                break;
            case 'waiting_reminder':
                $subject = __('Reminder: your video "%s" is still waiting to be processed', true);
                $heading = __('Your video is still waiting in the queue');
                $lead = sprintf(__('Your video %s is still waiting in the encoding queue after %s.'), $title, $time);
                break;
            case 'processing':
                $subject = __('Your video "%s" is taking longer than usual to process', true);
                $heading = __('Your video is taking longer than usual');
                $lead = sprintf(__('Your video %s has been processing for %s, which is longer than usual.'), $title, $time);
                break;
            case 'processing_reminder':
                $subject = __('Reminder: your video "%s" is still processing', true);
                $heading = __('Your video is still processing');
                $lead = sprintf(__('Your video %s is still processing after %s.'), $title, $time);
                break;
            case 'error':
                $subject = __('Your video "%s" could not be processed', true);
                $heading = __('Your video could not be processed');
                $lead = sprintf(__('Your video %s could not be processed by the encoder.'), $title);
                break;
            default: // error_reminder
                $subject = __('Your video "%s" still could not be processed', true);
                $heading = __('Your video still could not be processed');
                $lead = sprintf(__('Your video %s has been in error for %s and was not processed.'), $title, $time);
                break;
        }

        $paragraphs = [];
        $name = trim(strip_tags((string) $ownerName));
        $paragraphs[] = empty($name) ? __('Hello,') : sprintf(__('Hello %s,'), htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        $paragraphs[] = $lead;
        $details = [];
        if ($plainTitle !== '') {
            $details[__('Video')] = $safeTitle;
        }
        if ($isError) {
            $tone = 'error';
            $badge = __('Failed');
            $paragraphs[] = self::getReasonText($alert['reason']);
            if ($alert['type'] === 'error_reminder') {
                $details[__('In error for')] = $time;
            }
            if ($alert['reason'] === 'transfer_failed') {
                // The encoded files are kept, so the administrator can retry without a new upload.
                $notice = __('The converted files were kept. Contact the site administrator to retry the transfer; you do not need to upload the video again.');
            } else {
                $notice = __('Please upload the video again. If it fails again, contact the site administrator.');
            }
        } else {
            $tone = $isWaiting ? 'info' : 'warning';
            $badge = $isWaiting ? __('Waiting') : __('Processing');
            $stage = self::getStatusLabel($alert['queue_status']);
            if (!empty($stage)) {
                $details[__('Current stage')] = $stage;
            }
            $details[$isWaiting ? __('Waiting for') : __('Processing for')] = $time;
            if ($isWaiting && !empty($alert['queue_position'])) {
                $details[__('Position in the queue')] = intval($alert['queue_position']);
            }
            $notice = __('Large or long videos can take more time. You do not need to upload the video again.');
        }
        if (!empty($alert['queue_id'])) {
            $details[__('Reference')] = sprintf(__('Encoder job #%d'), $alert['queue_id']);
        }

        $footer = [];
        if ($alert['reminder_minutes'] === 1440) {
            $footer[] = __('We will send at most one reminder per day while the status does not change.');
        } elseif ($alert['reminder_minutes'] > 0) {
            $footer[] = sprintf(__('We will send at most one reminder every %s while the status does not change.'), htmlspecialchars(self::formatMinutes($alert['reminder_minutes']), ENT_QUOTES, 'UTF-8'));
        }
        if (!empty($footer) && !empty($alert['retention_days'])) {
            $footer[] = sprintf(__('Reminders stop after %d days.'), $alert['retention_days']);
        }

        return [
            'subject' => sprintf($subject, $plainTitle),
            'body' => self::renderEmail($tone, $badge, $heading, $paragraphs, $details, $notice, __('Manage your video'), (string) $manageURL, implode(' ', $footer)),
        ];
    }

    /**
     * @return array ['subject' => plain text, 'body' => HTML]
     */
    public static function buildSystemEmail(array $alert)
    {
        $details = [];
        if (!empty($alert['encoder_url'])) {
            $details[__('Encoder')] = htmlspecialchars($alert['encoder_url'], ENT_QUOTES, 'UTF-8');
        }
        switch ($alert['check']) {
            case 'disk_low':
                $subject = __('Encoder alert: low disk space', true);
                $heading = __('The encoder is running out of disk space');
                $tone = 'error';
                $percent = empty($alert['disk_total']) ? 0 : round($alert['disk_free'] * 100 / $alert['disk_total'], 1);
                $lead = sprintf(
                    __('Only %s of %s is free (%s%%). Encodings fail when the disk is full. Delete old files or add storage.'),
                    htmlspecialchars(humanFileSize($alert['disk_free']), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(humanFileSize($alert['disk_total']), ENT_QUOTES, 'UTF-8'),
                    $percent
                );
                break;
            case 'queue_stalled':
                $subject = __('Encoder alert: the encoding queue is stalled', true);
                $heading = __('The encoding queue is stalled');
                $tone = 'warning';
                $lead = sprintf(
                    __('%d videos are waiting, the oldest for %s, and nothing is processing. The cron tried to restart the queue. Check the encoder log and the queue page.'),
                    $alert['waiting'],
                    htmlspecialchars(self::formatMinutes($alert['oldest_waiting_minutes']), ENT_QUOTES, 'UTF-8')
                );
                break;
            default: // error_spike
                $subject = __('Encoder alert: many videos failed in the last hour', true);
                $heading = __('Many videos failed in the last hour');
                $tone = 'error';
                $lead = sprintf(__('%d videos failed in the last hour.'), $alert['errors_last_hour']);
                if (!empty($alert['last_error'])) {
                    $details[__('Last error')] = '<code style="font-family:Consolas,Menlo,monospace;font-size:12px;font-weight:normal;">' . htmlspecialchars($alert['last_error'], ENT_QUOTES, 'UTF-8') . '</code>';
                }
                break;
        }
        $footer = __('You receive this because your account is an administrator of this encoder.');
        if (!empty($alert['reminder_minutes'])) {
            $footer .= ' ' . sprintf(__('The same alert is repeated at most every %s while the problem continues.'), htmlspecialchars(self::formatMinutes($alert['reminder_minutes']), ENT_QUOTES, 'UTF-8'));
        } else {
            $footer .= ' ' . __('The same alert is repeated at most every few hours while the problem continues.');
        }
        return [
            'subject' => $subject,
            'body' => self::renderEmail($tone, __('Encoder alert'), $heading, [$lead], $details, '', __('Open the encoder'), $alert['encoder_url'], $footer),
        ];
    }

    /**
     * Lays out the alert inside view/include/emailTemplate.html (added by sendSiteEmail()).
     * Tables and inline styles only, because most e-mail clients drop <style> blocks.
     * Every text argument must already be escaped HTML; only $buttonURL is escaped here.
     */
    private static function renderEmail($tone, $badge, $heading, array $paragraphs, array $details, $notice, $buttonLabel, $buttonURL, $footer)
    {
        $tones = [
            'info' => ['#2563eb', '#eff6ff', '#1e40af'],
            'warning' => ['#d97706', '#fffbeb', '#92400e'],
            'error' => ['#dc2626', '#fef2f2', '#991b1b'],
        ];
        list($accent, $soft, $ink) = isset($tones[$tone]) ? $tones[$tone] : $tones['info'];
        $font = 'font-family:Arial,Helvetica,sans-serif;';
        $layout = 'role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"';

        $html = '<table ' . $layout . ' style="border-collapse:collapse;' . $font . 'color:#1f2937;">';
        $html .= '<tr><td style="border-top:4px solid ' . $accent . ';padding:24px 0 4px 0;">'
            . '<span style="display:inline-block;padding:4px 12px;border-radius:12px;background-color:' . $soft . ';color:' . $ink . ';' . $font . 'font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">' . $badge . '</span>'
            . '<h2 style="margin:16px 0 0 0;' . $font . 'font-size:22px;line-height:1.3;font-weight:bold;color:#111827;text-align:left;">' . $heading . '</h2>'
            . '</td></tr>';

        $html .= '<tr><td style="padding:12px 0 4px 0;">';
        foreach ($paragraphs as $paragraph) {
            $html .= '<p style="margin:0 0 12px 0;' . $font . 'font-size:15px;line-height:1.6;color:#374151;">' . $paragraph . '</p>';
        }
        $html .= '</td></tr>';

        if (!empty($details)) {
            $rows = '';
            $remaining = count($details);
            foreach ($details as $label => $value) {
                $border = --$remaining > 0 ? 'border-bottom:1px solid #e5e7eb;' : '';
                $rows .= '<tr>'
                    . '<td width="38%" style="padding:10px 16px;' . $border . $font . 'font-size:13px;line-height:1.4;color:#6b7280;vertical-align:top;">' . $label . '</td>'
                    . '<td style="padding:10px 16px;' . $border . $font . 'font-size:14px;line-height:1.4;font-weight:bold;color:#111827;vertical-align:top;word-break:break-word;">' . $value . '</td>'
                    . '</tr>';
            }
            $html .= '<tr><td style="padding:8px 0;"><table ' . $layout . ' style="border-collapse:separate;border:1px solid #e5e7eb;border-radius:8px;background-color:#f9fafb;">' . $rows . '</table></td></tr>';
        }

        if ($notice !== '') {
            $html .= '<tr><td style="padding:8px 0;"><table ' . $layout . '><tr>'
                . '<td style="border-left:4px solid ' . $accent . ';border-radius:4px;background-color:' . $soft . ';padding:12px 16px;' . $font . 'font-size:14px;line-height:1.5;color:' . $ink . ';">' . $notice . '</td>'
                . '</tr></table></td></tr>';
        }

        if ($buttonURL !== '') {
            $url = htmlspecialchars($buttonURL, ENT_QUOTES, 'UTF-8');
            $html .= '<tr><td align="center" style="padding:20px 0 8px 0;text-align:center;">'
                . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;width:auto;"><tr>'
                . '<td bgcolor="#2563eb" style="border-radius:6px;background-color:#2563eb;">'
                . '<a href="' . $url . '" style="display:inline-block;padding:12px 28px;' . $font . 'font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:6px;">' . $buttonLabel . '</a>'
                . '</td></tr></table>'
                . '<p style="margin:12px 0 0 0;' . $font . 'font-size:12px;line-height:1.5;color:#6b7280;word-break:break-all;">' . __('If the button does not work, copy this link into your browser:')
                . '<br><a href="' . $url . '" style="color:#2563eb;">' . $url . '</a></p>'
                . '</td></tr>';
        }

        if ($footer !== '') {
            $html .= '<tr><td style="padding:24px 0 0 0;"><table ' . $layout . '><tr>'
                . '<td style="border-top:1px solid #e5e7eb;padding:16px 0 0 0;' . $font . 'font-size:12px;line-height:1.5;color:#6b7280;">' . $footer . '</td>'
                . '</tr></table></td></tr>';
        }
        return $html . '</table>';
    }
}
