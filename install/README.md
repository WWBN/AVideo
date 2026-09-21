# Guided installation

Open `/install/` before configuring the application. Setup checks its requirements, tests the database connection without writing data, and provides English error messages with repair commands. **Ubuntu setup help and troubleshooting commands** appears only when a checked PHP extension, Composer/frontend dependency, or encoding tool is missing. Permission, schema, and PHP version failures alone do not display this section. The examples target Ubuntu 22.04, 24.04, and 26.04 with Apache and mod_php; use extension packages matching the PHP version serving the site.

Run Composer/npm as the application owner. Run CLI installation as the account that will run PHP, so that the generated configuration remains readable by that account. Installers do not change operating-system packages or permissions automatically.

Use a new database for a new installation. A failed schema import can leave empty tables because MySQL DDL is not transactional. Initial application records are transactional. Correct the reported cause and retry; setup does not delete existing application data. If database initialization succeeds but configuration publication fails, follow the recovery command and preserve the prepared file instead of reinstalling.

After completion, setup hides the form, requirements, paths, and troubleshooting details. Reopening setup does not execute the application configuration or reveal database credentials. Recover existing installations from their configuration backup; do not reinstall over populated tables.

## Shared presentation

The Streamer installation checklist distinguishes **passed**, **needs attention**, **not verified**, and **not applicable**, with totals per group and repair/verification guidance. Only the original installation requirements block setup; the additional checks are advisory and do not change CLI or database installation behavior.

Additional groups cover PHP extensions and effective upload/memory/time limits, Apache modules, HTTPS, temporary/cache directory permissions, local tool discovery, Encoder, Live, Python dependencies, User_Location and scheduled tasks. The upload recommendation remains 100M; the Ubuntu example's 8G upload/POST values are a deployment choice. Memory and execution recommendations are 512M and 7200 seconds. Unlimited memory (-1), POST size (0) and execution time (0) are handled separately. The POST readiness check accepts equal limits (including 8G/8G), unlimited POST size (0), or a POST limit of at least 2G even when the upload limit is higher. Below 2G, the POST limit should be at least the upload limit.

Tool discovery inspects the PHP service PATH and executable permissions, without running tools. On Windows, ImageMagick discovery uses `magick` rather than the unrelated system `convert.exe`. Nginx also checks the Ubuntu source-build location. A detected binary does not prove its version, module support or successful operation. Remote Encoder/Live services, Python environments, certificate validity/renewal, routing, plugin data import and cron execution remain **not verified**, with manual checks shown beside them. Database access still requires **Test connection**.

Reload after correcting requirements. `checklist-functions.php` provides standalone diagnostics without bootstrapping the application; `checklist.css` contains the Streamer-specific layout. All diagnostics are hidden once configuration exists.

Keep these files byte-identical across Streamer, Encoder and Encoder Network:

- `installer.css` and `installer.js`
- `ubuntu-help.php` and `ubuntu-help-functions.php`
- `assets/logo.png` and `assets/favicon.png`

The images are copies of the Streamer `view/img/logo.png` and `view/img/favicon.png`. Each repository carries its own copies so installations do not depend on a neighboring checkout or a CDN.

## Verification

Run the checklist tests with the existing PHPUnit suite. They use temporary fixtures and do not need a database, Python or browser automation:

```sh
php vendor/bin/phpunit tests/Unit/InstallerChecklistTest.php
```

Use an isolated MySQL/MariaDB server and a test account with CREATE/DROP and TRIGGER privileges. PHPUnit creates and removes only generated temporary databases and files; it never reads the real application configuration. Enable integration tests with `AVIDEO_INSTALLER_MYSQL_TESTS=1`. Connection settings are `AVIDEO_TEST_DB_HOST` (default `127.0.0.1`), `AVIDEO_TEST_DB_PORT` (`3306`), `AVIDEO_TEST_DB_USER` (`root`) and `AVIDEO_TEST_DB_PASSWORD` (empty). Set these in the test environment; do not put passwords in command-line arguments.

PHP must have the required installer extensions enabled. If child PHP processes need additional CLI options, set `AVIDEO_TEST_PHP_ARGS` to a JSON string array, for example `["-d","extension=gd","-d","extension=zip"]` for an XAMPP setup where those extensions are installed but disabled.

```sh
php vendor/bin/phpunit tests/Integration/InstallerIntegrationTest.php
```

To compare all three installers and check their locked pages and CLI isolation without a database, set `AVIDEO_TEST_ENCODER_ROOT` and `AVIDEO_TEST_NETWORK_ROOT` to the corresponding repository paths:

```sh
php vendor/bin/phpunit tests/Integration/InstallersConsistencyTest.php
```

These integration suites are invoked explicitly and skip external-service cases when their environment settings are absent. They use PHP, PHPUnit, MySQLi and cURL; Python, Playwright and the MySQL CLI are not required. HTTP tests run a temporary PHP server on localhost and exercise the real installer endpoint.

Browser validation remains manual: check desktop/mobile layout, test the database connection, submit mismatched passwords, and confirm successful installation removes the form and checklist. Test a real Encoder connection and a complete encoding/upload workflow in your deployment as well.

## Streamer CLI

`cli.php` reads the deployment environment. Set `SYSTEM_ADMIN_PASSWORD` explicitly for unattended setup. If omitted during a new installation, the generated password is saved in `videos/.initial_admin_password.php` with restricted permissions; it is not written to logs. Existing installations do not regenerate it. The optional bundled Encoder runs in a separate PHP process. `ENCODER_DB_MYSQL_HOST` and `ENCODER_DB_MYSQL_PORT` can override its database connection.
