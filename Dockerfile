FROM ubuntu/apache2:2.4-20.04_edge

# Update OS
RUN apt-get update && apt-get upgrade -y
#RUN apt-get install dialog apt-utils -y
# Install utils
RUN apt-get install systemctl apt-transport-https lsb-release logrotate git curl vim net-tools iputils-ping nano wget -y --no-install-recommends
# Install all the rest but no php yet
#RUN DEBIAN_FRONTEND="noninteractive" apt-get install sshpass net-tools mariadb-server mariadb-client ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile python build-essential make libpcre3 libpcre3-dev libssl-dev unzip htop python3-pip  -y 
RUN DEBIAN_FRONTEND="noninteractive" apt-get install sshpass net-tools ffmpeg git libimage-exiftool-perl libapache2-mod-xsendfile python build-essential make libpcre3 libpcre3-dev libssl-dev unzip htop python3-pip  -y 
RUN cd /var/www/html && git clone https://github.com/WWBN/AVideo.git && cd /var/www/html &&  git clone https://github.com/WWBN/AVideo-Encoder.git &&  curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl &&  chmod a+rx /usr/local/bin/youtube-dl && chown www-data:www-data /var/www/html/AVideo/plugin &&  chmod 755 /var/www/html/AVideo/plugin &&  curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl &&  chmod a+rx /usr/local/bin/youtube-dl && a2enmod rewrite &&  chown www-data:www-data /var/www/html/AVideo/plugin &&  chmod 755 /var/www/html/AVideo/plugin && cd /var/www/html/AVideo/plugin/User_Location/install && unzip install.zip &&  pip3 install youtube-dl &&  pip3 install --upgrade youtube-dl &&  a2enmod expires &&  a2enmod headers &&  chmod 777 /var/www/html/AVideo/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/ &&  mkdir /var/www/tmp &&  chmod 777 /var/www/tmp


RUN a2enmod rewrite headers expires ssl xsendfile

# Set new default virtualhost
RUN rm /etc/apache2/sites-enabled/000-default.conf
#COPY /var/www/html/AVideo/deploy/apache/avideo.conf /etc/apache2/sites-enabled/avideo.conf
RUN cd /var/www/html/AVideo
RUN cp /var/www/html/AVideo/deploy/apache/avideo.conf /etc/apache2/sites-enabled/avideo.conf

WORKDIR /var/www/html/AVideo/

# Set Permision
# Create folder if not exists
RUN mkdir /var/www/html/AVideo/videos
RUN chown www-data:www-data /var/www/html/AVideo/videos && chmod 777 /var/www/html/AVideo/videos
# Now we install php stuff
RUN apt-get install php7.4 php7.4-common php7.4-cli php7.4-json php7.4-mbstring php7.4-curl php7.4-mysql php7.4-bcmath php7.4-xml php7.4-gd php7.4-zip libapache2-mod-php7.4 php7.4-intl -y
CMD apachectl -D FOREGROUND
RUN service apache2 restart
