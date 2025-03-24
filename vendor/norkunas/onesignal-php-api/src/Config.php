<?php

declare(strict_types=1);

namespace OneSignal;

final class Config
{
    /**
     * @var non-empty-string
     */
    private string $applicationId;

    /**
     * @var non-empty-string
     */
    private string $applicationAuthKey;

    /**
     * @var non-empty-string|null
     */
    private ?string $userAuthKey;

    /**
     * @param non-empty-string      $applicationId
     * @param non-empty-string      $applicationAuthKey
     * @param non-empty-string|null $userAuthKey
     */
    public function __construct(string $applicationId, string $applicationAuthKey, ?string $userAuthKey = null)
    {
        $this->applicationId = $applicationId;
        $this->applicationAuthKey = $applicationAuthKey;
        $this->userAuthKey = $userAuthKey;
    }

    /**
     * Get OneSignal application id.
     *
     * @return non-empty-string
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Get OneSignal application authentication key.
     *
     * @return non-empty-string
     */
    public function getApplicationAuthKey(): string
    {
        return $this->applicationAuthKey;
    }

    /**
     * Get user authentication key.
     *
     * @return non-empty-string|null
     */
    public function getUserAuthKey(): ?string
    {
        return $this->userAuthKey;
    }
}
