FROM php:8.2-fpm-alpine

# Install nginx and required PHP extensions
RUN apk add --no-cache nginx supervisor

# Create nginx user and directories
RUN mkdir -p /var/log/nginx /var/lib/nginx/tmp /run/nginx

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy website files
COPY html /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]