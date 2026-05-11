FROM wordpress:latest

# Install WP-CLI
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    mariadb-client \
    iputils-ping \
    nano \
    && apt-get upgrade -y \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN pecl install xdebug

# Configure wp-cli.ini
RUN echo "[xdebug]" > /usr/local/etc/php/conf.d/wp-cli.ini && \
    echo ";zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/wp-cli.ini && \
    echo "xdebug.mode=off" >> /usr/local/etc/php/conf.d/wp-cli.ini

# Create WP-CLI wrapper
RUN echo '#!/bin/bash' > /usr/local/bin/wp-cli && \
    echo 'php -d xdebug.mode=off /usr/local/bin/wp "$@"' >> /usr/local/bin/wp-cli && \
    chmod +x /usr/local/bin/wp-cli

# Copy custom PHP configuration
COPY config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Configure MySQL client
RUN echo '[client]\nssl=0\n[mysql]\nssl=0' > /etc/mysql/conf.d/client.cnf

# Set working directory
WORKDIR /var/www/html