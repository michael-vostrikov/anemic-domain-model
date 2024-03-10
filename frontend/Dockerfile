FROM yiisoftware/yii2-php:8.1-apache

# Change document root for Apache
RUN sed -i -e 's|/app/web|/app/frontend/web|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i -e 's|xdebug.client_port=9005|xdebug.client_port=9000|g' /usr/local/etc/php/conf.d/xdebug.ini
