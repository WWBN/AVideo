<?php

// New implementation: no single-use proof-key grant exists in user.php/functionsSecurity.php.
class LocalEncoderLogin
{
    const TTL = 120;
    private $directory;

    public function __construct($directory = null)
    {
        global $global;
        $this->directory = $directory ?? (rtrim(sys_get_temp_dir(), '/\\') . '/avideo-local-login-' . hash('sha256', $global['systemRootPath']));
    }

    public function issue($usersId, $passwordVersion, $challenge)
    {
        if ((int) $usersId <= 0 || !is_string($challenge) || !preg_match('/^[A-Za-z0-9_-]{43}$/D', $challenge)) {
            throw new InvalidArgumentException('Invalid Local Encoder authorization request');
        }
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Cannot prepare Local Encoder authorization');
        }
        // Expired and consumed grants contain no reusable credentials. Bound cleanup work per request.
        $checked = 0;
        foreach (new DirectoryIterator($this->directory) as $entry) {
            if (++$checked > 500) { break; }
            if ($entry->isFile() && preg_match('/^[a-f0-9]{64}\.json$/D', $entry->getFilename()) && $entry->getMTime() < time() - self::TTL) {
                if (!unlink($entry->getPathname())) { throw new RuntimeException('Cannot remove expired authorization'); }
            }
        }
        $code = bin2hex(random_bytes(32));
        $file = fopen($this->path($code), 'x+b');
        if ($file === false) { throw new RuntimeException('Cannot create Local Encoder authorization'); }
        try {
            chmod($this->path($code), 0600);
            $data = json_encode(array('users_id' => (int) $usersId, 'password_version' => $passwordVersion,
                'challenge' => $challenge, 'expires' => time() + self::TTL));
            if ($data === false || fwrite($file, $data) !== strlen($data)) {
                throw new RuntimeException('Cannot save Local Encoder authorization');
            }
        } finally { fclose($file); }
        return $code;
    }

    public function consume($code, $verifier)
    {
        if (!is_string($code) || !preg_match('/^[a-f0-9]{64}$/D', $code)
            || !is_string($verifier) || !preg_match('/^[A-Za-z0-9_-]{43}$/D', $verifier)) {
            return false;
        }
        $file = @fopen($this->path($code), 'r+b');
        if ($file === false) { return false; }
        try {
            if (!flock($file, LOCK_EX)) { throw new RuntimeException('Cannot lock Local Encoder authorization'); }
            $data = json_decode(stream_get_contents($file), true);
            $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
            if (!is_array($data) || ($data['expires'] ?? 0) <= time()
                || !is_string($data['challenge'] ?? null) || !hash_equals($data['challenge'], $challenge)) {
                return false;
            }
            // Keep the locked inode: a simultaneous redemption must observe the consumed record.
            if (!ftruncate($file, 0) || !fflush($file)) { throw new RuntimeException('Cannot consume Local Encoder authorization'); }
            return $data;
        } finally { fclose($file); }
    }

    private function path($code)
    {
        return $this->directory . '/' . hash('sha256', $code) . '.json';
    }
}
