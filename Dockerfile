FROM php:8.2-apache

# Instalar extensões necessárias
RUN docker-php-ext-install json

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos
COPY . /var/www/html/

# Criar pasta data com permissões
RUN mkdir -p /var/www/html/data && chmod 777 /var/www/html/data

# Configurar Apache para usar router.php
RUN echo '<?php include "router.php"; ?>' > /var/www/html/index.php

# Configurar .htaccess
RUN echo 'RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^ index.php [QSA,L]' > /var/www/html/.htaccess

# Expor porta 80
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
