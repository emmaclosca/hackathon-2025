<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }

    public function save(Expense $expense): void
    {
        if ($expense->getId() === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO expenses (user_id, date, category, amount_cents, description)
                VALUES (:user_id, :date, :category, :amount_cents, :description)'
            );
            $statement->execute([
                'user_id' => $expense->getUserId(),
                'date' => $expense->getDate()->format('Y-m-d H:i:s'),
                'category' => $expense->getCategory(),
                'amount_cents' => $expense->amountCents,
                'description' => $expense->getDescription(),
            ]);
        } else {
            $statement = $this->pdo->prepare(
                'UPDATE expenses SET date = :date, category = :category, amount_cents = :amount_cents, description = :description WHERE id = :id'   
            );
            $statement->execute([
                'id' => $expense->getId(),
                'date' => $expense->getDate()->format('Y-m-d H:i:s'),
                'category' => $expense->getCategory(),
                'amount_cents' => $expense->amountCents,
                'description' => $expense->getDescription(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        $sql = 'SELECT * FROM expenses WHERE user_id = :user_id';
        $params = ['user_id' => $criteria['user_id']];

        if (isset($criteria['year']) && isset($criteria['month'])) {
            $sql .= ' AND strftime("%Y", date) = :year AND strftime("%m", date) = :month';
            $params['year'] = (string)$criteria['year'];
            $params['month'] = sprintf('%02d', $criteria['month']);
        }

        $sql .= ' ORDER BY date DESC LIMIT :limit OFFSET :offset';

        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $statement->bindValue(":$key", $val);
        }

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $from, PDO::PARAM_INT);
        
        $statement->execute();

        $results = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->createExpenseFromData($row);
        }

        return $results;
    }

    public function countBy(array $criteria): int
    {
        $sql = 'SELECT COUNT(*) FROM expenses WHERE user_id = :user_id';
        $params = ['user_id' => $criteria['user_id']];

        if (isset($criteria['year']) && isset($criteria['month'])) {
            $sql .= ' AND strftime("%Y", date) = :year AND strftime("%m", date) = :month';
            $params['year'] = (string)$criteria['year'];
            $params['month'] = sprintf('%02d', $criteria['month']);
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function listExpenditureYears(User $user): array
    {
        $statement = $this->pdo->prepare('SELECT DISTINCT strftime("%Y", date) as year FROM expenses WHERE user_id = :user_id ORDER BY year DESC');
        $statement->execute(['user_id' => $user->id]);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        // TODO: Implement sumAmountsByCategory() method.
        return [];
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        return [];
    }

    public function sumAmounts(array $criteria): float
    {
        // TODO: Implement sumAmounts() method.
        return 0;
    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            (int) $data['amount_cents'],
            $data['description'],
        );
    }

    public function findDistinctYearsByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT DISTINCT strftime("%Y", date) as year FROM expenses WHERE user_id = :user_id ORDER BY year DESC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
