<?php
namespace Vimeo;

use PHPUnit\Framework\TestCase;
use Vimeo\Vimeo;

class VimeoTest extends TestCase
{
    protected $clientId = 'client_id';
    protected $clientSecret = 'client_secret';

    public function testRequestGetUserInformation()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->request('/users/userwillnotbefound');

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testRequestGetUserInformationWithAccessToken()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret, 'fake_access_token');

        // Act
        $result = $vimeo->request('/users/userwillnotbefound');

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testRequestGetUserInformationWithParams()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->request('/users/userwillnotbefound', array('fake_key=fake_value'));

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testGetToken()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $vimeo->setToken('fake_access_token');

        // Assert
        $this->assertSame('fake_access_token', $vimeo->getToken());
    }

    public function testGetCurlOptions()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $vimeo->setCurlOptions(array('custom_name' => 'custom_value'));
        $result = $vimeo->getCurlOptions();

        // Assert
        $this->assertInternalType('array', $result);
        $this->assertSame('custom_value', $result['custom_name']);
    }

    public function testAccessTokenWithCallingFakeRedirectUri()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->accessToken('fake_auth_code', 'https://fake.redirect.uri');

        // Assert
        $this->assertSame('invalid_client', $result['body']['error']);
    }

    public function testClientCredentialsWithDefaultScope()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->clientCredentials();

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testClientCredentialsWithArrayScope()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->clientCredentials(array('public'));

        // Assert
        $this->assertSame('You must provide a valid authenticated access token.', $result['body']['error']);
    }

    public function testBuildAuthorizationEndpointWithDefaultScopeAndNullState()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri');

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public', $result);
    }

    public function testBuildAuthorizationEndpointWithNullScopeAndNullState()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri', null);

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public', $result);
    }

    public function testBuildAuthorizationEndpointWithArrayScopeAndNullState()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->buildAuthorizationEndpoint('https://fake.redirect.uri', array('public', 'private'));

        // Assert
        $this->assertSame('https://api.vimeo.com/oauth/authorize?response_type=code&client_id=client_id&redirect_uri=https%3A%2F%2Ffake.redirect.uri&scope=public+private', $result);
    }

    public function testBuildAuthorizationEndpointWithArrayScopeAndState()
    {
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
    public function testUploadWithNonExistedFile()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->upload('./the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadWithInvalidParamShouldReturnVimeoRequestException()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->upload(__DIR__.'/../../composer.json', array('invalid_param'));
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testReplaceWithNonExistedFile()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->replace('https://vimeo.com/241711006', './the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadImageWithNonExistedFile()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadImage('https://vimeo.com/241711006', './the_file_is_invalid');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoUploadException
     */
    public function testUploadTexttrackWithNonExistedFile()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);

        // Act
        $result = $vimeo->uploadTexttrack('https://vimeo.com/241711006', './the_file_is_invalid', 'fake_track_type', 'zh_TW');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testReplaceWithVideoUriShouldReturnVimeoRequestException()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);
 
        // Act
        $result = $vimeo->replace('https://vimeo.com/241711006', __DIR__.'/../../composer.json');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testUploadImageWithPictureUriShouldReturnVimeoRequestException()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);
 
        // Act
        $result = $vimeo->uploadImage('https://vimeo.com/user59081751', __DIR__.'/../../composer.json');
    }

    /**
     * @expectedException Vimeo\Exceptions\VimeoRequestException
     */
    public function testUploadTexttrackWithPictureUriAndInvalidParamShouldReturnVimeoRequestException()
    {
        // Arrange
        $vimeo = new Vimeo($this->clientId, $this->clientSecret);
 
        // Act
        $result = $vimeo->uploadTexttrack('https://vimeo.com/user59081751', __DIR__.'/../../composer.json', 'fake_track_type', 'zh_TW');
    }
}
