FROM php:7.2-apache
ENV TZ="Europe/Rome"
RUN printf '[PHP]\ndate.timezone = "Europe/Rome"\n' > /usr/local/etc/php/conf.d/tzone.ini
COPY / /var/www/html/