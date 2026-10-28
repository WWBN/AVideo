<?php

/** Read-only preflight for a queued Encoder job, before downloading or transferring media. */
class EncoderJobStatus
{
    public static function inspect($videosId, $hash)
    {
        $videosId = (int) $videosId;
        $signed = false;
        if (is_string($hash) && $hash !== '' && strlen($hash) <= 4096) {
            $payload = json_decode((string) decryptString($hash));
            $signed = is_object($payload) && (int) ($payload->videos_id ?? 0) === $videosId
                && !empty($payload->timeout) && !empty($payload->ownerFingerprint);
        }
        if ($videosId < 1 || (!$signed && !User::isLogged())) {
            return self::failure('authentication_required', $videosId);
        }
        $video = new Video('', '', $videosId, true);
        if (!$video->getId()) {
            // An expired job hash proves only the old association. It may report deletion,
            // but it never grants login, a replacement video, or renewed upload credentials.
            return self::failure($signed || User::isAdmin() ? 'destination_deleted' : 'destination_unavailable', $videosId);
        }
        if (!User::isLogged() && $signed && (int) Video::getVideoIdFromHash($hash) === $videosId) {
            $_REQUEST['video_id_hash'] = $hash;
            useVideoHashOrLogin();
        }
        if (!User::isLogged()) {
            return self::failure('authentication_required', $videosId);
        }
        if (!Video::canEncoderEdit($videosId)) {
            return self::failure('destination_access_denied', $videosId);
        }
        if (!User::canUpload()) {
            return self::failure('uploads_disabled', $videosId);
        }
        return [
            'error' => false, 'code' => 'ready', 'destination_id' => $videosId,
            'msg' => 'Destination verified. The encoded output can be recovered automatically.',
            'video_id_hash' => $video->getVideoIdHash(),
            'encoderChunkToken' => getToken(604800, 'EncoderChunkUpload'),
        ];
    }

    private static function failure($code, $videosId)
    {
        $messages = [
            'destination_deleted' => 'The destination video was deleted. This job cannot be recovered automatically. Encoded files were kept.',
            'destination_unavailable' => 'The destination video is unavailable. Check the job account and destination. Encoded files were kept.',
            'authentication_required' => 'The site did not accept this job authorization. Automatic recovery cannot continue with the saved access. Encoded files were kept.',
            'destination_access_denied' => 'The job account cannot edit the destination video. Automatic recovery is blocked. Encoded files were kept.',
            'uploads_disabled' => 'Uploads are disabled for the job account. Automatic recovery is blocked. Encoded files were kept.',
        ];
        return ['error' => true, 'code' => $code, 'retryable' => false,
            'destination_id' => (int) $videosId, 'msg' => $messages[$code]];
    }
}
