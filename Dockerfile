FROM registry.cn-shenzhen.aliyuncs.com/yansongda/php-fpm:7.4

LABEL maintainer="yansongda <me@yansongda.cn>"

ARG build_env=prod

ENV BUILD_ENV=${build_env:-"prod"}

WORKDIR /www

COPY php.ini /usr/local/etc/php/
COPY . /www

RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-grpc.ini \
    && cd /www \
    && composer install --no-dev \
    && composer clearcache \
    && composer dump-autoload -o

EXPOSE 8080 8888 8889

ENTRYPOINT ["php", "/www/bin/hyperf.php", "start"]
