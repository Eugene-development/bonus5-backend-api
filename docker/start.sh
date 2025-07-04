#!/bin/sh

echo "Инициализация Laravel приложения..."

# Установка прав доступа
echo "Установка прав доступа..."
chown -R laravel:laravel /var/www/storage /var/www/bootstrap/cache

# Генерация ключа приложения если он не установлен
if [ -z "$APP_KEY" ]; then
    echo "Генерация APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Проверка подключения к БД (с таймаутом)
echo "Проверка подключения к базе данных..."
if timeout 30 php artisan migrate:status > /dev/null 2>&1; then
    echo "Подключение к базе данных успешно!"
else
    echo "ПРЕДУПРЕЖДЕНИЕ: Не удалось подключиться к базе данных или проверить статус миграций"
    echo "Приложение продолжит запуск, но функциональность БД может быть недоступна"
fi

# Кэширование конфигурации для production
if [ "$APP_ENV" = "production" ]; then
    echo "Оптимизация для production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Запуск миграций (опционально, только если БД доступна)
# if [ "${ENABLE_MIGRATIONS:-true}" = "true" ]; then
#     echo "Запуск миграций..."
#     if ! php artisan migrate --force; then
#         echo "ПРЕДУПРЕЖДЕНИЕ: Миграции не выполнены. Проверьте подключение к БД и права доступа"
#     fi
# else
#     echo "Миграции отключены через ENABLE_MIGRATIONS=false"
# fi

# Очистка кэша
echo "Очистка кэша..."
php artisan cache:clear

echo "Запуск приложения..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
