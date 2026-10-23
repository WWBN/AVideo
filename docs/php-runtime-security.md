# PHP runtime security notice (2026-10-21)

AVideo now displays a persistent, nonblocking notice to authenticated administrators when the web process does not meet the version criteria for CVE-2026-109618 and CVE-2026-109619. It appears in the shared navigation template and in **Admin Panel → Health Check** (also available from Configuration). The health check also shows a limited positive result when the criteria are met. Visitors and ordinary users receive no runtime information from this feature.

## Official findings and scope

The [PHP security notice of 21 October 2026](https://www.php.net/archive/2026.php#2026-10-21-5) announces these inclusive minimum fixed releases:

| Branch | Minimum fixed stable release |
| --- | --- |
| PHP 8.2 | 8.2.35 |
| PHP 8.3 | 8.3.36 |
| PHP 8.4 | 8.4.27 |
| PHP 8.5 | 8.5.12 |

These are upstream PHP runtime vulnerabilities, not an AVideo application-code vulnerability fixed by this update. Both official advisories classify the impact as Critical (CVSS v4.0 9.2):

- [CVE-2026-109618 / GHSA-w8hw-w9fx-j79w](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w): interrupted allocation can leave request memory invalid and permit unauthenticated remote code execution. The demonstrated mod_php startup path precedes application code. The advisory also identifies FPM susceptibility; an application-code search cannot establish immunity.
- [CVE-2026-109619 / GHSA-6wmf-4rv5-p3rp](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp): request-owned state can outlive its memory and be consumed by another request in the same worker. Session lifecycle exploitation is validated for both mod_php and FPM. Fatal errors, timeouts, upload processing and initialization diagnostics can be relevant; exploitation depends on the build and reachable state transitions.

No exploit, allocation exhaustion, upload flood or production-server probe is part of this feature or its tests. Existing input filtering in `objects/security.php`, authentication, CSRF protection and application error handling cannot repair PHP before application execution. No local exploit chain or whole-installation safety has been established by this change.

For example, an administrator using Brazilian Portuguese with PHP 8.2.34 / FPM sees these core details, followed by the scope explanation and official links:

> **Segurança do runtime PHP — CVE-2026-109618 / CVE-2026-109619**
>
> Potencialmente afetado; confirme a correção com sua hospedagem.
>
> Processo web observado: PHP 8.2.34; SAPI fpm-fcgi.
>
> Versão mínima corrigida para este ramo: 8.2.35.
>
> Atualizar somente o AVideo não corrige o PHP. Atualize o runtime web ou confirme com sua hospedagem um backport para as duas CVEs.

## Interpretation

`objects/PHPRuntimeSecurity.php` reads **PHP_VERSION, PHP_VERSION_ID and PHP_SAPI in the process handling the web request**. It cross-checks the numeric ID against the version components and uses `version_compare()` within the detected branch. `php -v` describes the CLI and is not a substitute for this observation. FPM is never exempted.

Debian and Ubuntu backport fixes without changing PHP_VERSION (Ubuntu 24.04 keeps reporting 8.3.6, Ubuntu 26.04 8.5.4). When the version criterion is not met and the SAPI is mod_php, FPM or CGI, the same web process also reads two local files: its own `/proc/self/maps`, which must contain the package's engine file (`/usr/lib/apache2/modules/libphpX.Y.so`, `/usr/sbin/php-fpmX.Y`, `/usr/lib/cgi-bin/phpX.Y` or `/usr/bin/php-cgiX.Y`), and `/usr/share/doc/phpX.Y-common/changelog.Debian.gz`, whose newest entry must be the reported version and which must list both CVE IDs on no line that also says revert, disable, drop or remove. If open_basedir is set, a file is missing or unreadable, or anything does not match, the result is unchanged.

| Observation | Result |
| --- | --- |
| Known web SAPI, plain stable release in 8.2–8.5, at or above that branch's minimum | Meets version criteria for these two CVEs only |
| Known web SAPI, base release below its minimum | Potentially affected; confirm the fix with the hosting provider |
| PHP 8.1 or older | Potentially affected; upstream no longer maintains these branches; obtain vendor evidence or upgrade |
| RC, alpha, beta, dev, snapshot, malformed/inconsistent detection, unknown or CLI SAPI | Unknown; verify the actual web runtime |
| Future/unmapped branch, including PHP 8.6 or 9.x | Unknown; review newer upstream information before updating the mapping |
| Distribution/custom suffix below the minimum | Potentially affected; a suffix does not prove a backport |
| Distribution/custom suffix at or above the minimum | Unknown; conservatively require package-specific vendor evidence |
| Any of the three rows above (below the minimum, vendor suffix, 8.1 or older) on a Debian/Ubuntu package whose engine file this process runs and whose changelog lists both CVEs | Fixed by the installed distribution package: no notice on ordinary pages; Health Check shows it with the package version |
| Same, but the package replaced the engine file after the process started (`(deleted)` in `/proc/self/maps`) | Potentially affected; the notice asks to restart Apache or PHP-FPM |

A later plain stable patch in a mapped branch meets the version criterion; this is not verification of its binary or package contents. No distribution name or version suffix is used to infer a fix; the only package evidence read automatically is the Debian/Ubuntu changelog described above. Other vendors (for example RHEL/Remi, cPanel EasyApache, Plesk or custom builds) may have backported both fixes without changing the base version: ask for evidence identifying the exact installed package/build and coverage of **both** CVEs. This implementation has no dismissal or manual-confirmation setting, so it cannot silently turn a vendor claim into automatic verification. Keep any administrator-declared confirmation in your operational records, bound to the installation, node/container, package/build, observed version/SAPI, date and vendor evidence; recheck it after environment changes.

The warning disappears on ordinary admin pages after a plain stable version meets these two CVEs' criteria. That says nothing about general system security. **Updating only AVideo does not update or fix PHP.** Temporary configuration mitigations are not treated as a completed fix.

## Existing architecture and boundaries

- Authorization reuses `User::isLogged()` and `User::isAdmin()`. Rendering is confined to an HTML include, with output escaping and a non-HTML Content-Type guard. No request/bootstrap hook, JSON response field, webhook contract or media path is changed. The notice uses Bootstrap alert classes, a heading, a `status` landmark, readable text and ordinary links, without a modal or a dismiss button.
- The same include is used once per request by `view/include/navbar.php` and `admin/health_check.php`. `__()` and the existing locale catalogs provide translations. The optional Notifications/UserNotifications delivery mechanisms target content events and are not required; this feature sends no email, push notification or telemetry.
- Detection is inexpensive and recomputed locally; there is no session, database, file, Memcached or cross-node result cache. The two local files above are read only on Debian-style SAPIs whose version criterion fails (the Ubuntu changelog is about 50 KB uncompressed; at most 4 MB is read). The Cache plugin skips full-page cache reads/writes for authenticated administrators, including previously cached admin HTML. Admin HTML requests using this include send `Cache-Control: private, no-store, max-age=0` when headers are still writable. Configure any external cache to honor that header and exclude authenticated admin traffic.
- One request observes one process. A load balancer may send the next request to a different worker/node. A passing result does not inventory every worker, container, other site or separate Encoder. Check each directly through authorized administration routes; do not expose a public diagnostics endpoint.
- Relevant existing flows inspected include multipart uploads (`objects/aVideoQueueEncoder.json.php`, Encoder receive/chunk callbacks), optional spreadsheet imports (`plugin/Flixhouse/import_spreadsheet_videos.json.php`), runtime limit helpers (`objects/functionsExec.php`, `objects/functionsImages.php`) and shutdown handling (`objects/functionsAVideo.php`). These are context, not proof of exploitation or safety; they are unchanged. Runtime inspection must also cover deployed extensions, per-request configuration, startup diagnostics, fatal errors and timeouts. Absence of observed errors is not proof of a fix.
- Composer still requires PHP `^8.1`; no minimum requirement or dependency is changed. Core syntax remains compatible with the repository's supported baseline. The Dockerfile installs distribution PHP and mod_php on Ubuntu; Compose bind-mounts the application and a separate Encoder. Neither rebuilding an image nor changing mounted application code alone proves that PHP contains the fixes.

## Distributing and validating the update

1. Deliver the complete application change, including the helper, shared view, Cache plugin change and locale entries, using each installation's normal reviewed AVideo update process. Existing sites still need the usual backups and plugin compatibility checks. **Installations that do not receive the new code will not display this warning.** A separately deployed frontend that never renders AVideo's navigation must use the authenticated native Health Check to see it.
2. Sign in as an administrator and view a normal page and Health Check on each installation/node. Check that a private window and a non-admin account receive no notice or version data from this feature. Use a real web request, not the CLI version.
3. Arrange compatible PHP package updates, or confirm package-specific backports with the hosting provider. Independently deployed Encoders and other PHP sites require their own runtime checks. If containers are used, preserve volumes/configuration and local images, review the package evidence in the replacement image, and deploy through the normal maintenance process. This application update does not pull/build images, install packages, change php.ini or limits/sessions, restart services, or scan other servers.
   - **Bundled Docker Compose stack.** `docker compose up -d --build` reuses the cached `apt-get install` layers, so an image built before the fix keeps the old PHP even after `git pull`. Both `Dockerfile` and `Dockerfile.live` use Ubuntu 26.04, explicitly install PHP 8.5 packages and reject a package older than Ubuntu's fixed `8.5.4-0ubuntu1.5`. Rebuild the two PHP images without the cache, then recreate only those services: `docker compose build --pull --no-cache avideo live` and `docker compose up -d --no-deps --no-build avideo live`. Use the deployment's existing Compose files and environment. Do not add `--pull` for the database services: `Dockerfile.mariadb` uses `mariadb:latest` and could jump to a new major version. Ubuntu fixed both CVEs in `8.5.4-0ubuntu1.5` (26.04) and `8.3.6-0ubuntu0.24.04.13` (24.04, older images); check the installed package with `docker compose exec avideo sh -c "dpkg -l 'php8.*-common' | grep ^ii"` (and the same for `live`). `PHP_VERSION` keeps Ubuntu's base version (8.5.4 / 8.3.6); after the rebuild the notice reads the package changelog, so ordinary pages show nothing and Health Check shows "Fixed by the installed distribution package". The standalone Encoder image (`.compose/encoder/Dockerfile`, `php:8.2-apache`) uses the upstream release and is rebuilt the same way from its own folder.
4. After the operator's runtime update, ensure all workers/nodes actually use the intended package (including the operator-managed worker restart/replacement), then revisit the web Health Check. Verify normal login, playback, uploads/imports and Encoder callbacks in a suitable test environment. Avoid stress tests or exploit traffic.

## Automated validation and limits

Run `php vendor/phpunit/phpunit/phpunit --do-not-cache-result tests/Security/PHPRuntimeSecurityTest.php`. It covers each branch's preceding/fixed/following release, old/future/prerelease/vendor builds, invalid detection, web SAPIs, authorization, escaping/translations, backport uncertainty, the Debian/Ubuntu changelog evidence (accepted cases, every incomplete or mismatched case, the restart case and the local file readers), repeated observations, and the actual Cache plugin's admin skip path. An isolated temporary PHP server bound to loopback verifies the real `cli-server` SAPI, admin cache headers and unchanged public/JSON/media/webhook sample responses. It does not load installation secrets or connect to a production database.

These tests verify the detector and template boundary, not authenticated end-to-end execution of every installed plugin or the site's actual mod_php/FPM deployment. The repository's PHP 8.1/8.2/8.3 CI matrix remains unchanged. Each operator must validate the installed package/backport, worker rollout, external caches, theme/layout and normal application integrations on their own deployment.
