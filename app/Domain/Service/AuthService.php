<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(string $username, string $password): User
    {
        $usernameTaken = $this->users->findByUsername($username);
        if ($usernameTaken !== null) {
            throw new \RuntimeException('This username has been taken, try a different username.');
        }

        if (strlen($username) < 4) {
            throw new \RuntimeException('The length of your username must be at least 4 character.');
        }

        if (strlen($password) < 8 || !preg_match("/\d/", $password)){
            throw new \RuntimeException('Your password must have at least 8 characters and a number.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = new User(null, $username, $passwordHash, new \DateTimeImmutable());
        $this->users->save($user);

        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        error_log("Attempting login for user: $username");

        $user = $this->users->findByUsername($username);

        if ($user === null) {
            throw new \RuntimeException('Invalid Username.');
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new \RuntimeException('Invalid Password.');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;

        return true;
    }
}
