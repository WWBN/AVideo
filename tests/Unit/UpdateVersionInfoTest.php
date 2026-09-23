<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/objects/functionsUpdate.php';

class UpdateVersionInfoTest extends TestCase
{
    private $directory;

    private function git(...$args)
    {
        $output = [];
        $command = 'git -c ' . escapeshellarg('safe.directory=' . $this->directory)
            . ' -C ' . escapeshellarg($this->directory) . ' '
            . implode(' ', array_map('escapeshellarg', $args));
        exec($command . ' 2>&1', $output, $code);
        $this->assertSame(0, $code, implode("\n", $output));
        return trim(implode("\n", $output));
    }

    private function createRepository()
    {
        if (!function_exists('exec')) {
            $this->markTestSkipped('Git metadata needs exec.');
        }
        exec('git --version 2>&1', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('Git is unavailable.');
        }
        $this->directory = str_replace('\\', '/', sys_get_temp_dir()) . '/avideo-version-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        $this->git('init');
        $this->git('checkout', '-b', 'version-test');
        file_put_contents($this->directory . '/example.txt', 'version fixture');
        $this->git('add', 'example.txt');
        $this->git('-c', 'user.name=AVideo Test', '-c', 'user.email=test@example.invalid', '-c', 'commit.gpgsign=false', 'commit', '-m', 'test: initial revision');
        $this->git('-c', 'tag.gpgsign=false', 'tag', 'v29.1.0');
    }

    protected function tearDown(): void
    {
        if ($this->directory) {
            $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($entries as $entry) {
                if ($entry->isDir()) {
                    rmdir($entry->getPathname());
                } else {
                    chmod($entry->getPathname(), 0666);
                    unlink($entry->getPathname());
                }
            }
            rmdir($this->directory);
        }
        parent::tearDown();
    }

    public function testMissingMetadataDoesNotGuessAnInstalledVersion(): void
    {
        $info = getAVideoUpdateGitInfo(__DIR__);
        $this->assertSame('', $info['sha']);
        $this->assertSame('unknown', getAVideoUpdateCommitStatus('', str_repeat('a', 40)));
        $this->assertSame('unknown', getAVideoUpdateCommitStatus(str_repeat('a', 40), ''));
    }

    public function testReleaseComparisonDirectionAndUnavailableResponse(): void
    {
        $local = str_repeat('a', 40);
        $release = str_repeat('b', 40);
        $this->assertSame('identical', getAVideoUpdateCommitStatus($local, $local));
        foreach (['ahead', 'behind', 'diverged'] as $status) {
            $this->assertSame($status, getAVideoUpdateCommitStatus($local, $release, ['status' => $status]));
        }
        $this->assertSame('unknown', getAVideoUpdateCommitStatus($local, $release, ['message' => 'API rate limit exceeded']));
        $this->assertSame('unknown', getAVideoUpdateCommitStatus($local, $release));
    }

    public function testGitReportsExactTagAndDetachedCheckout(): void
    {
        $this->createRepository();
        $info = getAVideoUpdateGitInfo($this->directory);
        $this->assertSame($this->git('rev-parse', 'HEAD'), $info['sha']);
        $this->assertSame('version-test', $info['branch']);
        $this->assertSame('v29.1.0', $info['tag']);
        $this->assertNotFalse(strtotime($info['date']));
        $this->git('checkout', '--detach');
        $this->assertSame('', getAVideoUpdateGitInfo($this->directory)['branch']);
    }

    public function testReleaseResolvesTagInsteadOfMovingTargetBranch(): void
    {
        $this->createRepository();
        $sha = $this->git('rev-parse', 'HEAD');
        $masterSha = str_repeat('b', 40);
        $paths = [];
        $result = getAVideoUpdateOverview($this->directory, function ($path) use ($sha, $masterSha, &$paths) {
            $paths[] = $path;
            $responses = [
                'releases/latest' => ['tag_name' => 'v29.1.0', 'target_commitish' => 'master', 'draft' => false, 'prerelease' => false],
                'commits/v29.1.0' => ['sha' => $sha],
                'commits/master' => ['sha' => $masterSha, 'commit' => ['committer' => ['date' => '2026-09-23T17:24:00Z']]],
                'compare/' . $masterSha . '...' . $sha . '?per_page=1' => ['status' => 'behind']
            ];
            $this->assertArrayHasKey($path, $responses);
            return ['data' => $responses[$path], 'checked_at' => 1234];
        });
        $this->assertSame('identical', $result['status']);
        $this->assertSame($sha, $result['release_sha']);
        $this->assertSame($masterSha, $result['master']['sha']);
        $this->assertSame('behind', $result['repository_status']);
        $this->assertSame('2026-09-23T17:24:00Z', $result['master']['commit']['committer']['date']);
        $this->assertCount(4, $paths);
    }

    public function testUnavailableGitHubStillReturnsLocalMetadata(): void
    {
        $this->createRepository();
        $result = getAVideoUpdateOverview($this->directory, function () {
            return ['data' => null, 'checked_at' => 1234];
        });
        $this->assertSame($this->git('rev-parse', 'HEAD'), $result['local']['sha']);
        $this->assertNull($result['release']);
        $this->assertNull($result['master']);
        $this->assertSame('unknown', $result['status']);
        $this->assertSame('unknown', $result['repository_status']);
    }

    public function testComparisonUsesReleaseAsBaseAndInstalledCommitAsHead(): void
    {
        $this->createRepository();
        $local = $this->git('rev-parse', 'HEAD');
        $release = str_repeat('b', 40);
        $result = getAVideoUpdateOverview($this->directory, function ($path) use ($local, $release) {
            $responses = [
                'releases/latest' => ['tag_name' => 'v29.0'],
                'commits/v29.0' => ['sha' => $release],
                'commits/master' => ['sha' => $local],
                'compare/' . $release . '...' . $local . '?per_page=1' => ['status' => 'ahead']
            ];
            $this->assertArrayHasKey($path, $responses);
            return ['data' => $responses[$path], 'checked_at' => 1234];
        });
        $this->assertSame('ahead', $result['status']);
        $this->assertSame('identical', $result['repository_status']);
    }

    public function testRepositoryStatusDoesNotDependOnARelease(): void
    {
        $this->createRepository();
        $local = $this->git('rev-parse', 'HEAD');
        $remote = str_repeat('b', 40);
        foreach (['ahead', 'behind', 'diverged', 'unknown'] as $status) {
            $result = getAVideoUpdateOverview($this->directory, function ($path) use ($local, $remote, $status) {
                $responses = [
                    'releases/latest' => null,
                    'commits/master' => ['sha' => $remote],
                    'compare/' . $remote . '...' . $local . '?per_page=1' => $status === 'unknown' ? null : ['status' => $status]
                ];
                $this->assertArrayHasKey($path, $responses);
                return ['data' => $responses[$path], 'checked_at' => 1234];
            });
            $this->assertSame('unknown', $result['status']);
            $this->assertSame($status, $result['repository_status']);
        }
    }

    public function testReleaseAndMasterAtSameCommitShareOneComparison(): void
    {
        $this->createRepository();
        $local = $this->git('rev-parse', 'HEAD');
        $remote = str_repeat('b', 40);
        $paths = [];
        $result = getAVideoUpdateOverview($this->directory, function ($path) use ($local, $remote, &$paths) {
            $paths[] = $path;
            $responses = [
                'releases/latest' => ['tag_name' => 'v29.1.0'],
                'commits/v29.1.0' => ['sha' => $remote],
                'commits/master' => ['sha' => $remote],
                'compare/' . $remote . '...' . $local . '?per_page=1' => ['status' => 'behind']
            ];
            $this->assertArrayHasKey($path, $responses);
            return ['data' => $responses[$path], 'checked_at' => 1234];
        });
        $this->assertSame('behind', $result['status']);
        $this->assertSame('behind', $result['repository_status']);
        $this->assertCount(4, $paths);
    }

    public function testSharedObjectCacheIsReadWithoutAnotherNetworkRequest(): void
    {
        if (!function_exists('proc_open')) {
            $this->markTestSkipped('Process isolation requires proc_open.');
        }
        $code = <<<'PHP'
class ObjectYPT {
    public static function getCacheGlobal($key, $ttl, $ignoreSession) {
        return (object) ['checked_at' => time(), 'data' => (object) ['tag_name' => 'v29.1.0']];
    }
}
function object_to_array($value) { return json_decode(json_encode($value), true); }
function url_get_contents() { throw new Exception('Cached metadata must not access the network.'); }
require $argv[1];
echo json_encode(getAVideoUpdateGitHubData('releases/latest'));
PHP;
        $process = proc_open([PHP_BINARY, '-r', $code, dirname(__DIR__, 2) . '/objects/functionsUpdate.php'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $result = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame(['tag_name' => 'v29.1.0'], json_decode($result, true)['data']);
    }
}
