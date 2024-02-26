<?php

declare(strict_types=1);

namespace Bunny\Storage;

class Client
{
    private const DEFAULT_STORAGE_ZONE = 'de';

    private string $apiAccessKey;
    private string $storageZoneName;
    private string $storageZoneRegion;

    public function __construct(
        string $apiKey,
        string $storageZoneName,
        string $storageZoneRegion = self::DEFAULT_STORAGE_ZONE,
    ) {
        $this->apiAccessKey = $apiKey;
        $this->storageZoneRegion = $storageZoneRegion;
        $this->storageZoneName = $storageZoneName;
    }

    public function listFiles(string $path): mixed
    {
        $normalizedPath = $this->normalizePath($path, true);

        return json_decode($this->sendHttpRequest($normalizedPath));
    }

    public function delete(string $path): mixed
    {
        $normalizedPath = $this->normalizePath($path);

        return $this->sendHttpRequest($normalizedPath, 'DELETE');
    }

    public function upload(string $localPath, string $path): string
    {
        $fileStream = fopen($localPath, 'r');
        if (false === $fileStream) {
            throw new Exception('The local file could not be opened.');
        }

        $dataLength = filesize($localPath);
        if (false === $dataLength) {
            throw new Exception('Local file not found: '.$localPath);
        }

        $normalizedPath = $this->normalizePath($path);

        return $this->sendHttpRequest($normalizedPath, 'PUT', $fileStream, $dataLength);
    }

    public function download(string $path, string $localPath): string
    {
        $fileStream = fopen($localPath, 'w+');
        if (false === $fileStream) {
            throw new Exception('The local file could not be opened for writing.');
        }

        $normalizedPath = $this->normalizePath($path);

        return $this->sendHttpRequest($normalizedPath, 'GET', null, null, $fileStream);
    }

    public function exists(string $path): bool
    {
        try {
            $result = $this->sendHttpRequest($path, 'DESCRIBE');
            if ('' === $result) {
                return false;
            }

            $metadata = json_decode($result, true);
            if (!is_array($metadata)) {
                return false;
            }

            return isset($metadata['Guid']) && 36 === strlen($metadata['Guid']);
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param resource $uploadFile
     * @param resource $downloadFileHandler
     */
    private function sendHttpRequest(string $url, string $method = 'GET', $uploadFile = null, int $uploadFileSize = null, $downloadFileHandler = null): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getBaseUrl().$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: '.$this->apiAccessKey]);

        if ('PUT' === $method && null !== $uploadFile) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_INFILE, $uploadFile);
            curl_setopt($ch, CURLOPT_INFILESIZE, $uploadFileSize);
        } elseif ('GET' !== $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ('GET' === $method && null !== $downloadFileHandler) {
            curl_setopt($ch, CURLOPT_FILE, $downloadFileHandler);
        }

        /** @var string|false $output */
        $output = curl_exec($ch);
        $curlError = curl_errno($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (false === $output) {
            throw new Exception('An unknown error has occurred during the request. Status code: '.$curlError);
        }

        if (404 === $responseCode) {
            throw new FileNotFoundException($url);
        } elseif (401 === $responseCode) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        } elseif ($responseCode < 200 || $responseCode > 299) {
            throw new Exception('An unknown error has occurred during the request. Status code: '.$responseCode);
        }

        return $output;
    }

    private function normalizePath(string $path, bool $isDirectory = false): string
    {
        if (!str_starts_with($path, "/{$this->storageZoneName}/") && !str_starts_with($path, "{$this->storageZoneName}/")) {
            $path = "{$this->storageZoneName}/".$path;
        }

        $path = str_replace('\\', '/', $path);

        if (!$isDirectory && '/' !== $path && str_ends_with($path, '/')) {
            throw new Exception('The requested path is invalid.');
        }

        // Remove double slashes
        while (str_contains($path, '//')) {
            $path = str_replace('//', '/', $path);
        }

        $path = ltrim($path, '/');

        if ($isDirectory) {
            $path = $path.'/';
        }

        return $path;
    }

    private function getBaseUrl(): string
    {
        if (self::DEFAULT_STORAGE_ZONE == $this->storageZoneRegion || '' == $this->storageZoneRegion) {
            return 'https://storage.bunnycdn.com/';
        } else {
            return "https://{$this->storageZoneRegion}.storage.bunnycdn.com/";
        }
    }
}
