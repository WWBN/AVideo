<?php


namespace Vimeo\Upload;

use GuzzleHttp\Client as GuzzleClient;
use Vimeo\Exceptions\VimeoUploadException;
use Psr\Http\Message\ResponseInterface;

class TusClient
{
    public const SUCCESS_STATUS_CODES = [
        200,
        204
    ];
    public const TUS_PROTOCOL_VERSION = '1.0.0';
    protected const HEADER_CONTENT_TYPE = 'application/offset+octet-stream';

    /** @var GuzzleClient */
    protected $guzzle_client;

    /** @var string */
    protected $url;

    /** @var string */
    protected $file_path;

    /** @var array<string, string> */
    protected $default_headers = ['Tus-Resumable' => self::TUS_PROTOCOL_VERSION];

    public function __construct(string $url, string $file_path)
    {
        $this->url = $url;
        $this->guzzle_client = new GuzzleClient(['http_errors' => 'false']);
        $this->file_path = $file_path;
    }

    public function upload(int $bytes = 0): int
    {
        $bytes = $bytes < 0 ? 0 : $bytes;

        $offset = $this->sendHeadRequest();

        return $this->sendPatchRequest($bytes, $offset);
    }

    protected function sendHeadRequest(): int
    {
        $response = $this->sendAndValidateRequest('HEAD', [
            'headers' => $this->default_headers + ['http_errors' => false]
        ]);

        return (int) current($response->getHeader('upload-offset'));
    }

    protected function sendPatchRequest(int $bytes, int $offset): int
    {
        $file_data = $this->getFileData($bytes, $offset);

        $headers = $this->default_headers + [
            'Content-Type' => self::HEADER_CONTENT_TYPE,
            'Content-Length' => \strlen($file_data),
            'Upload-Offset' => $offset,
        ];

        $response = $this->sendAndValidateRequest('PATCH', [
            'body' => $file_data,
            'headers' => $headers,
        ]);

        return (int) current($response->getHeader('upload-offset'));
    }

    protected function sendAndValidateRequest(string $method, array $options = []): ResponseInterface
    {

        $response = $this->guzzle_client->request($method, $this->url, $options);

        $body = $response->getBody()->getContents();
        $status_code = $response->getStatusCode();

        if (in_array($status_code, self::SUCCESS_STATUS_CODES)) {
            return $response;
        }

        $retryable = $status_code === 429 || (500 <= $status_code && $status_code < 600);
        if ($retryable) {
            throw new VimeoUploadException("$method request to $this->url failed", $status_code, true);
        }

        throw new VimeoUploadException("$method request to $this->url failed", $status_code);
    }

    protected function getFileData(int $bytes, int $offset): string
    {
        if (!file_exists($this->file_path)) {
            throw new VimeoUploadException(
                "Cannot upload file {$this->file_path}: file does not exist"
            );
        }

        $handle = @fopen($this->file_path, 'rb');

        if ($handle === false) {
            throw new VimeoUploadException("Cannot open {$this->file_path}");
        }

        $position = fseek($handle, $offset, SEEK_SET);
        if ($position === -1) {
            throw new VimeoUploadException(
                "Error seeking to position {$offset} in file {$this->file_path}"
            );
        }

        $data = fread($handle, $bytes);

        if ($data === false) {
            throw new VimeoUploadException("Error reading file {$this->file_path}");
        }

        fclose($handle);

        return $data;
    }
}
