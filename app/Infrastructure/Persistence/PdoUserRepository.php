<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function find(mixed $id): ?User
    {
        $query = 'SELECT * FROM users WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return new User(
            $data['id'],
            $data['username'],
            $data['passwordHash'],
            new DateTimeImmutable($data['createdAt']),
        );
    }

    public function findByUsername(string $username): ?User
{
    $query = 'SELECT * FROM users WHERE username = :username';
    $statement = $this->pdo->prepare($query);
    $statement->execute(['username' => $username]);
    $data = $statement->fetch();

    if ($data === false) {
        return null;
    }

    return new User(
        $data['id'],
        $data['username'],
        $data['passwordHash'],
        new DateTimeImmutable($data['createdAt']),
    );
}

        public function save(User $user): void
    {
        $query = 'INSERT INTO users (username, passwordHash, createdAt) VALUES (:username, :passwordHash, :createdAt)';
        $statement = $this->pdo->prepare($query);
        $statement->execute([
            'username' => $user->username,
            'passwordHash' => $user->passwordHash,
            'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
        ]);
    }
}
