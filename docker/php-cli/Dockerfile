FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig5 libonig-dev libxml2-dev unzip zip \
    && docker-php-ext-install dom mbstring xml xmlwriter \
    && apt-get purge -y --auto-remove libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "bin/play_game.php"]
