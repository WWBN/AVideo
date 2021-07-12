FROM ubuntu:latest

# Update SO
RUN apt-get update && apt-get upgrade -y

# Install utils
RUN apt-get install apt-transport-https lsb-release logrotate git curl vim net-tools iputils-ping -y --no-install-recommends

# Install all the rest
RUN DEBIAN_FRONTEND="noninteractive" apt-get install wget sshpass nano net-tools curl apache2 php7.4 libapache2-mod-php7.4 php7.4-mysql php7.4-curl php7.4-gd php7.4-intl php7.4-xml apache2 mysql-server mysql-client ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile python build-essential make libpcre3 libpcre3-dev libssl-dev unzip htop python3-pip  -y 

RUN cd /var/www/html && git clone https://github.com/WWBN/AVideo.git && cd /var/www/html &&  git clone https://github.com/WWBN/AVideo-Encoder.git &&  curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl &&  chmod a+rx /usr/local/bin/youtube-dl && chown www-data:www-data /var/www/html/AVideo/plugin &&  chmod 755 /var/www/html/AVideo/plugin &&  curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl &&  chmod a+rx /usr/local/bin/youtube-dl && a2enmod rewrite &&  chown www-data:www-data /var/www/html/AVideo/plugin &&  chmod 755 /var/www/html/AVideo/plugin && cd /var/www/html/AVideo/plugin/User_Location/install && unzip install.zip &&  pip3 install youtube-dl &&  pip3 install --upgrade youtube-dl &&  a2enmod expires &&  a2enmod headers &&  chmod 777 /var/www/html/AVideo/objects/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/ &&  mkdir /var/www/tmp &&  chmod 777 /var/www/tmp
 
# RUN mysql -u root -e "USE mysql;CREATE USER 'youphptube'@'localhost' IDENTIFIED BY 'youphptube';GRANT ALL PRIVILEGES ON *.* TO 'youphptube'@'localhost'; FLUSH PRIVILEGES; "

RUN mkdir ~/build && cd ~/build && git clone https://github.com/arut/nginx-rtmp-module.git &&  git clone https://github.com/nginx/nginx.git && cd nginx &&  ./auto/configure --with-http_ssl_module --with-http_stub_status_module --add-module=../nginx-rtmp-module --with-cc-opt="-Wimplicit-fallthrough=0" && make &&  make install && cd /usr/local/nginx/html &&  wget https://raw.githubusercontent.com/WWBN/AVideo/master/plugin/Live/install/stat.xsl &&  apt install python3-pip -y &&  pip3 install glances &&  apt install certbot python3-certbot-apache software-properties-common -y 

# Add Ondrej's repo to the sources list
#RUN sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'

# Set localtime to UTC
RUN ln -fs /usr/share/zoneinfo/UTC /etc/localtime

RUN a2enmod rewrite headers expires ssl http2 xsendfile && service apache2 restart

# Set new default virtualhost
RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY deploy/apache/avideo.conf /etc/apache2/sites-enabled/avideo.conf

# Install supervisord
#RUN apt-get -y install supervisor

# Install php7.4
# RUN apt-get -y -f install php7.4 php7.4-common php7.4-cli php7.4-json php7.4-mbstring php7.4-curl php7.4-mysql php7.4-bcmath php7.4-xml php7.4-gd php7.4-zip --no-install-recommends

# COPY  . /var/www/html/AVideo
WORKDIR /var/www/html/AVideo/

# Set Permision
# Create folder if not exists
#RUN mkdir /var/www/html/AVideo/videos
#RUN chown www-data:www-data /var/www/html/AVideo/videos && chmod 755 /var/www/html/AVideo/videos
#VOLUME [ "/storage/data" ]

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

EXPOSE 80
EXPOSE 443
# EXPOSE 8443 for nginx HTTPS
# EXPOSE 2053 for Sockets
# EXPOSE 1935 for RTMP connection
#CMD ["supervisord"]

# By default, simply start apache.
CMD /usr/sbin/apache2ctl -D FOREGROUND
CMD /usr/local/nginx/sbin/nginx

RUN cd /var/www/html/AVideo/install && php install.php {{ .Env.webSiteRootURL }}
