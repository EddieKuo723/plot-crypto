FROM php:8.2-apache

# Install dependencies for GD (with FreeType for TTF fonts) and curl
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd curl \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
WORKDIR /var/www/html/

EXPOSE 80