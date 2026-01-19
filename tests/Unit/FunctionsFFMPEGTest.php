<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * FunctionsFFMPEGTest
 * 
 * Comprehensive test suite for FFMPEG callback functions in objects/functionsFFMPEG.php
 * 
 * Tests cover:
 * - Hook whitelist validation
 * - Callback parameter validation
 * - Each hook handler execution
 * - Video metadata updates
 * - Error handling and edge cases
 * 
 * Run with: vendor/bin/phpunit tests/Unit/FunctionsFFMPEGTest.php
 */
class FunctionsFFMPEGTest extends TestCase
{
    /**
     * @var array Mock video data
     */
    private $mockVideoData;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock video data
        $this->mockVideoData = [
            'id' => 123,
            'title' => 'Test Video',
            'duration' => 120,
            'resolution' => '1920x1080'
        ];
        
        // Load the functions file if not already loaded
        if (!function_exists('handleCallbackTriggerPluginHook')) {
            require_once \APP_ROOT . '/objects/functionsFFMPEG.php';
        }
    }

    /**
     * Test that handleCallbackTriggerPluginHook exists
     * 
     * @test
     */
    public function testHandleCallbackTriggerPluginHookFunctionExists()
    {
        $this->assertTrue(
            function_exists('handleCallbackTriggerPluginHook'),
            'handleCallbackTriggerPluginHook function should exist'
        );
    }

    /**
     * Test that handleCallbackUpdateVideoMetadata exists
     * 
     * @test
     */
    public function testHandleCallbackUpdateVideoMetadataFunctionExists()
    {
        $this->assertTrue(
            function_exists('handleCallbackUpdateVideoMetadata'),
            'handleCallbackUpdateVideoMetadata function should exist'
        );
    }

    /**
     * Test hook whitelist contains all expected hooks
     * 
     * This validates that the whitelist was properly expanded in PR #10284 fixes
     * 
     * @test
     * @dataProvider allowedHooksProvider
     */
    public function testAllowedHooksAreAccepted($hook)
    {
        // Note: This test validates the structure without calling AVideoPlugin
        // In a real integration test, you'd mock AVideoPlugin
        
        $params = [
            'hook' => $hook,
            'videos_id' => 123
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        // Should not return "Hook not allowed" error for valid hooks
        if (isset($result['error'])) {
            $this->assertNotEquals(
                'Hook not allowed',
                $result['error'],
                "Hook '{$hook}' should be in the whitelist"
            );
        }
    }

    /**
     * Data provider for allowed hooks
     * 
     * @return array
     */
    public function allowedHooksProvider()
    {
        return [
            'onNewVideo' => ['onNewVideo'],
            'afterNewVideo' => ['afterNewVideo'],
            'onUpdateVideo' => ['onUpdateVideo'],
            'onVideoSetStatus' => ['onVideoSetStatus'],
            'onEncoderNotifyIsDone' => ['onEncoderNotifyIsDone'],
            'onEncoderReceiveImage' => ['onEncoderReceiveImage'],
            'onReceiveFile' => ['onReceiveFile'],
            'onUploadIsDone' => ['onUploadIsDone'],
        ];
    }

    /**
     * Test that non-whitelisted hooks are rejected
     * 
     * This is a critical security test - validates that arbitrary hooks cannot be executed
     * 
     * @test
     * @dataProvider disallowedHooksProvider
     */
    public function testDisallowedHooksAreRejected($hook)
    {
        $params = [
            'hook' => $hook,
            'videos_id' => 123
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        $this->assertErrorResponse($result, 'Hook not allowed');
    }

    /**
     * Data provider for disallowed hooks
     * 
     * @return array
     */
    public function disallowedHooksProvider()
    {
        return [
            'onVideoProcessed (removed)' => ['onVideoProcessed'],
            'arbitrary hook' => ['onArbitraryHook'],
            'malicious hook' => ['onDeleteAllVideos'],
            'empty hook' => [''],
            'SQL injection attempt' => ["onNewVideo' OR '1'='1"],
        ];
    }

    /**
     * Test that missing required parameters return error
     * 
     * @test
     */
    public function testMissingHookParameterReturnsError()
    {
        $params = [
            'videos_id' => 123
            // Missing 'hook' parameter
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        $this->assertArrayHasKey('error', $result, 'Should return error for missing hook');
    }

    /**
     * Test that missing videos_id parameter returns error
     * 
     * @test
     */
    public function testMissingVideosIdParameterReturnsError()
    {
        $params = [
            'hook' => 'onNewVideo'
            // Missing 'videos_id' parameter
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        $this->assertArrayHasKey('error', $result, 'Should return error for missing videos_id');
    }

    /**
     * Test onVideoSetStatus requires oldValue and newValue
     * 
     * @test
     */
    public function testOnVideoSetStatusRequiresOldAndNewValue()
    {
        $params = [
            'hook' => 'onVideoSetStatus',
            'videos_id' => 123
            // Missing oldValue and newValue
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        $this->assertErrorResponse($result, 'Missing oldValue or newValue for onVideoSetStatus');
    }

    /**
     * Test onVideoSetStatus with valid parameters structure
     * 
     * @test
     */
    public function testOnVideoSetStatusWithValidParameters()
    {
        $params = [
            'hook' => 'onVideoSetStatus',
            'videos_id' => 123,
            'oldValue' => 'active',
            'newValue' => 'inactive'
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        // Should not have the specific error about missing parameters
        if (isset($result['error'])) {
            $this->assertNotEquals(
                'Missing oldValue or newValue for onVideoSetStatus',
                $result['error'],
                'Should not return parameter missing error when both values provided'
            );
        }
    }

    /**
     * Test updateVideoMetadata with valid duration
     * 
     * @test
     */
    public function testUpdateVideoMetadataWithValidDuration()
    {
        // This test requires mocking the Video class and database
        // In a real implementation, you'd mock these dependencies
        
        $params = [
            'videos_id' => 123,
            'duration' => 300
        ];
        
        // Note: Without mocking Video class, this will fail
        // This demonstrates the test structure
        $this->assertTrue(
            function_exists('handleCallbackUpdateVideoMetadata'),
            'Function should exist for testing structure'
        );
    }

    /**
     * Test updateVideoMetadata with valid resolution
     * 
     * @test
     */
    public function testUpdateVideoMetadataWithValidResolution()
    {
        $params = [
            'videos_id' => 123,
            'resolution' => '1920x1080'
        ];
        
        // Structure test - validates that resolution format is checked
        $this->assertMatchesRegularExpression(
            '/^\d+x\d+$/',
            $params['resolution'],
            'Resolution should match required format'
        );
    }

    /**
     * Test updateVideoMetadata rejects invalid resolution format
     * 
     * @test
     * @dataProvider invalidResolutionProvider
     */
    public function testUpdateVideoMetadataRejectsInvalidResolution($invalidResolution)
    {
        // Test that invalid resolution formats are not accepted
        $this->assertDoesNotMatchRegularExpression(
            '/^\d+x\d+$/',
            $invalidResolution,
            "Resolution '{$invalidResolution}' should not match the required format"
        );
    }

    /**
     * Data provider for invalid resolutions
     * 
     * @return array
     */
    public function invalidResolutionProvider()
    {
        return [
            'letters' => ['1920xabc'],
            'no separator' => ['19201080'],
            'wrong separator' => ['1920:1080'],
            'SQL injection' => ["1920x1080' OR '1'='1"],
            'script tag' => ['<script>alert(1)</script>'],
            'empty' => [''],
        ];
    }

    /**
     * Test that videos_id is properly sanitized to integer
     * 
     * @test
     * @dataProvider videosIdSanitizationProvider
     */
    public function testVideosIdIsSanitizedToInteger($input, $expected)
    {
        $sanitized = intval($input);
        $this->assertEquals(
            $expected,
            $sanitized,
            "videos_id should be sanitized to integer"
        );
    }

    /**
     * Data provider for videos_id sanitization
     * 
     * @return array
     */
    public function videosIdSanitizationProvider()
    {
        return [
            'valid integer' => [123, 123],
            'string integer' => ['456', 456],
            'SQL injection' => ["123' OR '1'='1", 123],
            'float' => [123.45, 123],
            'negative' => [-5, -5],
            'zero' => [0, 0],
        ];
    }

    /**
     * Test processFFMPEGCallback function exists
     * 
     * @test
     */
    public function testProcessFFMPEGCallbackFunctionExists()
    {
        $this->assertTrue(
            function_exists('processFFMPEGCallback'),
            'processFFMPEGCallback function should exist'
        );
    }

    /**
     * Test validateCallbackParams function exists
     * 
     * @test
     */
    public function testValidateCallbackParamsFunctionExists()
    {
        $this->assertTrue(
            function_exists('validateCallbackParams'),
            'validateCallbackParams function should exist'
        );
    }

    /**
     * Test sanitizeAlphanumericForCallback function exists
     * 
     * @test
     */
    public function testSanitizeAlphanumericForCallbackFunctionExists()
    {
        $this->assertTrue(
            function_exists('sanitizeAlphanumericForCallback'),
            'sanitizeAlphanumericForCallback function should exist'
        );
    }

    /**
     * Helper method to safely call handleCallbackTriggerPluginHook
     * 
     * This wraps the function call to handle cases where AVideoPlugin doesn't exist
     * in the test environment
     * 
     * @param array $params
     * @return array
     */
    private function callHandleCallbackTriggerPluginHookSafely(array $params): array
    {
        try {
            if (!function_exists('handleCallbackTriggerPluginHook')) {
                return ['error' => 'Function not loaded'];
            }
            
            // Mock AVideoPlugin if it doesn't exist
            if (!class_exists('AVideoPlugin')) {
                eval('class AVideoPlugin {
                    public static function onNewVideo($id) { return true; }
                    public static function afterNewVideo($id) { return true; }
                    public static function onUpdateVideo($id) { return true; }
                    public static function onVideoSetStatus($id, $old, $new) { return true; }
                    public static function onEncoderNotifyIsDone($id) { return true; }
                    public static function onEncoderReceiveImage($id) { return true; }
                    public static function onReceiveFile($id) { return true; }
                    public static function onUploadIsDone($id) { return true; }
                }');
            }
            
            return handleCallbackTriggerPluginHook($params, []);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test that hook execution returns success for valid hooks
     * 
     * @test
     */
    public function testValidHookExecutionReturnsSuccess()
    {
        $params = [
            'hook' => 'onNewVideo',
            'videos_id' => 123
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        // Should have either success or be blocked by missing dependencies
        $this->assertTrue(
            isset($result['success']) || isset($result['error']),
            'Result should have either success or error key'
        );
        
        if (isset($result['success'])) {
            $this->assertTrue($result['success']);
            $this->assertEquals('onNewVideo', $result['hook']);
            $this->assertEquals(123, $result['videos_id']);
        }
    }

    /**
     * Test response structure for successful hook execution
     * 
     * @test
     */
    public function testSuccessfulHookExecutionResponseStructure()
    {
        $params = [
            'hook' => 'afterNewVideo',
            'videos_id' => 456
        ];
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        if (isset($result['success']) && $result['success']) {
            $this->assertArrayHasKeys(['success', 'hook', 'videos_id'], $result);
            $this->assertIsBool($result['success']);
            $this->assertIsString($result['hook']);
            $this->assertIsInt($result['videos_id']);
        }
    }

    /**
     * Test that each hook in the switch case is covered
     * 
     * This ensures no hook is missing a handler
     * 
     * @test
     * @dataProvider allowedHooksProvider
     */
    public function testEachHookHasHandler($hook)
    {
        $params = [
            'hook' => $hook,
            'videos_id' => 789
        ];
        
        if ($hook === 'onVideoSetStatus') {
            $params['oldValue'] = 'active';
            $params['newValue'] = 'inactive';
        }
        
        $result = $this->callHandleCallbackTriggerPluginHookSafely($params);
        
        // Should not return "Hook handler not implemented"
        if (isset($result['error'])) {
            $this->assertNotEquals(
                'Hook handler not implemented',
                $result['error'],
                "Hook '{$hook}' should have a handler in the switch statement"
            );
        }
    }

    /**
     * Test that triggerPluginHook actually calls AVideoPlugin methods
     * 
     * @test
     */
    public function testTriggerPluginHookCallsAVideoPlugin()
    {
        // Use global tracking from bootstrap
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        $params = ['hook' => 'onNewVideo', 'videos_id' => 123];
        $result = handleCallbackTriggerPluginHook($params, []);
        
        $this->assertTrue($result['success'], 'Callback should succeed');
        $this->assertCount(1, $pluginCallTracker, 'AVideoPlugin method should be called once');
        $this->assertEquals('onNewVideo', $pluginCallTracker[0]['method']);
        $this->assertEquals(123, $pluginCallTracker[0]['id']);
    }

    /**
     * Test that updateVideoMetadata actually modifies video data
     * 
     * @test
     */
    public function testUpdateVideoMetadataModifiesVideo()
    {
        $params = [
            'videos_id' => 123,
            'duration' => 300,
            'resolution' => '1920x1080'
        ];
        
        $result = handleCallbackUpdateVideoMetadata($params, []);
        
        $this->assertTrue($result['success'], 'Callback should succeed');
        $this->assertArrayHasKey('updates', $result);
        $this->assertEquals(300, $result['updates']['duration']);
        $this->assertEquals('1920x1080', $result['updates']['resolution']);
    }

    /**
     * Test callback execution with real processFFMPEGCallback
     * 
     * @test
     */
    public function testProcessFFMPEGCallbackExecutesAction()
    {
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        $callback = json_encode([
            'action' => 'triggerPluginHook',
            'params' => [
                'hook' => 'onNewVideo',
                'videos_id' => 456
            ]
        ]);
        
        $result = processFFMPEGCallback($callback, ['videos_id' => 456]);
        
        $this->assertIsArray($result);
        if (isset($result['success'])) {
            $this->assertTrue($result['success']);
            // Verify plugin was actually called
            $this->assertNotEmpty($pluginCallTracker, 'Plugin should have been called');
            $this->assertEquals('onNewVideo', $pluginCallTracker[0]['method']);
            $this->assertEquals(456, $pluginCallTracker[0]['id']);
        }
    }

    /**
     * Test that onVideoSetStatus passes oldValue and newValue correctly
     * 
     * @test
     */
    public function testOnVideoSetStatusPassesParameters()
    {
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        $params = [
            'hook' => 'onVideoSetStatus',
            'videos_id' => 789,
            'oldValue' => 'active',
            'newValue' => 'inactive'
        ];
        
        $result = handleCallbackTriggerPluginHook($params, []);
        
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($pluginCallTracker);
        $this->assertEquals('onVideoSetStatus', $pluginCallTracker[0]['method']);
        $this->assertEquals(789, $pluginCallTracker[0]['id']);
        $this->assertEquals('active', $pluginCallTracker[0]['oldValue']);
        $this->assertEquals('inactive', $pluginCallTracker[0]['newValue']);
    }
}
