# Based on the work of @hannah98, thanks for that!
# https://github.com/hannah98/youphptube-docker

FROM ubuntu:xenial

MAINTAINER TheAssassin <theassassin@assassinate-you.net>

RUN apt-get update \
    && apt-get install -y apache2 php7.0 libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-gd php7.0-intl ffmpeg libimage-exiftool-perl python git curl python-pip \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/tmp/* /root/.cache \
    && a2enmod rewrite

RUN pip install -U youtube-dl

WORKDIR /var/www/html

RUN rm -rf /var/www/html/*
COPY . /var/www/html

ADD docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
