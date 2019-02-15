# User management system
This is a basic user management system api built with symfony 4

## Setup
Clone the project and run composer to install libraries
```bash
composer install
```

Update the `.env` to sooth your database configuration and run db create command
```bash
php bin/console doctrine:database:create
```

Run migration to create the database tables
```bash
php bin/console doctrine:migrations:migrate
```

## Endpoints
Two main endpoints are provided. One for `Users` (`/users`) and another for `Groups` (`/groups`).
AWT token is required to access these endpoints. User Provider is implemented using Symfony `in_memory`. 
|username| admin|
|password| admin|

For HATEOAS API response format, prepend the endpoints with `/api`.

For more details, run `php bin/console debug:router`. 

## Running Test
Create test db by running database create command
```bash
php bin/console doctrine:database:create -e test
```

Run migration to create test database tables
```bash
php bin/console doctrine:migrations:migrate -e test
```

Run tests after successful database migration
```bash
php bin/phpunit
```

## UML

## Database Model


