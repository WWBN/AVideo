FROM ubuntu/apache2:2.4-22.10_edge

LABEL maintainer="TRW <trw@acoby.de>" \
      org.label-schema.schema-version="1.0" \
      org.label-schema.version="1.1.0" \
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

# Retrieve package list
RUN apt update

# Install dependencies
RUN apt-get update -y && apt-get upgrade -y \
      && apt install -y --no-install-recommends dos2unix bash-completion lsof rsyslog cron rsync ca-certificates apt-transport-https software-properties-common curl \
      && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
      && chmod a+rx /usr/local/bin/yt-dlp \
      && apt install -y --no-install-recommends sshpass nano net-tools curl apache2 php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-sqlite3 php8.1-curl php8.1-gd php8.1-intl \
      php-zip mysql-client ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile python3-certbot-apache -y  && a2enmod xsendfile && cd /var/www/html \
      && apt install -y --no-install-recommends && curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl  \
      && chmod a+rx /usr/local/bin/youtube-dl && apt install -y --no-install-recommends build-essential libpcre3 libpcre3-dev libssl-dev php8.1-xml -y  \
      && a2enmod rewrite \
      && apt install -y --no-install-recommends unzip -y && apt install -y --no-install-recommends htop python3-pip  \
      && pip3 install youtube-dl && pip3 install --upgrade youtube-dl && a2enmod expires  \
      && a2enmod headers  

COPY deploy/apache/avideo.conf /etc/apache2/sites-enabled/000-default.conf
COPY deploy/apache/docker-entrypoint /usr/local/bin/docker-entrypoint
COPY deploy/apache/wait-for-db.php /usr/local/bin/wait-for-db.php
#COPY deploy/apache/phpmyadmin.conf /etc/apache2/conf-available/phpmyadmin.conf

COPY deploy/apache/crontab /etc/cron.d/crontab
RUN dos2unix /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN chmod +x /etc/cron.d/crontab
RUN service cron start
RUN crontab /etc/cron.d/crontab

# Configure AVideo
RUN dos2unix /usr/local/bin/docker-entrypoint && \
    chmod 755 /usr/local/bin/docker-entrypoint && \
    chmod +x /usr/local/bin/docker-entrypoint && \
    pip3 install youtube-dl && \
    sed -i 's/^post_max_size.*$/post_max_size = 10G/' /etc/php/8.1/apache2/php.ini && \
    sed -i 's/^upload_max_filesize.*$/upload_max_filesize = 10G/' /etc/php/8.1/apache2/php.ini && \
    sed -i 's/^max_execution_time.*$/max_execution_time = 7200/' /etc/php/8.1/apache2/php.ini && \
    sed -i 's/^memory_limit.*$/memory_limit = 512M/' /etc/php/8.1/apache2/php.ini && \
    a2enmod rewrite expires headers ssl xsendfile

# Add Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

VOLUME /var/www/tmp
RUN mkdir -p /var/www/tmp && \
    chown www-data:www-data /var/www/tmp && \
    chmod 777 /var/www/tmp

WORKDIR /var/www/html/AVideo/

EXPOSE $SOCKET_PORT
EXPOSE $HTTP_PORT
EXPOSE $HTTPS_PORT
EXPOSE $PHPMYADMIN_PORT
EXPOSE $PHPMYADMIN_ENCODER_PORT

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["apache2-foreground"]
HEALTHCHECK --interval=60s --timeout=55s --start-period=1s CMD curl --fail https://localhost/ || exit 1  
