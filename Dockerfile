FROM php:7.2-alpine
LABEL MAINTAINER="Mickaël Andrieu <mickael.andrieu@prestashop.com>"

RUN apk add --update libxslt-dev && \
    docker-php-ext-install xsl

RUN curl -fsSL https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN composer global require 'edgedesign/phpqa' 'friendsofphp/php-cs-fixer' 'jakub-onderka/php-parallel-lint' 'phpstan/phpstan' 'vimeo/psalm' --no-progress --no-scripts --no-interaction

ENV PATH /root/.composer/vendor/bin:$PATH

ENTRYPOINT ["phpqa"]
CMD ["--help"]