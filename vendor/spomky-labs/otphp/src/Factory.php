<?php

declare(strict_types=1);

namespace OTPHP;

use function count;
use OTPHP\Exception\InvalidProvisioningUriException;
use Psr\Clock\ClockInterface;
use function sprintf;
use Throwable;

/**
 * This class is used to load OTP object from a provisioning Uri.
 *
 * @readonly
 *
 * @see \OTPHP\Test\FactoryTest
 */
final class Factory implements FactoryInterface
{
    public static function loadFromProvisioningUri(string $uri, ?ClockInterface $clock = null): OTPInterface
    {
        try {
            $parsed_url = Url::fromString($uri);
            $parsed_url->getScheme() === 'otpauth' || throw new InvalidProvisioningUriException('Invalid scheme.');
        } catch (Throwable $throwable) {
            throw new InvalidProvisioningUriException(
                'Not a valid OTP provisioning URI',
                $throwable->getCode(),
                $throwable
            );
        }
        if ($clock === null) {
            trigger_deprecation(
                'spomky-labs/otphp',
                '11.3.0',
                'The parameter "$clock" will become mandatory in 12.0.0. Please set a valid PSR Clock implementation instead of "null".'
            );
            $clock = new InternalClock();
        }

        try {
            $otp = self::createOTP($parsed_url, $clock);

            self::populateOTP($otp, $parsed_url);
        } catch (InvalidProvisioningUriException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new InvalidProvisioningUriException(
                'Not a valid OTP provisioning URI',
                $throwable->getCode(),
                $throwable
            );
        }

        return $otp;
    }

    private static function populateParameters(OTPInterface $otp, Url $data): void
    {
        foreach ($data->getQuery() as $key => $value) {
            $otp->setParameter($key, $value);
        }
    }

    private static function populateOTP(OTPInterface $otp, Url $data): void
    {
        self::populateParameters($otp, $data);
        $result = explode(':', rawurldecode(substr($data->getPath(), 1)));

        if (count($result) < 2) {
            $otp->setIssuerIncludedAsParameter(false);

            return;
        }

        $issuerFromLabel = $result[0];
        $issuerFromParameter = $otp->getIssuer();

        if ($issuerFromParameter !== null) {
            // Issuer parameter takes precedence over issuer in label
            // According to Google Authenticator spec: "they should be equal" but not required to be
            $otp->setIssuerIncludedAsParameter(true);
        } else {
            // No issuer parameter, use the issuer from label
            $issuerFromLabel !== '' || throw new InvalidProvisioningUriException(
                'Issuer from label must not be empty.'
            );
            $otp->setIssuer($issuerFromLabel);
        }
    }

    private static function createOTP(Url $parsed_url, ClockInterface $clock): OTPInterface
    {
        switch ($parsed_url->getHost()) {
            case 'totp':
                $totp = TOTP::createFromSecret($parsed_url->getSecret(), $clock);
                $totp->setLabel(self::getLabel($parsed_url->getPath()));

                return $totp;
            case 'hotp':
                $hotp = HOTP::createFromSecret($parsed_url->getSecret());
                $hotp->setLabel(self::getLabel($parsed_url->getPath()));

                return $hotp;
            default:
                throw new InvalidProvisioningUriException(sprintf('Unsupported "%s" OTP type', $parsed_url->getHost()));
        }
    }

    /**
     * @param non-empty-string $data
     * @return non-empty-string
     */
    private static function getLabel(string $data): string
    {
        $result = explode(':', rawurldecode(substr($data, 1)));
        $label = count($result) === 2 ? $result[1] : $result[0];
        $label !== '' || throw new InvalidProvisioningUriException('Label must not be empty.');

        return $label;
    }
}
