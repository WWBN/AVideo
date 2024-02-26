<?php

namespace TusPhp\Tus;

use TusPhp\File;
use Carbon\Carbon;
use TusPhp\Config;
use Ramsey\Uuid\Uuid;
use TusPhp\Exception\TusException;
use TusPhp\Exception\FileException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use TusPhp\Exception\ConnectionException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Client extends AbstractTus
{
    /** @var GuzzleClient */
    protected $client;

    /** @var string */
    protected $filePath;

    /** @var int */
    protected $fileSize = 0;

    /** @var string */
    protected $fileName;

    /** @var string */
    protected $key;

    /** @var string */
    protected $url;

    /** @var string */
    protected $checksum;

    /** @var int */
    protected $partialOffset = -1;

    /** @var bool */
    protected $partial = false;

    /** @var string */
    protected $checksumAlgorithm = 'sha256';

    /** @var array */
    protected $metadata = [];

    /** @var array */
    protected $headers = [];

    /**
     * Client constructor.
     *
     * @param string $baseUri
     * @param array  $options
     *
     * @throws \ReflectionException
     */
    public function __construct(string $baseUri, array $options = [])
    {
        $this->headers      = $options['headers'] ?? [];
        $options['headers'] = [
            'Tus-Resumable' => self::TUS_PROTOCOL_VERSION,
        ] + ($this->headers);

        $this->client = new GuzzleClient(
            ['base_uri' => $baseUri] + $options
        );

        Config::set(__DIR__ . '/../Config/client.php');

        $this->setCache('file');
    }

    /**
     * Set file properties.
     *
     * @param string      $file File path.
     * @param string|null $name File name.
     *
     * @return Client
     */
    public function file(string $file, string $name = null): self
    {
        $this->filePath = $file;

        if ( ! file_exists($file) || ! is_readable($file)) {
            throw new FileException('Cannot read file: ' . $file);
        }

        $this->fileName = $name ?? basename($this->filePath);
        $this->fileSize = filesize($file);

        $this->addMetadata('filename', $this->fileName);

        return $this;
    }

    /**
     * Get file path.
     *
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * Set file name.
     *
     * @param string $name
     *
     * @return Client
     */
    public function setFileName(string $name): self
    {
        $this->addMetadata('filename', $this->fileName = $name);

        return $this;
    }

    /**
     * Get file name.
     *
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * Get file size.
     *
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * Get guzzle client.
     *
     * @return GuzzleClient
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * Set checksum.
     *
     * @param string $checksum
     *
     * @return Client
     */
    public function setChecksum(string $checksum): self
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum.
     *
     * @return string
     */
    public function getChecksum(): string
    {
        if (empty($this->checksum)) {
            $this->setChecksum(hash_file($this->getChecksumAlgorithm(), $this->getFilePath()));
        }

        return $this->checksum;
    }

    /**
     * Add metadata.
     *
     * @param string $key
     * @param string $value
     *
     * @return Client
     */
    public function addMetadata(string $key, string $value): self
    {
        $this->metadata[$key] = base64_encode($value);

        return $this;
    }

    /**
     * Remove metadata.
     *
     * @param string $key
     *
     * @return Client
     */
    public function removeMetadata(string $key): self
    {
        unset($this->metadata[$key]);

        return $this;
    }

    /**
     * Set metadata.
     *
     * @param array $items
     *
     * @return Client
     */
    public function setMetadata(array $items): self
    {
        $items = array_map('base64_encode', $items);

        $this->metadata = $items;

        return $this;
    }

    /**
     * Get metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get metadata for Upload-Metadata header.
     *
     * @return string
     */
    protected function getUploadMetadataHeader(): string
    {
        $metadata = [];

        foreach ($this->getMetadata() as $key => $value) {
            $metadata[] = "{$key} {$value}";
        }

        return implode(',', $metadata);
    }

    /**
     * Set key.
     *
     * @param string $key
     *
     * @return Client
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        $this->url = $this->getCache()->get($this->getKey())['location'] ?? null;

        if ( ! $this->url) {
            throw new FileException('File not found.');
        }

        return $this->url;
    }

    /**
     * Set checksum algorithm.
     *
     * @param string $algorithm
     *
     * @return Client
     */
    public function setChecksumAlgorithm(string $algorithm): self
    {
        $this->checksumAlgorithm = $algorithm;

        return $this;
    }

    /**
     * Get checksum algorithm.
     *
     * @return string
     */
    public function getChecksumAlgorithm(): string
    {
        return $this->checksumAlgorithm;
    }

    /**
     * Check if current upload is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $expiresAt = $this->getCache()->get($this->getKey())['expires_at'] ?? null;

        return empty($expiresAt) || Carbon::parse($expiresAt)->lt(Carbon::now());
    }

    /**
     * Check if this is a partial upload request.
     *
     * @return bool
     */
    public function isPartial(): bool
    {
        return $this->partial;
    }

    /**
     * Get partial offset.
     *
     * @return int
     */
    public function getPartialOffset(): int
    {
        return $this->partialOffset;
    }

    /**
     * Set offset and force this to be a partial upload request.
     *
     * @param int $offset
     *
     * @return self
     */
    public function seek(int $offset): self
    {
        $this->partialOffset = $offset;

        $this->partial();

        return $this;
    }

    /**
     * Upload file.
     *
     * @param int $bytes Bytes to upload
     *
     * @throws TusException
     * @throws GuzzleException
     * @throws ConnectionException
     *
     * @return int
     */
    public function upload(int $bytes = -1): int
    {
        $bytes  = $bytes < 0 ? $this->getFileSize() : $bytes;
        $offset = $this->partialOffset < 0 ? 0 : $this->partialOffset;

        try {
            // Check if this upload exists with HEAD request.
            $offset = $this->sendHeadRequest();
        } catch (FileException | ClientException $e) {
            // Create a new upload.
            $this->url = $this->create($this->getKey());
        } catch (ConnectException $e) {
            throw new ConnectionException("Couldn't connect to server.");
        }

        // Verify that upload is not yet expired.
        if ($this->isExpired()) {
            throw new TusException('Upload expired.');
        }

        // Now, resume upload with PATCH request.
        return $this->sendPatchRequest($bytes, $offset);
    }

    /**
     * Returns offset if file is partially uploaded.
     *
     * @throws GuzzleException
     *
     * @return bool|int
     */
    public function getOffset()
    {
        try {
            $offset = $this->sendHeadRequest();
        } catch (FileException | ClientException $e) {
            return false;
        }

        return $offset;
    }

    /**
     * Create resource with POST request.
     *
     * @param string $key
     *
     * @throws FileException
     * @throws GuzzleException
     *
     * @return string
     */
    public function create(string $key): string
    {
        return $this->createWithUpload($key, 0)['location'];
    }

    /**
     * Create resource with POST request and upload data using the creation-with-upload extension.
     *
     * @see https://tus.io/protocols/resumable-upload.html#creation-with-upload
     *
     * @param string $key
     * @param int    $bytes -1 => all data; 0 => no data
     *
     * @throws GuzzleException
     *
     * @return array [
     *     'location' => string,
     *     'offset' => int
     * ]
     */
    public function createWithUpload(string $key, int $bytes = -1): array
    {
        $bytes = $bytes < 0 ? $this->fileSize : $bytes;

        $headers = $this->headers + [
            'Upload-Length' => $this->fileSize,
            'Upload-Key' => $key,
            'Upload-Checksum' => $this->getUploadChecksumHeader(),
            'Upload-Metadata' => $this->getUploadMetadataHeader(),
        ];

        $data = '';
        if ($bytes > 0) {
            $data = $this->getData(0, $bytes);

            $headers += [
                'Content-Type' => self::HEADER_CONTENT_TYPE,
                'Content-Length' => \strlen($data),
            ];
        }

        if ($this->isPartial()) {
            $headers += ['Upload-Concat' => 'partial'];
        }

        try {
            $response = $this->getClient()->post($this->apiPath, [
                'body' => $data,
                'headers' => $headers,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        $statusCode = $response->getStatusCode();

        if (HttpResponse::HTTP_CREATED !== $statusCode) {
            throw new FileException('Unable to create resource.');
        }

        $uploadOffset   = $bytes > 0 ? current($response->getHeader('upload-offset')) : 0;
        $uploadLocation = current($response->getHeader('location'));

        $this->getCache()->set($this->getKey(), [
            'location' => $uploadLocation,
            'expires_at' => Carbon::now()->addSeconds($this->getCache()->getTtl())->format($this->getCache()::RFC_7231),
        ]);

        return [
            'location' => $uploadLocation,
            'offset' => $uploadOffset,
        ];
    }

    /**
     * Concatenate 2 or more partial uploads.
     *
     * @param string $key
     * @param mixed  $partials
     *
     * @throws GuzzleException
     *
     * @return string
     */
    public function concat(string $key, ...$partials): string
    {
        $response = $this->getClient()->post($this->apiPath, [
            'headers' => $this->headers + [
                'Upload-Length' => $this->fileSize,
                'Upload-Key' => $key,
                'Upload-Checksum' => $this->getUploadChecksumHeader(),
                'Upload-Metadata' => $this->getUploadMetadataHeader(),
                'Upload-Concat' => self::UPLOAD_TYPE_FINAL . ';' . implode(' ', $partials),
            ],
        ]);

        $data       = json_decode($response->getBody(), true);
        $checksum   = $data['data']['checksum'] ?? null;
        $statusCode = $response->getStatusCode();

        if (HttpResponse::HTTP_CREATED !== $statusCode || ! $checksum) {
            throw new FileException('Unable to create resource.');
        }

        return $checksum;
    }

    /**
     * Send DELETE request.
     *
     * @throws FileException
     * @throws GuzzleException
     *
     * @return void
     */
    public function delete()
    {
        try {
            $this->getClient()->delete($this->getUrl());
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if (HttpResponse::HTTP_NOT_FOUND === $statusCode || HttpResponse::HTTP_GONE === $statusCode) {
                throw new FileException('File not found.');
            }
        }
    }

    /**
     * Set as partial request.
     *
     * @param bool $state
     *
     * @return void
     */
    protected function partial(bool $state = true)
    {
        $this->partial = $state;

        if ( ! $this->partial) {
            return;
        }

        $key = $this->getKey();

        if (false !== strpos($key, self::PARTIAL_UPLOAD_NAME_SEPARATOR)) {
            [$key, /* $partialKey */] = explode(self::PARTIAL_UPLOAD_NAME_SEPARATOR, $key);
        }

        $this->key = $key . self::PARTIAL_UPLOAD_NAME_SEPARATOR . Uuid::uuid4()->toString();
    }

    /**
     * Send HEAD request.
     *
     * @throws FileException
     * @throws GuzzleException
     *
     * @return int
     */
    protected function sendHeadRequest(): int
    {
        $response   = $this->getClient()->head($this->getUrl());
        $statusCode = $response->getStatusCode();

        if (HttpResponse::HTTP_OK !== $statusCode) {
            throw new FileException('File not found.');
        }

        return (int) current($response->getHeader('upload-offset'));
    }

    /**
     * Send PATCH request.
     *
     * @param int $bytes
     * @param int $offset
     *
     * @throws TusException
     * @throws FileException
     * @throws GuzzleException
     * @throws ConnectionException
     *
     * @return int
     */
    protected function sendPatchRequest(int $bytes, int $offset): int
    {
        $data    = $this->getData($offset, $bytes);
        $headers = $this->headers + [
            'Content-Type' => self::HEADER_CONTENT_TYPE,
            'Content-Length' => \strlen($data),
            'Upload-Checksum' => $this->getUploadChecksumHeader(),
        ];

        if ($this->isPartial()) {
            $headers += ['Upload-Concat' => self::UPLOAD_TYPE_PARTIAL];
        } else {
            $headers += ['Upload-Offset' => $offset];
        }

        try {
            $response = $this->getClient()->patch($this->getUrl(), [
                'body' => $data,
                'headers' => $headers,
            ]);

            return (int) current($response->getHeader('upload-offset'));
        } catch (ClientException $e) {
            throw $this->handleClientException($e);
        } catch (ConnectException $e) {
            throw new ConnectionException("Couldn't connect to server.");
        }
    }

    /**
     * Handle client exception during patch request.
     *
     * @param ClientException $e
     *
     * @return \Exception
     */
    protected function handleClientException(ClientException $e)
    {
        $response   = $e->getResponse();
        $statusCode = $response !== null ? $response->getStatusCode() : HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

        if (HttpResponse::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE === $statusCode) {
            return new FileException('The uploaded file is corrupt.');
        }

        if (HttpResponse::HTTP_CONTINUE === $statusCode) {
            return new ConnectionException('Connection aborted by user.');
        }

        if (HttpResponse::HTTP_UNSUPPORTED_MEDIA_TYPE === $statusCode) {
            return new TusException('Unsupported media types.');
        }

        return new TusException((string) $response->getBody(), $statusCode);
    }

    /**
     * Get X bytes of data from file.
     *
     * @param int $offset
     * @param int $bytes
     *
     * @return string
     */
    protected function getData(int $offset, int $bytes): string
    {
        $file   = new File();
        $handle = $file->open($this->getFilePath(), $file::READ_BINARY);

        $file->seek($handle, $offset);

        $data = $file->read($handle, $bytes);

        $file->close($handle);

        return $data;
    }

    /**
     * Get upload checksum header.
     *
     * @return string
     */
    protected function getUploadChecksumHeader(): string
    {
        return $this->getChecksumAlgorithm() . ' ' . base64_encode($this->getChecksum());
    }
}
