<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\ExpenseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Repository\UserRepositoryInterface; 

use Slim\Views\Twig;

class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 20;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private readonly UserRepositoryInterface $userRepository,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id']; 
        $user = $this->userRepository->find($userId);
        $query = $request->getQueryParams();

        $page = (int)($query['page'] ?? 1);
        $pageSize = (int)($query['pageSize'] ?? self::PAGE_SIZE); 
        $month = isset($query['month']) ? (int)$query['month'] : (int)date('m');
        $year = isset($query['year']) ? (int)$query['year'] : (int)date('Y');

        $criteria = ['user_id' => $userId];
        if ($year && $month) {
            $criteria['year'] = $year;
            $criteria['month'] = $month;
        }

        $expenses = $this->expenseService->list($user, $year, $month, $page, $pageSize);
        $totalExpenses = $this->expenseService->count($criteria);
        $years = $this->expenseService->getAvailableYears($user);

        return $this->render($response, 'expenses/index.twig', [
            'expenses' => $expenses,
            'page'     => $page,
            'pageSize' => $pageSize,
            'totalExpenses' => $totalExpenses,
            'years' => $years,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $categories = ['Food', 'Transport', 'Health', 'Fun'];

        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id']; 
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $response->withStatus(403);
        }

        $data = $request->getParsedBody();

        try {
            $amount = (float) $data['amount'];
            $description = trim($data['description']);
            $category = trim($data['category']);
            $date = new \DateTimeImmutable($data['date']);

            $this->expenseService->create($user, $amount, $description, $date, $category);

            return $response->withHeader('Location', '/expenses') -> withStatus(302);

        } catch (\Throwable $e) {
            $categories = ['Food', 'Transport', 'Health', 'Fun'];

            return $this->render($response, 'expenses/create.twig', [
                'error' => $e->getMessage(),
                'categories' => $categories, 
                'old' => $data,
            ]);
        }
    }

    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $response->withStatus(403); 
        }
        $expenseId = (int)($routeParams['id'] ?? 0);
        $expense = $this->expenseService->find($expenseId);

        if (!$expense || $expense->getUserId() !== $userId) {
            return $response->withStatus(403);
        }

        $categories = ['Food', 'Transport', 'Health', 'Fun'];

        return $this->view->render($response, 'expenses/edit.twig', [
            'expense' => $expense,
            'categories' => $categories,
        ]);  
    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);

        $expenseId = (int) $routeParams['id'];
        $expense = $this->expenseService->find($expenseId);

        if (!$expense || $expense->getUserId() !== $userId) {
            return $response->withStatus(403);
        }

        $data = $request->getParsedBody();

        try {
            $amount = (float) $data['amount'];
            $description = trim($data['description']);
            $category = trim($data['category']);
            $date = new \DateTimeImmutable($data['date']);

            $this->expenseService->update($expense, $amount, $description, $date, $category);

            return $response->withHeader('Location', '/expenses')->withStatus(302);

        } catch (\Throwable $e) {
            $categories = ['Food', 'Transport', 'Health', 'Fun'];

            return $this->view->render($response, 'expenses/edit.twig', [
                'error' => $e->getMessage(),
                'expense' => $expense,
                'categories' => $categories, 
                'old' => $data,
            ]);
        } 
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        $userId = $_SESSION['user_id'];

        $expenseId = (int) $routeParams['id'];
        $expense = $this->expenseService->find($expenseId);

        if (!$expense || $expense->getUserId() !== $userId) {
            return $response->withStatus(403);
        }

        $this->expenseService->delete($expenseId);

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }
}
