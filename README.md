# 🚀 LimitX - Limit Order Exchange Mini Engine

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
</p>

A robust, real-time cryptocurrency limit order exchange engine built with Laravel 12, Vue.js 3, and Inertia.js. This application provides a modern trading platform interface with real-time order matching, live order book updates, and comprehensive portfolio management.

---

## 📋 Table of Contents

-   [Features](#-features)
-   [Technology Stack](#-technology-stack)
-   [Prerequisites](#-prerequisites)
-   [Installation](#-installation)
-   [Configuration](#-configuration)
-   [Database Setup](#-database-setup)
-   [Running the Application](#-running-the-application)
-   [Test Accounts](#-test-accounts)
-   [API Documentation](#-api-documentation)
-   [Project Structure](#-project-structure)
-   [Troubleshooting](#-troubleshooting)
-   [License](#-license)

---

## ✨ Features

-   **Limit Order Trading**: Place buy/sell limit orders with precise price and amount control
-   **Real-Time Order Matching**: Automatic price-time priority matching engine
-   **Live Order Book**: Dynamic bid/ask order book display with real-time updates
-   **Portfolio Management**: Track USD balance and crypto asset holdings
-   **Trade History**: Complete history of all executed trades
-   **Real-Time Notifications**: Instant trade execution notifications via WebSockets (Pusher)
-   **Secure Authentication**: Laravel Sanctum-based SPA authentication
-   **Modern UI**: Beautiful, responsive dark theme interface
-   **Multi-Asset Support**: Trade BTC, ETH, SOL, ADA, and more

---

## 🛠 Technology Stack

| Layer              | Technology                 |
| ------------------ | -------------------------- |
| **Backend**        | Laravel 12, PHP 8.2+       |
| **Frontend**       | Vue.js 3.5, Inertia.js 2.0 |
| **Styling**        | Tailwind CSS 4.0           |
| **Database**       | MySQL 8.0+                 |
| **Authentication** | Laravel Sanctum            |
| **Real-Time**      | Pusher + Laravel Echo      |
| **Build Tool**     | Vite 6.0                   |

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed on your system:

| Requirement | Version        | Check Command     |
| ----------- | -------------- | ----------------- |
| PHP         | 8.2 or higher  | `php -v`          |
| Composer    | 2.x            | `composer -V`     |
| Node.js     | 18.x or higher | `node -v`         |
| npm         | 9.x or higher  | `npm -v`          |
| MySQL       | 8.0 or higher  | `mysql --version` |
| Git         | Latest         | `git --version`   |

### Required PHP Extensions

-   BCMath
-   Ctype
-   cURL
-   DOM
-   Fileinfo
-   JSON
-   Mbstring
-   OpenSSL
-   PCRE
-   PDO (MySQL)
-   Tokenizer
-   XML

---

## 🔧 Installation

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd limit-order-exchange-mini-engine
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node.js Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

Copy the example environment file and configure it:

```bash
# Linux/macOS
cp .env.example .env

# Windows (Command Prompt)
copy .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

---

## ⚙️ Configuration

### Database Configuration

Open the `.env` file and configure your MySQL database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=limit_order_exchange
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

### Create the Database

Log into MySQL and create the database:

```sql
CREATE DATABASE limit_order_exchange CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Pusher Configuration (Real-Time Features)

For real-time trade notifications, configure Pusher in your `.env` file:

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

# Vite requires these for frontend
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

> **Note**: Create a free Pusher account at [pusher.com](https://pusher.com) to get your credentials.

### Application URL Configuration

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
SESSION_DOMAIN=localhost
```

---

## 🗃️ Database Setup

### Run Migrations

Execute the database migrations to create all required tables:

```bash
php artisan migrate
```

### Seed the Database (Optional but Recommended)

Populate the database with test users, assets, and sample orders:

```bash
php artisan db:seed
```

This will create:

-   **3 Test Users** with USD balances and crypto assets
-   **Sample Open Orders** in the order book for demonstration

---

## 🚀 Running the Application

### Option 1: Quick Start (Development)

Use the built-in composer script to run all services concurrently:

```bash
composer dev
```

This command starts:

-   Laravel development server (http://localhost:8000)
-   Vite development server (Hot Module Replacement)
-   Queue worker (for background jobs)
-   Laravel Pail (real-time log viewer)

### Option 2: Manual Start (Step by Step)

If you prefer to run services separately, open multiple terminals:

**Terminal 1 - Laravel Server:**

```bash
php artisan serve
```

**Terminal 2 - Vite Development Server:**

```bash
npm run dev
```

**Terminal 3 - Queue Worker (For background jobs):**

```bash
php artisan queue:work
```

### Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

---

## 🔐 Test Accounts

After running the database seeder, you can log in with these test accounts:

| Name           | Email               | Password | USD Balance |
| -------------- | ------------------- | -------- | ----------- |
| Alice Trader   | alice@example.com   | password | $100,000.00 |
| Bob Trader     | bob@example.com     | password | $25,000.00  |
| Charlie Trader | charlie@example.com | password | $100,000.00 |

Each user also has various crypto asset holdings (BTC, ETH, SOL, ADA) ready for trading.

---

## 📡 API Documentation

### Public Endpoints

| Method | Endpoint                  | Description                       |
| ------ | ------------------------- | --------------------------------- |
| GET    | `/api/orderbook/{symbol}` | Get order book for a trading pair |

### Protected Endpoints (Require Authentication)

| Method | Endpoint                  | Description                              |
| ------ | ------------------------- | ---------------------------------------- |
| GET    | `/api/profile`            | Get user profile with balance and assets |
| GET    | `/api/orders`             | Get user's orders                        |
| POST   | `/api/orders`             | Place a new limit order                  |
| POST   | `/api/orders/{id}/cancel` | Cancel an open order                     |

### Order Request Body

```json
{
    "symbol": "BTC",
    "side": "buy",
    "price": "45000.00",
    "amount": "0.5"
}
```

### Supported Trading Pairs

-   BTC/USD
-   ETH/USD
-   SOL/USD
-   ADA/USD

---

## 📁 Project Structure

```
limit-order-exchange-mini-engine/
├── app/
│   ├── Events/           # Real-time events (OrderMatched)
│   ├── Exceptions/       # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/  # API and Web controllers
│   │   ├── Middleware/   # Inertia and auth middleware
│   │   └── Requests/     # Form request validation
│   ├── Models/           # Eloquent models (User, Order, Asset, Trade)
│   └── Services/         # Business logic (OrderService)
├── database/
│   ├── migrations/       # Database schema
│   ├── seeders/          # Test data seeders
│   └── factories/        # Model factories for testing
├── resources/
│   ├── css/              # Tailwind CSS styles
│   ├── js/
│   │   ├── Components/   # Vue.js components
│   │   ├── Composables/  # Vue.js composables (useToast)
│   │   ├── Layouts/      # Page layouts
│   │   └── Pages/        # Inertia pages
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # API routes
│   ├── web.php           # Web routes
│   └── channels.php      # Broadcast channels
└── tests/                # PHPUnit tests
```

---

## 🔨 Production Deployment

### Build for Production

```bash
# Install dependencies without dev packages
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Production Environment

Update your `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=.your-domain.com
```

### Web Server Configuration

For **Nginx**, add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

For **Apache**, ensure `mod_rewrite` is enabled and `.htaccess` is allowed.

---

## 🐛 Troubleshooting

### Common Issues

**1. MySQL Key Length Error**

```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long
```

**Solution**: This is already handled in `AppServiceProvider.php`. If you still encounter it, run:

```bash
php artisan migrate:fresh --seed
```

**2. Vite Dev Server Not Connecting**

**Solution**: Ensure both Laravel and Vite servers are running:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

**3. Real-Time Notifications Not Working**

**Solution**:

-   Verify Pusher credentials in `.env`
-   Clear config cache: `php artisan config:clear`
-   Check browser console for WebSocket errors

**4. 401 Unauthenticated on API Calls**

**Solution**: Ensure `SANCTUM_STATEFUL_DOMAINS` includes your domain:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
```

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Reset database with fresh seed data
php artisan migrate:fresh --seed

# View real-time logs
php artisan pail

# Run tests
php artisan test
```

---

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

Built with ❤️ for VirgoSoft Technical Assessment

---

<p align="center">
  <strong>Happy Trading! 📈</strong>
</p>
