# Guided installation

Open `/install/` before configuring the application. Setup checks its requirements, tests the database connection without writing data, and provides English error messages with repair commands. Expand **Ubuntu setup help and troubleshooting commands** for PHP packages, service checks, file permissions, routing, and product-specific dependencies. The examples target Ubuntu 22.04 and 24.04; use extension packages matching the PHP version serving the site.

Run Composer/npm as the application owner. Run CLI installation as the account that will run PHP, so that the generated configuration remains readable by that account. Installers do not change operating-system packages or permissions automatically.

Use a new database for a new installation. A failed schema import can leave empty tables because MySQL DDL is not transactional. Initial application records are transactional. Correct the reported cause and retry; setup does not delete existing application data. If database initialization succeeds but configuration publication fails, follow the recovery command and preserve the prepared file instead of reinstalling.

After completion, setup hides the form, requirements, paths, and troubleshooting details. Reopening setup does not execute the application configuration or reveal database credentials. Recover existing installations from their configuration backup; do not reinstall over populated tables.

## Shared presentation

Keep these files byte-identical across Streamer, Encoder and Encoder Network:

- `installer.css` and `installer.js`
- `ubuntu-help.php` and `ubuntu-help-functions.php`
- `assets/logo.png` and `assets/favicon.png`

The images are copies of the Streamer `view/img/logo.png` and `view/img/favicon.png`. Each repository carries its own copies so installations do not depend on a neighboring checkout or a CDN.

## Verification

Use an isolated MySQL/MariaDB server and a test account with CREATE/DROP privileges. The integration scripts create and remove only their temporary databases and files; they never read the real application configuration. Supply a password through `MYSQL_PWD` when needed. PHP must have the required extensions enabled.

```sh
python3 tests/installer_integration.py --php php --mysql mysql --port 3306
```

From the Streamer checkout, compare all three installers and check their locked pages without a database:

```sh
python3 tests/installers_consistency.py --php php --encoder /path/to/AVideo-Encoder --network /path/to/AVideo-Encoder-Network
```

HTTP/database tests use a local mock Streamer for remote administrator verification. Test a real Streamer connection and a complete encoding/upload workflow in your deployment as well.

## Streamer CLI

`cli.php` reads the deployment environment. Set `SYSTEM_ADMIN_PASSWORD` explicitly for unattended setup. If omitted during a new installation, the generated password is saved in `videos/.initial_admin_password.php` with restricted permissions; it is not written to logs. Existing installations do not regenerate it. The optional bundled Encoder runs in a separate PHP process. `ENCODER_DB_MYSQL_HOST` and `ENCODER_DB_MYSQL_PORT` can override its database connection.
