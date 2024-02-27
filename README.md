# Subscription Manager Backend

This is an PHP/Symfony Powered API for 
Subscription Manager Application.

## Requirements

- PHP > 8.2
- MySQL > 8.0

## API Endpoints:

### Authentication
- `POST /api/auth/login`: Authenticate a user and retrieve an access token.
- `POST /api/auth/register`: Register a new user.

### Subscriptions
- `GET /api/subscriptions`: Get a list of all the available subscriptions.
- `GET /api/subscriptions/{subscription_id}`: Get a single subscription by ID.
- `POST /api/subscriptions/{subscription_id}/subscribe`: Subscribe to a subscription.
- `POST /api/subscriptions/{subscription_id}/unsubscribe`: Unsubscribe from a subscription.
- `GET /api/subscriptions/me`: Get the current user's subscriptions.

### Payment
- `POST /api/payment`: Make a payment to subscribe to a subscription.

Further Details can be found in:

- `/api/doc`:

## Running Application:

1. Install dependencies: `composer install`
2. Run migrations: `php bin/console doctrine:migrations:migrate`
3. Run fixtures to load testing data: `php bin/console doctrine:fixtures:load`
4. Start symfony server: `symfony server:start`