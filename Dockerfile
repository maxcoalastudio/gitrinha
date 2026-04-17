FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos
COPY . /var/www/html/

# Criar pasta data com permissões
RUN mkdir -p /var/www/html/data && chmod 777 /var/www/html/data

# Criar arquivos JSON iniciais
RUN echo '[]' > /var/www/html/data/users.json && \
    echo '[]' > /var/www/html/data/battles.json && \
    echo '{}' > /var/www/html/data/scores.json

# Configurar Apache para usar router.php
RUN echo '<?php include "router.php"; ?>' > /var/www/html/index.php

# Configurar .htaccess para rotas amigáveis
RUN echo 'RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^ index.php [QSA,L]' > /var/www/html/.htaccess

# Expor porta 80
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
