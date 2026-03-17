# My Laravel App

## Overview
This is a Laravel application designed to demonstrate the framework's capabilities and provide a foundation for building web applications.

## Project Structure
The project follows the standard Laravel directory structure, which includes:

- **app/**: Contains the application logic, including controllers.
- **bootstrap/**: Initializes the application.
- **config/**: Configuration files for the application.
- **public/**: The entry point for the application, including assets.
- **resources/**: Contains views and other resources.
- **routes/**: Defines the application's routes.
- **composer.json**: Lists dependencies and scripts for the application.

## Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   ```

2. Navigate to the project directory:
   ```
   cd my-laravel-app
   ```

3. Install dependencies using Composer:
   ```
   composer install
   ```

4. Set up your environment file:
   ```
   cp .env.example .env
   ```

5. Generate the application key:
   ```
   php artisan key:generate
   ```

6. Run the migrations (if any):
   ```
   php artisan migrate
   ```

## Usage

To start the development server, run:
```
php artisan serve
```
You can then access the application at `http://localhost:8000`.

## Custom 404 Page
The application includes a custom 404 error page located at `resources/views/errors/404.blade.php`, which extends the main layout and provides a user-friendly message for not found errors.

## License
This project is open-source and available under the [MIT License](LICENSE).