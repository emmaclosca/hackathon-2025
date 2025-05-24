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
        // TODO: check that a user with same username does not exist, create new user and persist
        // TODO: make sure password is not stored in plain, and proper PHP functions are used for that
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

        // TODO: here is a sample code to start with
        $user = new User(null, $username, $passwordHash, new \DateTimeImmutable());
        $this->users->save($user);

        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        // TODO: implement this for authenticating the user
        // TODO: make sur ethe user exists and the password matches
        // TODO: don't forget to store in session user data needed afterwards
        $user = $this->users->findByUsername($username);

        if ($user === null) {
            throw new \RuntimeException('Invalid Username.');
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new \RuntimeException('Invalid Password.');
        }

        // If the login session was successfull, then store the information 
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;

        return true;
    }
}
