FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    mariadb-server \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

# install PHP dependencies inside the image if composer.json exists, then fix ownership
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN if [ -f /var/www/html/composer.json ]; then \
      composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress; \
    fi && \
    chown -R www-data:www-data /var/www/html/var /var/www/html/vendor || true

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV MYSQL_ROOT_PASSWORD=secret
ENV MYSQL_DATABASE=test_database
ENV MYSQL_USER=appuser
ENV MYSQL_PASSWORD=apppass

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Initialize MariaDB directories (permissions only; actual init happens at container start)
RUN mkdir -p /var/run/mysqld && chown -R mysql:mysql /var/run/mysqld
RUN mkdir -p /var/lib/mysql && chown -R mysql:mysql /var/lib/mysql

COPY bazaDanych.sql /tmp/bazaDanych.sql

# Create a safe startup script: initialize DB if empty, start mysqld, wait, set root password, create DB/user, import SQL, then start Apache
RUN cat > /start.sh <<'EOF'
#!/bin/bash
set -e

# prepare dirs
mkdir -p /var/run/mysqld
chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

INIT_DB=0
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "Initializing MariaDB data directory..."
  mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null
  INIT_DB=1
fi

# ensure 'db' hostname resolves to localhost for apps using host 'db'
if ! grep -q "127.0.0.1 db" /etc/hosts; then
  echo "127.0.0.1 db" >> /etc/hosts
fi

echo "Starting MariaDB (background)..."
mysqld_safe --datadir=/var/lib/mysql --user=mysql &

# wait for mysqld to be ready
for i in {1..30}; do
  if mysqladmin ping -h 127.0.0.1 --silent; then
    echo "MariaDB is up"
    break
  fi
  echo "Waiting for MariaDB to start (${i})..."
  sleep 1
done

# if newly initialized, set root password if provided
if [ "${INIT_DB}" = "1" ] && [ -n "${MYSQL_ROOT_PASSWORD}" ]; then
  echo "Setting root password..."
  mysql -u root <<SQL
ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
FLUSH PRIVILEGES;
SQL
fi

# create application database and user (idempotent)
if [ -n "${MYSQL_DATABASE}" ]; then
  echo "Creating database and application user if needed..."
  # Try with root password first, fallback to no-password root
  if mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e '\n' 2>/dev/null; then
    mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
  else
    mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
  fi
fi

# import DB file into the application database if present
if [ -f /tmp/bazaDanych.sql ]; then
  echo "Importing /tmp/bazaDanych.sql..."
  if [ -n "${MYSQL_DATABASE}" ]; then
    if mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e '\n' 2>/dev/null; then
      mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" < /tmp/bazaDanych.sql
    else
      mysql -u root "${MYSQL_DATABASE}" < /tmp/bazaDanych.sql
    fi
  else
    # fallback: import without selecting DB
    if mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e '\n' 2>/dev/null; then
      mysql -u root -p"${MYSQL_ROOT_PASSWORD}" < /tmp/bazaDanych.sql
    else
      mysql -u root < /tmp/bazaDanych.sql
    fi
  fi
  echo "Database import finished"
fi

echo "Starting Apache (foreground)..."
exec apache2-foreground
EOF

RUN chmod +x /start.sh

EXPOSE 80 3306

CMD ["/start.sh"]