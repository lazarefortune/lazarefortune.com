# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **lazarefortune.com**, a personal website for a web developer built with Symfony 6.4 and modern frontend technologies. The project uses a Domain-Driven Design (DDD) architecture and includes features like courses, authentication, premium subscriptions, and content management.

## Development Commands

### Frontend Development
- `pnpm install` - Install frontend dependencies
- `pnpm run dev` - Build assets for development
- `pnpm run watch` - Watch and rebuild assets on changes
- `pnpm run dev-server` - Start Webpack dev server with hot reload
- `pnpm run build` - Build assets for production

### Backend Development
- `composer install` - Install PHP dependencies
- `php bin/console doctrine:migrations:migrate` - Run database migrations
- `php bin/console doctrine:database:create --if-not-exists` - Create database if not exists

### Docker Environment
- `make setup` - Build and start Docker containers
- `make build` - Build Docker containers
- `make up` - Start Docker containers
- `make down` - Stop Docker containers

### Testing & Quality
- `vendor/bin/phpunit` - Run PHPUnit tests
- `vendor/bin/phpstan analyse` - Run PHPStan static analysis (level 8)

### Installation
- `make install` - Complete installation (dependencies, database setup, migrations)

## Architecture

### Domain-Driven Design Structure
The `src/Domain/` directory contains business logic organized by bounded contexts:

- **Auth/** - Authentication, registration, password reset, user management
- **Course/** - Course content, formations, technologies, chapters
- **Premium/** - Subscription management, payments, plans
- **Comment/** - Comment system for content
- **Badge/** - Achievement/badge system for users
- **Notification/** - In-app notification system
- **Newsletter/** - Email newsletter management
- **Search/** - Search functionality
- **Quiz/** - Quiz system with scoring
- **Contact/** - Contact form and message handling
- **Feedback/** - User feedback collection
- **History/** - User progress tracking

### Key Technical Details

- **PHP 8.1+** with Symfony 6.4 framework
- **Domain Events** - Used throughout for decoupled communication between domains
- **Repository Pattern** - Data access abstraction in each domain
- **Form Handling** - Symfony Forms with custom types
- **Event Subscribers** - Domain-specific event handling
- **Payment Integration** - PayPal and Stripe support
- **OAuth** - Google and GitHub authentication
- **Image Processing** - Intervention/Image and Liip/ImagineBundle
- **Video Processing** - YouTube integration and video metadata

### Frontend Stack
- **Webpack Encore** - Asset compilation and bundling
- **React** - Component-based UI elements
- **Stimulus** - Symfony UX Stimulus bridge for progressive enhancement
- **Sass/SCSS** - Styling with Tailwind CSS integration
- **Custom Elements** - Web components for reusable functionality

### Database
- **Doctrine ORM** - Entity management and migrations
- **MySQL/MariaDB** - Primary database
- **Redis** - Caching and sessions

### File Structure Notes
- `src/Http/` - Controllers, forms, security, Twig extensions
- `src/Infrastructure/` - External services, payments, storage, search
- `assets/` - Frontend code (JS, SCSS, React components)
- `templates/` - Twig templates organized by layout type
- `var/` - Contains GeoLite2 database for geolocation

### Development Environment
- Docker-based development setup
- Webpack dev server on `0.0.0.0` for container access
- Hot module replacement enabled for frontend development

### Testing
- PHPUnit for unit and integration tests
- Symfony test environment configured
- Test fixtures using Alice/Faker

## Important Notes
- The project requires GeoLite2-Country.mmdb file in `var/` directory for geolocation features
- Uses custom form types for complex UI elements
- Implements comprehensive event-driven architecture
- Payment processing integrated with multiple providers
- Multi-language support (French primary)