<?php

namespace BackblazeB2;

use BackblazeB2\Exceptions\B2Exception;
use BackblazeB2\Exceptions\NotFoundException;
use BackblazeB2\Exceptions\ValidationException;
use BackblazeB2\Http\Client as HttpClient;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    const B2_API_BASE_URL = 'https://api.backblazeb2.com';
    const B2_API_V1 = '/b2api/v1/';
    protected $accountId;
    protected $applicationKey;
    protected $authToken;
    protected $apiUrl;
    protected $downloadUrl;
    protected $client;
    protected $reAuthTime;
    protected $authTimeoutSeconds;

    /**
     * Accepts the account ID, application key and an optional array of options.
     *
     * @param $accountId
     * @param $applicationKey
     * @param array $options
     *
     * @throws \Exception
     */
    public function __construct($accountId, $applicationKey, array $options = [])
    {
        $this->accountId = $accountId;
        $this->applicationKey = $applicationKey;

        $this->authTimeoutSeconds = 12 * 60 * 60; // 12 hour default
        if (isset($options['auth_timeout_seconds'])) {
            $this->authTimeoutSeconds = $options['auth_timeout_seconds'];
        }

        // set reauthorize time to force an authentication to take place
        $this->reAuthTime = Carbon::now('UTC')->subSeconds($this->authTimeoutSeconds * 2);

        $this->client = new HttpClient(['exceptions' => false]);
        if (isset($options['client'])) {
            $this->client = $options['client'];
        }
    }

    /**
     * Create a bucket with the given name and type.
     *
     * @param array $options
     *
     * @throws ValidationException
     * @throws GuzzleException     If the request fails.
     * @throws B2Exception         If the B2 server replies with an error.
     *
     * @return Bucket
     */
    public function createBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        $response = $this->sendAuthorizedRequest('POST', 'b2_create_bucket', [
            'accountId'  => $this->accountId,
            'bucketName' => $options['BucketName'],
            'bucketType' => $options['BucketType'],
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Updates the type attribute of a bucket by the given ID.
     *
     * @param array $options
     *
     * @throws ValidationException
     * @throws GuzzleException     If the request fails.
     * @throws B2Exception         If the B2 server replies with an error.
     *
     * @return Bucket
     */
    public function updateBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $response = $this->sendAuthorizedRequest('POST', 'b2_update_bucket', [
            'accountId'  => $this->accountId,
            'bucketId'   => $options['BucketId'],
            'bucketType' => $options['BucketType'],
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Returns a list of bucket objects representing the buckets on the account.
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return array
     */
    public function listBuckets()
    {
        $buckets = [];

        $response = $this->sendAuthorizedRequest('POST', 'b2_list_buckets', [
            'accountId' => $this->accountId,
        ]);

        foreach ($response['buckets'] as $bucket) {
            $buckets[] = new Bucket($bucket['bucketId'], $bucket['bucketName'], $bucket['bucketType']);
        }

        return $buckets;
    }

    /**
     * Deletes the bucket identified by its ID.
     *
     * @param array $options
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return bool
     */
    public function deleteBucket(array $options)
    {
        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $this->sendAuthorizedRequest('POST', 'b2_delete_bucket', [
            'accountId' => $this->accountId,
            'bucketId'  => $options['BucketId'],
        ]);

        return true;
    }

    /**
     * Uploads a file to a bucket and returns a File object.
     *
     * @param array $options
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return File
     */
    public function upload(array $options)
    {
        // Clean the path if it starts with /.
        if (substr($options['FileName'], 0, 1) === '/') {
            $options['FileName'] = ltrim($options['FileName'], '/');
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        // Retrieve the URL that we should be uploading to.

        $response = $this->sendAuthorizedRequest('POST', 'b2_get_upload_url', [
            'bucketId' => $options['BucketId'],
        ]);

        $uploadEndpoint = $response['uploadUrl'];
        $uploadAuthToken = $response['authorizationToken'];

        if (is_resource($options['Body'])) {
            // We need to calculate the file's hash incrementally from the stream.
            $context = hash_init('sha1');
            hash_update_stream($context, $options['Body']);
            $hash = hash_final($context);

            // Similarly, we have to use fstat to get the size of the stream.
            $size = fstat($options['Body'])['size'];

            // Rewind the stream before passing it to the HTTP client.
            rewind($options['Body']);
        } else {
            // We've been given a simple string body, it's super simple to calculate the hash and size.
            $hash = sha1($options['Body']);
            $size = strlen($options['Body']);
        }

        if (!isset($options['FileLastModified'])) {
            $options['FileLastModified'] = round(microtime(true) * 1000);
        }

        if (!isset($options['FileContentType'])) {
            $options['FileContentType'] = 'b2/x-auto';
        }

        $customHeaders = $options['Headers'] ?? [];
        $response = $this->client->guzzleRequest('POST', $uploadEndpoint, [
            'headers' => array_merge([
                'Authorization'                      => $uploadAuthToken,
                'Content-Type'                       => $options['FileContentType'],
                'Content-Length'                     => $size,
                'X-Bz-File-Name'                     => $options['FileName'],
                'X-Bz-Content-Sha1'                  => $hash,
                'X-Bz-Info-src_last_modified_millis' => $options['FileLastModified'],
            ], $customHeaders),
            'body' => $options['Body'],
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Download a file from a B2 bucket.
     *
     * @param array $options
     *
     * @return bool
     */
    public function download(array $options)
    {
        if (!isset($options['FileId']) && !isset($options['BucketName']) && isset($options['BucketId'])) {
            $options['BucketName'] = $this->getBucketNameFromId($options['BucketId']);
        }

        $this->authorizeAccount();

        $requestUrl = null;
        $customHeaders = $options['Headers'] ?? [];
        $requestOptions = [
            'headers' => array_merge([
                'Authorization' => $this->authToken,
            ], $customHeaders),
            'sink' => isset($options['SaveAs']) ? $options['SaveAs'] : null,
        ];

        if (isset($options['FileId'])) {
            $requestOptions['query'] = ['fileId' => $options['FileId']];
            $requestUrl = $this->downloadUrl.'/b2api/v1/b2_download_file_by_id';
        } else {
            $requestUrl = sprintf('%s/file/%s/%s', $this->downloadUrl, $options['BucketName'], $options['FileName']);
        }

        $response = $this->client->guzzleRequest('GET', $requestUrl, $requestOptions, false);

        return isset($options['SaveAs']) ? true : $response;
    }

    /**
     * Copy a file.
     *
     * $options:
     * required BucketName or BucketId the source bucket
     * required FileName the file to copy
     * required SaveAs the path and file name to save to
     * optional DestinationBucketId or DestinationBucketName, the destination bucket
     *
     * @param array $options
     *
     * @throws B2Exception
     * @throws GuzzleException
     * @throws NotFoundException
     *
     * @return File
     */
    public function copy(array $options)
    {
        $options['FileName'] = ltrim($options['FileName'], '/');
        $options['SaveAs'] = ltrim($options['SaveAs'], '/');

        if (!isset($options['DestinationBucketId']) && isset($options['DestinationBucketName'])) {
            $options['DestinationBucketId'] = $this->getBucketIdFromName($options['DestinationBucketName']);
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $sourceFiles = $this->listFiles([
            'BucketId' => $options['BucketId'],
            'FileName' => $options['FileName'],
        ]);
        $sourceFileId = !empty($sourceFiles) ? $sourceFiles[0]->getId() : false;
        if (!$sourceFileId) {
            throw new NotFoundException('Source file not found in B2');
        }

        $json = [
            'sourceFileId' => $sourceFileId,
            'fileName'     => $options['SaveAs'],
        ];
        if (isset($options['DestinationBucketId'])) {
            $json['DestinationBucketId'] = $options['DestinationBucketId'];
        }

        $response = $this->sendAuthorizedRequest('POST', 'b2_copy_file', $json);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Retrieve a collection of File objects representing the files stored inside a bucket.
     *
     * @param array $options
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return array
     */
    public function listFiles(array $options)
    {
        // if FileName is set, we only attempt to retrieve information about that single file.
        $fileName = !empty($options['FileName']) ? $options['FileName'] : null;

        $nextFileName = null;
        $maxFileCount = 1000;

        $prefix = isset($options['Prefix']) ? $options['Prefix'] : '';
        $delimiter = isset($options['Delimiter']) ? $options['Delimiter'] : null;

        $files = [];

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        if ($fileName) {
            $nextFileName = $fileName;
            $maxFileCount = 1;
        }

        $this->authorizeAccount();

        // B2 returns, at most, 1000 files per "page". Loop through the pages and compile an array of File objects.
        while (true) {
            $response = $this->sendAuthorizedRequest('POST', 'b2_list_file_names', [
                'bucketId'      => $options['BucketId'],
                'startFileName' => $nextFileName,
                'maxFileCount'  => $maxFileCount,
                'prefix'        => $prefix,
                'delimiter'     => $delimiter,
            ]);

            foreach ($response['files'] as $file) {
                // if we have a file name set, only retrieve information if the file name matches
                if (!$fileName || ($fileName === $file['fileName'])) {
                    $files[] = new File($file['fileId'], $file['fileName'], $file['contentSha1'], $file['size'], $file['contentType'], $file['fileInfo'], $file['bucketId'], $file['action'], $file['uploadTimestamp']);
                }
            }

            if ($fileName || $response['nextFileName'] === null) {
                // We've got all the files - break out of loop.
                break;
            }

            $nextFileName = $response['nextFileName'];
        }

        return $files;
    }

    /**
     * Test whether a file exists in B2 for the given bucket.
     *
     * @param array $options
     *
     * @return bool
     */
    public function fileExists(array $options)
    {
        $files = $this->listFiles($options);

        return !empty($files);
    }

    /**
     * Returns a single File object representing a file stored on B2.
     *
     * @param array $options
     *
     * @throws GuzzleException
     * @throws NotFoundException If no file id was provided and BucketName + FileName does not resolve to a file, a NotFoundException is thrown.
     * @throws GuzzleException   If the request fails.
     * @throws B2Exception       If the B2 server replies with an error.
     *
     * @return File
     */
    public function getFile(array $options)
    {
        if (!isset($options['FileId']) && isset($options['BucketId']) && isset($options['FileName'])) {
            $files = $this->listFiles([
                'BucketId' => $options['BucketId'],
                'FileName' => $options['FileName'],
            ]);

            if (empty($files)) {
                throw new NotFoundException();
            }

            $options['FileId'] = $files[0]->getId();
        } elseif (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $options['FileId'] = $this->getFileIdFromBucketAndFileName($options['BucketName'], $options['FileName']);

            if (!$options['FileId']) {
                throw new NotFoundException();
            }
        }

        $response = $this->sendAuthorizedRequest('POST', 'b2_get_file_info', [
            'fileId' => $options['FileId'],
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Deletes the file identified by ID from Backblaze B2.
     *
     * @param array $options
     *
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws GuzzleException   If the request fails.
     * @throws B2Exception       If the B2 server replies with an error.
     *
     * @return bool
     */
    public function deleteFile(array $options)
    {
        if (!isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileName'] = $file->getName();
        }

        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileId'] = $file->getId();
        }

        $this->sendAuthorizedRequest('POST', 'b2_delete_file_version', [
            'fileName' => $options['FileName'],
            'fileId'   => $options['FileId'],
        ]);

        return true;
    }

    /**
     * Fetches authorization and uri for a file, to allow a third-party system to download public and private files.
     *
     * @param array $options
     *
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws GuzzleException   If the request fails.
     * @throws B2Exception       If the B2 server replies with an error.
     *
     * @return array
     */
    public function getFileUri(array $options)
    {
        if (!isset($options['FileId']) && !isset($options['BucketName']) && isset($options['BucketId'])) {
            $options['BucketName'] = $this->getBucketNameFromId($options['BucketId']);
        }

        $this->authorizeAccount();

        if (isset($options['FileId'])) {
            $requestUri = $this->downloadUrl.'/b2api/v1/b2_download_file_by_id?fileId='.urlencode($options['FileId']);
        } else {
            $requestUri = sprintf('%s/file/%s/%s', $this->downloadUrl, $options['BucketName'], $options['FileName']);
        }

        return [
            'Authorization' => $this->authToken,
            'Uri'           => $requestUri,
        ];
    }

    /**
     * Authorize the B2 account in order to get an auth token and API/download URLs.
     */
    protected function authorizeAccount()
    {
        if (Carbon::now('UTC')->timestamp < $this->reAuthTime->timestamp) {
            return;
        }

        $response = $this->client->guzzleRequest('GET', self::B2_API_BASE_URL.self::B2_API_V1.'b2_authorize_account', [
            'auth' => [$this->accountId, $this->applicationKey],
        ]);

        $this->authToken = $response['authorizationToken'];
        $this->apiUrl = $response['apiUrl'].self::B2_API_V1;
        $this->downloadUrl = $response['downloadUrl'];
        $this->reAuthTime = Carbon::now('UTC');
        $this->reAuthTime->addSeconds($this->authTimeoutSeconds);
    }

    /**
     * Maps the provided bucket name to the appropriate bucket ID.
     *
     * @param $name
     *
     * @return mixed
     */
    protected function getBucketIdFromName($name)
    {
        $buckets = $this->listBuckets();

        foreach ($buckets as $bucket) {
            if ($bucket->getName() === $name) {
                return $bucket->getId();
            }
        }
    }

    /**
     * Maps the provided bucket ID to the appropriate bucket name.
     *
     * @param $id
     *
     * @return mixed
     */
    protected function getBucketNameFromId($id)
    {
        $buckets = $this->listBuckets();

        foreach ($buckets as $bucket) {
            if ($bucket->getId() === $id) {
                return $bucket->getName();
            }
        }
    }

    /**
     * @param $bucketName
     * @param $fileName
     *
     * @return mixed
     */
    protected function getFileIdFromBucketAndFileName($bucketName, $fileName)
    {
        $files = $this->listFiles([
            'BucketName' => $bucketName,
            'FileName'   => $fileName,
        ]);

        foreach ($files as $file) {
            if ($file->getName() === $fileName) {
                return $file->getId();
            }
        }
    }

    /**
     * Uploads a large file using b2 large file procedure.
     *
     * @param array $options
     *
     * @return File
     */
    public function uploadLargeFile(array $options)
    {
        if (substr($options['FileName'], 0, 1) === '/') {
            $options['FileName'] = ltrim($options['FileName'], '/');
        }

        //if last char of path is not a "/" then add a "/"
        if (substr($options['FilePath'], -1) != '/') {
            $options['FilePath'] = $options['FilePath'].'/';
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        if (!isset($options['FileContentType'])) {
            $options['FileContentType'] = 'b2/x-auto';
        }

        $this->authorizeAccount();

        // 1) b2_start_large_file, (returns fileId)
        $start = $this->startLargeFile($options['FileName'], $options['FileContentType'], $options['BucketId']);

        // 2) b2_get_upload_part_url for each thread uploading (takes fileId)
        $url = $this->getUploadPartUrl($start['fileId']);

        // 3) b2_upload_part for each part of the file
        $parts = $this->uploadParts($options['FilePath'].$options['FileName'], $url['uploadUrl'], $url['authorizationToken'], $options);

        $sha1s = [];

        foreach ($parts as $part) {
            $sha1s[] = $part['contentSha1'];
        }

        // 4) b2_finish_large_file.
        return $this->finishLargeFile($start['fileId'], $sha1s);
    }

    /**
     * Starts the large file upload process.
     *
     * @param $fileName
     * @param $contentType
     * @param $bucketId
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return mixed
     */
    protected function startLargeFile($fileName, $contentType, $bucketId)
    {
        return $this->sendAuthorizedRequest('POST', 'b2_start_large_file', [
            'fileName'      => $fileName,
            'contentType'   => $contentType,
            'bucketId'      => $bucketId,
        ]);
    }

    /**
     * Gets the url for the next large file part upload.
     *
     * @param $fileId
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return mixed
     */
    protected function getUploadPartUrl($fileId)
    {
        return $this->sendAuthorizedRequest('POST', 'b2_get_upload_part_url', [
            'fileId' => $fileId,
        ]);
    }

    /**
     * Uploads the file as "parts" of 100MB each.
     *
     * @param $filePath
     * @param $uploadUrl
     * @param $largeFileAuthToken
     * @param $options
     *
     * @return array
     */
    protected function uploadParts($filePath, $uploadUrl, $largeFileAuthToken, $options = [])
    {
        $return = [];

        $minimum_part_size = 100 * (1000 * 1000);

        $local_file_size = filesize($filePath);
        $total_bytes_sent = 0;
        $bytes_sent_for_part = $minimum_part_size;
        $sha1_of_parts = [];
        $part_no = 1;
        $file_handle = fopen($filePath, 'r');

        while ($total_bytes_sent < $local_file_size) {

            // Determine the number of bytes to send based on the minimum part size
            if (($local_file_size - $total_bytes_sent) < $minimum_part_size) {
                $bytes_sent_for_part = ($local_file_size - $total_bytes_sent);
            }

            // Get a sha1 of the part we are going to send
            fseek($file_handle, $total_bytes_sent);
            $data_part = fread($file_handle, $bytes_sent_for_part);
            array_push($sha1_of_parts, sha1($data_part));
            fseek($file_handle, $total_bytes_sent);

            $customHeaders = $options['Headers'] ?? [];
            $response = $this->client->guzzleRequest('POST', $uploadUrl, [
                'headers' => array_merge([
                    'Authorization'                      => $largeFileAuthToken,
                    'Content-Length'                     => $bytes_sent_for_part,
                    'X-Bz-Part-Number'                   => $part_no,
                    'X-Bz-Content-Sha1'                  => $sha1_of_parts[$part_no - 1],
                ], $customHeaders),
                'body' => $data_part,
            ]);

            $return[] = $response;

            // Prepare for the next iteration of the loop
            $part_no++;
            $total_bytes_sent = $bytes_sent_for_part + $total_bytes_sent;
        }

        fclose($file_handle);

        return $return;
    }

    /**
     * Finishes the large file upload procedure.
     *
     * @param       $fileId
     * @param array $sha1s
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return File
     */
    protected function finishLargeFile($fileId, array $sha1s)
    {
        $response = $this->sendAuthorizedRequest('POST', 'b2_finish_large_file', [
            'fileId'        => $fileId,
            'partSha1Array' => $sha1s,
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Sends a authorized request to b2 API.
     *
     * @param string $method
     * @param string $route
     * @param array  $json
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return mixed
     */
    protected function sendAuthorizedRequest($method, $route, $json = [])
    {
        $this->authorizeAccount();

        return $this->client->guzzleRequest($method, $this->apiUrl.$route, [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => $json,
        ]);
    }
}
