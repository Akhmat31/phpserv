FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-interaction --optimize-autoloader \
    && if [ -d plugins ]; then \
         cd plugins && composer dump-autoload --no-interaction; \
       fi

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]