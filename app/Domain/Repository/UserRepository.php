<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findByUsername(string $username): ?User
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $statement->execute(['username' => $username]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return new User(
            (int)$result['id'],
            $result['username'],
            $result['passwordHash'],
            new \DateTimeImmutable($result['createdAt'])
        );
    }

    public function find(mixed $id): ?User
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return new User(
            (int)$result['id'],
            $result['username'],
            $result['passwordHash'],
            new \DateTimeImmutable($result['createdAt'])
        );
    }

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO users (username, passwordHash, createdAt) VALUES (:username, :passwordHash, :createdAt)'
            );
            
            $statement->execute([
                'username' => $user->username,
                'passwordHash' => $user->passwordHash,
                'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
            ]);
        } else {
            $statement = $this->pdo->prepare(
                'UPDATE users SET username = :username, passwordHash = :passwordHash WHERE id = :id'
            );

            $statement->execute([
                'username' => $user->username,
                'passwordHash' => $user->passwordHash,
                'id' => $user->id,
            ]);
        }
    }
}
