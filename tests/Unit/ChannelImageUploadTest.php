<?php

namespace Tests\ChannelImages;

use PHPUnit\Framework\TestCase;

// Load the real image validation helpers without bootstrapping an installation.
$channelValidationSource = file_get_contents(\APP_ROOT . '/objects/functionsImages.php');
$channelValidationStart = strpos($channelValidationSource, 'function normalizeBase64ImageData(');
$channelValidationEnd = strpos($channelValidationSource, 'function saveBase64DataToPNGImage(', $channelValidationStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($channelValidationSource, $channelValidationStart, $channelValidationEnd - $channelValidationStart));
eval('namespace ' . __NAMESPACE__ . '; use \InvalidArgumentException; use \RuntimeException; use \Throwable; ' . substr(file_get_contents(\APP_ROOT . '/objects/channelImageUpload.php'), 5));

function __($message) { return $message; }
function make_path($path) { return is_dir($path) || mkdir($path, 0700, true); }
function rename($source, $destination)
{
    if (($GLOBALS['channelImageRenameFailure'] ?? '') === $destination) {
        $GLOBALS['channelImageRenameFailure'] = '';
        return false;
    }
    return \rename($source, $destination);
}

class ChannelImageUploadTest extends TestCase
{
    private $directory;
    private $previousPost;

    protected function setUp(): void
    {
        if (!extension_loaded('gd')) { $this->markTestSkipped('GD is required for channel images.'); }
        $this->directory = sys_get_temp_dir() . '/avideo-channel-image-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        $this->previousPost = $_POST;
        $GLOBALS['channelImageRenameFailure'] = '';
    }

    protected function tearDown(): void
    {
        $_POST = $this->previousPost;
        unset($GLOBALS['channelImageRenameFailure']);
        if (!$this->directory) { return; }
        foreach (glob($this->directory . '/{,.}*', GLOB_BRACE) as $path) {
            if (is_file($path)) { unlink($path); }
        }
        if (is_dir($this->directory . '/blocked.jpg')) { rmdir($this->directory . '/blocked.jpg'); }
        rmdir($this->directory);
    }

    private function imageInput($format = 'png', $transparent = false)
    {
        $image = imagecreatetruecolor(256, 144);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 0, 0, $transparent ? 127 : 0));
        imagefilledrectangle($image, 70, 0, 185, 143, imagecolorallocate($image, 0, 200, 0));
        ob_start();
        $writer = 'image' . $format;
        $writer($image);
        $data = ob_get_clean();
        if (is_resource($image)) { imagedestroy($image); }
        $_POST['image'] = 'data:image/' . $format . ';base64,' . base64_encode($data);
    }

    public function testFirstCoverCreatesAllCorrectlyEncodedCenteredVariants()
    {
        $this->imageInput();
        $variants = [];
        foreach (['tv' => [2550, 1440], 'desktop_max' => [2550, 423], 'tablet' => [1855, 423], 'desktop_min' => [1546, 423]] as $name => $size) {
            $variants[] = [$this->directory . '/' . $name . '.jpg', $size[0], $size[1]];
        }
        $this->assertTrue(saveChannelImageUpload($this->directory . '/background.jpg', 2560, 1440, $variants));
        foreach (array_merge([[$this->directory . '/background.jpg', 2560, 1440]], $variants) as $variant) {
            $info = getimagesize($variant[0]);
            $this->assertSame([$variant[1], $variant[2], IMAGETYPE_JPEG], array_slice($info, 0, 3));
            $image = imagecreatefromjpeg($variant[0]);
            $center = imagecolorsforindex($image, imagecolorat($image, (int) ($variant[1] / 2), (int) ($variant[2] / 2)));
            $this->assertGreaterThan(180, $center['green']);
            if (is_resource($image)) { imagedestroy($image); }
        }
    }

    public function testPhotoRemainsTransparentPNGAndIsSquare()
    {
        $this->imageInput('png', true);
        $path = $this->directory . '/photo.png';
        saveChannelImageUpload($path, 150, 150);
        $this->assertSame([150, 150, IMAGETYPE_PNG], array_slice(getimagesize($path), 0, 3));
        $image = imagecreatefrompng($path);
        $this->assertSame(127, imagecolorsforindex($image, imagecolorat($image, 0, 0))['alpha']);
        if (is_resource($image)) { imagedestroy($image); }
    }

    public function testTransparentCoverUsesWhiteInsteadOfBlack()
    {
        $this->imageInput('png', true);
        $path = $this->directory . '/background.jpg';
        saveChannelImageUpload($path, 256, 144);
        $image = imagecreatefromjpeg($path);
        $pixel = imagecolorsforindex($image, imagecolorat($image, 0, 0));
        $this->assertGreaterThan(245, min($pixel['red'], $pixel['green'], $pixel['blue']));
        if (is_resource($image)) { imagedestroy($image); }
    }

    /** @dataProvider inputFormats */
    public function testSupportedRasterInputsAreNormalized($format)
    {
        if (!function_exists('image' . $format)) { $this->markTestSkipped($format . ' unavailable'); }
        $this->imageInput($format);
        $path = $this->directory . '/photo.png';
        $this->assertTrue(saveChannelImageUpload($path, 150, 150));
        $this->assertSame(IMAGETYPE_PNG, getimagesize($path)[2]);
    }

    public function inputFormats() { return [['jpeg'], ['png'], ['webp'], ['gif']]; }

    /** @dataProvider invalidInputs */
    public function testInvalidImagesLeaveExistingFileUntouched($input)
    {
        $path = $this->directory . '/photo.png';
        file_put_contents($path, 'original');
        $_POST['image'] = $input;
        try {
            saveChannelImageUpload($path, 150, 150);
            $this->fail('Invalid image accepted');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('original', file_get_contents($path));
        }
    }

    public function invalidInputs() { return [[null], [[]], [''], ['not an image'], ['data:image/png;base64,' . base64_encode('not an image')]]; }

    public function testFailedVariantDoesNotReplaceOriginalOrLeaveStagedFiles()
    {
        $this->imageInput();
        $path = $this->directory . '/background.jpg';
        file_put_contents($path, 'original');
        mkdir($this->directory . '/blocked.jpg');
        try {
            saveChannelImageUpload($path, 256, 144, [[$this->directory . '/blocked.jpg', 100, 100]]);
            $this->fail('Invalid destination accepted');
        } catch (\RuntimeException $exception) {
            $this->assertSame('original', file_get_contents($path));
            $this->assertSame([], glob($this->directory . '/.channel-image-*'));
        }
    }

    /** @dataProvider existingDestinations */
    public function testCommitFailureRestoresEveryPreviouslyInstalledImage($existing)
    {
        $this->imageInput();
        $path = $this->directory . '/background.jpg';
        $variation = $this->directory . '/desktop.jpg';
        if ($existing) { file_put_contents($path, 'original cover'); }
        file_put_contents($variation, 'original crop');
        $GLOBALS['channelImageRenameFailure'] = $variation;
        try {
            saveChannelImageUpload($path, 256, 144, [[$variation, 100, 100]]);
            $this->fail('Failed rename accepted');
        } catch (\RuntimeException $exception) {
            if ($existing) { $this->assertSame('original cover', file_get_contents($path)); }
            else { $this->assertFileDoesNotExist($path); }
            $this->assertSame('original crop', file_get_contents($variation));
            $this->assertSame([], glob($this->directory . '/.channel-image-*'));
            $this->assertSame([], glob($this->directory . '/.channel-backup-*'));
        }
    }

    public function existingDestinations() { return [[true], [false]]; }

    public function testAccountSaveFailureRestoresExistingPhoto()
    {
        $this->imageInput();
        $path = $this->directory . '/photo.png';
        file_put_contents($path, 'original photo');
        try {
            saveChannelImageUpload($path, 150, 150, [], 'image', function () { return false; });
            $this->fail('Failed account update accepted');
        } catch (\RuntimeException $exception) {
            $this->assertSame('original photo', file_get_contents($path));
            $this->assertSame([], glob($this->directory . '/.channel-backup-*'));
        }
    }
}
