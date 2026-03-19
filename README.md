# AgriAssistAI 

>  **This project is currently under active development.**

## About
AgriAssistAI is an intelligent plant health monitoring and disease diagnosis system. It leverages AI to analyze plant images and provide real-time health assessments, helping gardeners and farmers identify plant diseases and receive actionable care recommendations.

## Status
 Work in Progress — features may be incomplete or change at any time.

## Tech Stack
- **Backend**: Laravel (PHP 8+)
- **Frontend**: TypeScript / React with Inertia.js
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Database**: MySQL
- **Testing**: Pest
- **Package Manager**: Composer (PHP), npm (JavaScript)

## Getting Started

### Requirements
- PHP 8.1+
- Node.js 18+
- Composer
- MySQL 8.0+

### Installation
```bash
# Clone the repository
git clone https://github.com/alegasan/AgriAssistAI.git
cd AgriAssistAI

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Set up environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Start development server
npm run dev

# In another terminal, start Laravel
php artisan serve
```

## Development

### Running Tests
```bash
# Run all tests with Pest
php artisan test

# Run tests with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Run Pint for PHP code formatting
./vendor/bin/pint

# Run ESLint for TypeScript/JavaScript
npm run lint
```


## Author
[Alegasan](https://github.com/alegasan)

---


