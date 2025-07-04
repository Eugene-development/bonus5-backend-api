# Multi-stage build для оптимизации размера образа
FROM php:8.2-fpm-alpine AS base

# Минимальные PHP расширения (как в ваших предыдущих проектах)
RUN docker-php-ext-install pdo pdo_mysql

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

# Конфигурация PHP-FPM для Docker
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Переключение на пользователя laravel
USER laravel

# Экспорт порта PHP-FPM
EXPOSE 9000

# Команда запуска только PHP-FPM
CMD ["php-fpm"]

###########################################
# Development Build Stage
###########################################
FROM base AS development

# Xdebug для разработки (ОПЦИОНАЛЬНО - раскомментируйте если нужен)
# RUN apk add --no-cache php82-pecl-xdebug \
#     && echo "zend_extension=/usr/lib/php82/modules/xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Копирование всех файлов приложения сначала
COPY . .

# Установка всех зависимостей (включая dev)
RUN composer install --optimize-autoloader

# Завершение установки Composer
RUN composer dump-autoload --optimize

# Создание необходимых директорий и установка прав
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R laravel:laravel /var/www

# Переключение на пользователя laravel
USER laravel

# Экспорт порта PHP-FPM
EXPOSE 9000

# Команда запуска для разработки (PHP-FPM)
CMD ["php-fpm"]
