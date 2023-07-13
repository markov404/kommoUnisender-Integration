# Образ php + fpm + alpine из внешнего репозитория
FROM php:7.4.23-fpm-alpine3.13 as base
 
# Задаем расположение рабочей директории
ENV WORK_DIR /var/www/application
 
RUN set -xe \
    && docker-php-ext-install -j$(nproc) pdo \
    && docker-php-ext-install -j$(nproc) pdo_mysql
 
FROM base
 
# Указываем, что текущая папка проекта копируется в рабочую дирректорию контейнера https://docs.docker.com/engine/reference/builder/#copy
COPY . ${WORK_DIR}
 
# Expose port 9000
EXPOSE 9000

RUN apk --update add --no-cache bash

# Задаем файл cron
COPY crontabfile-tokens /var/spool/cron/crontabs/root

# Файл для параллельного запуска cron и fpm
COPY entrypoint.bash /usr/sbin/entrypoint.bash

# Выдаем всем права на выполнение файла
RUN chmod a+x /usr/sbin/entrypoint.bash

# Выполняем файл
ENTRYPOINT /usr/sbin/entrypoint.bash