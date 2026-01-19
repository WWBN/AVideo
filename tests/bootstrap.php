<?php
/**
 * PHPUnit Bootstrap File
 * 
 * This file sets up the testing environment and loads necessary dependencies.
 * It's automatically loaded before running tests (configured in phpunit.xml).
 */

// Define the base path for the application
define('TEST_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__));

// Load Composer's autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Initialize error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Mock configuration if needed
if (!defined('TESTING')) {
    define('TESTING', true);
}

// Mock the _error_log function
if (!function_exists('_error_log')) {
    function _error_log($message, $file = '', $line = '') {
        // Do nothing in tests - just prevent errors
        return true;
    }
}

// Global tracker for plugin calls
global $pluginCallTracker;
$pluginCallTracker = [];

// Mock the Video class if not loaded
if (!class_exists('Video')) {
    class Video {
        public function __construct($id = 0) {}
        public function setDuration($duration) { return true; }
        public function setVideoDownloadedLink($link) { return true; }
        public function setMainVideoResolution($resolution) { return true; }
        public function save() { return true; }
        public function getCleanTitle() { return 'Test Video'; }
        public function getVideos_id() { return 1; }
        public function getUsers_id() { return 1; }
    }
}

// Mock AVideoPlugin with tracking support
if (!class_exists('AVideoPlugin')) {
    class AVideoPlugin {
        public static function onNewVideo($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onNewVideo', 'id' => $id];
            return true;
        }
        
        public static function afterNewVideo($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'afterNewVideo', 'id' => $id];
            return true;
        }
        
        public static function onUpdateVideo($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onUpdateVideo', 'id' => $id];
            return true;
        }
        
        public static function onVideoSetStatus($id, $oldValue, $newValue) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onVideoSetStatus', 'id' => $id, 'oldValue' => $oldValue, 'newValue' => $newValue];
            return true;
        }
        
        public static function onEncoderNotifyIsDone($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onEncoderNotifyIsDone', 'id' => $id];
            return true;
        }
        
        public static function onEncoderReceiveImage($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onEncoderReceiveImage', 'id' => $id];
            return true;
        }
        
        public static function onReceiveFile($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onReceiveFile', 'id' => $id];
            return true;
        }
        
        public static function onUploadIsDone($id) {
            global $pluginCallTracker;
            $pluginCallTracker[] = ['method' => 'onUploadIsDone', 'id' => $id];
            return true;
        }
    }
}

/**
 * Helper function to create mock configuration
 * Prevents tests from loading actual database configuration
 */
function mockConfiguration() {
    global $global, $config;
    
    if (!isset($global)) {
        $global = [
            'mysqli' => null,
            'debug' => false,
        ];
    }
    
    if (!isset($config)) {
        $config = new stdClass();
        $config->databaseHost = 'localhost';
        $config->databaseUser = 'test';
        $config->databasePass = 'test';
        $config->databaseName = 'test_db';
    }
}

// Setup mock configuration
mockConfiguration();
