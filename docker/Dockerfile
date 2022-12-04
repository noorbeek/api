FROM php:8.0-apache

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y git unzip zip sendmail locales

RUN sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && locale-gen
RUN sed -i '/nl_NL.UTF-8/s/^# //g' /etc/locale.gen && locale-gen
ENV LANG nl_NL.UTF-8  
ENV LANGUAGE nl_NL:nl  
ENV LC_ALL nl_NL.UTF-8  

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gettext gd pdo_mysql bcmath zip intl opcache

COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/composer

RUN echo "Europe/Amsterdam" > /etc/timezone
RUN echo "sendmail_path=/usr/sbin/sendmail -t -i" >> /usr/local/etc/php/conf.d/sendmail.ini

RUN sed -i '/#!\/bin\/sh/aservice sendmail restart' /usr/local/bin/docker-php-entrypoint
RUN sed -i '/#!\/bin\/sh/aecho "$(hostname -i)\t$(hostname) $(hostname).localhost" >> /etc/hosts' /usr/local/bin/docker-php-entrypoint