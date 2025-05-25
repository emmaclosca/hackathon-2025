# Budget Tracker – Evozon PHP Internship Hackathon 2025

## Starting from the skeleton

Prerequisites:

- PHP >= 8.1 with the usual extension installed, including PDO.
- [Composer](https://getcomposer.org/download)
- Sqlite3 (or another database tool that allows handling SQLite databases)
- Git
- A good PHP editor: PHPStorm or something similar

About the skeleton:

- The skeleton is built on Slim (`slim/slim : ^4.0`)
- The templating engine of choice is Twig (`slim/twig-view`)
- The dependency injection container of choice is `php-di/php-di`
- The database access layer of choice is plain PDO
- The configuration should be provided in a .env file (`vlucas/phpdotenv`)
- There is logging support by using `monolog/monolog`
- Input validation should be simply done using `webmozart/assert` and throwing Slim dedicated HTTP exceptions

## Step-by-step set-up

Install dependencies:

```
composer install
```

Set up the database:

```
cd database
./apply_migrations.sh
```

Note: be aware that, if you are using WSL2 (Windows Subsystem for Linux), you'll have trouble opening SQLite databases
with a DB management app (PHPStorm, for example) in Windows **when they are stored within the virtualized WSL2 drive**.
The solution is to store the `db.sqlite` file on the Windows drive (`/mnt/c`) and configure the path to the file in the
application config (`.env`):

```
cd database
./apply_migrations.sh /mnt/c/Users/<user>/AppData/Local/Temp/db.sqlite
```

Copy `.env.example` to `.env` and configure as necessary:

```
cp .env.example .env
```

Run the built-in server on http://localhost:8000

```
composer start
```

## Features

## Tasks

### Before you start coding

Make sure you inspect the skeleton and identify the important parts:

- `public/index.php` - the web entry point
- `app/Kernel.php` - DI container and application setup
- classes under `app` - this is where most of your code will go
- templates under `templates` are almost complete, at least in terms of static mark-up; all you need is to make use of
  the Twig syntax to make them dynamic.

### Main tasks — for having a functional application

Start coding: search for `// TODO: ...` and fill in the necessary logic. Don't limit yourself to that; you can do
whatever you want, design it the way you see fit. The TODOs are a starting point that you may choose to use.

### Extra tasks — for extra points

Solve extra requirements for extra points. Some of them you can implement from the start, others we prefer you to attack
after you have a fully functional application, should you have time left. More instructions on this in the assignment.

### Deliver well designed quality code

Before delivering your solution, make sure to:

- format every file and make sure there is no commented code left, and code looks spotless

- run static analysis tools to check for code issues:

```
composer analyze
```

- run unit tests (in case you added any):

```
composer test
```

A solution with passing analysis and unit tests will receive extra points.

## Delivery details

Participant:
- Full name: Emma Closca
- Email address: emmaclosca@gmail.com

Features fully implemented:

- User Authentication (Register)
    Displays a register user page (GET).
    Registers a new user within the application (POST). 
    Mandatory fields for creating new users are: username, password.
    Validation rules: username (≥ 4 chars), password (≥ 8 chars, 1 number).
    On success: redirect to /login page.
    On failure: render /register page and show corresponding error messages.

- User Authentication (Login/Logout)
    Displays a login page (GET).
    Logs in a user, starting a new user session (POST).
    On success: set the logged in user in the session, then redirect to Dashboard page.
    On failure: render Login page and show corresponding error message.
    Logs out a user:
      the session is destroyed
      redirects to /login.
  
- CRUD (LIST)
  List monthly expenses for the logged in user, sorted and paginated. 
  The default year-month for the listing is the current one. Regarding the previous years, they are shown in the select input only if the user had expenses during that year. Current year    is always an option. The user is allowed to select a new year-month and press a button to reload the expenses list.
  Expenses are sorted descending by date.
  Clicking on the “Add” button at the top navigates to the Expenses – Add route.
  Columns: description, amount (formatted € with 2 decimals), category, “Edit” link, “Delete” link.
  Clicking on the “Edit” link navigates to Expenses – Edit route.
  Clicking on the “Delete” link navigates to Expenses – Delete route.

  - CRUD (ADD)
  Renders a form for filling in the details of a new expense:
    Date: a date type input, default today.
    Category: a select with category names as options.
    Amount: a numeric input for introducing the amount as a float.
    Description: textarea
  Validation rules (backend side):
    Date ≤ today
    Category selected
    Amount > 0
    Description not empty
  Form action is: /expenses (POST)
  On success: redirect back to Expenses – List.
  On failure: redirect back to Expenses – Add, with prefilled previous values.

- CRUD (EDIT)
  Renders a pre-filled edit form for a given expense entity, identified by ID in the route.
  Form action is: /expenses/{id} (POST)
  Same validation & redirect logic as Expenses – Add.
  
- CRUD (DELETE)
  Must hard-delete the expense provided by ID.
  On success: redirect back to Expenses – List.
  On failure: redirect back to Expenses – List.
  
- EXTRA
  use prepared statements always when querying the DB.
  ensure a user may change/delete only his/her own expenses.
  Ensure that running composer analyze in project root outputs success message from PHPMD and PHPStan
  using the proper password hashing function in PHP.
  implement a “password again” input for ensuring no password typos.
  using the proper password verify function in PHP.

- Other instructions about setting up the application (if any): ...
