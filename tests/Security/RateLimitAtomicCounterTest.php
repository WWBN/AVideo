<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * Source-scanning regression tests (no live DB in this suite's harness) for
 * the atomic rate_limits counter in objects/functionsSecurity.php.
 */
class RateLimitAtomicCounterTest extends TestCase
{
    private function functionsSecuritySource(): string
    {
        return file_get_contents(dirname(__DIR__, 2) . '/objects/functionsSecurity.php');
    }

    /**
     * @test
     */
    public function testRateLimitIncrementUsesAtomicUpsertNotReadThenWrite()
    {
        $source = $this->functionsSecuritySource();
        $fnStart = strpos($source, 'function rateLimitIncrementAndGet');
        $this->assertNotFalse($fnStart, 'rateLimitIncrementAndGet() must exist');
        $fnBody = substr($source, $fnStart, 1600);

        $this->assertStringContainsString('INSERT INTO rate_limits', $fnBody);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $fnBody);
        $this->assertStringContainsString('LAST_INSERT_ID(', $fnBody);
        $this->assertStringContainsString("hash('sha256'", $fnBody);
    }

    /**
     * @test
     */
    public function testRateLimitFallsBackToLegacyCacheWhenDbWriteFails()
    {
        $source = $this->functionsSecuritySource();
        $fnStart = strpos($source, 'function rateLimitIncrementAndGet');
        $fnBody = substr($source, $fnStart, 2200);

        $this->assertStringContainsString('is_numeric($result)', $fnBody);
        $this->assertStringContainsString('getCacheGlobal($key', $fnBody);
        $this->assertStringContainsString('setCacheGlobal($key', $fnBody);
    }

    /**
     * Cleanup must stay a cheap probabilistic prefilter (no cache I/O) gating
     * an atomic DB lease - not run unconditionally on every request.
     *
     * @test
     */
    public function testCleanupUsesProbabilisticPrefilterWrappingAnAtomicDbLease()
    {
        $source = $this->functionsSecuritySource();
        $fnStart = strpos($source, 'function rateLimitIncrementAndGet');
        $fnBody = substr($source, $fnStart, 4000);

        $this->assertStringContainsString('mt_rand(', $fnBody);
        $this->assertStringContainsString('cleanup_lease', $fnBody);
        $this->assertStringContainsString('DELETE FROM rate_limits', $fnBody);

        // DELETE must be reachable only through the atomic lease branch, not
        // as soon as the probabilistic prefilter hits.
        $leaseCheckPos = strpos($fnBody, "writeSql(\$leaseSql, 's', [\$leaseKey]) === 1");
        $deletePos = strpos($fnBody, 'DELETE FROM rate_limits');
        $this->assertNotFalse($leaseCheckPos, 'The lease result must be strictly compared (=== 1) before the DELETE runs');
        $this->assertTrue($leaseCheckPos < $deletePos, 'DELETE must be gated by the atomic lease check, not the prefilter alone');
    }

    /**
     * Regression guard for two real bugs this file's history already hit:
     * (1) 'login.json.php' was added to autoRateLimitGuard()'s exact-basename
     * bypass list, but the basename is shared by 4 files (core + encoder +
     * LoginWordPress + LoginLDAP) and only the core one self-limits, so the
     * other 3 silently lost sitewide rate limiting; (2) a later full-path fix
     * used a path SUFFIX match, which the encoder copy also satisfies despite
     * not self-limiting. The current design must resolve the full, real path
     * (systemRootPath + realpath), not a basename or suffix. This only checks
     * the source pattern - it can't assert against .compose/ (gitignored,
     * absent from a clean checkout).
     *
     * @test
     */
    public function testPathScopedBypassResolvesFullPathNotSuffix()
    {
        $root = dirname(__DIR__, 2);
        $source = file_get_contents($root . '/objects/functionsSecurity.php');

        $fnStart = strpos($source, 'function autoRateLimitGuard');
        $this->assertNotFalse($fnStart);
        $builtinBypassPos = strpos($source, 'static $builtinBypass', $fnStart);
        $bypassSection = substr($source, $fnStart, $builtinBypassPos - $fnStart);

        $this->assertStringContainsString('systemRootPath', $bypassSection);
        $this->assertStringContainsString('realpath(', $bypassSection);
        $this->assertStringContainsString('pathScopedBypass', $bypassSection);
    }

    /**
     * Regression guard for the exact mistake this file's history already hit:
     * 'login.json.php' was added to autoRateLimitGuard()'s exact-basename
     * bypass list on the assumption every file with that basename self-limits
     * - but the basename is shared by 4 files (core + encoder + LoginWordPress
     * + LoginLDAP) and only the core one calls enforceRateLimit() itself, so
     * the other 3 would silently lose sitewide rate limiting. Same reasoning
     * applies to 'getToken.json.php' (VideoHLS) for any third-party plugin
     * sharing that basename. Both must stay exempted only via the
     * path-scoped bypass above, never by basename alone.
     *
     * @test
     */
    public function testPathScopedBasenamesAbsentFromBuiltinBypassList()
    {
        $root = dirname(__DIR__, 2);
        $source = file_get_contents($root . '/objects/functionsSecurity.php');

        $fnStart = strpos($source, 'function autoRateLimitGuard');
        $this->assertNotFalse($fnStart);
        $arrayStart = strpos($source, 'static $builtinBypass', $fnStart);
        $this->assertNotFalse($arrayStart);
        $arrayEnd = strpos($source, '];', $arrayStart);
        $arrayBody = substr($source, $arrayStart, $arrayEnd - $arrayStart);
        $arrayBody = preg_replace('/^\s*\/\/.*$/m', '', $arrayBody); // strip comments mentioning them

        preg_match_all("/'([A-Za-z0-9_.]+\\.json\\.php)'/", $arrayBody, $m);
        $this->assertNotContains('login.json.php', $m[1], "login.json.php must not be exact-basename bypassed");
        $this->assertNotContains('getToken.json.php', $m[1], "getToken.json.php must not be exact-basename bypassed");
    }
}
