<?php

namespace App\Entity;

use DateTime;

interface BannerAlertInterface
{
    public const LEVEL_INFO = 0;
    public const LEVEL_WARNING = 1;

    public function setId(int $id): void;
    public function getId(): int;
    public function setName(string $name): void;
    public function getName(): string;
    public function setDescription(string $description): void;
    public function getDescription(): string;
    public function setLevel(int $level): void;
    public function getLevel(): int;
    public function setActive(bool $active): void;
    public function isActive(): bool;
    public function setDateCreation(DateTime $dateCreation): void;
    public function getDateCreation(): DateTime;
}
