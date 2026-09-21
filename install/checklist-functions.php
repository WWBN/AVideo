<?php
// Pre-install diagnostics cannot bootstrap the application or its configured plugins.
// Core checks in functions.php / health_check_functions.php depend on that bootstrap.
function installerChecklistBytes($value) {
    if (!preg_match('/^\s*(-?\d+)\s*([KMG]?)\s*$/i', (string) $value, $match)) { return null; }
    $power = array_search(strtoupper($match[2]), ['', 'K', 'M', 'G'], true);
    return (float) $match[1] * pow(1024, $power);
}

function installerChecklistLimit($setting, $value) {
    $limits = ['upload_max_filesize' => 100 * 1024 * 1024, 'post_max_size' => 100 * 1024 * 1024,
        'memory_limit' => 512 * 1024 * 1024, 'max_execution_time' => 7200];
    $unlimited = ['post_max_size' => 0, 'memory_limit' => -1, 'max_execution_time' => 0];
    $bytes = installerChecklistBytes($value);
    if ($bytes === null || !isset($limits[$setting])) { return 'unknown'; }
    if (isset($unlimited[$setting]) && $bytes === (float) $unlimited[$setting]) { return 'passed'; }
    return $bytes >= $limits[$setting] ? 'passed' : 'failed';
}

function installerChecklistPrograms(array $names, $path = null, $windows = null) {
    $windows = $windows === null ? PHP_OS_FAMILY === 'Windows' : $windows;
    $path = $path === null ? (string) getenv('PATH') : $path;
    $found = [];
    foreach (explode($windows ? ';' : ':', $path) as $directory) {
        $directory = trim($directory, " \t\n\r\0\x0B\"");
        // Ignore relative PATH entries: the service working directory can change.
        if ($directory === '' || ($windows ? !preg_match('~^(?:[a-z]:[/\\\\]|[/\\\\]{2})~i', $directory) : $directory[0] !== '/')) { continue; }
        foreach ($names as $name) {
            foreach ($windows ? ['.exe', '.bat', '.cmd'] : [''] as $suffix) {
                $candidate = rtrim($directory, '/\\') . '/' . $name . $suffix;
                if (!@is_file($candidate) || !@is_readable($candidate) || !@is_executable($candidate)) { continue; }
                $resolved = realpath($candidate);
                if ($resolved !== false) { $found[$windows ? strtolower($resolved) : $resolved] = $resolved; }
            }
        }
    }
    return array_values($found);
}

function installerAdditionalChecks() {
    if (installerConfigured()) { return []; }
    $checks = [];
    $python = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    $add = function ($group, $label, $status, $detail, $help = '', $command = '') use (&$checks) {
        $checks[] = compact('group', 'label', 'status', 'detail', 'help', 'command');
    };
    $extensionHelp = 'Enable the extension for the PHP version serving this page, restart PHP, and reload setup.';
    foreach (['xml', 'intl', 'sqlite3', 'pdo_mysql'] as $extension) {
        $loaded = extension_loaded($extension);
        $add('Recommended PHP configuration', 'PHP ' . $extension, $loaded ? 'passed' : 'failed',
            $loaded ? 'Extension loaded by the web PHP runtime.' : 'Extension is not loaded by this PHP runtime.', $extensionHelp);
    }
    foreach (['upload_max_filesize' => '100M', 'post_max_size' => '100M', 'memory_limit' => '512M', 'max_execution_time' => '7200 seconds'] as $setting => $recommended) {
        $value = ini_get($setting);
        $add('Recommended PHP configuration', $setting, installerChecklistLimit($setting, $value),
            'Current: ' . ($value === false ? 'unavailable' : $value) . '. Recommended: ' . $recommended . '.',
            'Edit the web PHP configuration and restart PHP. The Ubuntu example uses 8G upload/post limits, 512M memory and 7200 seconds; adapt these to your workload. Unlimited memory (-1), POST (0) and execution time (0) are accepted.');
    }
    $upload = installerChecklistBytes(ini_get('upload_max_filesize'));
    $post = installerChecklistBytes(ini_get('post_max_size'));
    $consistent = $upload === null || $post === null ? 'unknown' : (($post === 0.0 || $post >= $upload || $post >= 2 * 1024 * 1024 * 1024) ? 'passed' : 'failed');
    $add('Recommended PHP configuration', 'POST capacity for file uploads', $consistent,
        'post_max_size: ' . ini_get('post_max_size') . '; upload_max_filesize: ' . ini_get('upload_max_filesize') . '.',
        'For this readiness check, post_max_size of 2G or more is sufficient. Lower values should be at least upload_max_filesize. Equal limits and unlimited POST size (0) are accepted.');
    $add('Recommended PHP configuration', 'PHP file uploads', filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN) ? 'passed' : 'failed',
        'file_uploads: ' . (ini_get('file_uploads') ?: 'Off'), 'Enable file_uploads in the web PHP configuration to accept uploads.');

    $modules = function_exists('apache_get_modules') ? array_map('strtolower', apache_get_modules()) : null;
    $nginx = $modules === null && stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false;
    foreach (['rewrite', 'expires', 'headers', 'xsendfile'] as $module) {
        $status = $modules === null ? ($nginx ? 'na' : 'unknown') : (in_array('mod_' . $module, $modules, true) ? 'passed' : 'failed');
        $add('Web server', 'Apache mod_' . $module, $status,
            $modules === null ? ($nginx ? 'This request is served by Nginx; Apache modules do not apply here.' : 'Apache modules cannot be inspected from this PHP runtime.') : ($status === 'passed' ? 'Module loaded.' : 'Module not loaded.'),
            'For Apache, enable the module and reload the service. On Ubuntu, xsendfile also needs libapache2-mod-xsendfile. With PHP-FPM, verify modules on the web server itself.');
    }
    $add('Web server', 'Routing and media delivery', 'unknown',
        'Module presence alone does not verify URL rewriting or X-Sendfile delivery.',
        'For Apache, review AllowOverride and the AVideo .htaccess rules. For Nginx, configure equivalent routing. After installation, open a video URL and test playback/downloads.');
    $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    $add('Web server', 'HTTPS on this request', $https ? 'passed' : 'unknown',
        $https ? 'PHP reports HTTPS for this request; certificate validity is not checked here.' : 'PHP does not report HTTPS. TLS may terminate at a reverse proxy.',
        'Verify the final public site URL in a browser, including deployments behind a reverse proxy.');
    $add('Web server', 'Certificate validity and renewal', 'unknown',
        'The public certificate and renewal schedule require a deployment check.',
        'Verify hostname, expiration and certificate chain on the public URL. If using Certbot, check its renewal timer and run a renewal dry run on the TLS server.',
        PHP_OS_FAMILY === 'Windows' ? '' : 'sudo certbot certificates' . "\n" . 'sudo certbot renew --dry-run');

    $temporary = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
    foreach (['PHP upload temporary directory' => $temporary,
        'PHP system temporary directory' => sys_get_temp_dir(),
        'HTMLPurifier serializer cache' => installerRoot() . 'vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer'] as $label => $path) {
        $status = @is_dir($path) && @is_writable($path) ? 'passed' : 'failed';
        $add('Directories', $label, $status, 'Path: ' . $path,
            'Ensure this directory exists and grants write access to the PHP service account. This checks permissions; no files are created.');
    }
    $add('Directories', 'Live temporary directory (/var/www/tmp)', PHP_OS_FAMILY === 'Windows' ? 'na' : (@is_dir('/var/www/tmp') && @is_writable('/var/www/tmp') ? 'passed' : 'unknown'),
        PHP_OS_FAMILY === 'Windows' ? 'The Ubuntu path does not apply on Windows.' : 'Checks the default local directory used by the Ubuntu Live setup.',
        'If using Live/restreaming on this server, verify its actual temporary directory and write access. A remote Live server must be checked separately.');

    foreach (['exec', 'shell_exec', 'proc_open'] as $function) {
        $add('Local media tools', 'PHP ' . $function, function_exists($function) ? 'passed' : 'failed',
            function_exists($function) ? 'Function is available to this PHP runtime.' : 'Function is unavailable to this PHP runtime.',
            'Media tools need process execution on the server where they run. Review disabled functions with your hosting provider if local processing is required.');
    }
    foreach (['FFmpeg' => ['ffmpeg'], 'FFprobe' => ['ffprobe'], 'ImageMagick' => PHP_OS_FAMILY === 'Windows' ? ['magick'] : ['magick', 'convert'],
        'ExifTool' => ['exiftool'], 'unzip' => ['unzip'], 'Python' => ['python3', 'python'], 'pip' => ['pip3', 'pip'],
        'yt-dlp' => ['yt-dlp'], 'Nginx' => ['nginx'], 'Certbot' => ['certbot']] as $label => $names) {
        $paths = installerChecklistPrograms($names);
        if ($label === 'Nginx' && !$paths && PHP_OS_FAMILY !== 'Windows' && @is_executable('/usr/local/nginx/sbin/nginx') && @is_file('/usr/local/nginx/sbin/nginx')) {
            $paths[] = '/usr/local/nginx/sbin/nginx';
        }
        $add('Local media tools', $label . ' executable', $paths ? 'passed' : 'unknown',
            $paths ? 'Executable found: ' . implode(', ', $paths) : 'Not detected in the PHP service PATH; installation elsewhere is not ruled out.',
            'This checks executable presence and permissions, not its version or successful operation. Verify it under the account that runs the relevant service. Encoder and Live tools may be on another server.');
        if ($label === 'yt-dlp') {
            $add('Encoder, Live and optional features', 'yt-dlp executable selection', count($paths) === 1 ? 'passed' : 'unknown',
                count($paths) . ' distinct executable(s) found in the PHP service PATH.',
                'Verify which executable the Encoder uses. If multiple copies exist, align its configured path with the pip-managed installation. No binaries are removed by setup.');
        }
    }
    $add('Encoder, Live and optional features', 'Encoder connection and processing', 'unknown',
        'A local checkout does not prove the Encoder is configured or reachable.',
        'After installing the Streamer, configure the Encoder URL and test an upload through conversion and return to this site. Check the remote Encoder environment if it runs elsewhere.');
    $add('Encoder, Live and optional features', 'yt-dlp / curl_cffi impersonation', 'unknown',
        'Python package compatibility and impersonation support need a runtime test on the Encoder.',
        'Run these with the same Python environment and account used by the Encoder:',
        "yt-dlp --version\nyt-dlp --list-impersonate-targets\n" . $python . ' -m pip show yt-dlp curl_cffi');
    $add('Encoder, Live and optional features', 'Nginx RTMP / Live service', 'unknown',
        'An installed Nginx binary does not confirm RTMP support or a running Live service.',
        'On the Live server, inspect build modules and configuration, then publish and play a test stream. The Ubuntu source-build example installs Nginx under /usr/local/nginx.',
        PHP_OS_FAMILY === 'Windows' ? '' : "/usr/local/nginx/sbin/nginx -V\nsudo /usr/local/nginx/sbin/nginx -t");
    $locationFile = installerRoot() . 'plugin/User_Location/install/install.sql';
    $add('Encoder, Live and optional features', 'User_Location installation data', @is_readable($locationFile) && @filesize($locationFile) > 0 ? 'passed' : 'failed',
        'Checks for the extracted plugin/User_Location/install/install.sql file.',
        'If using this plugin, extract its install.zip and install its IP tables through the plugin setup. File presence does not confirm table import or location lookup.');
    $add('Encoder, Live and optional features', 'User_Location tables and lookup', 'unknown',
        'Plugin tables and IP lookup are not checked before the site database is installed.',
        'After setup, enable User_Location, follow its installation instructions, and test IPv4 and IPv6 lookup if required.');
    $add('Encoder, Live and optional features', 'Vosk speech recognition', 'unknown',
        'Python package, model files and transcription need verification on the processing server.',
        'If using speech recognition, verify Vosk in the service Python environment and run a transcription with the configured model.', $python . ' -m pip show vosk');
    $add('Encoder, Live and optional features', 'Scheduled tasks and yt-dlp updates', 'unknown',
        'The service and root schedules are not inspected by this web page.',
        'Review the Scheduler/Live service tasks and the yt-dlp update schedule on the relevant server. Check recent successful runs and logs; keep unrelated existing jobs.',
        PHP_OS_FAMILY === 'Windows' ? '' : "sudo crontab -u www-data -l\nsudo crontab -u root -l");
    return $checks;
}
