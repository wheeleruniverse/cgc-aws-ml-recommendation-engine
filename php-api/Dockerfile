FROM bref/php-74-fpm

RUN curl -s https://getcomposer.org/installer | php

RUN php composer.phar require bref/bref

COPY . /var/task

CMD _HANDLER=index.php /opt/bootstrap
