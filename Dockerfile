FROM nginx:alpine

# Install PHP and PHP-FPM
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-session \
    php82-json \
    php82-mbstring \
    php82-openssl

# Create PHP-FPM directories
RUN mkdir -p /run/php

# Copy nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy website files
COPY html /usr/share/nginx/html

# Copy PHP-FPM configuration
COPY php-fpm.conf /etc/php82/php-fpm.d/www.conf

# Create startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Set permissions
RUN chown -R nginx:nginx /usr/share/nginx/html

EXPOSE 80

CMD ["/start.sh"]