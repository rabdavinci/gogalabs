#!/bin/sh

# Start PHP-FPM in background
php-fpm82 --daemonize

# Start nginx in foreground
nginx -g 'daemon off;'