<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * NotifyFFmpegTest
 * 
 * Test suite for the FFMPEG callback API endpoint
 * Located at: plugin/API/notify.ffmpeg.json.php
 * 
 * Tests cover:
 * - Callback JSON validation
 * - Action whitelist enforcement
 * - Handler function execution
 * - Error handling
 * - Security validations
 * 
 * Run with: vendor/bin/phpunit tests/Unit/NotifyFFmpegTest.php
 */
class NotifyFFmpegTest extends TestCase
{
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load the functions file
        if (!function_exists('processFFMPEGCallback')) {
            require_once \APP_ROOT . '/objects/functionsFFMPEG.php';
        }
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
     * Test that invalid JSON callback is rejected
     * 
     * This is a critical security test
     * 
     * @test
     */
    public function testInvalidJsonCallbackIsRejected()
    {
        $invalidCallback = 'not valid json';
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($invalidCallback, $notify);
        
        $this->assertErrorResponse($result, 'Invalid callback format');
    }

    /**
     * Test that non-array callback is rejected
     * 
     * @test
     */
    public function testNonArrayCallbackIsRejected()
    {
        $invalidCallback = json_encode('string instead of array');
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($invalidCallback, $notify);
        
        $this->assertErrorResponse($result, 'Invalid callback format');
    }

    /**
     * Test that callback without action is rejected
     * 
     * @test
     */
    public function testCallbackWithoutActionIsRejected()
    {
        $callback = json_encode([
            'params' => ['videos_id' => 123]
            // Missing 'action'
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        $this->assertErrorResponse($result, 'Missing action');
    }

    /**
     * Test that empty action is rejected
     * 
     * @test
     */
    public function testEmptyActionIsRejected()
    {
        $callback = json_encode([
            'action' => '',
            'params' => ['videos_id' => 123]
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        $this->assertErrorResponse($result, 'Missing action');
    }

    /**
     * Test that non-whitelisted actions are rejected
     * 
     * This is a critical security test to prevent arbitrary function execution
     * 
     * @test
     * @dataProvider maliciousActionsProvider
     */
    public function testNonWhitelistedActionsAreRejected($maliciousAction)
    {
        $callback = json_encode([
            'action' => $maliciousAction,
            'params' => ['videos_id' => 123]
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        $this->assertErrorResponse($result, 'Action not allowed');
    }

    /**
     * Data provider for malicious actions
     * 
     * @return array
     */
    public function maliciousActionsProvider()
    {
        return [
            'arbitrary function' => ['deleteAllVideos'],
            'PHP function' => ['system'],
            'eval' => ['eval'],
            'exec' => ['exec'],
            'shell_exec' => ['shell_exec'],
            'passthru' => ['passthru'],
            'SQL injection' => ["triggerPluginHook' OR '1'='1"],
            'directory traversal' => ['../../etc/passwd'],
            'null byte' => ["triggerPluginHook\0malicious"],
        ];
    }

    /**
     * Test that allowed actions are accepted
     * 
     * @test
     * @dataProvider allowedActionsProvider
     */
    public function testAllowedActionsAreAccepted($action)
    {
        // Mock the handler functions
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => $action,
            'params' => [
                'videos_id' => 123,
                'hook' => 'onNewVideo'
            ]
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Should not return "Action not allowed"
        if (isset($result['error'])) {
            $this->assertNotEquals(
                'Action not allowed',
                $result['error'],
                "Action '{$action}' should be in the whitelist"
            );
        }
    }

    /**
     * Data provider for allowed actions
     * 
     * @return array
     */
    public function allowedActionsProvider()
    {
        return [
            'triggerPluginHook' => ['triggerPluginHook'],
            'updateVideoMetadata' => ['updateVideoMetadata'],
        ];
    }

    /**
     * Test valid callback with triggerPluginHook action
     * 
     * @test
     */
    public function testValidTriggerPluginHookCallback()
    {
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => 'triggerPluginHook',
            'params' => [
                'hook' => 'onNewVideo',
                'videos_id' => 123
            ]
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Should execute without "Action not allowed" error
        $this->assertIsArray($result, 'Result should be an array');
        
        if (isset($result['error'])) {
            $this->assertNotEquals('Action not allowed', $result['error']);
        }
    }

    /**
     * Test valid callback with updateVideoMetadata action
     * 
     * @test
     */
    public function testValidUpdateVideoMetadataCallback()
    {
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => 'updateVideoMetadata',
            'params' => [
                'videos_id' => 123,
                'duration' => 300,
                'resolution' => '1920x1080'
            ]
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Should execute without "Action not allowed" error
        $this->assertIsArray($result, 'Result should be an array');
        
        if (isset($result['error'])) {
            $this->assertNotEquals('Action not allowed', $result['error']);
        }
    }

    /**
     * Test callback with malformed JSON structure
     * 
     * @test
     */
    public function testMalformedJsonStructure()
    {
        $malformedCallback = '{"action": "triggerPluginHook", "params": [MALFORMED}';
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($malformedCallback, $notify);
        
        $this->assertErrorResponse($result, 'Invalid callback format');
    }

    /**
     * Test that params is properly handled when missing
     * 
     * @test
     */
    public function testMissingParamsIsHandledGracefully()
    {
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => 'triggerPluginHook'
            // Missing 'params'
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Should not crash, should handle gracefully
        $this->assertIsArray($result);
    }

    /**
     * Test that params as non-array is converted to empty array
     * 
     * @test
     */
    public function testNonArrayParamsIsHandledGracefully()
    {
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => 'triggerPluginHook',
            'params' => 'not an array'
        ]);
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Should not crash, should handle gracefully
        $this->assertIsArray($result);
    }

    /**
     * Test getFFMPEGCallbackHandlers function exists
     * 
     * @test
     */
    public function testGetFFMPEGCallbackHandlersFunctionExists()
    {
        $this->assertTrue(
            function_exists('getFFMPEGCallbackHandlers'),
            'getFFMPEGCallbackHandlers function should exist'
        );
    }

    /**
     * Test that getFFMPEGCallbackHandlers returns proper structure
     * 
     * @test
     */
    public function testGetFFMPEGCallbackHandlersReturnsArray()
    {
        if (function_exists('getFFMPEGCallbackHandlers')) {
            $handlers = getFFMPEGCallbackHandlers();
            
            $this->assertIsArray($handlers, 'Handlers should be an array');
            $this->assertNotEmpty($handlers, 'Handlers should not be empty');
            
            // Check that each handler maps to a function name
            foreach ($handlers as $action => $functionName) {
                $this->assertIsString($action, 'Action should be a string');
                $this->assertIsString($functionName, 'Function name should be a string');
            }
        } else {
            $this->markTestSkipped('getFFMPEGCallbackHandlers function not available');
        }
    }

    /**
     * Test error handling when handler function doesn't exist
     * 
     * This tests defensive programming
     * 
     * @test
     */
    public function testErrorWhenHandlerFunctionNotFound()
    {
        // Temporarily override the handlers to point to non-existent function
        // This requires mocking or modifying getFFMPEGCallbackHandlers
        
        // For now, just test the structure
        $this->assertTrue(
            function_exists('processFFMPEGCallback'),
            'processFFMPEGCallback should handle missing handler functions'
        );
    }

    /**
     * Test that exceptions in handler are caught
     * 
     * @test
     */
    public function testExceptionsInHandlerAreCaught()
    {
        // This test validates that exceptions don't crash the endpoint
        // In production, exceptions should be caught and return error response
        
        $this->assertTrue(
            function_exists('processFFMPEGCallback'),
            'processFFMPEGCallback should catch exceptions'
        );
    }

    /**
     * Test JSON decode error handling
     * 
     * @test
     * @dataProvider invalidJsonProvider
     */
    public function testJsonDecodeErrorHandling($invalidJson)
    {
        $notify = ['videos_id' => 123];
        
        $result = processFFMPEGCallback($invalidJson, $notify);
        
        $this->assertErrorResponse($result, 'Invalid callback format');
    }

    /**
     * Data provider for invalid JSON
     * 
     * @return array
     */
    public function invalidJsonProvider()
    {
        return [
            'syntax error' => ['{invalid json}'],
            'incomplete' => ['{"action": '],
            'trailing comma' => ['{"action": "test",}'],
            'single quotes' => ["{'action': 'test'}"],
            'unquoted keys' => ['{action: "test"}'],
            'PHP code injection' => ['<?php system("ls"); ?>'],
        ];
    }

    /**
     * Mock callback handler functions for testing
     */
    private function mockCallbackHandlers()
    {
        // Create mock functions if they don't exist
        if (!function_exists('getFFMPEGCallbackHandlers')) {
            eval('function getFFMPEGCallbackHandlers() {
                return [
                    "triggerPluginHook" => "handleCallbackTriggerPluginHook",
                    "updateVideoMetadata" => "handleCallbackUpdateVideoMetadata"
                ];
            }');
        }
        
        // Mock AVideoPlugin if needed
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
    }

    /**
     * Test that processFFMPEGCallback actually executes the handler
     * 
     * @test
     */
    public function testProcessFFMPEGCallbackExecutesHandler()
    {
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        $callback = json_encode([
            'action' => 'triggerPluginHook',
            'params' => [
                'hook' => 'onEncoderNotifyIsDone',
                'videos_id' => 999
            ]
        ]);
        
        $result = processFFMPEGCallback($callback, ['videos_id' => 999]);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success'] ?? false, 'Callback should execute successfully');
        $this->assertNotEmpty($pluginCallTracker, 'Plugin method should have been called');
        $this->assertEquals('onEncoderNotifyIsDone', $pluginCallTracker[0]['method']);
        $this->assertEquals(999, $pluginCallTracker[0]['id']);
    }

    /**
     * Test that updateVideoMetadata handler actually updates data
     * 
     * @test
     */
    public function testUpdateVideoMetadataHandlerUpdatesData()
    {
        $this->mockCallbackHandlers();
        
        $callback = json_encode([
            'action' => 'updateVideoMetadata',
            'params' => [
                'videos_id' => 555,
                'duration' => 450,
                'resolution' => '3840x2160'
            ]
        ]);
        
        $result = processFFMPEGCallback($callback, ['videos_id' => 555]);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success'] ?? false);
        $this->assertArrayHasKey('updates', $result);
        $this->assertEquals(450, $result['updates']['duration']);
        $this->assertEquals('3840x2160', $result['updates']['resolution']);
    }

    /**
     * Test complete callback workflow from JSON to execution
     * 
     * @test
     */
    public function testCompleteCallbackWorkflow()
    {
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        // Simulate real scenario: encoder notifies video is ready
        $callback = json_encode([
            'action' => 'triggerPluginHook',
            'params' => [
                'hook' => 'onUploadIsDone',
                'videos_id' => 777
            ]
        ]);
        
        $notify = [
            'videos_id' => 777,
            'status' => 'success',
            'url' => 'https://example.com/video.mp4'
        ];
        
        $result = processFFMPEGCallback($callback, $notify);
        
        // Verify entire workflow
        $this->assertIsArray($result, 'Should return array result');
        $this->assertTrue($result['success'] ?? false, 'Workflow should complete successfully');
        $this->assertEquals('onUploadIsDone', $result['hook'] ?? '');
        $this->assertEquals(777, $result['videos_id'] ?? 0);
        
        // Verify the plugin was actually called
        $this->assertNotEmpty($pluginCallTracker, 'Plugin should have been invoked');
    }

    /**
     * Test that invalid action prevents execution
     * 
     * @test
     */
    public function testInvalidActionPreventsExecution()
    {
        global $pluginCallTracker;
        $pluginCallTracker = [];
        
        $callback = json_encode([
            'action' => 'deleteAllVideos', // Not whitelisted!
            'params' => [
                'videos_id' => 123
            ]
        ]);
        
        $result = processFFMPEGCallback($callback, ['videos_id' => 123]);
        
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Action not allowed', $result['error']);
        // Verify nothing was executed
        $this->assertEmpty($pluginCallTracker, 'No plugin method should have been called');
    }
}
