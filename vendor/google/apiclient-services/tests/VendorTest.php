<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\CloudFunctions\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for when this package is installed in a vendored directory
 *
 * @runInSeparateProcess
 */
class VendorTest extends TestCase
{
    /**
     * @dataProvider provideApiClientVersions
     */
    public function testApiClientVersions($apiClientVersion)
    {
        if (
            'dev-master' === $apiClientVersion
            && version_compare(PHP_VERSION, '5.6', '<')
        ) {
            self::markTestSkipped('dev-master of google/api-client can only run on 5.6');
        }
        if ('true' === getenv('SKIP_VENDOR_TESTS')) {
            self::markTestSkipped('Explicitly skipping the vendor tests');
        }

        $tmpDir = $this->installToTmpDir($apiClientVersion);

        $testFiles = [
            'mixed1',
            'mixed2',
            'namespaces',
            'underscores',
            'typehints1',
            'typehints2'
        ];

        foreach ($testFiles as $file) {
            $output = null;
            copy(__DIR__ . "/fixtures/$file.php", "$tmpDir/$file.php");
            exec("php $tmpDir/$file.php", $output);

            $this->assertSame(['Done!'], $output, "$file test");
        }
    }

    public function provideApiClientVersions()
    {
        return [
            ['dev-master'],
            ['2.7.2'],
        ];
    }

    private function installToTmpDir($apiClientVersion)
    {
        // get the current branch to run in the test
        exec('git rev-parse --abbrev-ref HEAD', $output);
        if ('HEAD' === $output[0]) {
            // if we are not on a branch, get the current sha. This is required
            // for GH actions
            $output = null;
            exec('git rev-parse HEAD', $output);
        }
        $this->assertCount(1, $output);

        $tmpDir = sprintf('%s/apiclient-services-%s', sys_get_temp_dir(), rand());
        mkdir($tmpDir);
        chdir($tmpDir);
        echo "Running tests in $tmpDir\n";

        // Copy Fixtures
        $composerJson = sprintf(
            file_get_contents(__DIR__ . '/fixtures/composer.json'),
            dirname(__DIR__),
            $apiClientVersion,
            $output[0]
        );
        file_put_contents('composer.json', $composerJson);
        passthru('composer install', $returnVar);
        $this->assertSame(0, $returnVar);

        return $tmpDir;
    }
}
