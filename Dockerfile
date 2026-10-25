FROM ubuntu:26.04

LABEL maintainer="Daniel Neto <developer@streamphp.com>" \
      org.label-schema.schema-version="2.0" \
      org.label-schema.version="2.0.0" \
      org.label-schema.name="avideo-platform" \
      org.label-schema.description="Audio Video Platform" \
      org.label-schema.url="https://github.com/WWBN/AVideo" \
      org.label-schema.vendor="WWBN"

ARG DEBIAN_FRONTEND=noninteractive

ARG SOCKET_PORT
ARG HTTP_PORT
ARG HTTPS_PORT
ARG DB_MYSQL_HOST
ARG DB_MYSQL_PORT
ARG DB_MYSQL_NAME
ARG DB_MYSQL_USER
ARG DB_MYSQL_PASSWORD
ARG SERVER_NAME
ARG ENABLE_PHPMYADMIN
ARG PHPMYADMIN_PORT
ARG PHPMYADMIN_ENCODER_PORT
ARG CREATE_TLS_CERTIFICATE
ARG TLS_CERTIFICATE_FILE
ARG TLS_CERTIFICATE_KEY
ARG CONTACT_EMAIL
ARG SYSTEM_ADMIN_PASSWORD
ARG WEBSITE_TITLE
ARG MAIN_LANGUAGE
ARG NGINX_RTMP_PORT
ARG NGINX_HTTP_PORT
ARG NGINX_HTTPS_PORT

RUN chmod 1777 /tmp

# Update package list and install basic utilities
RUN apt-get update && apt-get install -y --no-install-recommends \
    apt-transport-https \
    bash-completion \
    ca-certificates \
    cron \
    curl \
    dos2unix \
    git \
    htop \
    iputils-ping \
    lsof \
    nano \
    net-tools \
    rsyslog \
    rsync \
    software-properties-common \
    unzip \
    wget \
    sshpass \
    mysql-client && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Apache and Apache modules
RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-xsendfile \
    libapache2-mod-php8.5 \
    libimage-exiftool-perl \
    memcached && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libssl-dev \
    zlib1g-dev \
    php8.5 \
    php8.5-cli \
    php8.5-curl \
    php8.5-gd \
    php8.5-intl \
    php8.5-mysql \
    php8.5-sqlite3 \
    php8.5-xml \
    php8.5-zip \
    php-pear \
    php8.5-mbstring \
    php8.5-memcached \
    imagemagick && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Ubuntu 26.04 backports both CVE-2026-109618 / CVE-2026-109619 fixes.
RUN dpkg --compare-versions \
    "$(dpkg-query -W -f='${Version}' php8.5-common)" ge '8.5.4-0ubuntu1.5'

# Install multimedia tools
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Python packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3-certbot-apache \
    python3-pip && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Download and install yt-dlp
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp && \
    chmod a+rx /usr/local/bin/yt-dlp

# Enable necessary Apache modules
RUN a2enmod xsendfile rewrite expires headers ssl

# Video Transcription for the SubtitleSwitcher Plugin
RUN pip3 install vosk --break-system-packages

# Copy configuration files
COPY deploy/apache/avideo.conf /etc/apache2/sites-available/avideo.conf
COPY deploy/apache/localhost.conf /etc/apache2/sites-available/localhost.conf
COPY deploy/apache/docker-entrypoint /usr/local/bin/docker-entrypoint
COPY deploy/apache/crontab /etc/cron.d/crontab
COPY deploy/apache/sync-certificates.sh /usr/local/bin/apache-sync-certificates.sh
COPY deploy/apache/certbot-renew.sh /usr/local/bin/certbot-renew.sh
COPY deploy/apache/certbot-deploy-hook.sh /usr/local/bin/certbot-deploy-hook.sh

RUN if [ "$SERVER_NAME" != "localhost" ] || [[ "$SERVER_NAME" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]] ; \
    then \
        cp /etc/apache2/sites-available/avideo.conf /etc/apache2/sites-enabled/000-default.conf; \
    else \
        cp /etc/apache2/sites-available/localhost.conf /etc/apache2/sites-enabled/000-default.conf; \
    fi

# Set permissions for crontab
RUN dos2unix /etc/cron.d/crontab && \
    chmod 0644 /etc/cron.d/crontab && \
    chmod +x /etc/cron.d/crontab

# Configure AVideo
RUN dos2unix /usr/local/bin/docker-entrypoint && \
    chmod 755 /usr/local/bin/docker-entrypoint && \
    chmod +x /usr/local/bin/docker-entrypoint

RUN dos2unix /usr/local/bin/apache-sync-certificates.sh /usr/local/bin/certbot-renew.sh /usr/local/bin/certbot-deploy-hook.sh && \
    chmod 755 /usr/local/bin/apache-sync-certificates.sh /usr/local/bin/certbot-renew.sh /usr/local/bin/certbot-deploy-hook.sh && \
    chmod +x /usr/local/bin/apache-sync-certificates.sh /usr/local/bin/certbot-renew.sh /usr/local/bin/certbot-deploy-hook.sh

# 🛠️  Improved RUN block — creates a single script and tunes PHP-Memcached sessions
RUN cat >/usr/local/bin/configure-php.sh <<'EOS'
#!/bin/sh
# Detect active PHP version (e.g., 8.2)
PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
INI="/etc/php/${PHP_VERSION}/apache2/php.ini"

# ── Upload / execution limits ─────────────────────────────────────────────
sed -i 's/^post_max_size\s*=.*/post_max_size = 10G/'            "$INI"
sed -i 's/^upload_max_filesize\s*=.*/upload_max_filesize = 10G/' "$INI"
sed -i 's/^max_execution_time\s*=.*/max_execution_time = 7200/' "$INI"
sed -i 's/^memory_limit\s*=.*/memory_limit = 512M/'             "$INI"

# Log PHP errors to STDOUT (visible via `docker logs`)
echo "error_log = /dev/stdout" >> "$INI"

# ── PHP sessions stored in Memcached ─────────────────────────────────────
cat >> "$INI" <<'EOF'
; Reuse cached PHP file existence checks on slow bind mounts.
; Keep automatic source-change detection enabled for development and upgrades.
opcache.enable_file_override   = 1
opcache.validate_timestamps    = 1
opcache.revalidate_freq        = 2

; ---------- PHP sessions via Memcached ----------
session.save_handler           = memcached
session.save_path              = "memcached:11211?persistent=1&timeout=2&retry_interval=5"

; Lightweight locking to avoid race conditions without freezing the site
memcached.sess_locking         = 1
memcached.sess_lock_expire     = 5        ; release lock after 5 s (default)
; wait_min/wait_max are MILLISECONDS: 1000/5000 made every concurrent request
; of the same session wait at least 1 s. Short waits need many retries, or
; session_start() fails (user looks logged out) before the lock is released.
memcached.sess_lock_wait_min   = 10       ; 10 ms first retry, doubling...
memcached.sess_lock_wait_max   = 100      ; ...up to 100 ms between attempts
memcached.sess_lock_retries    = 100      ; ~10 s total before giving up
memcached.sess_consistent_hash = On
memcached.sess_remove_failed_servers = 1
EOF
EOS

RUN chmod +x /usr/local/bin/configure-php.sh


# Run the PHP configuration script
RUN sed -i 's/\r$//' /usr/local/bin/configure-php.sh && /usr/local/bin/configure-php.sh

# Add Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Redirect Apache logs to stdout and stderr
RUN ln -sf /proc/self/fd/1 /var/log/apache2/access.log && \
    ln -sf /proc/self/fd/2 /var/log/apache2/error.log

# Create directory and set permissions
RUN mkdir -p /var/www/tmp && \
    chown www-data:www-data /var/www/tmp && \
    chmod 777 /var/www/tmp

WORKDIR /var/www/html/AVideo/

EXPOSE $SOCKET_PORT
EXPOSE $HTTP_PORT
EXPOSE $HTTPS_PORT
EXPOSE $PHPMYADMIN_PORT
EXPOSE $PHPMYADMIN_ENCODER_PORT
EXPOSE 3000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
