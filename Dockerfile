FROM php:8.2-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo_mysql

# Enable Apache modules and ensure single MPM (prefork)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory & permissions
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Custom entrypoint to bind Apache to dynamic Railway $PORT
RUN printf '#!/bin/sh\nPORT="${PORT:-80}"\nsed -i "s/Listen [0-9]*/Listen $PORT/g" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \\*:[0-9]*>/<VirtualHost \\*:$PORT>/g" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]
