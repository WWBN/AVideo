FROM ubuntu/apache2:2.4-22.04_edge

LABEL maintainer="TRW <trw@acoby.de>" \
      org.label-schema.schema-version="1.0" \
      org.label-schema.version="1.1.0" \
      org.label-schema.name="avideo-platform" \
      org.label-schema.description="Audio Video Platform" \
      org.label-schema.url="https://github.com/WWBN/AVideo" \
      org.label-schema.vendor="WWBN"

ARG DEBIAN_FRONTEND=noninteractive
ARG VERSION_ENCODER=3.7

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
RUN apt install -y --no-install-recommends \
      systemctl \
      apt-transport-https \
      lsb-release \
      logrotate \
      git \
      unzip \
      curl \
      wget && \
    apt install -y \
      ffmpeg \
      libimage-exiftool-perl \
      libapache2-mod-xsendfile \
      libapache2-mod-php7.4 \
      python \
      build-essential \
      make \
      libpcre3 \
      libpcre3-dev \
      libssl-dev \
      python3-pip \ 
      php7.4 \
      php7.4-common \
      php7.4-cli \
      php7.4-json \
      php7.4-mbstring \
      php7.4-curl \
      php7.4-mysql \
      php7.4-bcmath \
      php7.4-xml \
      php7.4-gd \
      php7.4-zip \
      php7.4-intl

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
    git clone -b $VERSION_ENCODER --depth 1 https://github.com/WWBN/AVideo-Encoder.git Encoder && \
    chown -R www-data:www-data /var/www/html/AVideo && \
    cd /var/www/html/AVideo/plugin/User_Location/install && \
    unzip install.zip && \
    sed -i 's/^post_max_size.*$/post_max_size = 100M/' /etc/php/7.4/apache2/php.ini && \
    sed -i 's/^upload_max_filesize.*$/upload_max_filesize = 100M/' /etc/php/7.4/apache2/php.ini && \
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
