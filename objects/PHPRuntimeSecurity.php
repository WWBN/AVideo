<?php

/**
 * Local version criteria for the PHP runtime advisories published on 2026-10-21.
 * A Debian/Ubuntu backport is accepted only from the changelog of the package this process runs
 * (local files; no network access, shell commands or shared cache).
 * New implementation: no equivalent in functions*.php, admin health checks or Notifications.
 */
class PHPRuntimeSecurity
{
    const FIXED_VERSIONS = ['8.2' => '8.2.35', '8.3' => '8.3.36', '8.4' => '8.4.27', '8.5' => '8.5.12'];
    const SOURCES = [
        'PHP security notice' => 'https://www.php.net/archive/2026.php#2026-10-21-5',
        'CVE-2026-109618' => 'https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w',
        'CVE-2026-109619' => 'https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp',
    ];
    const CVE_IDS = ['CVE-2026-109618', 'CVE-2026-109619'];
    // Debian/Ubuntu file holding the PHP engine of each web SAPI (%s = branch, e.g. 8.3).
    const DISTRIBUTION_ENGINE_FILES = [
        'apache2handler' => ['/usr/lib/apache2/modules/libphp%s.so'],
        'fpm-fcgi' => ['/usr/sbin/php-fpm%s'],
        'cgi-fcgi' => ['/usr/lib/cgi-bin/php%s', '/usr/bin/php-cgi%s'],
        'cgi' => ['/usr/lib/cgi-bin/php%s', '/usr/bin/php-cgi%s'],
    ];

    public static function detect()
    {
        // Always read the process serving this request, never CLI output or another node's cache.
        try {
            $result = self::evaluate(PHP_VERSION, PHP_VERSION_ID, PHP_SAPI);
            if (self::acceptsDistributionEvidence($result)) {
                $result = self::evaluateDistributionEvidence($result, self::readProcessMaps(), self::readDistributionChangelog($result['branch']));
            }
            return $result;
        } catch (\Throwable $th) {
            _error_log('PHP runtime security check failed');
            return self::evaluate(null, null, null);
        }
    }

    public static function evaluate($version, $versionId, $sapi)
    {
        $result = [
            'version' => is_string($version) ? $version : '',
            'version_id' => is_int($versionId) ? $versionId : null,
            'sapi' => is_string($sapi) ? $sapi : '',
            'branch' => '',
            'fixed_version' => null,
            'status' => 'unknown',
            'reason' => 'detection_failed',
        ];
        if (!is_string($version) || !preg_match('/^([1-9][0-9]?)\.(0|[1-9][0-9]?)\.(0|[1-9][0-9]?)(.*)$/D', $version, $parts)) {
            return $result;
        }
        $base = $parts[1] . '.' . $parts[2] . '.' . $parts[3];
        $result['branch'] = $parts[1] . '.' . $parts[2];
        $result['fixed_version'] = self::FIXED_VERSIONS[$result['branch']] ?? null;
        if (!is_int($versionId) || $versionId !== ((int) $parts[1] * 10000 + (int) $parts[2] * 100 + (int) $parts[3])) {
            return $result;
        }
        // Unknown and non-web SAPIs cannot establish the web runtime's status.
        if (!in_array($sapi, ['apache2handler', 'apache2filter', 'cgi-fcgi', 'cgi', 'fpm-fcgi', 'litespeed', 'frankenphp', 'cli-server'], true)) {
            $result['reason'] = 'unknown_sapi';
            return $result;
        }
        $suffix = $parts[4];
        if (preg_match('/(?:dev|alpha|beta|rc|snapshot)/i', $suffix)) {
            $result['reason'] = 'prerelease';
            return $result;
        }
        if ($suffix !== '' && !preg_match('/^[-+][a-z0-9][a-z0-9.+~_-]*$/iD', $suffix)) {
            $result['reason'] = 'unrecognized_version';
            return $result;
        }
        if (version_compare($base, '8.2.0', '<')) {
            $result['status'] = 'potentially_affected';
            $result['reason'] = 'legacy_branch';
        } elseif ($result['fixed_version'] === null) {
            $result['reason'] = 'unmapped_branch';
        } elseif (version_compare($base, $result['fixed_version'], '<')) {
            $result['status'] = 'potentially_affected';
            $result['reason'] = 'below_fixed_version';
        } elseif ($suffix !== '') {
            // A vendor suffix is not evidence of the contents of a particular package.
            $result['reason'] = 'vendor_build';
        } else {
            $result['status'] = 'meets_version_criteria';
            $result['reason'] = 'fixed_release';
        }
        return $result;
    }

    private static function acceptsDistributionEvidence(array $result)
    {
        return in_array($result['reason'], ['below_fixed_version', 'vendor_build', 'legacy_branch'], true) && isset(self::DISTRIBUTION_ENGINE_FILES[$result['sapi']]);
    }

    /**
     * Debian/Ubuntu backport fixes without changing PHP_VERSION (Ubuntu 24.04 stays 8.3.6). The fix is
     * accepted only when this process's memory map shows it runs the package's engine file and the
     * changelog of that package (newest entry = the reported version) lists every CVE, none of them
     * reverted. Anything missing or unreadable leaves $result unchanged.
     */
    public static function evaluateDistributionEvidence(array $result, $processMaps, $changelog)
    {
        if (!self::acceptsDistributionEvidence($result) || !is_string($processMaps) || !is_string($changelog)) {
            return $result;
        }
        $files = [];
        foreach (self::DISTRIBUTION_ENGINE_FILES[$result['sapi']] as $file) {
            $files[] = preg_quote(sprintf($file, $result['branch']), '~');
        }
        if (!preg_match('~ (?:' . implode('|', $files) . ')( \(deleted\))?$~m', $processMaps, $engine)) {
            return $result;
        }
        preg_match('/^\d+\.\d+\.\d+/', $result['version'], $base);
        if (!preg_match('~^php' . preg_quote($result['branch'], '~') . ' \(((?:\d+:)?' . preg_quote($base[0], '~') . '(?:[-+\~][^)\s]*)?)\) ~', $changelog, $package)) {
            return $result;
        }
        foreach (self::CVE_IDS as $cve) {
            $cve = preg_quote($cve, '~') . '(?!\d)';
            if (!preg_match('~' . $cve . '~', $changelog) || preg_match('~^(?=.*' . $cve . ')(?=.*\b(?:revert|disabl|drop|remov)).*$~mi', $changelog)) {
                return $result;
            }
        }
        $result['package'] = 'php' . $result['branch'] . ' ' . $package[1];
        if (!empty($engine[1])) {
            // dpkg replaced the file after this process started, so it still runs the old copy.
            $result['reason'] = 'restart_required';
            return $result;
        }
        $result['status'] = 'distribution_backport';
        $result['reason'] = 'distribution_changelog';
        return $result;
    }

    public static function readProcessMaps($file = '/proc/self/maps')
    {
        // open_basedir also hides /proc and /usr/share/doc: no evidence then.
        if (ini_get('open_basedir') || !is_readable($file)) {
            return null;
        }
        $maps = file_get_contents($file);
        return is_string($maps) ? $maps : null;
    }

    public static function readDistributionChangelog($branch, $directory = '/usr/share/doc')
    {
        $file = rtrim($directory, '/') . '/php' . $branch . '-common/changelog.Debian.gz';
        if (ini_get('open_basedir') || !function_exists('gzopen') || !is_readable($file)) {
            return null;
        }
        $changelog = file_get_contents('compress.zlib://' . $file, false, null, 0, 4194304);
        return is_string($changelog) ? $changelog : null;
    }

    public static function isHTMLResponse(array $headers, $scriptName = '')
    {
        // JSON/API entry points may set their response header after loading templates.
        if (preg_match('~(?:\.json(?:\.php)?$|/plugin/API/)~i', str_replace('\\', '/', $scriptName))) {
            return false;
        }
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') === 0 &&
                !preg_match('~^Content-Type:\s*(?:text/html|application/xhtml\+xml)(?:\s*;|\s*$)~iD', $header)) {
                return false;
            }
        }
        return true;
    }

    public static function renderAdminNotice(array $result, $showPassing = false)
    {
        if (!class_exists('User', false) || !User::isLogged() || !User::isAdmin() || !self::isHTMLResponse(headers_list(), $_SERVER['SCRIPT_NAME'] ?? '')) {
            return '';
        }
        $passing = in_array($result['status'], ['meets_version_criteria', 'distribution_backport'], true);
        if ($passing && !$showPassing) {
            return '';
        }
        $escape = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };
        $title = __('PHP runtime security', true);
        if ($result['status'] === 'distribution_backport') {
            $message = __('Fixed by the installed distribution package: its changelog lists both CVEs and this web process uses it. This does not establish general system security.', true);
        } elseif ($passing) {
            $message = __('Version criteria met for these two CVEs only. This does not establish general system security.', true);
        } elseif ($result['status'] === 'potentially_affected') {
            $message = __('Potentially affected; confirm the fix with your hosting provider.', true);
        } else {
            $message = __('Unknown status; the PHP runtime fix could not be verified. Contact your hosting provider.', true);
        }
        $html = '<section class="alert alert-' . ($passing ? 'info' : 'warning') . '" role="status" aria-label="' . $escape($title) . '">';
        $html .= '<h2 class="h4">' . $escape($title) . ' &mdash; CVE-2026-109618 / CVE-2026-109619</h2>';
        $html .= '<p><strong>' . $escape($message) . '</strong></p>';
        $html .= '<p>' . $escape(__('Observed web process', true)) . ': PHP <code>' . $escape($result['version'] ?: __('Unknown', true)) . '</code>; SAPI <code>' . $escape($result['sapi'] ?: __('Unknown', true)) . '</code>.</p>';
        if (!empty($result['package'])) {
            $html .= '<p>' . $escape(__('Distribution package', true)) . ': <code>' . $escape($result['package']) . '</code>.</p>';
        }
        if ($result['reason'] === 'restart_required') {
            $html .= '<p>' . $escape(__('The installed PHP package lists both fixes, but this web process still runs the copy loaded before the update. Restart Apache or PHP-FPM.', true)) . '</p>';
        }
        if (!empty($result['fixed_version'])) {
            $html .= '<p>' . $escape(__('Minimum fixed release for this branch', true)) . ': <strong>' . $escape($result['fixed_version']) . '</strong>. ' . $escape(__('Use this release or a later stable release in the same branch.', true)) . '</p>';
        } else {
            $html .= '<p>' . $escape(__('No verified fixed release is mapped for this branch. Use a supported, compatible PHP branch or obtain package-specific vendor evidence.', true)) . '</p>';
            $html .= '<p>' . $escape(__('Known fixed releases', true)) . ': ' . $escape(implode(', ', self::FIXED_VERSIONS)) . '.</p>';
        }
        if ($result['reason'] === 'legacy_branch') {
            $html .= '<p>' . $escape(__('PHP 8.1 and older no longer receive upstream security updates. Ask your vendor about both fixes or upgrade to a supported branch.', true)) . '</p>';
        }
        $html .= '<p>' . $escape(__('Updating AVideo alone does not fix PHP. Update the web runtime or confirm a backport for both CVEs with your hosting provider.', true)) . '</p>';
        $html .= '<p>' . $escape(__('A distribution name or version suffix does not verify a backport. Prerelease, custom and unmapped builds require vendor verification.', true)) . '</p>';
        $html .= '<p>' . $escape(__('This observation covers only the process serving this request, not every worker, node, container or separate Encoder. Check each environment.', true)) . '</p>';
        $html .= '<p>' . $escape(__('Official sources', true)) . ': ';
        foreach (self::SOURCES as $label => $url) {
            $html .= '<a href="' . $escape($url) . '" target="_blank" rel="noopener noreferrer">' . $escape($label === 'PHP security notice' ? __('PHP security notice', true) : $label) . '</a> ';
        }
        return $html . '</p></section>';
    }
}
