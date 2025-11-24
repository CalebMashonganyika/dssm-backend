# Use official PHP + Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy your PHP code into the Apache document root
COPY src/ /var/www/html/

# Expose port 80
EXPOSE 80