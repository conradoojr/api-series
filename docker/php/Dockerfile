FROM conradoojr/php-casperjs:v1
MAINTAINER Conrado Junior <conraodojr@outlook.com>

RUN docker-php-ext-install mbstring pcntl

RUN apt-get install -y git
RUN rm -rf /var/cache/apk/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Running container as non-root user
RUN sed -i 's/user = www-data/; user = www-data/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/; group = www-data/g' /usr/local/etc/php-fpm.d/www.conf
USER www-data
ENV PATH=$PATH:/home/www-data/.composer/vendor/bin
