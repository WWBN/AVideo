FROM ubuntu/apache2:2.4-22.04_edge

LABEL maintainer="TRW <trw@acoby.de>" \
      org.label-schema.schema-version="1.0" \
      org.label-schema.version="1.1.0" \
      org.label-schema.name="avideo-platform" \
      org.label-schema.description="Audio Video Platform" \
      org.label-schema.url="https://github.com/WWBN/AVideo" \
      org.label-schema.vendor="WWBN"

ARG DEBIAN_FRONTEND=noninteractive

ENV DB_MYSQL_HOST database
ENV DB_MYSQL_PORT 3306
ENV DB_MYSQL_NAME avideo
ENV DB_MYSQL_USER avideo
ENV DB_MYSQL_PASSWORD avideo

ENV SERVER_NAME localhost
ENV ENABLE_PHPMYADMIN yes
ENV CREATE_TLS_CERTIFICATE yes
ENV TLS_CERTIFICATE_FILE /etc/apache2/ssl/localhost.crt
ENV TLS_CERTIFICATE_KEY /etc/apache2/ssl/localhost.key
ENV CONTACT_EMAIL admin@localhost
ENV SYSTEM_ADMIN_PASSWORD password
ENV WEBSITE_TITLE AVideo
ENV MAIN_LANGUAGE en_US

# Retrieve package list
RUN apt update

# Install dependencies
RUN apt-get update -y && apt-get upgrade -y \
      && apt install -y --no-install-recommends ca-certificates apt-transport-https software-properties-common curl \
      && curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
      && chmod a+rx /usr/local/bin/yt-dlp \
      && apt install -y --no-install-recommends sshpass nano net-tools curl apache2 php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-curl php8.1-gd php8.1-intl \
      php-zip mysql-client ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile -y  && a2enmod xsendfile && cd /var/www/html \
      && git clone https://github.com/WWBN/AVideo.git \
      && apt install -y --no-install-recommends && curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl  \
      && chmod a+rx /usr/local/bin/youtube-dl && apt install -y --no-install-recommends build-essential libpcre3 libpcre3-dev libssl-dev php8.1-xml -y  \
      && a2enmod rewrite && chown www-data:www-data /var/www/html/AVideo/plugin && chmod 755 /var/www/html/AVideo/plugin  \
      && apt install -y --no-install-recommends unzip -y && apt install -y --no-install-recommends htop python3-pip  \
      && pip3 install youtube-dl && pip3 install --upgrade youtube-dl && a2enmod expires  \
      && a2enmod headers  

COPY deploy/apache/avideo.conf /etc/apache2/sites-enabled/000-default.conf
COPY deploy/apache/phpmyadmin.conf /etc/apache2/conf-available/phpmyadmin.conf
COPY deploy/docker-entrypoint /usr/local/bin/docker-entrypoint
COPY deploy/wait-for-db.php /usr/local/bin/wait-for-db.php

COPY admin /var/www/html/AVideo/admin
COPY feed /var/www/html/AVideo/feed
COPY install /var/www/html/AVideo/install
COPY locale /var/www/html/AVideo/locale
COPY node_modules /var/www/html/AVideo/node_modules
COPY objects /var/www/html/AVideo/objects
COPY plugin /var/www/html/AVideo/plugin
COPY storage /var/www/html/AVideo/storage
COPY updatedb /var/www/html/AVideo/updatedb
COPY vendor /var/www/html/AVideo/vendor
COPY view /var/www/html/AVideo/view
COPY _config.yml /var/www/html/AVideo
COPY .htaccess /var/www/html/AVideo
COPY CNAME /var/www/html/AVideo
COPY LICENSE /var/www/html/AVideo
COPY README.md /var/www/html/AVideo
COPY web.config /var/www/html/AVideo
COPY index.php /var/www/html/AVideo
COPY git.json.php /var/www/html/AVideo
COPY sw.js /var/www/html/AVideo/

# Configure AVideo
RUN chmod 755 /usr/local/bin/docker-entrypoint && \
    pip3 install youtube-dl && \
    cd /var/www/html/AVideo && \
    git config --global advice.detachedHead false && \
    git clone https://github.com/WWBN/AVideo-Encoder.git Encoder && \
    chown -R www-data:www-data /var/www/html/AVideo && \
    cd /var/www/html/AVideo/plugin/User_Location/install && \
    unzip install.zip && \
    sed -i 's/^post_max_size.*$/post_max_size = 10G/' /etc/php/8.1/apache2/php.ini && \
    sed -i 's/^upload_max_filesize.*$/upload_max_filesize = 10G/' /etc/php/8.1/apache2/php.ini && \
    a2enmod rewrite expires headers ssl xsendfile

VOLUME /var/www/tmp
RUN mkdir -p /var/www/tmp && \
    chown www-data:www-data /var/www/tmp && \
    chmod 777 /var/www/tmp

VOLUME /var/www/html/AVideo/videos
RUN mkdir -p /var/www/html/AVideo/videos && \
    chown www-data:www-data /var/www/html/AVideo/videos && \
    chmod 777 /var/www/html/AVideo/videos

WORKDIR /var/www/html/AVideo/

EXPOSE 443

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["apache2-foreground"]
HEALTHCHECK --interval=60s --timeout=55s --start-period=1s CMD curl --fail https://localhost/ || exit 1  
