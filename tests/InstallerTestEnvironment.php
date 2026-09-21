<?php

/** Disposable installer files and PHP processes; never loads a real site configuration. */
class InstallerTestEnvironment
{
    public $root;
    private $processes = [];

    public function __construct($source)
    {
        $directory = sys_get_temp_dir() . '/avideo-installer-phpunit-' . bin2hex(random_bytes(8));
        if (!mkdir($directory, 0700)) { throw new RuntimeException('Cannot create installer test directory.'); }
        $this->root = realpath($directory);
        $this->copyDirectory($source . '/install', 'install');
    }

    public function path($relative)
    {
        if (preg_match('~(^|[/\\\\])\.\.([/\\\\]|$)|^[\\\\/]|:~', $relative)) {
            throw new InvalidArgumentException('Test paths must stay inside the temporary directory.');
        }
        return $this->root . '/' . $relative;
    }

    public function write($relative, $content)
    {
        $path = $this->path($relative);
        if (!is_dir(dirname($path))) { mkdir(dirname($path), 0700, true); }
        if (file_put_contents($path, $content) === false) { throw new RuntimeException('Cannot write installer test fixture.'); }
    }

    public function copyDirectory($source, $relative)
    {
        $destination = $this->path($relative);
        if (!is_dir($destination)) { mkdir($destination, 0700, true); }
        foreach (new DirectoryIterator($source) as $entry) {
            if ($entry->isDot()) { continue; }
            if ($entry->isLink()) { throw new RuntimeException('Installer fixtures must not contain symlinks.'); }
            $target = $relative . '/' . $entry->getFilename();
            if ($entry->isDir()) { $this->copyDirectory($entry->getPathname(), $target); }
            else { $this->write($target, file_get_contents($entry->getPathname())); }
        }
    }

    public function prepareStreamer($source)
    {
        $this->write('objects/bcp47.php', file_get_contents($source . '/objects/bcp47.php'));
        foreach (glob($source . '/locale/*.php') as $file) { $this->write('locale/' . basename($file), '<?php'); }
        foreach (['vendor/autoload.php', 'vendor/erusev/parsedown/Parsedown.php', 'node_modules/jquery/dist/jquery.min.js',
            'objects/include_config.php', 'index.php', 'install/installPluginsTables.php'] as $file) {
            $this->write($file, '<?php');
        }
        mkdir($this->path('videos'), 0700);
    }

    public function prepareEncoder()
    {
        $this->write('objects/include_config.php', "<?php \$global['mysqli'] = new mysqli(\$mysqlHost,\$mysqlUser,\$mysqlPass,\$mysqlDatabase,(int)\$mysqlPort); echo \$global['mysqli']->host_info;");
        foreach (['jquery/dist/jquery.min.js', 'bootstrap/dist/js/bootstrap.min.js', 'bootstrap/dist/css/bootstrap.min.css'] as $asset) {
            $this->write('node_modules/' . $asset, '');
        }
        mkdir($this->path('videos'), 0700);
        mkdir($this->path('objects'), 0700);
    }

    private function command(array $arguments)
    {
        $extra = json_decode(getenv('AVIDEO_TEST_PHP_ARGS') ?: '[]', true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($extra) || array_filter($extra, function ($value) { return !is_string($value); })) {
            throw new InvalidArgumentException('AVIDEO_TEST_PHP_ARGS must be a JSON array of PHP CLI arguments.');
        }
        return array_merge([PHP_BINARY], $extra, $arguments);
    }

    public function run(array $arguments, $input = '', array $environment = [])
    {
        $variables = getenv();
        foreach ($environment as $key => $value) {
            if ($value === null) { unset($variables[$key]); }
            else { $variables[$key] = (string) $value; }
        }
        $process = proc_open($this->command($arguments), [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $this->root, $variables);
        if (!is_resource($process)) { throw new RuntimeException('Cannot start PHP test process.'); }
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return ['exit' => proc_close($process), 'stdout' => $stdout, 'stderr' => $stderr];
    }

    public function startServer()
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $error, $message);
        if (!$socket) { throw new RuntimeException('Cannot reserve a test HTTP port.'); }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $process = proc_open($this->command(['-S', $address, '-t', $this->root]),
            [0 => ['pipe', 'r'], 1 => ['file', $this->path('server.log'), 'a'], 2 => ['file', $this->path('server.log'), 'a']], $pipes, $this->root);
        if (!is_resource($process)) { throw new RuntimeException('Cannot start PHP HTTP server.'); }
        fclose($pipes[0]);
        $this->processes[] = $process;
        return 'http://' . $address . '/install/';
    }

    public function startRouter($relative)
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $error, $message);
        if (!$socket) { throw new RuntimeException('Cannot reserve a test HTTP port.'); }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $process = proc_open($this->command(['-S', $address, $this->path($relative)]),
            [0 => ['pipe', 'r'], 1 => ['file', $this->path('router.log'), 'a'], 2 => ['file', $this->path('router.log'), 'a']], $pipes, $this->root);
        if (!is_resource($process)) { throw new RuntimeException('Cannot start PHP HTTP router.'); }
        fclose($pipes[0]);
        $this->processes[] = $process;
        return 'http://' . $address . '/';
    }

    public function cleanup()
    {
        foreach ($this->processes as $process) { proc_terminate($process); proc_close($process); }
        $this->processes = [];
        if (!$this->root || !is_dir($this->root)) { return; }
        // Validate the absolute deletion target and never follow symlinks during cleanup.
        if (realpath($this->root) !== $this->root || dirname($this->root) !== realpath(sys_get_temp_dir()) ||
            !preg_match('/^avideo-installer-phpunit-[a-f0-9]{16}$/D', basename($this->root))) {
            throw new RuntimeException('Refusing to remove a directory outside the installer test fixture.');
        }
        $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($entries as $entry) {
            if ($entry->isDir() && !$entry->isLink()) { rmdir($entry->getPathname()); }
            else { unlink($entry->getPathname()); }
        }
        rmdir($this->root);
        $this->root = null;
    }
}
