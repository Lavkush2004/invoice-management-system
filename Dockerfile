FROM php:8.2-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo_mysql

# Ensure only prefork MPM is enabled
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf && \
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf && \
    a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory & permissions
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Custom entrypoint to bind Apache to dynamic Railway $PORT
RUN printf '#!/bin/sh\nrm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*\nPORT="${PORT:-80}"\nsed -i "s/Listen [0-9]*/Listen $PORT/g" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \\*:[0-9]*>/<VirtualHost \\*:$PORT>/g" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]
