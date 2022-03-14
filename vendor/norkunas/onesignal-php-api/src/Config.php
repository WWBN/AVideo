<?php

declare(strict_types=1);

namespace OneSignal;

final class Config
{
    private $applicationId;
    private $applicationAuthKey;
    private $userAuthKey;

    public function __construct(string $applicationId, string $applicationAuthKey, string $userAuthKey = null)
    {
        $this->applicationId = $applicationId;
        $this->applicationAuthKey = $applicationAuthKey;
        $this->userAuthKey = $userAuthKey;
    }

    /**
     * Get OneSignal application id.
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Get OneSignal application authentication key.
     */
    public function getApplicationAuthKey(): string
    {
        return $this->applicationAuthKey;
    }

    /**
     * Get user authentication key.
     */
    public function getUserAuthKey(): ?string
    {
        return $this->userAuthKey;
    }
}
