<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        $this->logger->info('Register page requested');

        return $this->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        $inputData = (array)$request->getParsedBody();

        $username = trim($inputData['username'] ?? ''); 
        $password = $inputData['password'] ?? '';
        $confirmPassword = $inputData['confirmPassword'] ?? '';

        $errors = []; 

        if ($username === '') {
            $errors['username'] = 'You must input a username.';
        }
        if ($password === '') {
            $errors['password'] = 'You must input a password.';
        }
        if ($confirmPassword === '') {
            $errors['confirmPassword'] = 'You must input your password again.';
        }
        if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
            $errors['confirmPassword'] = 'The passwords you inputted do not match.';
        }

        if (!empty($errors)) {
            return $this->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'existingInput' => ['username' => $username]
            ]);
        }

        try {
            $this->authService->register($username, $password); 

            return $response->withHeader('Location', '/login')->withStatus(302);

        } catch (\RuntimeException $ex) {
            $errors['message'] = $ex->getMessage();

            return $this->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'existingInput' => ['username' => $username]
         ]);
        }
    }

    public function showLogin(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
            $inputData = (array)$request->getParsedBody();

            $username = trim($inputData['username'] ?? ''); 
            $password = $inputData['password'] ?? '';

            $errors = []; 

            if ($username === '') {
                $errors['username'] = 'You must input a username.';
            }
            if ($password === '') {
                $errors['password'] = 'You must input a password.';
            }

            if (!empty($errors)) {
            return $this->render($response, 'auth/login.twig', [
                'errors' => $errors,
                'existingInput' => ['username' => $username]
            ]);
        }

        try {
            $this->authService->attempt($username, $password); 

            return $response->withHeader('Location', '/')->withStatus(302); 

        } catch (\RuntimeException $ex) {
            $errors['message'] = $ex->getMessage();

            return $this->render($response, 'auth/login.twig', [
                'errors' => $errors,
                'existingInput' => ['username' => $username]
        ]);
        }   
    }

    public function logout(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION = [];
        session_destroy();

        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
