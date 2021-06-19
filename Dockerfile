FROM php:7.4-fpm-alpine

RUN \
    apk add --no-cache curl bash $PHPIZE_DEPS

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug-2.8.1 && docker-php-ext-enable xdebug

RUN docker-php-ext-install pdo pdo_mysql

RUN apk add rabbitmq-c-dev

RUN pecl install amqp; \
    docker-php-ext-enable amqp;

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo 'zend_extension="/usr/local/lib/php/extensions/no-debug-non-zts-20190902/xdebug.so"' >> /usr/local/etc/php/php.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo Europe/Portugal > /etc/timezone

RUN apk add nodejs npm

COPY ./entrypoint.sh /

RUN ["chmod", "+x", "/entrypoint.sh"]

WORKDIR /var/www/html/csv-file-loader-app

ENTRYPOINT ["/entrypoint.sh"]

# Create a group and user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup --uid 1000

# Tell docker that all future commands should run as the appuser user
USER appuser

EXPOSE 9000

CMD ["php-fpm"]