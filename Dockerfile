# Multi-stage build для оптимизации размера образа
FROM php:8.2-fpm-alpine AS base

# Установка системных зависимостей
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    nginx \
    supervisor \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql

# Установка Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Создание пользователя для Laravel
RUN addgroup -g 1000 -S laravel \
    && adduser -u 1000 -S laravel -G laravel

# Установка рабочей директории
WORKDIR /var/www

###########################################
# Production Build Stage
###########################################
FROM base AS production

# Копирование файлов composer сначала для кэширования слоев
COPY composer.json composer.lock ./

# Установка PHP зависимостей (только production)
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Копирование всех файлов приложения
COPY . .

# Завершение установки Composer
RUN composer dump-autoload --optimize

# Создание необходимых директорий и установка прав
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R laravel:laravel /var/www

# Конфигурация Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Конфигурация PHP-FPM
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Конфигурация Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Создание стартового скрипта
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Переключение на пользователя laravel
USER laravel

# Экспорт порта
EXPOSE 80

# Переключение обратно на root для запуска supervisor
USER root

# Команда запуска
CMD ["/usr/local/bin/start.sh"]

###########################################
# Development Build Stage
###########################################
FROM base AS development

# Установка Xdebug для разработки
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Копирование файлов composer
COPY composer.json composer.lock ./

# Установка всех зависимостей (включая dev)
RUN composer install --optimize-autoloader

# Копирование всех файлов приложения
COPY . .

# Завершение установки Composer
RUN composer dump-autoload --optimize

# Создание необходимых директорий и установка прав
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R laravel:laravel /var/www

# Переключение на пользователя laravel
USER laravel

# Экспорт порта
EXPOSE 8000

# Команда запуска для разработки
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
