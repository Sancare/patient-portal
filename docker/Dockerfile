##
# Backend builder
##
FROM php:8.2-apache AS backend-builder

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt update && \
    apt install -y libxml2-dev libpq-dev libonig-dev libcurl4-openssl-dev libzip-dev && \
    apt clean && \
    docker-php-ext-install -j$(nproc) xml \
    intl \
    pdo \
    pgsql \
    mbstring \
    curl \
    zip

RUN mkdir /build

# Copy the sources and run composer with production settings
COPY . /build/
RUN cd /build && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --quiet && \
    APP_ENV=prod php composer.phar install --no-dev --optimize-autoloader --no-progress --prefer-dist


##
# Frontend builder
##
FROM node:20-alpine AS frontend-builder

RUN mkdir /build

# Copy the sources and build the assets
COPY . /build/
RUN cd build && npm install && npm run build


##
# Final Image
##
FROM php:8.2-apache AS final-image

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt update && \
    apt install -y libxml2-dev libpq-dev libonig-dev libcurl4-openssl-dev libzip-dev && \
    apt clean && \
    docker-php-ext-install -j$(nproc) xml \
    intl \
    pdo \
    pgsql \
    mbstring \
    curl \
    zip
# The first commands of this image are the same as backend-builder to use the cache

# Setup the apache configuration
COPY docker/apache-site.conf "$APACHE_CONFDIR/conf-available/app.conf"
RUN a2disconf docker-php && \
    a2enconf app && \
    a2enmod rewrite && \
    a2enmod negotiation

COPY docker/entrypoint.sh /usr/local/bin/

# Copy the content of the app. We explicitely specify the directories to avoid including dev or test realted files
COPY --from=backend-builder /build/bin /app/bin
COPY --from=backend-builder /build/config /app/config
COPY --from=backend-builder /build/migrations /app/migrations
COPY --from=backend-builder /build/public /app/public
COPY --from=backend-builder /build/src /app/src
COPY --from=backend-builder /build/templates /app/templates
COPY --from=backend-builder /build/translations /app/translations
COPY --from=backend-builder /build/vendor /app/vendor
COPY --from=backend-builder /build/.env /build/composer.json /build/composer.lock /build/symfony.lock /app/

COPY --from=frontend-builder /build/public/build /app/public/build

ENV APP_ENV "prod"
ENV APP_DEBUG "0"
env APP_SECRET ""
ENV DATABASE_URL ""
ENV DEFAULT_USERNAME "admin"
ENV DEFAULT_PASSWORD ""

ENTRYPOINT ["entrypoint.sh"]
WORKDIR /app
