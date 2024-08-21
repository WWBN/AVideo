<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;
use InvalidArgumentException;
use ParagonIE\ConstantTime\Base32;
use function Safe\sprintf;

trait ParameterTrait
{
    /**
     * @var array<string, mixed>
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $issuer;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var bool
     */
    private $issuer_included_as_parameter = true;

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        $parameters = $this->parameters;

        if (null !== $this->getIssuer() && true === $this->isIssuerIncludedAsParameter()) {
            $parameters['issuer'] = $this->getIssuer();
        }

        return $parameters;
    }

    public function getSecret(): string
    {
        return $this->getParameter('secret');
    }

    public function setSecret(?string $secret): void
    {
        $this->setParameter('secret', $secret);
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->setParameter('label', $label);
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): void
    {
        $this->setParameter('issuer', $issuer);
    }

    public function isIssuerIncludedAsParameter(): bool
    {
        return $this->issuer_included_as_parameter;
    }

    public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter): void
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    public function getDigits(): int
    {
        return $this->getParameter('digits');
    }

    private function setDigits(int $digits): void
    {
        $this->setParameter('digits', $digits);
    }

    public function getDigest(): string
    {
        return $this->getParameter('algorithm');
    }

    private function setDigest(string $digest): void
    {
        $this->setParameter('algorithm', $digest);
    }

    public function hasParameter(string $parameter): bool
    {
        return \array_key_exists($parameter, $this->parameters);
    }

    public function getParameter(string $parameter)
    {
        if ($this->hasParameter($parameter)) {
            return $this->getParameters()[$parameter];
        }

        throw new InvalidArgumentException(sprintf('Parameter "%s" does not exist', $parameter));
    }

    public function setParameter(string $parameter, $value): void
    {
        $map = $this->getParameterMap();

        if (true === \array_key_exists($parameter, $map)) {
            $callback = $map[$parameter];
            $value = $callback($value);
        }

        if (property_exists($this, $parameter)) {
            $this->$parameter = $value;
        } else {
            $this->parameters[$parameter] = $value;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParameterMap(): array
    {
        return [
            'label' => function ($value) {
                Assertion::false($this->hasColon($value), 'Label must not contain a colon.');

                return $value;
            },
            'secret' => function ($value): string {
                if (null === $value) {
                    $value = Base32::encodeUpper(random_bytes(64));
                }
                $value = trim(mb_strtoupper($value), '=');

                return $value;
            },
            'algorithm' => function ($value): string {
                $value = mb_strtolower($value);
                Assertion::inArray($value, hash_algos(), sprintf('The "%s" digest is not supported.', $value));

                return $value;
            },
            'digits' => function ($value): int {
                Assertion::greaterThan($value, 0, 'Digits must be at least 1.');

                return (int) $value;
            },
            'issuer' => function ($value) {
                Assertion::false($this->hasColon($value), 'Issuer must not contain a colon.');

                return $value;
            },
        ];
    }

    private function hasColon(string $value): bool
    {
        $colons = [':', '%3A', '%3a'];
        foreach ($colons as $colon) {
            if (false !== mb_strpos($value, $colon)) {
                return true;
            }
        }

        return false;
    }
}
