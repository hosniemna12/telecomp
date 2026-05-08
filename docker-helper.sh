#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Docker Compose command
COMPOSE="docker-compose"

# Show usage
if [ $# -eq 0 ]; then
    echo -e "${BLUE}TeleCompensation Docker Helper${NC}"
    echo ""
    echo "Usage: ./docker-helper.sh <command> [arguments]"
    echo ""
    echo -e "${GREEN}Available commands:${NC}"
    echo "  up              Start all containers"
    echo "  down            Stop all containers"
    echo "  build           Build images"
    echo "  restart         Restart containers"
    echo "  logs            Show logs"
    echo "  shell           SSH into app container"
    echo "  db-shell        SSH into database container"
    echo "  migrate         Run migrations"
    echo "  seed            Run seeders"
    echo "  fresh           Fresh database (migrate + seed)"
    echo "  test            Run tests"
    echo "  tinker          Open Laravel Tinker"
    echo "  artisan         Run artisan command"
    echo "  npm             Run npm command"
    echo "  python          Run python command"
    echo "  ps              Show running containers"
    echo "  clean           Remove all containers and volumes"
    echo ""
    exit 0
fi

command=$1
shift

case "$command" in
    up)
        echo -e "${BLUE}Starting containers...${NC}"
        $COMPOSE up -d
        echo -e "${GREEN}✓ Containers started${NC}"
        $COMPOSE ps
        ;;
    
    down)
        echo -e "${BLUE}Stopping containers...${NC}"
        $COMPOSE down
        echo -e "${GREEN}✓ Containers stopped${NC}"
        ;;
    
    build)
        echo -e "${BLUE}Building images...${NC}"
        $COMPOSE build
        echo -e "${GREEN}✓ Build complete${NC}"
        ;;
    
    restart)
        echo -e "${BLUE}Restarting containers...${NC}"
        $COMPOSE restart
        echo -e "${GREEN}✓ Containers restarted${NC}"
        ;;
    
    logs)
        $COMPOSE logs -f app
        ;;
    
    shell)
        echo -e "${BLUE}Opening shell in app container...${NC}"
        $COMPOSE exec app bash
        ;;
    
    db-shell)
        echo -e "${BLUE}Opening shell in database container...${NC}"
        $COMPOSE exec db psql -U telecompensation -d telecompensation
        ;;
    
    migrate)
        echo -e "${BLUE}Running migrations...${NC}"
        $COMPOSE exec app php artisan migrate
        echo -e "${GREEN}✓ Migrations complete${NC}"
        ;;
    
    seed)
        echo -e "${BLUE}Running seeders...${NC}"
        $COMPOSE exec app php artisan db:seed
        echo -e "${GREEN}✓ Seeding complete${NC}"
        ;;
    
    fresh)
        echo -e "${YELLOW}⚠ This will refresh the database. Continue? (y/n)${NC}"
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            echo -e "${BLUE}Refreshing database...${NC}"
            $COMPOSE exec app php artisan migrate:fresh --seed
            echo -e "${GREEN}✓ Database refreshed${NC}"
        else
            echo -e "${RED}Cancelled${NC}"
        fi
        ;;
    
    test)
        echo -e "${BLUE}Running tests...${NC}"
        $COMPOSE exec app php artisan test "$@"
        ;;
    
    tinker)
        echo -e "${BLUE}Opening Tinker...${NC}"
        $COMPOSE exec app php artisan tinker
        ;;
    
    artisan)
        $COMPOSE exec app php artisan "$@"
        ;;
    
    npm)
        $COMPOSE exec app npm "$@"
        ;;
    
    python)
        $COMPOSE exec python python "$@"
        ;;
    
    ps)
        $COMPOSE ps
        ;;
    
    clean)
        echo -e "${YELLOW}⚠ This will remove all containers and volumes. Continue? (y/n)${NC}"
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            echo -e "${BLUE}Cleaning up...${NC}"
            $COMPOSE down -v
            echo -e "${GREEN}✓ Cleanup complete${NC}"
        else
            echo -e "${RED}Cancelled${NC}"
        fi
        ;;
    
    *)
        echo -e "${RED}Unknown command: $command${NC}"
        echo "Run './docker-helper.sh' for help"
        exit 1
        ;;
esac
