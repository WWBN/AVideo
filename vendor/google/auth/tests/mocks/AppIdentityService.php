<?php

namespace google\appengine\api\app_identity;

class AppIdentityService
{
    public static $scope;
    public static $accessToken = [
        'access_token' => 'xyz',
        'expiration_time' => '2147483646',
    ];
    public static $serviceAccountName;
    public static $applicationId;

    public static function getAccessToken($scope)
    {
        self::$scope = $scope;

        return self::$accessToken;
    }

    public static function signForApp($stringToSign)
    {
        return [
            'signature' => 'Signed: ' . $stringToSign
        ];
    }

    public static function getServiceAccountName()
    {
        return self::$serviceAccountName;
    }

    public static function getApplicationId()
    {
        return self::$applicationId;
    }
}
