FROM mcr.microsoft.com/devcontainers/php:8.4

# Node.js 22 (LTS) for the gulp asset build, from the official binary distribution
RUN curl -fsSL https://nodejs.org/dist/v22.23.4/node-v22.23.4-linux-x64.tar.xz | tar -xJ -C /usr/local --strip-components=1 && \
    docker-php-ext-install pdo_mysql && \
    rm -rf /var/lib/apt/lists/*
