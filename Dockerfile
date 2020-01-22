FROM wordpress:5.3.2-php7.2-apache

RUN apt-get update && \
	apt-get install -y  --no-install-recommends ssl-cert && \
	rm -r /var/lib/apt/lists/* && \
	a2enmod ssl rewrite expires && \
	a2ensite default-ssl

ENV PHP_INI_PATH "/usr/local/etc/php/php.ini"

RUN pecl install xdebug-2.6.1 && docker-php-ext-enable xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_port=9000" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_enable=1" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_connect_back=0" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_host=docker.for.mac.localhost" >> ${PHP_INI_PATH} \
    && echo "xdebug.idekey=IDEA_DEBUG" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_autostart=1" >> ${PHP_INI_PATH} \
    && echo "xdebug.remote_log=/tmp/xdebug.log" >> ${PHP_INI_PATH}

EXPOSE 80
EXPOSE 443
