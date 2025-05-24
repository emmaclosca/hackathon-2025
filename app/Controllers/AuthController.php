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
        // TODO: you also have a logger service that you can inject and use anywhere; file is var/app.log
        $this->logger->info('Register page requested');

        return $this->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user registration
        // Taking the input from the user and storing it into an array so we can access it
        $inputData = (array)$request->getParsedBody();

        // Here we are providing a value thats default if field is missing with ?? "",
        // While fetching the information 
        $username = trim($inputData['username'] ?? ''); 
        $password = $inputData['password'] ?? '';
        $confirmPassword = $inputData['confirmPassword'] ?? '';

        $errors = []; // This array is set up to collect errors

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

        // This if statement saves the username to avoid the user typing it again and re-renders to show the errors
        if (!empty($errors)) {
            return $this->render($response, 'auth/register.twig', [
                'errors' => $errors,
                'existingInput' => ['username' => $username]
            ]);
        }

        try {
            $this->authService->register($username, $password); // Calling the register method to validate the requirements and save the user

            return $response->withHeader('Location', '/login')->withStatus(302);

        // When an error is thrown, this catches it and re-renders the page 
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
        // TODO: call corresponding service to perform user login, handle login failures
            $inputData = (array)$request->getParsedBody();

            $username = trim($inputData['username'] ?? ''); 
            $password = $inputData['password'] ?? '';

            $errors = []; // This array is set up to collect errors

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
            $this->authService->attempt($username, $password); // Calling the attempt method to authenticate the user

            return $response->withHeader('Location', '/register')->withStatus(302); // LOCATION AFTER LOGIN !!!!!!!

        // When an error is thrown, this catches it and re-renders the page 
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
        // TODO: handle logout by clearing session data and destroying session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION = [];
        session_destroy();

        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
