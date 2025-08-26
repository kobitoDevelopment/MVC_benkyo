FROM php:8.2-apache

# 必要なパッケージとPHP拡張機能をインストール
RUN apt-get update \
    && apt-get install -y \
        libpq-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache設定でAllowOverrideを有効化
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# DocumentRootを/var/www/html/publicに設定
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/' /etc/apache2/sites-available/000-default.conf

# Directoryディレクティブも更新
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/\/var\/www\//\/var\/www\/html\/public\//' /etc/apache2/apache2.conf

# 作業ディレクトリを設定
WORKDIR /var/www/html