<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->uploaded = false;
$obj->filename = '';

if (!empty($global['disableAdvancedConfigurations'])) {
    die(json_encode($obj));
}

if (!User::isAdmin()) {
    $obj->msg = "You are not admin";
    die(json_encode($obj));
}

// CSRF protection: require a valid server-issued token.
// multipart/form-data is a CORS-safelisted Content-Type, so browsers send it
// cross-origin without an OPTIONS preflight. With SameSite=None on HTTPS the
// session cookie is also included, making a pure session check insufficient.
// SECURITY REVIEW (2026-09-28): a report claimed this token check is bypassable because globalToken is
// not session-bound — NOT a vulnerability. This is a *.json.php file, so include_config.php already ran
// autoCSRFGuard() (Origin/Referer same-domain check) before this line; see the note above verifyToken()
// in objects/functions.php.
if (!isGlobalTokenValid()) {
    http_response_code(403);
    $obj->msg = "Invalid or missing CSRF token";
    die(json_encode($obj));
}

// Validate that a file was actually uploaded
if (!isset($_FILES['input-b1']) || empty($_FILES['input-b1']['name'])) {
    $obj->msg = "No file uploaded";
    die(json_encode($obj));
}

// Check for upload errors
if ($_FILES['input-b1']['error'] !== UPLOAD_ERR_OK) {
    $obj->msg = "File upload error (code: " . $_FILES['input-b1']['error'] . ")";
    die(json_encode($obj));
}

// Verify the file was uploaded via HTTP POST (prevents local file inclusion)
if (!is_uploaded_file($_FILES['input-b1']['tmp_name'])) {
    $obj->msg = "Security error: file was not uploaded via POST";
    die(json_encode($obj));
}

$obj->filename = $_FILES['input-b1']['name'];

$allowed = ['zip'];
$path_parts = pathinfo($_FILES['input-b1']['name']);
$extension = isset($path_parts['extension']) ? $path_parts['extension'] : '';

if (!in_array(strtolower($extension), $allowed)) {
    $obj->msg = "File extension error (" . $_FILES['input-b1']['name'] . "), we allow only (" . implode(",", $allowed) . ")";
    die(json_encode($obj));
}


if (strcasecmp($extension, 'zip') == 0) {
    $destination = "{$global['systemRootPath']}plugin/";
    $obj->destination = $destination;
    $path = $_FILES['input-b1']['tmp_name'];

    // Security: Validate ZIP contents before extraction (CWE-434 / CWE-22 mitigation)
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        $obj->msg = "Could not open the ZIP file";
        die(json_encode($obj));
    }

    // Collect all entry names and determine the plugin directory name
    $pluginDirName = null;
    // SECURITY REVIEW (2026-09-28): reported as "blocklist omits .php, so a ZIP can ship a webshell next
    // to the main plugin file" — ADMIN SELF-ACTION / DO NOT FIX. Installing a plugin IS installing
    // arbitrary PHP: the required PluginName/PluginName.php is itself executable code that AVideoPlugin
    // runs on every request, so extra .php files add no capability the admin does not already exercise.
    // Blocking .php (or denying PHP execution under plugin/) would break every plugin. This endpoint is
    // admin-only, origin-guarded (autoCSRFGuard) and token-checked, and can be disabled entirely with
    // $global['disableAdvancedConfigurations']. If re-reported, see the triage instead of re-patching.
    $dangerousExtensions = ['phtml', 'pht', 'phar', 'shtml', 'cgi', 'pl', 'py', 'sh', 'bash', 'exe', 'bat', 'cmd', 'com', 'vbs', 'jsp', 'asp', 'aspx', 'htaccess'];

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);

        // Check for path traversal attempts (CWE-22)
        if (strpos($entryName, '..') !== false) {
            $zip->close();
            $obj->msg = "Security error: ZIP contains path traversal in entry: " . basename($entryName);
            die(json_encode($obj));
        }

        // Check for absolute paths
        if (preg_match('#^[/\\\\]|^[a-zA-Z]:#', $entryName)) {
            $zip->close();
            $obj->msg = "Security error: ZIP contains absolute path entry";
            die(json_encode($obj));
        }

        // Determine the top-level plugin directory from the first entry
        $parts = explode('/', $entryName);
        $topDir = $parts[0];
        if ($pluginDirName === null && !empty($topDir)) {
            $pluginDirName = $topDir;
        }

        // Verify all entries belong to the same top-level directory
        if (!empty($topDir) && $topDir !== $pluginDirName) {
            $zip->close();
            $obj->msg = "Security error: ZIP must contain a single plugin directory. Found: {$topDir} and {$pluginDirName}";
            die(json_encode($obj));
        }

        // Check for dangerous file extensions (non-PHP dangerous server-side scripts)
        $entryExtension = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
        if (in_array($entryExtension, $dangerousExtensions)) {
            $zip->close();
            $obj->msg = "Security error: ZIP contains a forbidden file type (.{$entryExtension}): " . basename($entryName);
            die(json_encode($obj));
        }

        // Check for hidden files/directories (e.g., .htaccess)
        $baseName = basename($entryName);
        if (!empty($baseName) && $baseName[0] === '.' && $baseName !== '.' && $baseName !== '..') {
            $zip->close();
            $obj->msg = "Security error: ZIP contains a hidden file: " . $baseName;
            die(json_encode($obj));
        }
    }

    if (empty($pluginDirName)) {
        $zip->close();
        $obj->msg = "Invalid plugin ZIP: could not determine plugin directory name";
        die(json_encode($obj));
    }

    // Sanitize the plugin directory name — only allow alphanumeric, underscores, and hyphens
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pluginDirName)) {
        $zip->close();
        $obj->msg = "Security error: Invalid plugin directory name. Only alphanumeric characters, underscores, and hyphens are allowed.";
        die(json_encode($obj));
    }

    // Verify the ZIP contains the required main plugin PHP file (PluginName/PluginName.php)
    $mainPluginFile = "{$pluginDirName}/{$pluginDirName}.php";
    $foundMainFile = false;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        if ($zip->getNameIndex($i) === $mainPluginFile) {
            $foundMainFile = true;
            break;
        }
    }

    if (!$foundMainFile) {
        $zip->close();
        $obj->msg = "Invalid plugin structure: missing required file {$mainPluginFile}";
        die(json_encode($obj));
    }

    // Validate that the main plugin file extends PluginAbstract
    // (structure check only, not a code-safety check — see the SECURITY REVIEW note above)
    $mainFileContent = $zip->getFromName($mainPluginFile);
    if ($mainFileContent === false || !preg_match('/class\s+\w+\s+extends\s+PluginAbstract\b/i', $mainFileContent)) {
        $zip->close();
        $obj->msg = "Invalid plugin: main file must contain a class extending PluginAbstract";
        die(json_encode($obj));
    }

    $zip->close();

    // All validations passed — proceed with extraction
    $dir = "{$destination}{$pluginDirName}";
    if (is_dir($dir)) {
        rrmdir($dir);
    }

    // Use ZipArchive for safe extraction instead of exec() to avoid command injection (CWE-78)
    // Extract file-by-file and verify each ends up inside the plugin/ directory
    $zip = new ZipArchive();
    if ($zip->open($path) === true) {
        $realDestination = realpath($destination);
        if ($realDestination === false) {
            $zip->close();
            $obj->msg = "Plugin destination directory does not exist";
            die(json_encode($obj));
        }
        $realDestination = rtrim($realDestination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $extractionFailed = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // Skip directory entries
            if (substr($entryName, -1) === '/') {
                $dirToCreate = $destination . $entryName;
                if (!is_dir($dirToCreate)) {
                    mkdir($dirToCreate, 0755, true);
                }
                // Verify the created directory is inside plugin/
                $realDir = realpath($dirToCreate);
                if ($realDir === false || strpos($realDir . DIRECTORY_SEPARATOR, $realDestination) !== 0) {
                    // Directory escaped plugin/ — remove it and abort
                    if ($realDir !== false && is_dir($realDir)) {
                        rmdir($realDir);
                    }
                    $extractionFailed = true;
                    $obj->msg = "Security error: extracted directory would escape the plugin directory";
                    break;
                }
                continue;
            }

            // Ensure parent directory exists
            $fileDestination = $destination . $entryName;
            $parentDir = dirname($fileDestination);
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0755, true);
            }

            // Extract single file
            if (!$zip->extractTo($destination, $entryName)) {
                $extractionFailed = true;
                $obj->msg = "Failed to extract file: " . basename($entryName);
                break;
            }

            // Post-extraction: verify the file actually landed inside plugin/
            $realFilePath = realpath($fileDestination);
            if ($realFilePath === false || strpos($realFilePath, $realDestination) !== 0) {
                // File escaped plugin/ directory — delete it and abort
                if ($realFilePath !== false && file_exists($realFilePath)) {
                    unlink($realFilePath);
                }
                $extractionFailed = true;
                $obj->msg = "Security error: extracted file would escape the plugin directory";
                break;
            }
        }

        $zip->close();

        if (!$extractionFailed) {
            $obj->uploaded = true;
            $obj->pluginName = $pluginDirName;
        } else {
            // Clean up partial extraction on failure
            $dir = "{$destination}{$pluginDirName}";
            if (is_dir($dir)) {
                rrmdir($dir);
            }
        }
    } else {
        $obj->msg = "Failed to extract ZIP file";
    }
}
die(json_encode($obj));
