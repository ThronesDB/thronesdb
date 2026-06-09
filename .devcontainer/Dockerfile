FROM mcr.microsoft.com/devcontainers/php:7.4

# Remove Yarn repo, then install Node.js 18
#RUN rm -f /etc/apt/sources.list.d/*yarn* && \
#    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
#    apt-get update && \
#    apt-get install -y nodejs npm && \
#    docker-php-ext-install pdo_mysql && \
#    rm -rf /var/lib/apt/lists/*

# Install Node.js 18 using official binary distribution
RUN curl -fsSL https://nodejs.org/dist/v18.19.1/node-v18.19.1-linux-x64.tar.xz | tar -xJ -C /usr/local --strip-components=1 && \
    docker-php-ext-install pdo_mysql && \
    rm -rf /var/lib/apt/lists/*