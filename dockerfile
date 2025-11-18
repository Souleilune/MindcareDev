# Use an official PHP image with Apache
FROM php:8.2-apache

# Enable necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files into Apache's web root
COPY . /var/www/html/

# Give proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (Render default)
EXPOSE 80
