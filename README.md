docker build -t larux/bonus5-api:250625 . && docker push larux/bonus5-api:250625

<!-- Только пересборка бэкенда (Production): -->

./scripts/deploy-production.sh --build

<!-- Полная пересборка и запуск Development: -->

./scripts/deploy-development.sh --build

<!-- Только пересборка бэкенда (Production): -->

cd production
docker-compose -f docker-compose.prod.yml build --no-cache backend
docker-compose -f docker-compose.prod.yml up -d backend

<!-- Только пересборка бэкенда (Development): -->

cd development
docker-compose -f docker-compose.dev.yml build --no-cache backend
docker-compose -f docker-compose.dev.yml up -d backend

<!-- Посмотреть собранные образы: -->

docker images | grep bonus5

<!-- Проверить запущенные контейнеры: -->

# Production

docker-compose -f production/docker-compose.prod.yml ps

# Development

docker-compose -f development/docker-compose.dev.yml ps

<!-- Посмотреть логи: -->

# Production

docker-compose -f production/docker-compose.prod.yml logs -f backend

# Development

docker-compose -f development/docker-compose.dev.yml logs -f backend
