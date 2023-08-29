FROM php:8.3-rc-apache

# Composer, Git, Requirements for Composer, php mysql extensions
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    apt update && \
    apt install -y \
      git \
      zlib1g-dev libzip-dev unzip && docker-php-ext-install zip && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-enable pdo_mysql

# Project Setup
RUN cd /var/www/html && \
    composer create-project laravel/laravel JasonsProject && \
    cd JasonsProject && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 storage && \
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=abcd/' .env
COPY ./config/laravel.conf /etc/apache2/sites-available    
RUN a2ensite laravel.conf
RUN a2enmod rewrite
COPY ./config/model/create_schema.php /var/www/html/JasonsProject/database/migrations/create_schema.php
COPY ./config/model/Account.php /var/www/html/JasonsProject/app/Models/Account.php
COPY ./config/model/AccountFactory.php /var/www/html/JasonsProject/database/factories/AccountFactory.php
COPY ./config/model/DemoSeeder.php /var/www/html/JasonsProject/database/seeders/DemoSeeder.php
COPY ./config/auth_config.json /var/www/html/JasonsProject/public/auth_config.json
COPY ./src/welcome.blade.php /var/www/html/JasonsProject/resources/views/welcome.blade.php
COPY ./src/web.php /var/www/html/JasonsProject/routes/web.php

# Clean up
RUN apt clean

