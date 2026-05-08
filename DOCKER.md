# Docker Setup Guide for TeleCompensation

## Prerequisites

- Docker Desktop (includes Docker and Docker Compose)
- Git
- 4GB+ available RAM
- 10GB+ disk space

## Quick Start

### 1. Clone and Setup

```bash
# Clone the repository
git clone <repository-url>
cd telecompensation

# Copy environment file
cp .env.docker .env

# Generate Laravel app key
docker-compose run --rm app php artisan key:generate
```

### 2. Build and Start

```bash
# Build images
docker-compose build

# Start all containers
docker-compose up -d

# Check status
docker-compose ps
```

### 3. Initialize Database

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Run seeders (optional)
docker-compose exec app php artisan db:seed
```

### 4. Access Application

- **Web App**: http://localhost
- **Redis**: localhost:6379
- **PostgreSQL**: localhost:5432

## Common Commands

### Using Make (Recommended)

```bash
make build          # Build images
make up             # Start containers
make down           # Stop containers
make logs           # View logs
make migrate        # Run migrations
make seed           # Run seeders
make shell          # SSH into app container
make artisan -- your:command  # Run artisan commands
make test           # Run tests
make tinker         # Open Laravel Tinker
```

### Direct Docker Compose

```bash
# View logs
docker-compose logs -f app

# Execute command
docker-compose exec app php artisan tinker

# SSH into container
docker-compose exec app bash

# Run tests
docker-compose exec app php artisan test

# Run npm commands
docker-compose exec app npm run dev
```

## Services

### app
- **Image**: Custom Laravel PHP-FPM
- **Port**: 9000 (internal)
- **Role**: Laravel application

### nginx
- **Image**: nginx:alpine
- **Port**: 80, 443
- **Role**: Web server & reverse proxy

### db
- **Image**: postgres:15-alpine
- **Port**: 5432
- **Role**: Primary database

### redis
- **Image**: redis:7-alpine
- **Port**: 6379
- **Role**: Cache, sessions, queue driver

### python
- **Image**: Custom Python environment
- **Role**: ML models & Python scripts

## Debugging

### View Logs

```bash
# All services
docker-compose logs

# Specific service
docker-compose logs app
docker-compose logs db

# Follow logs
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100
```

### Access Container Shell

```bash
# Laravel app
docker-compose exec app bash

# Database
docker-compose exec db psql -U telecompensation -d telecompensation

# Python
docker-compose exec python bash
```

### Rebuild Without Cache

```bash
docker-compose build --no-cache
```

## Development Workflow

### Running Tests

```bash
# All tests
make test

# Specific test
docker-compose exec app php artisan test tests/Feature/YourTest.php

# With coverage
docker-compose exec app php artisan test --coverage
```

### Database Operations

```bash
# Migrations
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan migrate:rollback

# Seeders
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan db:seed --class=SpecificSeeder
```

### Frontend Development

```bash
# Build assets
docker-compose exec app npm run build

# Watch assets
docker-compose exec app npm run dev

# Format code
docker-compose exec app npm run format
```

### PHP Code Quality

```bash
# Format with Pint
make format

# Lint with PHPStan
make lint
```

## Database Access

### PostgreSQL from Host

```bash
# Using Docker
docker-compose exec db psql -U telecompensation -d telecompensation

# Using psql client (if installed)
psql -h localhost -p 5432 -U telecompensation -d telecompensation
```

### Database Backup

```bash
# Backup
docker-compose exec db pg_dump -U telecompensation telecompensation > backup.sql

# Restore
docker-compose exec -T db psql -U telecompensation telecompensation < backup.sql
```

## Production Considerations

1. **Environment Variables**: Set strong passwords for DB and Redis
2. **App Key**: Generate with `php artisan key:generate`
3. **SSL/TLS**: Configure certificates in `docker/nginx/ssl`
4. **Scaling**: Use orchestration tools (Docker Swarm, Kubernetes)
5. **Monitoring**: Set up logging and monitoring
6. **Backups**: Implement regular database backups

## Troubleshooting

### Port Already in Use

```bash
# Change ports in docker-compose.yml
# Example for port 80:
# ports:
#   - "8000:80"
```

### Out of Memory

Increase Docker's memory allocation in Docker Desktop settings (Preferences → Resources → Memory).

### Database Connection Error

```bash
# Check if db container is running
docker-compose ps db

# View db logs
docker-compose logs db

# Restart db
docker-compose restart db
```

### Permission Errors on Storage

```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Rebuild Everything

```bash
# Complete reset (WARNING: deletes data)
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

## Performance Tips

1. **Use Docker Desktop resources efficiently**: Allocate enough CPU/Memory
2. **Enable BuildKit**: `DOCKER_BUILDKIT=1`
3. **Use .dockerignore**: Already configured
4. **Volume mounts**: Be mindful of performance on Windows/Mac
5. **Multi-stage builds**: Already implemented in Dockerfile

## Next Steps

1. Configure your `.env` file with actual credentials
2. Run `docker-compose up -d`
3. Run migrations
4. Start developing!

## Additional Resources

- [Docker Documentation](https://docs.docker.com)
- [Docker Compose Documentation](https://docs.docker.com/compose)
- [Laravel Docker](https://laravel.com/docs/deployment#docker)
