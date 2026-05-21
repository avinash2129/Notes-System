# 🐳 Docker Setup Guide

This project includes complete Docker containerization for easy deployment and development.

---

## Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose (included with Docker Desktop)

**Installation:** [Download Docker Desktop](https://www.docker.com/products/docker-desktop)

---

## Quick Start

### 1. Build and Start Containers

```bash
docker-compose up -d --build
```

**What this does:**
- Builds PHP-FPM image
- Starts Nginx web server
- Sets up SQLite database volume
- Creates network bridge for container communication

### 2. Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### 3. Access the Application

Open your browser:
```
http://127.0.0.1:8000
```

---

## Common Commands

### View Running Containers
```bash
docker-compose ps
```

**Output:**
```
NAME                COMMAND             STATUS
notes_app           php-fpm             Up 2 minutes
notes_nginx         nginx               Up 2 minutes
notes_db            tail -f /dev/null   Up 2 minutes
```

### View Logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs app
docker-compose logs nginx

# Follow logs in real-time
docker-compose logs -f app
```

### Execute Commands in Container
```bash
# Run Artisan commands
docker-compose exec app php artisan list
docker-compose exec app php artisan migrate:refresh
docker-compose exec app php artisan optimize:clear

# Access bash shell
docker-compose exec app bash

# Run PHP directly
docker-compose exec app php -v
```

### Stop Containers
```bash
# Stop but keep data
docker-compose stop

# Stop and remove (keeps volumes)
docker-compose down

# Remove everything including volumes
docker-compose down -v
```

---

## Project Structure

```
notes-system/
├── Dockerfile                    # PHP-FPM container definition
├── docker-compose.yml            # Orchestration (3 services)
├── .dockerignore                 # Files excluded from Docker build
├── docker/
│   └── nginx/
│       └── conf.d/
│           └── app.conf          # Nginx configuration
└── [other project files]
```

---

## Services Explained

### 1. PHP-FPM Service (app)
- **Image**: PHP 8.2 with FPM
- **Purpose**: Executes PHP code
- **Port**: 9000 (internal, not exposed)
- **Volume**: Application code mounted
- **Key Features**:
  - All PHP extensions installed (PDO, SQLite, MySQL, GD)
  - Composer already installed
  - Runs as www-data user

### 2. Nginx Service (nginx)
- **Image**: Nginx Alpine
- **Purpose**: Web server/reverse proxy
- **Port**: 8000 → 80 (container)
- **Config**: `docker/nginx/conf.d/app.conf`
- **Key Features**:
  - Routes requests to PHP-FPM
  - Serves static files
  - Security headers configured
  - Gzip compression enabled

### 3. Database Service (db)
- **Image**: Alpine Linux (minimal)
- **Purpose**: File volume for SQLite
- **Storage**: `db_data` volume
- **Key Features**:
  - Persistent storage
  - Database files in volume mount

---

## Configuration

### Environment Variables

Edit `docker-compose.yml` to customize:

```yaml
environment:
  - APP_ENV=local           # development environment
  - APP_DEBUG=true          # show debug info
  - DB_CONNECTION=sqlite    # use SQLite
  - OPENAI_API_KEY=         # add your API key here (optional)
```

### Using MySQL Instead of SQLite

1. Uncomment MySQL service in `docker-compose.yml`:
```yaml
mysql:
  image: mysql:8.0
  environment:
    MYSQL_DATABASE: notes_db
    MYSQL_ROOT_PASSWORD: root
```

2. Update app environment:
```yaml
environment:
  - DB_CONNECTION=mysql
  - DB_HOST=mysql
  - DB_DATABASE=notes_db
  - DB_USERNAME=root
  - DB_PASSWORD=root
```

3. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

---

## Advanced Features

### Enable Redis Caching

Uncomment in `docker-compose.yml`:
```yaml
redis:
  image: redis:7-alpine
  container_name: notes_redis
  ports:
    - "6379:6379"
  volumes:
    - redis_data:/data
```

Then in `.env`:
```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Enable PHPMyAdmin

Uncomment in `docker-compose.yml`:
```yaml
phpmyadmin:
  image: phpmyadmin:latest
  ports:
    - "8001:80"
```

Access at: http://127.0.0.1:8001

---

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs app

# Rebuild container
docker-compose build --no-cache

# Restart everything
docker-compose down && docker-compose up -d --build
```

### Permission Denied Errors

```bash
# Fix ownership inside container
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 storage bootstrap/cache
```

### Port Already in Use

Change port in `docker-compose.yml`:
```yaml
ports:
  - "8080:80"  # Use 8080 instead of 8000
```

### Database Migration Fails

```bash
# Ensure migrations run in container context
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan db:seed
```

### Composer Dependency Issues

```bash
# Update dependencies inside container
docker-compose exec app composer update
```

---

## Performance Tips

### 1. Cache Docker Layers
```bash
# Avoid rebuilding dependencies
docker-compose build --cache-from
```

### 2. Use Bind Mounts for Development
Already configured in `docker-compose.yml`:
```yaml
volumes:
  - ./:/var/www/html  # Live code reloading
```

### 3. Optimize Docker Desktop Settings
- Allocate 4+ GB RAM
- Allocate 2+ CPU cores
- Enable WSL 2 backend (Windows)

### 4. Use Production-Grade Image
For deployment, create lightweight image:
```dockerfile
FROM php:8.2-fpm-alpine
# Alpine is 50% smaller than debian-based
```

---

## Deployment Checklist

Before deploying to production:

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure real database (MySQL/PostgreSQL)
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure backup strategy for volumes
- [ ] Set resource limits in docker-compose.yml
- [ ] Use multi-stage builds for smaller images
- [ ] Implement health checks
- [ ] Configure logging to ELK/Splunk

---

## Health Check Example

Add to `docker-compose.yml`:

```yaml
app:
  healthcheck:
    test: ["CMD", "curl", "-f", "http://localhost:9000/ping"]
    interval: 30s
    timeout: 10s
    retries: 3
    start_period: 40s
```

---

## Multi-Container Development Example

Scale PHP workers:
```bash
docker-compose up -d --scale app=3
```

This creates 3 PHP-FPM containers behind Nginx load balancer.

---

## Container Architecture

```
┌─────────────────────────────────────────┐
│         Host Machine (Port 8000)        │
└────────────────────┬────────────────────┘
                     │
            ┌────────▼─────────┐
            │  Nginx Container │
            │  (Port 80)       │
            └────────┬─────────┘
                     │
        ┌────────────┼────────────┐
        │            │            │
   ┌────▼────┐  ┌───▼────┐  ┌───▼────┐
   │ App PHP-1│  │ App PHP-2│  │ App PHP-3│
   │ (9000)   │  │ (9000)   │  │ (9000)   │
   └─────────┬┘  └────┬────┘  └──┬──────┘
             └────────┼──────────┘
                      │
              ┌───────▼─────────┐
              │ SQLite/MySQL DB │
              │ (Volume Data)   │
              └─────────────────┘
```

---

## Security Best Practices

1. **Never expose sensitive data:**
   - Don't commit `.env` with real keys
   - Use `docker-compose.override.yml` for local secrets

2. **Limit container capabilities:**
   ```yaml
   cap_drop:
     - ALL
   cap_add:
     - NET_BIND_SERVICE
   ```

3. **Use read-only filesystems where possible:**
   ```yaml
   volumes:
     - ./app:/var/www/html:ro  # Read-only
   ```

4. **Keep images updated:**
   ```bash
   docker-compose pull
   docker-compose build --pull
   ```

---

## Useful References

- [Docker Documentation](https://docs.docker.com)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file)
- [Laravel Docker Documentation](https://laravel.com/docs/deployment#docker)
- [PHP-FPM Best Practices](https://www.php.net/manual/en/install.fpm.php)

---

**Questions?** Check container logs or refer to the main README.md
