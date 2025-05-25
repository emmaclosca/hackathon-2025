<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Psr\Http\Message\UploadedFileInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function list(User $user, int $year, int $month, int $pageNumber, int $pageSize): array
    {
        $offsetCalculation = ($pageNumber - 1) * $pageSize;

        $criteria = [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month,
        ];

        return $this->expenses->findBy($criteria,$offsetCalculation, $pageSize); 
    }

    public function count(array $criteria): int
    {
        return $this->expenses->countBy($criteria);
    }
    
    public function create(
        User $user,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('The amount of the expense must be greater than 0.');
        }

        if (trim($description) === '') {
            throw new \InvalidArgumentException('The description of the expense is required.');
        }

        if (trim($category) === '') {
            throw new \InvalidArgumentException('The category of the expense is required.');
        }

        $amountCents = (int) round($amount * 100);

        $expense = new Expense(null, $user->id, $date, $category, $amountCents, $description);
        $this->expenses->save($expense);
    }

    public function update(
        Expense $expense,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('The amount of the expense must be greater than 0.');
        }

        if (trim($description) === '') {
            throw new \InvalidArgumentException('The description of the expense is required.');
        }

        if (trim($category) === '') {
            throw new \InvalidArgumentException('The category of the expense is required.');
        }

        $expense->setAmountCents((int) round($amount * 100));
        $expense->setDescription($description);
        $expense->setDate($date);
        $expense->setCategory($category);

        $this->expenses->save($expense);
    }

    public function importFromCsv(User $user, UploadedFileInterface $csvFile): int
    {
        // TODO: process rows in file stream, create and persist entities
        // TODO: for extra points wrap the whole import in a transaction and rollback only in case writing to DB fails

        return 0; // number of imported rows
    }

    public function getAvailableYears(User $user): array
    {
        return $this->expenses->findDistinctYearsByUserId($user->id);
    }

    public function find(int $id): ?Expense
    {
        return $this->expenses->find($id);
    }

    public function delete(int $id): void
    {
        $this->expenses->delete($id);
    }
}
