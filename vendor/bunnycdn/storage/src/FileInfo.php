<?php

namespace Bunny\Storage;

class FileInfo
{
    private string $guid;
    private string $path;
    private string $name;
    private int $size;
    private bool $isDirectory;
    private string $checksum;
    private \DateTimeImmutable $dateCreated;
    private \DateTimeImmutable $dateModified;

    public function __construct(string $guid, string $path, string $name, int $size, bool $isDirectory, string $checksum, \DateTimeImmutable $dateCreated, \DateTimeImmutable $dateModified)
    {
        $this->guid = $guid;
        $this->path = $path;
        $this->name = $name;
        $this->size = $size;
        $this->isDirectory = $isDirectory;
        $this->checksum = $checksum;
        $this->dateCreated = $dateCreated;
        $this->dateModified = $dateModified;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getDateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getDateModified(): \DateTimeImmutable
    {
        return $this->dateModified;
    }
}
