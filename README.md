# Shenava Backend (Initial scaffold)

## Requirements
- PHP 8.0+
- Composer
- NodeJs(NPM)
- MySQL 8+
- Laragon (you said you use Laragon)

## Quick start
1. Copy `.env.example` to `.env` and fill DB + JWT secret.
2. `composer install`
3. Create database `shenava` and run SQL in `migrations/001_create_tables.sql`.
4. Point your virtual host to `public/` directory (e.g. http://shenava.test OR localhost/shenava).
5. Start server (Laragon) and test endpoints:
## API Endpoints Documentation:
### Authentication Endpoints:
- POST /api/v1/auth/register - User registration
- POST /api/v1/auth/login - User login
- POST /api/v1/auth/logout - User logout
- GET /api/v1/auth/me - Get current user
### Books Endpoints:
- GET /api/v1/books - Get all books with pagination
- GET /api/v1/books/{id} - Get single book details
- GET /api/v1/books/category/{category} - Get books by category
- GET /api/v1/books/featured - Get featured books
### Audio Playback Endpoints:
- GET /api/v1/audio/{chapter_id} - Get audio file URL
- POST /api/v1/listening/progress - Update listening progress
- GET /api/v1/listening/history - Get listening history
### User Preferences:
- PUT /api/v1/user/preferences - Update user preferences (dark mode, sleep timer, etc.)

## Git
Use branches like `feature/<name>` and the provided git commands.