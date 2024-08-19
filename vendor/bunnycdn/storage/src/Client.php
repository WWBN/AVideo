<?php

declare(strict_types=1);

namespace Bunny\Storage;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils as PromiseUtils;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private string $apiAccessKey;
    private string $storageZoneName;
    private string $baseUrl;
    private HttpClient $httpClient;

    public function __construct(
        string $apiKey,
        string $storageZoneName,
        string $storageZoneRegion = Region::FALKENSTEIN,
        ?HttpClient $httpClient = null
    ) {
        if (!isset(Region::LIST[$storageZoneRegion])) {
            throw new InvalidRegionException();
        }

        $this->apiAccessKey = $apiKey;
        $this->storageZoneName = $storageZoneName;
        $this->baseUrl = Region::getBaseUrl($storageZoneRegion);

        $this->httpClient = $httpClient ?? new HttpClient([
            'allow_redirects' => false,
            'http_errors' => false,
            'base_uri' => $this->baseUrl,
            'headers' => [
                'AccessKey' => $this->apiAccessKey,
            ],
        ]);
    }

    /**
     * @return FileInfo[]
     */
    public function listFiles(string $path): array
    {
        $response = $this->httpClient->request('GET', $this->normalizePath($path, true));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (200 === $response->getStatusCode()) {
            return $this->convertIntoFileInfo(json_decode($response->getBody()->getContents(), true));
        }

        throw new Exception('Could not list files');
    }

    public function delete(string $path): void
    {
        $isDirectory = str_ends_with($path, '/');
        $response = $this->httpClient->request('DELETE', $this->normalizePath($path, $isDirectory));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (200 === $response->getStatusCode()) {
            return;
        }

        /** @var bool|array{Message: string}|null $json */
        $json = json_decode($response->getBody()->getContents(), true);
        $message = 'Could not delete file';

        if (isset($json['Message']) && is_array($json) && is_string($json['Message'])) {
            $message = (string) $json['Message'];
        }

        if (404 === $response->getStatusCode()) {
            throw new FileNotFoundException($path);
        }

        throw new Exception($message);
    }

    public function putContents(string $path, string $contents, bool $withChecksum = true): void
    {
        $headers = [];
        if ($withChecksum) {
            $headers['Checksum'] = strtoupper(hash('sha256', $contents));
        }

        $promise = $this->makeUploadRequest($path, ['headers' => $headers, 'body' => $contents]);
        $promise->wait();
    }

    public function upload(string $localPath, string $path, bool $withChecksum = true): void
    {
        $promise = $this->uploadWithOptions($localPath, $path, $withChecksum);
        $promise->wait();
    }

    public function uploadAsync(string $localPath, string $path, bool $withChecksum = true): PromiseInterface
    {
        return $this->uploadWithOptions($localPath, $path, $withChecksum);
    }

    private function uploadWithOptions(string $localPath, string $path, bool $withChecksum): PromiseInterface
    {
        $fileStream = fopen($localPath, 'r');
        if (false === $fileStream) {
            throw new Exception('The local file could not be opened.');
        }

        $headers = [];
        if ($withChecksum) {
            $hash = hash_file('sha256', $localPath);
            if (false !== $hash) {
                $headers['Checksum'] = strtoupper($hash);
            }
        }

        return $this->makeUploadRequest($path, ['headers' => $headers, 'body' => $fileStream]);
    }

    /**
     * @param array{headers: array<array-key, mixed>, body: mixed} $options
     */
    private function makeUploadRequest(string $path, array $options): PromiseInterface
    {
        $response = $this->httpClient->requestAsync('PUT', $this->normalizePath($path), $options);

        return $response->then(function (ResponseInterface $response) {
            if (401 === $response->getStatusCode()) {
                throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
            }

            if (400 === $response->getStatusCode()) {
                throw new Exception('Checksum and file contents mismatched');
            }

            if (201 === $response->getStatusCode()) {
                return;
            }

            throw new Exception('Could not upload file');
        });
    }

    public function getContents(string $path): string
    {
        $response = $this->httpClient->request('GET', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (404 === $response->getStatusCode()) {
            throw new FileNotFoundException($path);
        }

        if (200 === $response->getStatusCode()) {
            return $response->getBody()->getContents();
        }

        throw new Exception('Could not download file');
    }

    public function download(string $path, string $localPath): void
    {
        $result = file_put_contents($localPath, $this->getContents($path));

        if (false === $result) {
            throw new Exception('The local file could not be opened for writing.');
        }
    }

    public function exists(string $path): bool
    {
        $response = $this->httpClient->request('DESCRIBE', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (404 === $response->getStatusCode()) {
            return false;
        }

        if (200 !== $response->getStatusCode()) {
            throw new Exception('Could not verify if the file exists');
        }

        $metadata = json_decode($response->getBody()->getContents(), true);
        if (!is_array($metadata)) {
            return false;
        }

        return isset($metadata['Guid']) && 36 === strlen($metadata['Guid']);
    }

    public function info(string $path): FileInfo
    {
        if (str_ends_with($path, '/')) {
            throw new \Exception('Directories are not supported.');
        }

        $response = $this->httpClient->request('DESCRIBE', $this->normalizePath($path));

        if (401 === $response->getStatusCode()) {
            throw new AuthenticationException($this->storageZoneName, $this->apiAccessKey);
        }

        if (404 === $response->getStatusCode()) {
            throw new FileNotFoundException($path);
        }

        if (200 !== $response->getStatusCode()) {
            throw new Exception('Could not verify if the file exists');
        }

        $metadata = json_decode($response->getBody()->getContents(), true);
        if (!is_array($metadata)) {
            throw new Exception('Could not parse the JSON response');
        }

        return $this->createFileInfo($metadata);
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

    /**
     * @param string[] $to_delete
     *
     * @return array<string, string>
     */
    public function deleteMultiple(array $to_delete): array
    {
        $requests = [];

        foreach ($to_delete as $path) {
            $isDirectory = str_ends_with($path, '/');
            $requests[$path] = $this->httpClient->requestAsync('DELETE', $this->normalizePath($path, $isDirectory));
        }

        $results = PromiseUtils::unwrap($requests);
        $errors = [];

        /** @var ResponseInterface $response */
        foreach ($results as $path => $response) {
            if (200 !== $response->getStatusCode()) {
                $data = json_decode($response->getBody()->getContents(), true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    if (is_array($data) && isset($data['Message'])) {
                        $errors[$path] = $data['Message'];
                        continue;
                    }
                }

                $errors[$path] = $response->getReasonPhrase();
            }
        }

        return $errors;
    }

    /**
     * @phpstan-param mixed $result
     *
     * @return FileInfo[]
     */
    private function convertIntoFileInfo($result): array
    {
        if (!is_array($result)) {
            return [];
        }

        $items = [];

        foreach ($result as $info) {
            $items[] = $this->createFileInfo($info);
        }

        return $items;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function createFileInfo(array $data): FileInfo
    {
        $path = $data['ObjectName'].'/'.$data['Path'];

        foreach (['DateCreated', 'LastChanged'] as $field) {
            $value = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.v', $data[$field]);

            if (false === $value) {
                $value = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $data[$field]);
            }

            if (false === $value) {
                throw new Exception('Invalid '.$field.' for file '.$path);
            }

            $data[$field] = $value;
        }

        return new FileInfo($data['Guid'], $data['Path'], $data['ObjectName'], $data['Length'], $data['IsDirectory'], \strtolower((string) $data['Checksum']), $data['DateCreated'], $data['LastChanged']);
    }
}
