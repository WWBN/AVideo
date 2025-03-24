<?php

declare(strict_types=1);

namespace OneSignal\Response\Segment;

use DateTimeImmutable;

class Segment
{
    /**
     * @var non-empty-string
     */
    protected string $id;

    /**
     * @var non-empty-string
     */
    protected string $name;

    protected DateTimeImmutable $createdAt;

    protected DateTimeImmutable $updatedAt;

    /**
     * @var non-empty-string
     */
    protected string $appId;

    protected bool $readOnly;

    protected bool $isActive;

    /**
     * @param non-empty-string $id
     * @param non-empty-string $name
     * @param non-empty-string $appId
     */
    public function __construct(
        string $id,
        string $name,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        string $appId,
        bool $readOnly,
        bool $isActive
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->appId = $appId;
        $this->readOnly = $readOnly;
        $this->isActive = $isActive;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
