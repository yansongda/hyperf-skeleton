FROM registry.cn-shenzhen.aliyuncs.com/yansongda/skeleton:hyperf-8.1

LABEL maintainer="yansongda <me@yansongda.cn>"

ARG build_env=prod

ENV BUILD_ENV=${build_env:-"prod"}

COPY . /www

RUN cd /www \
    && composer install --no-dev \
    && composer clearcache \
    && composer dump-autoload -o

EXPOSE 8080 8888 8889

ENTRYPOINT ["php", "/www/bin/hyperf.php", "start"]
