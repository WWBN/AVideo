FROM ubuntu:latest

# Update SO
RUN apt-get update && apt-get upgrade -y

# Install utils
RUN apt-get install apt-transport-https lsb-release logrotate git curl vim net-tools iputils-ping -y --no-install-recommends

# Install all the rest
RUN sudo apt-get update -y && sudo apt-get upgrade -y && sudo apt-get install sshpass nano net-tools curl apache2 php7.4 libapache2-mod-php7.4 php7.4-mysql php7.4-curl php7.4-gd php7.4-intl php7.4-xml  \
    mysql-server mysql-client ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile -y  && sudo a2enmod xsendfile && cd /var/www/html && \
    sudo git clone https://github.com/WWBN/AVideo.git && cd /var/www/html && sudo git clone https://github.com/WWBN/AVideo-Encoder.git && sudo apt-get install python -y && \
    sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl && sudo chmod a+rx /usr/local/bin/youtube-dl && sudo apt-get install build-essential libpcre3 libpcre3-dev libssl-dev php7.4-xml -y && sudo a2enmod rewrite && \
    sudo chown www-data:www-data /var/www/html/AVideo/plugin && sudo chmod 755 /var/www/html/AVideo/plugin && sudo apt-get install unzip -y && sudo apt-get install htop python3-pip \
    && sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl && sudo chmod a+rx /usr/local/bin/youtube-dl && sudo apt-get install build-essential libpcre3 libpcre3-dev libssl-dev -y && \
    sudo a2enmod rewrite && sudo chown www-data:www-data /var/www/html/AVideo/plugin && sudo chmod 755 /var/www/html/AVideo/plugin && sudo apt-get install unzip -y && cd /var/www/html/AVideo/plugin/User_Location/install && \
    sudo unzip install.zip && sudo pip3 install youtube-dl && sudo pip3 install --upgrade youtube-dl && sudo a2enmod expires && sudo a2enmod headers && sudo chmod 777 /var/www/html/AVideo/objects/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/ \
    && sudo mkdir /var/www/tmp && sudo chmod 777 /var/www/tmp && sudo apt-get install build-essential libssl-dev libpcre3 libpcre3-dev && sudo apt-get install --reinstall zlibc zlib1g zlib1g-dev -y && sudo mkdir ~/build && cd ~/build && \
    sudo git clone https://github.com/arut/nginx-rtmp-module.git && sudo git clone https://github.com/nginx/nginx.git && cd nginx && sudo ./auto/configure --with-http_ssl_module --with-http_stub_status_module --add-module=../nginx-rtmp-module --with-cc-opt="-Wimplicit-fallthrough=0" && 
    sudo make && sudo make install && cd /usr/local/nginx/html && sudo wget https://raw.githubusercontent.com/WWBN/AVideo/master/plugin/Live/install/stat.xsl && sudo apt install python3-pip -y && sudo pip3 install glances && sudo apt install certbot python3-certbot-apache software-properties-common -y \
    && sudo mysql -u root -e "USE mysql;CREATE USER 'youphptube'@'localhost' IDENTIFIED BY 'youphptube';GRANT ALL PRIVILEGES ON *.* TO 'youphptube'@'localhost'; FLUSH PRIVILEGES; "

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
# RUN apt-get -y -f install php7.4 php7.4-common php7.4-cli php7.4-json php7.4-mbstring php7.4-curl php7.4-mysql php7.4-bcmath php7.4-xml php7.4-gd php7.4-zip --no-install-recommends

# COPY  . /var/www/avideo
WORKDIR /var/www/avideo

# Set Permision
# Create folder if not exists
#RUN mkdir /var/www/avideo/videos
#RUN chown www-data:www-data /var/www/avideo/videos && chmod 755 /var/www/avideo/videos
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

RUN cd /var/www/avideo/install && php install.php {{ .Env.JIBRI_INSTANCE }}
