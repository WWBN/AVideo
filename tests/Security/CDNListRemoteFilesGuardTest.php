<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * plugin/CDN/tools/listRemoteFiles.php is a web tool that lists the CDN storage bucket once an admin sets
 * $basePath; it must refuse non-admins before reading the storage credentials (GHSA-cjqm-w3wj-398w).
 */
class CDNListRemoteFilesGuardTest extends TestCase
{
    public function testAdminCheckRunsBeforeTheStorageCredentialsAreRead()
    {
        $source = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/plugin/CDN/tools/listRemoteFiles.php'));

        $guard = strpos($source, "if (!User::isAdmin()) {\n    forbiddenPage(");
        $this->assertNotFalse($guard, 'listRemoteFiles.php must call forbiddenPage() for non-admins');
        $this->assertLessThan(strpos($source, "getDataObjectIfEnabled('CDN')"), $guard);
        $this->assertLessThan(strpos($source, 'curl_exec('), $guard);
    }
}
