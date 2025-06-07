#!/bin/sh

# Ожидание базы данных
echo "Ожидание базы данных..."
while ! nc -z "${DB_HOST:-mysql}" "${DB_PORT:-3306}"; do
    sleep 1
done
echo "База данных доступна!"

# Установка прав доступа
chown -R laravel:laravel /var/www/storage /var/www/bootstrap/cache

# Генерация ключа приложения если он не установлен
if [ -z "$APP_KEY" ]; then
    echo "Генерация APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Кэширование конфигурации для production
if [ "$APP_ENV" = "production" ]; then
    echo "Оптимизация для production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Запуск миграций
echo "Запуск миграций..."
php artisan migrate --force

# Очистка кэша
php artisan cache:clear

echo "Запуск приложения..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
