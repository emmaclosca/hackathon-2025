<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

final class Expense
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public DateTimeImmutable $date,
        public string $category,
        public int $amountCents,
        public string $description,
    ) {}

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setAmountCents(string $amountcents): void
    {
        $this->amountCents = $amountCents;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getAmount(): int
    {
        return $this->amountCents;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
