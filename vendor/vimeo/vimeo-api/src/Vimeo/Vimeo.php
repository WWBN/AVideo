<?php
namespace Vimeo;

use Carbon\Carbon;
use Vimeo\Exceptions\VimeoException;
use Vimeo\Exceptions\VimeoRequestException;
use Vimeo\Exceptions\VimeoUploadException;
use Vimeo\Upload\TusClientFactory;

/**
 *   Copyright 2013 Vimeo
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 */

if (!function_exists('json_decode')) {
    throw new \Exception('We could not find json_decode. json_decode is found in php 5.2 and up, but may be missing on some Linux systems due to licensing conflicts. If you are running ubuntu try "sudo apt-get install php5-json".');
}

class Vimeo
{
    const ROOT_ENDPOINT = 'https://api.vimeo.com';
    const AUTH_ENDPOINT = 'https://api.vimeo.com/oauth/authorize';
    const ACCESS_TOKEN_ENDPOINT = '/oauth/access_token';
    const CLIENT_CREDENTIALS_TOKEN_ENDPOINT = '/oauth/authorize/client';
    const VERSIONS_ENDPOINT = '/versions';
    const VERSION_STRING = 'application/vnd.vimeo.*+json; version=3.4';
    const USER_AGENT = 'vimeo.php 3.0.8; (http://developer.vimeo.com/api/docs)';

    /** @var array */
    protected $_curl_opts = array();

    /** @var array */
    protected $CURL_DEFAULTS = array();

    /** @var null|string */
    private $_client_id = null;

    /** @var null|string */
    private $_client_secret = null;

    /** @var null|string */
    private $_access_token = null;

    /** @var TusClientFactory */
    private $_tus_client_factory = null;

    /**
     * Creates the Vimeo library, and tracks the client and token information.
     *
     * @param string $client_id Your applications client id. Can be found on developer.vimeo.com/apps
     * @param string $client_secret Your applications client secret. Can be found on developer.vimeo.com/apps
     * @param string|null $access_token Your access token. Can be found on developer.vimeo.com/apps or generated using OAuth 2.
     * @param TusClientFactory|null $tus_client_interface Your tus client that will be used.
     */
    public function __construct(string $client_id, string $client_secret, string $access_token = null, TusClientFactory $tus_client_factory = null)
    {
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;
        $this->_access_token = $access_token;
        $this->CURL_DEFAULTS = array(
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            //Certificate must indicate that the server is the server to which you meant to connect.
            CURLOPT_SSL_VERIFYHOST => 2,
        );
        $this->_tus_client_factory = $tus_client_factory ?? new TusClientFactory();
    }

    /**
     * Make an API request to Vimeo.
     *
     * @param string $url A Vimeo API Endpoint. Should not include the host
     * @param array $params An array of parameters to send to the endpoint. If the HTTP method is GET, they will be added to the url, otherwise they will be written to the body
     * @param string $method The HTTP Method of the request
     * @param bool $json_body
     * @param array $headers An array of HTTP headers to pass along with the request.
     * @return array This array contains three keys, 'status' is the status code, 'body' is an object representation of the json response body, and headers are an associated array of response headers
     * @throws VimeoRequestException
     */
    public function request($url, $params = array(), $method = 'GET', $json_body = true, array $headers = array()): array
    {
        $headers = array_merge(array(
            'Accept' => self::VERSION_STRING,
            'User-Agent' => self::USER_AGENT,
        ), $headers);

        $method = strtoupper($method);

        // If a pre-defined `Authorization` header isn't present, then add a bearer token or client information.
        if (!isset($headers['Authorization'])) {
            if (!empty($this->_access_token)) {
                $headers['Authorization'] = 'Bearer ' . $this->_access_token;
            } else {
                // this may be a call to get the tokens, so we add the client info.
                $headers['Authorization'] = 'Basic ' . $this->_authHeader();
            }
        }

        //  Set the methods, determine the URL that we should actually request and prep the body.
        $curl_opts = array();
        switch ($method) {
            case 'GET':
            case 'HEAD':
                if (!empty($params)) {
                    $query_component = '?' . http_build_query($params, '', '&');
                } else {
                    $query_component = '';
                }

                $curl_url = self::ROOT_ENDPOINT . $url . $query_component;
                break;

            case 'POST':
            case 'PATCH':
            case 'PUT':
            case 'DELETE':
                if ($json_body && !empty($params)) {
                    $headers['Content-Type'] = 'application/json';
                    $body = json_encode($params);
                } else {
                    $body = http_build_query($params, '', '&');
                }

                $curl_url = self::ROOT_ENDPOINT . $url;
                $curl_opts = array(
                    CURLOPT_POST => true,
                    CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_POSTFIELDS => $body
                );
                break;

            default:
                throw new VimeoRequestException('This library does not support ' . $method . ' requests.');
        }

        // Set the headers
        foreach ($headers as $key => $value) {
            $curl_opts[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $key, $value);
        }

        $response = $this->_request($curl_url, $curl_opts);

        $response['body'] = json_decode($response['body'], true);

        return $response;
    }

    /**
     * Request the access token associated with this library.
     *
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->_access_token;
    }

    /**
     * Assign a new access token to this library.
     *
     * @param string $access_token the new access token
     */
    public function setToken(string $access_token): void
    {
        $this->_access_token = $access_token;
    }

    /**
     * Gets custom cURL options.
     *
     * @return array
     */
    public function getCURLOptions(): array
    {
        return $this->_curl_opts;
    }

    /**
     * Sets custom cURL options.
     *
     * @param array $curl_opts An associative array of cURL options.
     */
    public function setCURLOptions(array $curl_opts = array()): void
    {
        $this->_curl_opts = $curl_opts;
    }

    /**
     * Set a proxy to pass all API requests through.
     *
     * @param string $proxy_address Mandatory address of proxy.
     * @param string|null $proxy_port Optional number of port.
     * @param string|null $proxy_userpwd Optional `user:password` authentication.
     */
    public function setProxy(string $proxy_address, string $proxy_port = null, string $proxy_userpwd = null): void
    {
        $this->CURL_DEFAULTS[CURLOPT_PROXY] = $proxy_address;
        if ($proxy_port) {
            $this->CURL_DEFAULTS[CURLOPT_PROXYPORT] = $proxy_port;
        }

        if ($proxy_userpwd) {
            $this->CURL_DEFAULTS[CURLOPT_PROXYUSERPWD] = $proxy_userpwd;
        }
    }

    /**
     * Convert the raw headers string into an associated array
     *
     * @param string $headers
     * @return array
     */
    public static function parse_headers(string $headers): array
    {
        $final_headers = array();
        $list = explode("\n", trim($headers));

        $http = array_shift($list);

        foreach ($list as $header) {
            $parts = explode(':', $header, 2);
            $final_headers[trim($parts[0])] = isset($parts[1]) ? trim($parts[1]) : '';
        }

        return $final_headers;
    }

    /**
     * Request an access token. This is the final step of the
     * OAuth 2 workflow, and should be called from your redirect url.
     *
     * @param string $code The authorization code that was provided to your redirect url
     * @param string $redirect_uri The redirect_uri that is configured on your app page, and was used in buildAuthorizationEndpoint
     * @return array This array contains three keys, 'status' is the status code, 'body' is an object representation of the json response body, and headers are an associated array of response headers
     */
    public function accessToken(string $code, string $redirect_uri): array
    {
        return $this->request(self::ACCESS_TOKEN_ENDPOINT, array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri
        ), "POST", false);
    }

    /**
     * Get client credentials for requests.
     *
     * @param mixed $scope Scopes to request for this token from the server.
     * @return array Response from the server with the tokens, we also set it into this object.
     */
    public function clientCredentials($scope = 'public')
    {
        if (is_array($scope)) {
            $scope = implode(' ', $scope);
        }

        $token_response = $this->request(self::CLIENT_CREDENTIALS_TOKEN_ENDPOINT, array(
            'grant_type' => 'client_credentials',
            'scope' => $scope
        ), "POST", false);

        return $token_response;
    }

    /**
     * Build the url that your user.
     *
     * @param string $redirect_uri The redirect url that you have configured on your app page
     * @param string|array|null $scope An array of scopes that your final access token needs to access
     * @param string|null $state A random variable that will be returned on your redirect url. You should validate that this matches
     * @return string
     */
    public function buildAuthorizationEndpoint($redirect_uri, $scope = 'public', $state = null)
    {
        $query = array(
            "response_type" => 'code',
            "client_id" => $this->_client_id,
            "redirect_uri" => $redirect_uri
        );

        $query['scope'] = $scope;
        if (empty($scope)) {
            $query['scope'] = 'public';
        } elseif (is_array($scope)) {
            $query['scope'] = implode(' ', $scope);
        }

        if (!empty($state)) {
            $query['state'] = $state;
        }

        return self::AUTH_ENDPOINT . '?' . http_build_query($query);
    }

    /**
     * Upload a file.
     *
     * This should be used to upload a local file. If you want a form for your site to upload direct to Vimeo, you
     * should look at the `POST /me/videos` endpoint.
     *
     * @link https://developer.vimeo.com/api/endpoints/videos#POST/users/{user_id}/videos
     * @param string $file_path Path to the video file to upload.
     * @param array $params Parameters to send when creating a new video (name, privacy restrictions, etc.).
     * @return string Video URI
     * @throws VimeoRequestException
     * @throws VimeoUploadException
     */
    public function upload($file_path, array $params = array())
    {
        // Validate that our file is real.
        if (!is_file($file_path)) {
            throw new VimeoUploadException('Unable to locate file to upload.');
        }

        $file_size = filesize($file_path);

        // Ignore any specified upload approach and size.
        $params['upload']['approach'] = 'tus';
        $params['upload']['size'] = $file_size;

        // Use JSON filtering so we only receive the data that we need to make an upload happen.
        $uri = '/me/videos?fields=uri,upload';

        $attempt = $this->request($uri, $params, 'POST');
        if ($attempt['status'] !== 200) {
            $attempt_error = !empty($attempt['body']['error']) ? ' [' . $attempt['body']['error'] . ']' : '';
            throw new VimeoUploadException('Unable to initiate an upload.' . $attempt_error);
        }

        return $this->perform_upload_tus($file_path, $file_size, $attempt);
    }

    /**
     * Replace the source of a single Vimeo video.
     *
     * @link https://developer.vimeo.com/api/endpoints/videos#POST/videos/{video_id}/versions
     * @param string $video_uri Video uri of the video file to replace.
     * @param string $file_path Path to the video file to upload.
     * @return string Video URI
     * @throws VimeoRequestException
     * @throws VimeoUploadException
     */
    public function replace($video_uri, $file_path, array $params = array())
    {
        //  Validate that our file is real.
        if (!is_file($file_path)) {
            throw new VimeoUploadException('Unable to locate file to upload.');
        }

        $file_size = filesize($file_path);

        // Use JSON filtering so we only receive the data that we need to make an upload happen.
        $uri = $video_uri . self::VERSIONS_ENDPOINT . '?fields=upload';

        // Ignore any specified upload approach and size.
        $params['file_name'] = basename($file_path);
        $params['upload']['approach'] = 'tus';
        $params['upload']['size'] = $file_size;

        $attempt = $this->request($uri, $params, 'POST');
        if ($attempt['status'] !== 201) {
            $attempt_error = !empty($attempt['body']['error']) ? ' [' . $attempt['body']['error'] . ']' : '';
            throw new VimeoUploadException('Unable to initiate an upload.' . $attempt_error);
        }

        // `uri` doesn't come back from `/videos/:id/versions` so we need to manually set it here for uploading.
        $attempt['body']['uri'] = $video_uri;

        return $this->perform_upload_tus($file_path, $file_size, $attempt);
    }

    /**
     * Uploads an image to an individual picture response.
     *
     * @link https://developer.vimeo.com/api/upload/pictures
     * @param string $pictures_uri The pictures endpoint for a resource that allows picture uploads (eg videos and users)
     * @param string $file_path The path to your image file
     * @param boolean $activate Activate image after upload
     * @return string The URI of the uploaded image.
     * @throws VimeoRequestException
     * @throws VimeoUploadException
     */
    public function uploadImage($pictures_uri, $file_path, $activate = false)
    {
        // Validate that our file is real.
        if (!is_file($file_path)) {
            throw new VimeoUploadException('Unable to locate file to upload.');
        }

        $pictures_response = $this->request($pictures_uri, array(), 'POST');
        if ($pictures_response['status'] !== 201) {
            throw new VimeoUploadException('Unable to request an upload url from vimeo');
        }

        $upload_url = $pictures_response['body']['link'];

        $image_resource = fopen($file_path, 'r');

        $curl_opts = array(
            CURLOPT_TIMEOUT => 240,
            CURLOPT_UPLOAD => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_READDATA => $image_resource
        );

        $curl = curl_init($upload_url);

        // Merge the options
        curl_setopt_array($curl, $curl_opts + $this->CURL_DEFAULTS);
        $response = curl_exec($curl);
        $curl_info = curl_getinfo($curl);

        if (!$response || !is_string($response)) {
            $error = curl_error($curl);
            throw new VimeoUploadException($error);
        }

        curl_close($curl);

        if ($curl_info['http_code'] !== 200) {
            throw new VimeoUploadException($response);
        }

        // Activate the uploaded image
        if ($activate) {
            $completion = $this->request($pictures_response['body']['uri'], array('active' => true), 'PATCH');
        }

        return $pictures_response['body']['uri'];
    }

    /**
     * Uploads a text track.
     *
     * @link https://developer.vimeo.com/api/upload/texttracks
     * @param string $texttracks_uri The text tracks uri that we are adding our text track to
     * @param string $file_path The path to your text track file
     * @param string $track_type The type of your text track
     * @param string $language The language of your text track
     * @return string The URI of the uploaded text track.
     * @throws VimeoRequestException
     * @throws VimeoUploadException
     */
    public function uploadTexttrack($texttracks_uri, $file_path, $track_type, $language)
    {
        // Validate that our file is real.
        if (!is_file($file_path)) {
            throw new VimeoUploadException('Unable to locate file to upload.');
        }

        // To simplify the script we provide the filename as the text track name, but you can provide any value you want.
        $name = array_slice(explode("/", $file_path), -1);
        $name = $name[0];

        $texttrack_response = $this->request($texttracks_uri, array(
            'type' => $track_type,
            'language' => $language,
            'name' => $name
        ), 'POST');

        if ($texttrack_response['status'] !== 201) {
            throw new VimeoUploadException('Unable to request an upload url from vimeo');
        }

        $upload_url = $texttrack_response['body']['link'];

        $handle = fopen($file_path, 'r');
        $texttrack_resource = fread($handle, filesize($file_path));

        $curl_opts = array(
            CURLOPT_TIMEOUT => 240,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $texttrack_resource
        );

        $curl = curl_init($upload_url);

        // Merge the options
        curl_setopt_array($curl, $curl_opts + $this->CURL_DEFAULTS);
        $response = curl_exec($curl);
        $curl_info = curl_getinfo($curl);

        if (!$response || !is_string($response)) {
            $error = curl_error($curl);
            throw new VimeoUploadException($error);
        }

        curl_close($curl);
        fclose($handle);
        
        if ($curl_info['http_code'] !== 200) {
            throw new VimeoUploadException($response);
        }

        return $texttrack_response['body']['uri'];
    }

    /**
     * Internal function to handle requests, both authenticated and by the upload function.
     *
     * @param string $url
     * @param array $curl_opts
     * @return array
     * @throws VimeoRequestException
     */
    private function _request(string $url, $curl_opts = array()): array
    {
        // Merge the options (custom options take precedence).
        $curl_opts = $this->_curl_opts + $curl_opts + $this->CURL_DEFAULTS;

        // Call the API.
        $curl = curl_init($url);
        curl_setopt_array($curl, $curl_opts);
        $response = curl_exec($curl);
        $curl_info = curl_getinfo($curl);

        if (isset($curl_info['http_code']) && $curl_info['http_code'] === 0) {
            $curl_error = curl_error($curl);
            $curl_error = !empty($curl_error) ? ' [' . $curl_error .']' : '';
            throw new VimeoRequestException('Unable to complete request.' . $curl_error);
        } elseif (!$response || !is_string($response)) {
            $curl_error = curl_error($curl);
            $curl_error = !empty($curl_error) ? ' [' . $curl_error .']' : '';
            throw new VimeoRequestException('Unable to complete request.' . $curl_error);
        }

        curl_close($curl);

        // Retrieve the info
        $header_size = $curl_info['header_size'];
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Return it raw.
        return array(
            'body' => $body,
            'status' => $curl_info['http_code'],
            'headers' => self::parse_headers($headers)
        );
    }

    /**
     * Get authorization header for retrieving tokens/credentials.
     *
     * @return string
     */
    private function _authHeader(): string
    {
        if (empty($this->_client_id)) {
            throw new VimeoException('A client ID must be set in order to generate an auth header.');
        } elseif (empty($this->_client_secret)) {
            throw new VimeoException('A client secret must be set in order to generate an auth header.');
        }

        return base64_encode($this->_client_id . ':' . $this->_client_secret);
    }

    /**
     * Take an upload attempt and perform the actual upload via tus.
     *
     * @link https://tus.io/
     * @param string $file_path Path to the video file to upload.
     * @param int|float $file_size Size of the video file.
     * @param array $attempt Upload attempt data.
     * @return string
     * @throws VimeoUploadException
     */
    private function perform_upload_tus(string $file_path, $file_size, array $attempt): string
    {
        $default_chunk_size = (100 * 1024 * 1024); // 100 MB

        $url = $attempt['body']['upload']['upload_link'];
        $url_path = parse_url($url)['path'];

        $base_url = str_replace($url_path, '', $url);
        $api_path = $url_path;
        $api_pathp = explode('/', $api_path);
        $key = $api_pathp[count($api_pathp) - 1];
        $api_path = str_replace('/' . $key, '', $api_path);

        $bytes_uploaded = 0;
        $failures = 0;
        $chunk_size = $this->getTusUploadChunkSize($default_chunk_size, (int)$file_size);

        $client = $this->_tus_client_factory->getTusClient($base_url, $url);
        $client->setApiPath($api_path);
        $client->setKey($key)->file($file_path);
        $client->getCache()->set($client->getKey(),[
            'location' => $url,
            'expires_at' => Carbon::now()->addSeconds($client->getCache()->getTtl())->format($client->getCache()::RFC_7231),
        ]);

        do {
            try {
                $bytes_uploaded = $client->upload($chunk_size);
            } catch (\Exception $e) {
                // We likely experienced a timeout, but if we experience three in a row, then we should back off and
                // fail so as to not overwhelm servers that are, probably, down.
                if ($failures >= 3) {
                    throw new VimeoUploadException($e->getMessage());
                }

                $failures++;
                sleep((int)pow(4, $failures)); // sleep 4, 16, 64 seconds (based on failure count)
            }
        } while ($bytes_uploaded < $file_size);

        return $attempt['body']['uri'];
    }

    /**
     * Enforces the notion that a user may supply any `proposed_chunk_size`, as long as it results in 1024 or less
     * proposed chunks. In the event it does not, then the chunk size becomes the file size divided by 1024.
     *
     * @param int $proposed_chunk_size
     * @param int $file_size
     * @return int
     */
    private function getTusUploadChunkSize(int $proposed_chunk_size, int $file_size): int
    {
        $proposed_chunk_size = ($proposed_chunk_size <= 0) ? 1 : $proposed_chunk_size;
        $chunks = floor($file_size / $proposed_chunk_size);
        $divides_evenly = $file_size % $proposed_chunk_size === 0;
        $number_of_chunks_proposed = ($divides_evenly) ? $chunks : $chunks + 1;

        if ($number_of_chunks_proposed > 1024) {
            return (int)floor($file_size / 1024) + 1;
        }

        return $proposed_chunk_size;
    }
}
