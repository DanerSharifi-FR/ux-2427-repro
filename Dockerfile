FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update \
    && apt-get install -y symfony-cli \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

EXPOSE 8000

CMD ["symfony", "server:start", "--no-tls", "--allow-http", "--listen-ip=0.0.0.0", "--port=8000"]
