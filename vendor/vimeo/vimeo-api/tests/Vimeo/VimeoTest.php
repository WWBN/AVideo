<?php
namespace Vimeo;

use PHPUnit\Framework\TestCase;
use ReflectionObject;

class VimeoTest extends TestCase
{
    /** @var string */
    protected $clientId = 'client_id';

    /** @var string */
    protected $clientSecret = 'client_secret';

    public function testRequestGetUserInformation(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->request('/users/userwillnotbefound');

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testRequestGetUserInformationWithAccessToken(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret, 'fake_access_token');

        // Act
        $result = $vimeo->request('/users/userwillnotbefound');

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testRequestGetUserInformationWithParams(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->request('/users/userwillnotbefound', array('fake_key=fake_value'));

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testGetToken(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $vimeo->setToken('fake_access_token');

        // Assert
        $this->assertSame('fake_access_token', $vimeo->getToken());
    }

    public function testGetCurlOptions(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $vimeo->setCurlOptions(array('custom_name' => 'custom_value'));
        $result = $vimeo->getCurlOptions();

        // Assert
        $this->assertIsArray($result);
        $this->assertSame('custom_value', $result['custom_name']);
    }

    public function testAccessTokenWithCallingFakeRedirectUri(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->accessToken('fake_auth_code', 'https://fake.redirect.uri');

        // Assert
        $this->assertSame('invalid_client', $result['body']['error']);
    }

    public function testClientCredentialsWithDefaultScope(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->clientCredentials();

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testClientCredentialsWithArrayScope(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->clientCredentials(array('public'));

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testBuildAuthorizationEndpointWithDefaultScopeAndNullState(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri');

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public', $result);
    }

    public function testBuildAuthorizationEndpointWithNullScopeAndNullState(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri', null);

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public', $result);
    }

    public function testBuildAuthorizationEndpointWithArrayScopeAndNullState(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri', array('public', 'private'));

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public+private', $result);
    }

    public function testBuildAuthorizationEndpointWithArrayScopeAndState(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri', array('public'), 'fake_state');

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public&state=fake_state', $result);
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadWithNonExistedFile(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->upload('./the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadWithInvalidParamShouldReturnVimeoRequestException(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->upload(__DIR__.'/../../composer.json', array('invalid_param'));
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testReplaceWithNonExistedFile(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->replace('https://vimeo.com/241711006', './the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadImageWithNonExistedFile(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadImage('https://vimeo.com/241711006', './the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadTexttrackWithNonExistedFile(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadTexttrack('https://vimeo.com/241711006', './the_file_is_invalid', 'fake_track_type', 'zh_TW');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testReplaceWithVideoUriShouldReturnVimeoRequestException(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->replace('https://vimeo.com/241711006', __DIR__.'/../../composer.json');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testUploadImageWithPictureUriShouldReturnVimeoRequestException(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadImage('https://vimeo.com/user59081751', __DIR__.'/../../composer.json');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testUploadTexttrackWithPictureUriAndInvalidParamShouldReturnVimeoRequestException(): void
    {
        $this->markTestSkipped('Skipping until we have time to set up real tests with Travis secret storage.');

        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadTexttrack('https://vimeo.com/user59081751', __DIR__.'/../../composer.json', 'fake_track_type', 'zh_TW');
    }

    public function testGetTusUploadChunkSize(): void
    {
        $client = new Vimeo($this->clientId, $this->clientSecret);

        $reflector = new ReflectionObject($client);
        $method = $reflector->getMethod('getTusUploadChunkSize');
        $method->setAccessible(true);

        // The following cases result in < 1024 and as such should be allowed
        $this->assertSame(1, $method->invoke($client, 1, 1024));
        $this->assertSame(3, $method->invoke($client, 3, 1024));

        // A `chunk_size` larger than `file_size` is ok
        $this->assertSame(3, $method->invoke($client, 3, 1));
        $this->assertSame(1024, $method->invoke($client, 1024, 1));

        // A `chunk_size` <= 0 is equivalent to 1 byte.
        $this->assertSame(1, $method->invoke($client, 0, 1024));
        $this->assertSame(1, $method->invoke($client, -1000, 1024));

        // The following cases all result in > 1024 chunks.
        $this->assertSame(2, $method->invoke($client, 1, 1025));

        // 20 MB chunks for a 100000 MB file (100GB)
        $this->assertSame(102400001, $method->invoke($client, (20 * 1024 * 1024), (100000 * 1024 * 1024)));
    }
}
