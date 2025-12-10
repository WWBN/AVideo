<?php
/**
 * Health Check Functions - Centralized functions and configuration
 * This file contains all performance checking functions and threshold configurations
 */

// ========== CONFIGURATION / THRESHOLDS ==========
// Optimized for Kimsufi dedicated servers with standard SSD and 100 Mbps internet

class HealthCheckConfig {
    // Internet Speed Thresholds (in Mbps)
    // Kimsufi typically provides 100 Mbps (sometimes burst to 250-500 Mbps)
    const DOWNLOAD_CRITICAL = 10;    // Very low even for 100 Mbps connection
    const DOWNLOAD_WARNING = 30;     // Below expected for 100 Mbps
    const DOWNLOAD_OPTIMAL = 80;     // Good for 100 Mbps line

    const UPLOAD_CRITICAL = 5;       // Very low for dedicated server
    const UPLOAD_WARNING = 10;       // Below minimum for video hosting
    const UPLOAD_RECOMMENDED = 30;   // Good for 100 Mbps symmetric
    const UPLOAD_OPTIMAL = 80;       // Near line capacity

    // Ping/Latency Thresholds (in milliseconds)
    // Kimsufi servers usually have good latency (10-30ms in Europe)
    const PING_EXCELLENT = 30;       // Typical for Kimsufi in same region
    const PING_GOOD = 60;            // Acceptable for data centers
    const PING_WARNING = 150;        // May indicate routing issues

    // Disk Speed Thresholds (in MB/s)
    // Standard SATA SSD (not NVMe) typical speeds: 400-550 MB/s read, 300-500 MB/s write
    const DISK_READ_CRITICAL = 80;   // Very slow even for old SSD
    const DISK_READ_WARNING = 150;   // Below expected for standard SSD
    const DISK_READ_RECOMMENDED = 300; // Good performance for SATA SSD

    const DISK_WRITE_CRITICAL = 80;  // Very slow even for old SSD
    const DISK_WRITE_WARNING = 150;  // Below expected for standard SSD
    const DISK_WRITE_RECOMMENDED = 300; // Good performance for SATA SSD

    // Expected speeds for different disk types (in MB/s)
    const SSD_EXPECTED_SPEED = 200;  // Minimum expected for standard SATA SSD
    const NVME_EXPECTED_SPEED = 500; // Minimum for NVMe (Kimsufi doesn't typically have these)
    const NVME_OPTIMAL_SPEED = 1000; // Optimal NVMe performance
}

// ========== UTILITY FUNCTIONS ==========

function _isAPPInstalled($appName)
{
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

// ========== DISK FUNCTIONS ==========

function getDiskType($path = '/')
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows: Use multiple methods to detect drive type
        $drive = strtoupper(substr($path, 0, 1));

        // Method 1: Try PowerShell to get drive type and media type
        $psCommand = "Get-PhysicalDisk | Select-Object MediaType, BusType | ConvertTo-Json";
        $output = shell_exec("powershell -NoProfile -Command \"" . $psCommand . "\" 2>nul");

        if (!empty($output)) {
            $json = json_decode($output);
            if ($json) {
                // Handle both single object and array
                $disks = is_array($json) ? $json : [$json];
                foreach ($disks as $disk) {
                    if (isset($disk->MediaType)) {
                        $mediaType = $disk->MediaType;
                        $busType = isset($disk->BusType) ? $disk->BusType : '';

                        if (stripos($mediaType, 'SSD') !== false) {
                            if (stripos($busType, 'NVMe') !== false || $busType == 17) {
                                return 'M.2 NVMe';
                            }
                            return 'SSD';
                        } elseif (stripos($mediaType, 'HDD') !== false) {
                            return 'HDD';
                        } elseif ($mediaType == 4) { // 4 = SSD in Windows
                            if (stripos($busType, 'NVMe') !== false || $busType == 17) {
                                return 'M.2 NVMe';
                            }
                            return 'SSD';
                        } elseif ($mediaType == 3) { // 3 = HDD in Windows
                            return 'HDD';
                        }
                    }
                }
            }
        }

        // Method 2: Try WMIC (older method, more compatible)
        $wmicOutput = shell_exec("wmic diskdrive get Model,MediaType,InterfaceType 2>nul");
        if (!empty($wmicOutput)) {
            if (stripos($wmicOutput, 'SSD') !== false) {
                if (stripos($wmicOutput, 'NVMe') !== false) {
                    return 'M.2 NVMe';
                }
                return 'SSD';
            } elseif (stripos($wmicOutput, 'NVMe') !== false) {
                return 'M.2 NVMe';
            }
            return 'HDD (detected)';
        }

        // Method 3: Check specific drive letter with PowerShell
        $driveCheck = "Get-Partition -DriveLetter {$drive} -ErrorAction SilentlyContinue | Get-Disk | Get-PhysicalDisk | Select-Object MediaType | ConvertTo-Json";
        $driveOutput = shell_exec("powershell -NoProfile -Command \"" . $driveCheck . "\" 2>nul");

        if (!empty($driveOutput)) {
            $driveJson = json_decode($driveOutput);
            if ($driveJson && isset($driveJson->MediaType)) {
                if ($driveJson->MediaType == 4 || stripos($driveJson->MediaType, 'SSD') !== false) {
                    return 'SSD';
                } elseif ($driveJson->MediaType == 3 || stripos($driveJson->MediaType, 'HDD') !== false) {
                    return 'HDD';
                }
            }
        }

        return 'Unknown (Windows - unable to detect)';
    } else {
        // Linux: Multiple methods to detect disk type

        // Method 1: Get device from df
        $device = trim(shell_exec("df {$path} 2>/dev/null | tail -1 | awk '{print \$1}'"));
        if (empty($device)) {
            // Try alternative method with readlink
            $device = trim(shell_exec("readlink -f {$path} 2>/dev/null"));
            if (empty($device)) {
                return 'Unknown - Cannot detect device';
            }
        }

        // Special handling for Docker overlay filesystem
        if (strpos($device, 'overlay') !== false || strpos($device, 'shm') !== false || strpos($device, 'tmpfs') !== false) {
            // In Docker, try to find the underlying physical device
            // Method 1: Check /etc/mtab or /proc/mounts for the real device
            $mounts = @file_get_contents('/proc/mounts');
            if ($mounts !== false) {
                // Look for the root filesystem mount
                preg_match('/^(\/dev\/[^\s]+)\s+\/\s+/m', $mounts, $rootMatches);
                if (!empty($rootMatches[1])) {
                    $device = $rootMatches[1];
                } else {
                    // Try to find any /dev/ mount
                    preg_match('/^(\/dev\/(?:sd[a-z]+|nvme[0-9]+n[0-9]+|vd[a-z]+|md[0-9]+)[^\s]*)\s+/m', $mounts, $anyMatches);
                    if (!empty($anyMatches[1])) {
                        $device = $anyMatches[1];
                    }
                }
            }

            // Method 2: Try to get the device of the parent directory
            if (strpos($device, 'overlay') !== false) {
                $parentDevice = trim(shell_exec("df /var/lib/docker 2>/dev/null | tail -1 | awk '{print \$1}'"));
                if (!empty($parentDevice) && strpos($parentDevice, 'overlay') === false) {
                    $device = $parentDevice;
                } else {
                    // Try root directory
                    $rootDevice = trim(shell_exec("df / 2>/dev/null | tail -1 | awk '{print \$1}'"));
                    if (!empty($rootDevice) && strpos($rootDevice, 'overlay') === false) {
                        $device = $rootDevice;
                    }
                }
            }
        }

        // Special handling for LVM (Logical Volume Manager) devices
        if (strpos($device, '/dev/mapper/') !== false || strpos($device, '/dev/dm-') !== false || strpos($device, 'ubuntu--vg') !== false) {
            // LVM device detected - find the underlying physical volume
            $lvmDevice = basename($device);

            // Try to find the physical device using lvdisplay and pvdisplay
            $physicalDevice = trim(shell_exec("lvdisplay -C -o devices --noheadings /dev/mapper/{$lvmDevice} 2>/dev/null | sed 's/(.*//' | xargs"));

            if (empty($physicalDevice)) {
                // Alternative: Try with dmsetup to get underlying device
                $dmName = str_replace('/dev/mapper/', '', $device);
                $dmTable = shell_exec("dmsetup table {$dmName} 2>/dev/null");
                if (!empty($dmTable)) {
                    preg_match('/(\d+:\d+)/', $dmTable, $majorMinor);
                    if (!empty($majorMinor[1])) {
                        $physicalDevice = trim(shell_exec("lsblk -no NAME -r 2>/dev/null | while read dev; do [ \"\$(cat /sys/class/block/\$dev/dev 2>/dev/null)\" = \"{$majorMinor[1]}\" ] && echo \$dev && break; done"));
                    }
                }
            }

            if (empty($physicalDevice)) {
                // Try to get from /sys/block
                $dmNumber = str_replace('/dev/dm-', '', $device);
                if (is_numeric($dmNumber)) {
                    $slaves = shell_exec("ls -1 /sys/block/dm-{$dmNumber}/slaves/ 2>/dev/null");
                    if (!empty($slaves)) {
                        $slaveDevices = explode("\n", trim($slaves));
                        if (!empty($slaveDevices[0])) {
                            $physicalDevice = $slaveDevices[0];
                        }
                    }
                }
            }

            if (!empty($physicalDevice)) {
                // Extract base device name (e.g., sda from sda1)
                preg_match('/(sd[a-z]+|nvme[0-9]+n[0-9]+|vd[a-z]+|hd[a-z]+|xvd[a-z]+|mmcblk[0-9]+)/', $physicalDevice, $lvmMatches);
                if (!empty($lvmMatches[1])) {
                    $diskName = $lvmMatches[1];

                    // Check if physical disk is NVMe
                    if (strpos($diskName, 'nvme') === 0) {
                        return 'M.2 NVMe (LVM)';
                    }

                    // Check rotation of physical disk
                    $rotational = trim(shell_exec("cat /sys/block/{$diskName}/queue/rotational 2>/dev/null"));
                    if ($rotational === '0') {
                        return 'SSD (LVM)';
                    } elseif ($rotational === '1') {
                        return 'HDD (LVM)';
                    }
                }
            }

            // If we couldn't determine the physical device type, assume SSD (most common for LVM)
            return 'SSD (LVM - detected)';
        }

        // Extract base device name (e.g., sda from /dev/sda1, nvme0n1 from /dev/nvme0n1p1)
        // Support for: sd[a-z], nvme[0-9]n[0-9], vd[a-z], hd[a-z], xvd[a-z], mmcblk[0-9], md[0-9]
        preg_match('/\/dev\/(sd[a-z]+|nvme[0-9]+n[0-9]+|vd[a-z]+|hd[a-z]+|xvd[a-z]+|mmcblk[0-9]+|md[0-9]+)/', $device, $matches);

        if (empty($matches[1])) {
            // Try to extract just the disk name without partition
            $diskName = preg_replace('/[0-9]+$/', '', basename($device));
            if (empty($diskName)) {
                return 'Unknown - Cannot parse device: ' . $device;
            }
        } else {
            $diskName = $matches[1];
        }

        // Special handling for RAID devices (md0, md1, md2, etc)
        if (strpos($diskName, 'md') === 0) {
            // Get underlying physical devices from RAID array
            $mdstat = @file_get_contents('/proc/mdstat');
            if ($mdstat !== false) {
                // Parse mdstat to find physical devices
                if (preg_match('/' . $diskName . '\s*:.*?\[.*?\]\s+(sd[a-z]+|nvme[0-9]+n[0-9]+)/i', $mdstat, $mdMatches)) {
                    $physicalDisk = $mdMatches[1];

                    // Check if physical disk is NVMe
                    if (strpos($physicalDisk, 'nvme') === 0) {
                        return 'M.2 NVMe (RAID)';
                    }

                    // Check rotation of physical disk
                    $rotational = trim(shell_exec("cat /sys/block/{$physicalDisk}/queue/rotational 2>/dev/null"));
                    if ($rotational === '0') {
                        return 'SSD (RAID)';
                    } elseif ($rotational === '1') {
                        return 'HDD (RAID)';
                    }
                }
            }

            // Alternative: Check lsblk for RAID members
            $raidMembers = shell_exec("lsblk -ndo ROTA,NAME /dev/{$diskName} 2>/dev/null | head -1");
            if (!empty($raidMembers) && strpos($raidMembers, '0') === 0) {
                return 'SSD (RAID)';
            } elseif (!empty($raidMembers) && strpos($raidMembers, '1') === 0) {
                return 'HDD (RAID)';
            }

            // Try to get slave devices
            $slaves = shell_exec("ls -1 /sys/block/{$diskName}/slaves/ 2>/dev/null");
            if (!empty($slaves)) {
                $slaveDevices = explode("\n", trim($slaves));
                if (!empty($slaveDevices[0])) {
                    $slaveDisk = $slaveDevices[0];
                    $rotational = trim(shell_exec("cat /sys/block/{$slaveDisk}/queue/rotational 2>/dev/null"));
                    if ($rotational === '0') {
                        return 'SSD (RAID)';
                    } elseif ($rotational === '1') {
                        return 'HDD (RAID)';
                    }
                }
            }

            // Default for RAID: assume SSD (most Kimsufi/OVH use SSD in RAID)
            return 'SSD (RAID - detected)';
        }

        // Check if it's NVMe
        if (strpos($diskName, 'nvme') === 0) {
            return 'M.2 NVMe';
        }

        // Method 2: Check rotation rate
        $rotational = trim(shell_exec("cat /sys/block/{$diskName}/queue/rotational 2>/dev/null"));
        if ($rotational === '0') {
            return 'SSD';
        } elseif ($rotational === '1') {
            return 'HDD';
        }

        // Method 3: Check if device exists in /sys/block
        if (file_exists("/sys/block/{$diskName}")) {
            // Try to read rotational again with full path
            $rotFile = "/sys/block/{$diskName}/queue/rotational";
            if (file_exists($rotFile) && is_readable($rotFile)) {
                $rotational = trim(file_get_contents($rotFile));
                if ($rotational === '0') {
                    return 'SSD';
                } elseif ($rotational === '1') {
                    return 'HDD';
                }
            }

            // If we got here, assume SSD (most Kimsufi servers use SSD)
            return 'SSD (detected)';
        }

        // Method 4: Check lsblk if available
        $lsblkOutput = shell_exec("lsblk -d -o name,rota 2>/dev/null | grep {$diskName}");
        if (!empty($lsblkOutput)) {
            if (strpos($lsblkOutput, ' 0') !== false) {
                return 'SSD';
            } elseif (strpos($lsblkOutput, ' 1') !== false) {
                return 'HDD';
            }
        }

        return 'Unknown - Device: ' . $diskName;
    }
}

function getDiskIOSpeedImproved($path = null)
{
    // Use videos directory by default
    if ($path === null) {
        $path = getVideosDir();
    }

    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    $result = array(
        'read' => 'N/A',
        'write' => 'N/A',
        'sequential' => 'N/A',
        'random' => 'N/A',
        'readSpeed' => 0,
        'writeSpeed' => 0,
        'sequentialSpeed' => 0,
        'randomSpeed' => 0
    );

    try {
        // Test 1: Sequential Write Speed (50MB file for more accuracy)
        $testFile = rtrim($path, '/') . '/speed_test_seq_' . uniqid() . '.tmp';
        $fileSize = 50 * 1024 * 1024; // 50MB
        $data = str_repeat('0123456789ABCDEF', 3276800); // 50MB

        $startTime = microtime(true);
        $handle = @fopen($testFile, 'wb');
        if ($handle) {
            fwrite($handle, $data);
            fflush($handle);
            fclose($handle);
            $writeTime = microtime(true) - $startTime;

            if ($writeTime > 0) {
                $writeMBps = $fileSize / (1024 * 1024) / $writeTime;
                $result['write'] = number_format($writeMBps, 2) . ' MB/s';
                $result['writeSpeed'] = $writeMBps;
            }
        }

        // Test 2: Sequential Read Speed
        if (file_exists($testFile)) {
            // Clear file cache (if possible) - Linux only
            if (!$isWindows && function_exists('shell_exec')) {
                @shell_exec('sync; echo 3 > /proc/sys/vm/drop_caches 2>/dev/null');
            }

            $startTime = microtime(true);
            $handle = @fopen($testFile, 'rb');
            if ($handle) {
                while (!feof($handle)) {
                    fread($handle, 8192);
                }
                fclose($handle);
                $readTime = microtime(true) - $startTime;

                if ($readTime > 0) {
                    $readMBps = $fileSize / (1024 * 1024) / $readTime;
                    $result['read'] = number_format($readMBps, 2) . ' MB/s';
                    $result['readSpeed'] = $readMBps;
                    $result['sequential'] = number_format($readMBps, 2) . ' MB/s';
                    $result['sequentialSpeed'] = $readMBps;
                }
            }

            @unlink($testFile);
        }

        // Test 3: Random I/O Speed (4KB blocks - more realistic for video serving)
        $testFile = rtrim($path, '/') . '/speed_test_rand_' . uniqid() . '.tmp';
        $blockSize = 4096; // 4KB blocks
        $numBlocks = 1000; // 4MB total
        $data = str_repeat('X', $blockSize);

        $startTime = microtime(true);
        $handle = @fopen($testFile, 'wb');
        if ($handle) {
            for ($i = 0; $i < $numBlocks; $i++) {
                fwrite($handle, $data);
                if ($i % 100 === 0) {
                    fflush($handle);
                }
            }
            fclose($handle);

            $randomWriteTime = microtime(true) - $startTime;

            // Random read
            $startTime = microtime(true);
            $handle = @fopen($testFile, 'rb');
            if ($handle) {
                for ($i = 0; $i < 500; $i++) {
                    $offset = rand(0, $numBlocks - 1) * $blockSize;
                    fseek($handle, $offset);
                    fread($handle, $blockSize);
                }
                fclose($handle);

                $randomReadTime = microtime(true) - $startTime;
                $avgRandomTime = ($randomWriteTime + $randomReadTime) / 2;

                if ($avgRandomTime > 0) {
                    $randomMBps = ($numBlocks * $blockSize) / (1024 * 1024) / $avgRandomTime;
                    $result['random'] = number_format($randomMBps, 2) . ' MB/s';
                    $result['randomSpeed'] = $randomMBps;
                }
            }

            @unlink($testFile);
        }

        // Calculate IOPS (Input/Output Operations Per Second) for random I/O
        if ($result['randomSpeed'] > 0) {
            $iops = ($result['randomSpeed'] * 1024 * 1024) / $blockSize;
            $result['iops'] = number_format($iops, 0) . ' IOPS';
        }

    } catch (Exception $e) {
        @unlink($testFile);
    }

    return $result;
}

function evaluateDiskPerformance($diskType, $readSpeed, $writeSpeed)
{
    $warnings = [];
    $recommendations = [];

    // Evaluate based on disk type
    if ($diskType === 'HDD') {
        $warnings[] = 'HDD detected - not optimal for video encoding';
        $recommendations[] = 'For video encoding and hosting, we strongly recommend upgrading to an SSD or M.2 NVMe drive. HDDs are 5-10x slower and will cause encoding bottlenecks.';
    } elseif ($diskType === 'Unknown') {
        $recommendations[] = 'Could not detect disk type. Ensure you are using SSD or M.2 NVMe for optimal performance.';
    }

    // Evaluate read/write speeds
    if ($readSpeed !== null && $readSpeed > 0) {
        if ($readSpeed < HealthCheckConfig::DISK_READ_CRITICAL) {
            $warnings[] = 'Disk read speed is critically low';
            $recommendations[] = sprintf(
                'Read speed below %d MB/s will cause slow video streaming and encoding. Minimum %d MB/s recommended for basic usage, %d+ MB/s for professional hosting.',
                HealthCheckConfig::DISK_READ_CRITICAL,
                HealthCheckConfig::DISK_READ_WARNING,
                HealthCheckConfig::DISK_READ_RECOMMENDED
            );
        } elseif ($readSpeed < HealthCheckConfig::DISK_READ_WARNING) {
            $recommendations[] = sprintf(
                'Read speed below %d MB/s may cause performance issues. Consider upgrading to a faster SSD.',
                HealthCheckConfig::DISK_READ_WARNING
            );
        }
    }

    if ($writeSpeed !== null && $writeSpeed > 0) {
        if ($writeSpeed < HealthCheckConfig::DISK_WRITE_CRITICAL) {
            $warnings[] = 'Disk write speed is critically low';
            $recommendations[] = sprintf(
                'Write speed below %d MB/s will cause very slow video encoding and uploads. Minimum %d MB/s recommended, %d+ MB/s for professional hosting.',
                HealthCheckConfig::DISK_WRITE_CRITICAL,
                HealthCheckConfig::DISK_WRITE_WARNING,
                HealthCheckConfig::DISK_WRITE_RECOMMENDED
            );
        } elseif ($writeSpeed < HealthCheckConfig::DISK_WRITE_WARNING) {
            $recommendations[] = sprintf(
                'Write speed below %d MB/s may cause encoding delays. Consider upgrading to a faster SSD or M.2 NVMe drive.',
                HealthCheckConfig::DISK_WRITE_WARNING
            );
        }
    }

    // Recommendations based on disk type
    if ($diskType === 'SSD' && $writeSpeed !== null && $writeSpeed < HealthCheckConfig::SSD_EXPECTED_SPEED) {
        $recommendations[] = sprintf(
            'Your SSD performance is below expected (%d MB/s). Check for: disk health issues, TRIM support, or SATA bottlenecks.',
            HealthCheckConfig::SSD_EXPECTED_SPEED
        );
    }

    if ($diskType === 'M.2 NVMe' && $writeSpeed !== null && $writeSpeed < HealthCheckConfig::NVME_EXPECTED_SPEED) {
        $recommendations[] = sprintf(
            'Your M.2 NVMe performance is below expected (should be %d+ MB/s). Check for: PCIe lane limitations, thermal throttling, or disk health issues.',
            HealthCheckConfig::NVME_OPTIMAL_SPEED
        );
    }

    return ['warnings' => $warnings, 'recommendations' => $recommendations];
}

// ========== INTERNET SPEED FUNCTIONS ==========

function getInternetSpeed()
{
    $result = array(
        'download' => 'N/A',
        'upload' => 'N/A',
        'ping' => 'N/A',
        'status' => 'error'
    );

    try {
        // Test ping first using a reliable endpoint
        $pingUrl = 'https://www.google.com/favicon.ico';
        $pingStart = microtime(true);
        $ch = curl_init($pingUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request for minimal data
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $pingEnd = microtime(true);
        curl_close($ch);

        $pingTime = ($pingEnd - $pingStart) * 1000; // Convert to milliseconds
        $result['ping'] = number_format($pingTime, 0) . ' ms';

        // Test download speed using a larger file (about 1-5MB)
        // Using multiple CDN options as fallback
        $downloadUrls = [
            'https://speed.cloudflare.com/__down?bytes=5000000', // Cloudflare 5MB
            'https://proof.ovh.net/files/1Mb.dat', // OVH 1MB
            'https://ash-speed.hetzner.com/1GB.bin' // Hetzner (we'll only download part of it)
        ];

        $downloadSuccess = false;
        foreach ($downloadUrls as $testUrl) {
            $ch = curl_init($testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            // Limit download to 5MB max to avoid long waits
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);

            $downloadedSize = 0;
            $startTime = microtime(true);

            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $download_size, $downloaded, $upload_size, $uploaded) use (&$downloadedSize) {
                $downloadedSize = $downloaded;
                // Stop after 5MB
                return ($downloaded > 5000000) ? 1 : 0;
            });

            $fileContent = curl_exec($ch);
            $endTime = microtime(true);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200 && $fileContent !== false) {
                $fileSize = strlen($fileContent);
                $timeTaken = $endTime - $startTime;

                if ($timeTaken > 0 && $fileSize > 0) {
                    // Calculate speed in Mbps
                    $speedBps = $fileSize / $timeTaken;
                    $speedMbps = ($speedBps * 8) / (1024 * 1024);
                    $result['download'] = number_format($speedMbps, 2) . ' Mbps';
                    $result['status'] = 'success';
                    $downloadSuccess = true;
                    break;
                }
            }
        }

        // Test upload speed - multiple methods with fallbacks
        $uploadResult = testUploadSpeed();
        if ($uploadResult !== false) {
            $result['upload'] = $uploadResult;
        } else {
            $result['upload'] = 'Test failed';
        }

    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['msg'] = $e->getMessage();
    }

    return $result;
}

function testUploadSpeed() {
    // Method 1: Try file upload simulation (most compatible)
    $uploadUrls = [
        'https://httpbin.org/post',
        'https://postman-echo.com/post',
        'https://httpbin.org/anything'
    ];

    // Create test data - 2MB for balance between speed and compatibility
    $uploadSize = 2 * 1024 * 1024; // 2MB

    foreach ($uploadUrls as $uploadUrl) {
        try {
            // Method A: Try with file upload multipart (most compatible)
            $ch = curl_init($uploadUrl);

            // Create a temporary file in memory
            $tmpFile = tmpfile();
            $tmpPath = stream_get_meta_data($tmpFile)['uri'];
            fwrite($tmpFile, str_repeat('X', $uploadSize));
            rewind($tmpFile);

            $postData = [
                'file' => new CURLFile($tmpPath, 'application/octet-stream', 'test.bin')
            ];

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $startTime = microtime(true);
            $response = curl_exec($ch);
            $endTime = microtime(true);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($tmpFile);

            if ($httpCode >= 200 && $httpCode < 300 && $response !== false) {
                $timeTaken = $endTime - $startTime;
                if ($timeTaken > 0.05) { // At least 50ms
                    $speedBps = $uploadSize / $timeTaken;
                    $speedMbps = ($speedBps * 8) / (1024 * 1024);
                    return number_format($speedMbps, 2) . ' Mbps';
                }
            }

            // Method B: Try with raw POST data
            $ch = curl_init($uploadUrl);
            $uploadData = str_repeat('0123456789ABCDEF', 131072); // 2MB

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream', 'Expect:']);

            $startTime = microtime(true);
            $response = curl_exec($ch);
            $endTime = microtime(true);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && $response !== false) {
                $timeTaken = $endTime - $startTime;
                if ($timeTaken > 0.05) {
                    $speedBps = strlen($uploadData) / $timeTaken;
                    $speedMbps = ($speedBps * 8) / (1024 * 1024);
                    return number_format($speedMbps, 2) . ' Mbps';
                }
            }

        } catch (Exception $e) {
            continue; // Try next URL
        }
    }

    return false;
}

function evaluateInternetSpeed($downloadSpeed, $uploadSpeed, $pingValue)
{
    $warnings = [];
    $recommendations = [];

    // Evaluate download speed (important for CDN delivery and user experience)
    if ($downloadSpeed < HealthCheckConfig::DOWNLOAD_CRITICAL) {
        $warnings[] = 'Download speed is critically low';
        $recommendations[] = sprintf(
            'Download speed below %d Mbps may cause buffering for users. Consider upgrading your connection or using a CDN.',
            HealthCheckConfig::DOWNLOAD_CRITICAL
        );
    } elseif ($downloadSpeed < HealthCheckConfig::DOWNLOAD_WARNING) {
        $warnings[] = 'Download speed is below optimal';
        $recommendations[] = sprintf(
            'For better video streaming performance, we recommend at least %d Mbps download speed.',
            HealthCheckConfig::DOWNLOAD_WARNING
        );
    }

    // Evaluate upload speed (critical for video uploads and live streaming)
    if ($uploadSpeed > 0 && $uploadSpeed < HealthCheckConfig::UPLOAD_CRITICAL) {
        $warnings[] = 'Upload speed is critically low for video hosting';
        $recommendations[] = sprintf(
            'Upload speed below %d Mbps will cause very slow video uploads and encoding queue delays. Minimum %d Mbps recommended for basic usage, %d+ Mbps for professional hosting.',
            HealthCheckConfig::UPLOAD_CRITICAL,
            HealthCheckConfig::UPLOAD_WARNING,
            HealthCheckConfig::UPLOAD_RECOMMENDED
        );
    } elseif ($uploadSpeed > 0 && $uploadSpeed < HealthCheckConfig::UPLOAD_WARNING) {
        $warnings[] = 'Upload speed is low for optimal video hosting';
        $recommendations[] = sprintf(
            'Upload speed below %d Mbps may cause slow video uploads. We recommend at least %d Mbps for smooth video hosting and live streaming.',
            HealthCheckConfig::UPLOAD_WARNING,
            HealthCheckConfig::UPLOAD_RECOMMENDED
        );
    } elseif ($uploadSpeed > 0 && $uploadSpeed < HealthCheckConfig::UPLOAD_RECOMMENDED) {
        $recommendations[] = sprintf(
            'For professional video hosting with multiple concurrent uploads, consider upgrading to %d+ Mbps upload speed.',
            HealthCheckConfig::UPLOAD_OPTIMAL
        );
    }

    // Evaluate ping/latency
    if ($pingValue > HealthCheckConfig::PING_WARNING) {
        $warnings[] = 'High latency detected';
        $recommendations[] = sprintf(
            'Ping above %dms may cause delays in live streaming and real-time features. Check your network connection.',
            HealthCheckConfig::PING_WARNING
        );
    } elseif ($pingValue > HealthCheckConfig::PING_GOOD) {
        $recommendations[] = sprintf(
            'Ping above %dms may affect live streaming quality. Consider optimizing your network connection.',
            HealthCheckConfig::PING_GOOD
        );
    }

    return ['warnings' => $warnings, 'recommendations' => $recommendations];
}

function getPerformanceLevel($download, $upload, $ping)
{
    // Determine overall performance level for AVideo hosting
    $score = 0;

    // Download score (max 30 points)
    if ($download >= HealthCheckConfig::DOWNLOAD_OPTIMAL) $score += 30;
    elseif ($download >= HealthCheckConfig::DOWNLOAD_WARNING) $score += 25;
    elseif ($download >= HealthCheckConfig::DOWNLOAD_CRITICAL) $score += 15;
    else $score += 5;

    // Upload score (max 50 points - most critical for video hosting)
    if ($upload >= HealthCheckConfig::UPLOAD_OPTIMAL) $score += 50;
    elseif ($upload >= HealthCheckConfig::UPLOAD_RECOMMENDED) $score += 40;
    elseif ($upload >= HealthCheckConfig::UPLOAD_WARNING) $score += 25;
    elseif ($upload >= HealthCheckConfig::UPLOAD_CRITICAL) $score += 10;
    else $score += 5;

    // Ping score (max 20 points)
    if ($ping <= HealthCheckConfig::PING_EXCELLENT) $score += 20;
    elseif ($ping <= HealthCheckConfig::PING_GOOD) $score += 15;
    elseif ($ping <= HealthCheckConfig::PING_WARNING) $score += 10;
    else $score += 5;

    // Categorize performance
    if ($score >= 85) return 'excellent';
    if ($score >= 65) return 'good';
    if ($score >= 40) return 'fair';
    return 'poor';
}
