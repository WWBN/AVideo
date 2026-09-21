<?php
// Shared setup diagnostics. Keep this file identical in the three installers.
function installerHasMissingDependencies(array $checks) {
    $dependencies = ['MySQLi', 'cURL', 'Composer dependencies', 'Frontend assets', 'FFmpeg', 'FFprobe',
        'PHP mysqli', 'PHP curl', 'PHP gd', 'PHP mbstring', 'PHP zip', 'PHP zlib', 'PHP openssl',
        'PHP xml', 'PHP intl', 'PHP sqlite3'];
    foreach ($checks as $check) {
        if (!$check['ok'] && in_array($check['label'], $dependencies, true)) { return true; }
    }
    return false;
}
function installerUbuntuHelp() {
    $encoder = INSTALLER_PRODUCT === 'Encoder';
    $streamer = INSTALLER_PRODUCT === 'Streamer';
    $folder = $streamer ? 'AVideo' : ($encoder ? 'AVideo-Encoder' : 'AVideo-Encoder-Network');
    $path = PHP_OS_FAMILY === 'Windows' ? '/var/www/' . $folder : rtrim(installerRoot(), '/');
    $quote = "'" . str_replace("'", "'\"'\"'", $path) . "'";
    $packages = 'apache2 php libapache2-mod-php php-cli php-common php-mysql php-curl';
    if ($streamer || $encoder) { $packages .= ' php-sqlite3 php-gd php-intl php-zip php-mbstring php-xml'; }
    $sections = [
        ['title' => 'PHP and required extensions', 'text' => 'Ubuntu 22.04 / 24.04 / 26.04 with Apache and mod_php: these packages use the distribution default PHP version. If Apache uses another installed PHP version, install matching versioned packages. Restart Apache, then reload setup. CLI and web PHP can load different versions and ini files.',
            'command' => "sudo apt-get update\nsudo apt-get install " . $packages . "\nphp -v\nphp -m\nsudo systemctl restart apache2"],
        ['title' => 'Database service and connection errors', 'text' => 'Use the existing database server if you already have one. Install MariaDB only when you need a new local database server. For remote databases, check the host, port, firewall, and account permissions. The SQL client prompts for the password; do not put it in the command.',
            'command' => "# Only if a local database server is missing:\nsudo apt-get install mariadb-server mariadb-client\nsudo systemctl status mariadb\n# If you use MySQL instead:\n# sudo systemctl status mysql\nmysql --host=DATABASE_HOST --port=3306 --user=DATABASE_USER --password"],
        ['title' => 'Write permissions', 'text' => 'Run from your application directory. Replace www-data if PHP runs under another account. Grant access only to the setup destination; do not use chmod 777.',
            'command' => 'cd ' . $quote . "\nsudo apt-get install acl\n" . (($streamer || $encoder) ? "sudo mkdir -p videos\nsudo setfacl -m u:www-data:rwx videos" : 'sudo setfacl -m u:www-data:rwx .')],
        ['title' => 'Apache routing and PHP errors', 'text' => 'For Apache, enable rewrite and allow the application .htaccess rules in its virtual host. Inspect the Apache error log for PHP errors. CLI and web PHP can load different ini files.',
            'command' => "sudo a2enmod rewrite\nsudo apache2ctl configtest\nsudo systemctl reload apache2\nsudo tail -n 80 /var/log/apache2/error.log\nphp --ini"],
    ];
    if ($streamer) {
        $sections[] = ['title' => 'Composer and frontend dependencies', 'text' => 'Run package installation as the application owner, in the application directory. Restore missing source files from your checkout. Use the PHP version selected for this site.',
            'command' => 'cd ' . $quote . "\nsudo apt-get install composer nodejs npm\ncomposer install --no-dev --prefer-dist\nnpm install"];
    }
    if ($encoder) {
        $sections[] = ['title' => 'Composer and frontend dependencies', 'text' => 'Install the Encoder frontend packages as the application owner, from the Encoder directory.', 'command' => 'cd ' . $quote . "\nsudo apt-get install nodejs npm\nnpm install"];
        $sections[] = ['title' => 'Encoding tools', 'text' => 'FFmpeg and FFprobe must be available to the PHP service account. Enable PHP exec and proc_open for encoding workers in the web PHP configuration if your hosting provider disabled them.',
            'command' => "sudo apt-get install ffmpeg python3 python3-pip unzip\nsudo -u www-data ffmpeg -version\nsudo -u www-data ffprobe -version"];
    }
    if (!$streamer) {
        $sections[] = ['title' => 'Streamer connection and TLS certificates', 'text' => 'Use the final Streamer URL without redirects. Fix certificate problems rather than disabling verification. Replace STREAMER_URL with its public address.',
            'command' => "sudo apt-get install ca-certificates curl\nsudo update-ca-certificates\ncurl --head 'https://STREAMER_URL/login'"];
    }
    return $sections;
}
function installerRequirementHelp($check) {
    if ($check['label'] === 'Configuration write access') { return installerPermissionHelp(); }
    if ($check['label'] === 'Database schema') { return ['title' => 'Restore the schema', 'text' => 'Restore install/database.sql from the same release of this application, then reload setup.']; }
    $sections = installerUbuntuHelp();
    $section = $sections[0];
    foreach ($sections as $candidate) {
        if ((in_array($check['label'], ['Composer dependencies', 'Frontend assets'], true) && $candidate['title'] === 'Composer and frontend dependencies') || (in_array($check['label'], ['FFmpeg', 'FFprobe', 'PHP exec', 'PHP proc_open'], true) && $candidate['title'] === 'Encoding tools')) { $section = $candidate; }
    }
    return ['title' => 'Resolve the missing requirement', 'text' => $check['detail'] . ' Review the commands below, then reload setup.', 'command' => $section['command']];
}
