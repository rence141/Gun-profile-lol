# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite (useful if you expand the site later)
RUN a2enmod rewrite

# Copy your project files into the default web directory
COPY . /var/www/html/

# Set permissions so PHP can write to the JSON file
# (Crucial for the view counter to work)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 (Render maps this automatically)
EXPOSE 80