# Shenava Backend API Documentation

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)

## 📋 Table of Contents
- [Overview](#overview)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [API Documentation](#api-documentation)
- [Frontend Setup](#frontend-setup)
- [Development Guide](#development-guide)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)

## 🎯 Overview

Shenava is a comprehensive audiobook platform backend built with Laravel.
This API provides endpoints for user authentication, book management, audio playback, and user preferences.

### Key Features
- 🔐 JWT Authentication
- 📚 Book Catalog Management
- 🔊 Audio Streaming
- ⭐ User Preferences & History
- 🎯 Featured Books
- 📱 RESTful API

## 🛠 System Requirements

### Required Software
| Software     | Version | Purpose                       |
|--------------|---------|-------------------------------|
| **PHP**      | 8.0+    | Backend runtime               |
| **Composer** | 2.0+    | PHP dependency management     |
| **Node.js**  | 16+     | Frontend asset compilation    |
| **NPM**      | 8+      | JavaScript package management |
| **MySQL**    | 8.0+    | Database                      |
| **Laragon**  | Latest  | Local development environment |

### PHP Extensions Required
```bash
# Required PHP extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- Zip
```

## 🚀 Installation Guide

### Step 1: Clone Repository
```bash
git clone https://github.com/your-username/shenava-backend.git
cd shenava-backend
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Frontend Dependencies
```bash
# Install Node.js dependencies
npm install

# Or if using Yarn
yarn install
```

### Step 4: Build Frontend Assets
```bash
# Development build
npm run dev

# Production build
npm run build

# Watch for changes (development)
npm run watch
```

## ⚙️ Environment Configuration

### Copy Environment File
```bash
cp .env.example .env
```

### Configure Environment Variables
Edit `.env` file with your configuration:

```env
APP_NAME=Shenava
APP_ENV=local
APP_KEY=base64:your_generated_key_here
APP_DEBUG=true
APP_URL=http://shenava.test

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shenava
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your_jwt_secret_here

# File Storage
FILESYSTEM_DISK=local

# Audio File Configuration
AUDIO_STORAGE_PATH=public/audio
AUDIO_MAX_FILE_SIZE=102400
```

### Generate Application Key
```bash
php artisan key:generate
```

### Generate JWT Secret
```bash
php artisan jwt:secret
```

## 🗃 Database Setup

### Create Database
```sql
CREATE DATABASE shenava CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Run Migrations
```bash
# Run SQL file manually
mysql -u root -p shenava < migrations/001_create_tables.sql

# Or use Laravel migrations
php artisan migrate
```

### Seed Sample Data (Optional)
```bash
php artisan db:seed
```

## 🌐 Server Configuration

### Using Laragon
1. Start Laragon
2. Right-click Laragon icon → Apache & MySQL → Start
3. Add virtual host pointing to `shenava-backend/public`

### Virtual Host Configuration
```
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/shenava-backend/public"
    ServerName shenava.test
    <Directory "C:/laragon/www/shenava-backend/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## 📚 API Documentation

### Base URL
```
http://shenava.test/api/v1
```

### Authentication Header
```http
Authorization: Bearer {jwt_token}
```

---

## 🔐 Authentication Endpoints

### Register User
**POST** `/api/v1/auth/register`

#### Request Body
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Response
```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2024-01-15T10:30:00.000000Z"
        },
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### User Login
**POST** `/api/v1/auth/login`

#### Request Body
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Response
```json
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### Get Current User
**GET** `/api/v1/auth/me`

#### Headers
```http
Authorization: Bearer {jwt_token}
```

#### Response
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "preferences": {
            "dark_mode": true,
            "sleep_timer": 30,
            "playback_speed": 1.0
        },
        "created_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### User Logout
**POST** `/api/v1/auth/logout`

#### Headers
```http
Authorization: Bearer {jwt_token}
```

#### Response
```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

---

## 📖 Books Endpoints

### Get All Books
**GET** `/api/v1/books`

#### Query Parameters
| Parameter  | Type    | Default | Description            |
|------------|---------|---------|------------------------|
| `page`     | integer | 1       | Page number            |
| `per_page` | integer | 15      | Items per page         |
| `search`   | string  | null    | Search in title/author |
| `category` | string  | null    | Filter by category     |

#### Response
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "The Great Novel",
                "author": "Author Name",
                "description": "Book description...",
                "category": "Fiction",
                "cover_image": "http://shenava.test/storage/covers/cover.jpg",
                "audio_length": 7200,
                "rating": 4.5,
                "is_featured": true,
                "chapters_count": 12,
                "created_at": "2024-01-15T10:30:00.000000Z"
            }
        ],
        "first_page_url": "http://shenava.test/api/v1/books?page=1",
        "from": 1,
        "last_page": 5,
        "last_page_url": "http://shenava.test/api/v1/books?page=5",
        "links": ["..."],
        "next_page_url": "http://shenava.test/api/v1/books?page=2",
        "path": "http://shenava.test/api/v1/books",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 75
    }
}
```

### Get a Single Book
**GET** `/api/v1/books/{id}`

#### Response
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "title": "The Great Novel",
        "author": "Author Name",
        "description": "Full book description...",
        "category": "Fiction",
        "cover_image": "http://shenava.test/storage/covers/cover.jpg",
        "audio_length": 7200,
        "rating": 4.5,
        "is_featured": true,
        "chapters": [
            {
                "id": 1,
                "chapter_number": 1,
                "title": "Chapter 1: The Beginning",
                "audio_url": "http://shenava.test/storage/audio/chapter1.mp3",
                "duration": 3600,
                "file_size": 52428800
            }
        ],
        "created_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### Get Books by Category
**GET** `/api/v1/books/category/{category}`

#### Response
Same structure as "Get All Books" but filtered by category.

### Get Featured Books
**GET** `/api/v1/books/featured`

#### Response
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "title": "Featured Book 1",
            "author": "Author Name",
            "description": "Featured book description...",
            "category": "Fiction",
            "cover_image": "http://shenava.test/storage/covers/featured1.jpg",
            "audio_length": 5400,
            "rating": 4.8,
            "is_featured": true,
            "created_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

---

## 🔊 Audio Playback Endpoints

### Get Audio File URL
**GET** `/api/v1/audio/{chapter_id}`

#### Headers
```http
Authorization: Bearer {jwt_token}
```

#### Response
```json
{
    "status": "success",
    "data": {
        "chapter_id": 1,
        "audio_url": "http://shenava.test/storage/audio/chapter1.mp3",
        "expires_at": "2024-01-15T11:30:00.000000Z",
        "duration": 3600,
        "file_size": 52428800
    }
}
```

### Update Listening Progress
**POST** `/api/v1/listening/progress`

#### Headers
```http
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

#### Request Body
```json
{
    "chapter_id": 1,
    "current_time": 125,
    "completed": false
}
```

#### Response
```json
{
    "status": "success",
    "message": "Progress updated successfully",
    "data": {
        "chapter_id": 1,
        "current_time": 125,
        "completed": false,
        "updated_at": "2024-01-15T10:35:00.000000Z"
    }
}
```

### Get Listening History
**GET** `/api/v1/listening/history`

#### Headers
```http
Authorization: Bearer {jwt_token}
```

#### Query Parameters
| Parameter  | Type    | Default | Description    |
|------------|---------|---------|----------------|
| `page`     | integer | 1       | Page number    |
| `per_page` | integer | 10      | Items per page |

#### Response
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "book": {
                    "id": 1,
                    "title": "The Great Novel",
                    "author": "Author Name",
                    "cover_image": "http://shenava.test/storage/covers/cover.jpg"
                },
                "chapter": {
                    "id": 1,
                    "chapter_number": 1,
                    "title": "Chapter 1: The Beginning"
                },
                "current_time": 125,
                "completed": false,
                "last_listened": "2024-01-15T10:35:00.000000Z"
            }
        ],
        "total": 15
    }
}
```

---

## ⚙️ User Preferences

### Update User Preferences
**PUT** `/api/v1/user/preferences`

#### Headers
```http
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

#### Request Body
```json
{
    "dark_mode": true,
    "sleep_timer": 30,
    "playback_speed": 1.2,
    "auto_play": true,
    "quality": "high"
}
```

#### Response
```json
{
    "status": "success",
    "message": "Preferences updated successfully",
    "data": {
        "dark_mode": true,
        "sleep_timer": 30,
        "playback_speed": 1.2,
        "auto_play": true,
        "quality": "high",
        "updated_at": "2024-01-15T10:40:00.000000Z"
    }
}
```

## 🎨 Frontend Setup

### Required NPM Packages
```json
{
    "dependencies": {
        "axios": "^1.0.0",
        "vue": "^3.0.0",
        "pinia": "^2.0.0",
        "vue-router": "^4.0.0"
    },
    "devDependencies": {
        "laravel-vite-plugin": "^0.7.0",
        "vite": "^4.0.0",
        "tailwindcss": "^3.0.0",
        "postcss": "^8.0.0",
        "autoprefixer": "^10.0.0"
    }
}
```

### Install Frontend Dependencies
```bash
npm install axios vue@next pinia vue-router@next
npm install -D laravel-vite-plugin vite tailwindcss postcss autoprefixer
```

### Build Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

## 🔧 Development Guide

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/authentication

# Make changes and commit
git add .
git commit -m "feat: add user authentication"

# Push to remote
git push origin feature/authentication

# Create pull request
```

### Code Style
Follow PSR-12 coding standards:
```bash
# Check code style
composer check-style

# Fix code style
composer fix-style
```

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter AuthenticationTest

# Generate test coverage
php artisan test --coverage-html coverage/
```

## 🧪 Testing API Endpoints

### Using Postman
1. Import Postman collection from `docs/postman-collection.json`
2. Set base URL: `http://shenava.test/api/v1`
3. For authenticated endpoints, use the token from login response

### Example cURL Commands
```bash
# User Registration
curl -X POST http://shenava.test/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

# User Login
curl -X POST http://shenava.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Get Books (with auth)
curl -X GET http://shenava.test/api/v1/books \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🚀 Deployment

### Production Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=production_host
DB_DATABASE=production_db
DB_USERNAME=production_user
DB_PASSWORD=production_password

# JWT
JWT_SECRET=production_jwt_secret

# File Storage
FILESYSTEM_DISK=s3
```

### Deployment Steps
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🐛 Troubleshooting

### Common Issues

#### 1. JWT Token Issues
```bash
# Clear JWT secret and regenerate
php artisan cache:clear
php artisan config:clear
php artisan jwt:secret
```

#### 2. File Permissions
```bash
# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

#### 3. Database Connection
```bash
# Clear configuration cache
php artisan config:clear
php artisan cache:clear

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

#### 4. Audio File Access
- Ensure audio files are in `storage/app/audio` directory
- Run `php artisan storage:link` to create symbolic link
- Check file permissions

### Debug Mode
For development, enable debug mode in `.env`:
```env
APP_DEBUG=true
```

## 📞 Support

For technical support:
1. Check this documentation
2. Review API logs in `storage/logs/laravel.log`
3. Create an issue on GitHub repository
4. Contact development team

---

**Version:** 1.0.0  
**Last Updated:** January 2024  
**Maintainer:** Shenava Development Team
```

این مستندات کامل شامل:

- 🎯 معرفی کامل پروژه
- 🛠 نیازمندی‌های سیستم
- 🚀 راهنمای نصب قدم به قدم
- ⚙️ تنظیمات محیط
- 🗃 راه‌اندازی دیتابیس
- 🌐 پیکربندی سرور
- 📚 مستندات کامل API با مثال‌های JSON
- 🎨 راهنمای فرانت‌اند
- 🔧 راهنمای توسعه
- 🧪 تست API
- 🚀 دپلوی
- 🐛 عیب‌یابی

همه بخش‌ها به صورت کامل و با جزئیات نوشته شده‌اند.