FROM php:8.2-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory & permissions
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Custom entrypoint to bind Apache to Railway dynamic $PORT
RUN printf '#!/bin/sh\nPORT="${PORT:-80}"\nsed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf\nexec apache2-foreground\n' > /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]
