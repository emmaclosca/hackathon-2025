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
        // TODO: implement this action method to display the expenses page

        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user

        // parse request parameters
        $userId = $_SESSION['user_id']; // TODO: obtain logged-in user ID from session
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
        $totalExpenses = $this->expenseService->count($userId, $year, $month);
        $years = $this->expenseService->getAvailableYears($userId);

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
        // TODO: implement this action method to display the create expense page
        $categories = ['Food', 'Transport', 'Health', 'Fun'];
        // Hints:
        // - obtain the list of available categories from configuration and pass to the view

        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        // TODO: implement this action method to create a new expense
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

            // Hints:
            // - use the session to get the current user ID
            // - use the expense service to create and persist the expense entity
            // - rerender the "expenses.create" page with included errors in case of failure
            // - redirect to the "expenses.index" page in case of success

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
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not

        $expense = ['id' => 1];

        return $this->render($response, 'expenses/edit.twig', ['expense' => $expense, 'categories' => []]);
    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success

        return $response;
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page

        return $response;
    }
}
