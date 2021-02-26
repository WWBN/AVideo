FROM ubuntu:latest

# Update SO
RUN apt-get update && apt-get upgrade -y

# Install utils
RUN apt-get install apt-transport-https lsb-release logrotate git curl vim net-tools iputils-ping -y --no-install-recommends

# Add Ondrej's repo to the sources list
#RUN sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'

# Set localtime to UTC
RUN ln -fs /usr/share/zoneinfo/UTC /etc/localtime

# Update packages list
RUN apt-get update -y

# Install Apache
RUN apt-get -y install apache2

RUN a2enmod rewrite headers expires ssl http2 \
    && service apache2 restart

# Set new default virtualhost
RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY deploy/apache/avideo.conf /etc/apache2/sites-enabled/avideo.conf

# Install supervisord
#RUN apt-get -y install supervisor

# Install php7.4
RUN apt-get -y -f install php7.4 php7.4-common php7.4-cli php7.4-json php7.4-mbstring php7.4-curl php7.4-mysql php7.4-bcmath php7.4-xml php7.4-gd php7.4-zip --no-install-recommends

COPY  . /var/www/avideo
WORKDIR /var/www/avideo

# Set Permision
# Create folder if not exists
RUN mkdir /var/www/avideo/videos
RUN chown www-data:www-data /var/www/avideo/videos && chmod 755 /var/www/avideo/videos
#VOLUME [ "/storage/data" ]

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

EXPOSE 80
EXPOSE 443
#CMD ["supervisord"]

# By default, simply start apache.
CMD /usr/sbin/apache2ctl -D FOREGROUND
