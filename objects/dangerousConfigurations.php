<?php

/**
 * Plugin settings whose value reaches a shell command, a server-side file path or a server-side
 * network request. On sites with $global['disableAdvancedConfigurations'] (an admin account open
 * on purpose, e.g. a demo) the web UI cannot change them: a submitted value is dropped and the
 * stored value is kept (admin/save.json.php, objects/pluginAddDataObject.json.php), and the
 * fields are not shown in the admin settings forms (createTable()).
 *
 * The stored value is still used, so a site whose live server, storage or standalone FFmpeg
 * differs from the plugin default keeps working. Settings that are only FFmpeg options
 * (CustomizeAdvanced/Rebroadcaster ffmpegParameters) are also replaced by their default at the
 * point of use while the flag is on. List a setting here by plugin name and property name.
 */
function getDangerousPluginSettings()
{
    return [
        'API' => ['standAloneFFMPEG'],
        'AWS_S3' => ['endpoint'],
        'Backup' => ['directory', 'ftpServer', 'backupAllSufix', 'authKey'],
        'Cache' => ['cacheDir'],
        'CDN' => ['storage_hostname'],
        'CloneSite' => ['cloneSiteURL', 'cloneSiteSSHIP', 'cloneSiteSSHUser', 'cloneSiteSSHPort'],
        'CustomizeAdvanced' => ['ffmpegParameters', 'encoderNetwork'],
        'DiscordNotify' => ['webhook_url'],
        'FTP_Storage' => ['hostname', 'path', 'endpoint'],
        'Live' => ['server', 'playerServer', 'stats', 'controlURL', 'controlServer', 'restreamerURL', 'restreamStandAloneFFMPEG', 'hls_path'],
        'LoginLDAP' => ['server'],
        'LoginWordPress' => ['customWordPressSite'],
        'Notifications' => ['notifyVideoUploadWebhook'],
        'Rebroadcaster' => ['ffmpegParameters'],
        'SecureVideosDirectory' => ['videosAbsolutePath'],
        'SendRecordedToEncoder' => ['webSiteRootURL'],
        'SlackNotify' => ['webhook_url'],
        'User_Controll' => ['hls_path'],
        'YPTSocket' => ['host', 'port', 'server_crt_file', 'server_key_file'],
    ];
}

function isAdvancedConfigurationsDisabled()
{
    global $global;
    return !empty($global['disableAdvancedConfigurations']);
}

// Plugin names are matched without case, like the database lookup that loads the plugin.
function getDangerousConfigKeys($pluginName)
{
    foreach (getDangerousPluginSettings() as $name => $keys) {
        if (strcasecmp($name, (string) $pluginName) === 0) {
            return $keys;
        }
    }
    return [];
}

function isDangerousConfigKey($pluginName, $key)
{
    return in_array((string) $key, getDangerousConfigKeys($pluginName), true);
}

/**
 * Returns $submitted with every dangerous setting of $pluginName put back to its value in $stored
 * (or removed, when $stored does not have it, so the plugin default applies). Does nothing unless
 * disableAdvancedConfigurations is on. $changedKeys receives the settings whose submitted value
 * was dropped.
 */
function keepStoredDangerousPluginSettings($pluginName, $submitted, $stored, &$changedKeys = [])
{
    $changedKeys = [];
    if (!isAdvancedConfigurationsDisabled() || !is_object($submitted)) {
        return $submitted;
    }
    $result = clone $submitted;
    foreach (getDangerousConfigKeys($pluginName) as $key) {
        $hasStored = is_object($stored) && property_exists($stored, $key);
        if (!property_exists($result, $key) && !$hasStored) {
            continue;
        }
        if (property_exists($result, $key) && (!$hasStored || json_encode($result->$key) !== json_encode($stored->$key))) {
            $changedKeys[] = $key;
        }
        if ($hasStored) {
            $result->$key = $stored->$key;
        } else {
            unset($result->$key);
        }
    }
    return $result;
}
