**This Tutorial will teach you how to install YouPHPTube Streamer Site
what means it is the front end of YouPHPTube. You can watch it running
at https://demo.youphptube.com/ or https://tutorials.youphptube.com/**

If for any reason you need help to set up the YouPHPTube app or the server, fell free to ask us for help:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

https://www.youphptube.com/services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Just copy and paste this:

if you want to install both (encoder and streamer) use this compiled
code:

``sudo apt-get install apache2 php libapache2-mod-php php-mysql php-curl php-gd php-intl mysql-server mysql-client ffmpeg git libimage-exiftool-perl && cd /var/www/html && sudo git clone https://github.com/DanielnetoDotCom/YouPHPTube.git && cd /var/www/html && sudo git clone https://github.com/DanielnetoDotCom/YouPHPTube-Encoder.git && sudo apt-get install python && sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl && sudo chmod a+rx /usr/local/bin/youtube-dl && sudo a2enmod rewrite``

or if you want just the encoder use this:

``sudo apt-get install apache2 php libapache2-mod-php php-mysql php-curl php-gd php-intl mysql-server mysql-client git && cd /var/www/html && sudo git clone https://github.com/DanielnetoDotCom/YouPHPTube.git``

Also, you need those commands for concrete Ubuntu 18 (it's not in the
upper command because it's not tested):

::

    sudo apt install php-mbstring php-gettext
    sudo phpenmod mbstring
    sudo systemctl restart apache2

After that, you need to set your mysql-root-password. This step has
moved from Ubuntu 16 (password set while install) to Ubuntu 18 (password
can be set anytime).

The most simple seems to be this:

::

    sudo mysql_secure_installation

or

::

    sudo mysqladmin -u root OLDPASSWORD NEWPASSWORD

or, if none of them work

::

    sudo mysql
    ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'NEWPASSWORD';

Replace NEWPASSWORD with your password.

Source and more info is
`here <https://linuxconfig.org/how-to-reset-root-mysql-password-on-ubuntu-18-04-bionic-beaver-linux>`__,
maybe also `Issue
796 <https://github.com/DanielnetoDotCom/YouPHPTube/issues/796>`__ can
help you.

Do not forget the "Rewrite-modules"-steps downer! If you have
404-errors, check them!

Single command list
'''''''''''''''''''

If you have problems on installation, try it command by command, maybe
this helps you or give us more info, what's failing.

::

    sudo apt-get install apache2 php libapache2-mod-php php-mysql php-curl php-gd php-intl mysql-server mysql-client ffmpeg git libimage-exiftool-perl php-mbstring php-gettext python
    cd /var/www/html
    sudo git clone https://github.com/DanielnetoDotCom/YouPHPTube.git
    sudo git clone https://github.com/DanielnetoDotCom/YouPHPTube-Encoder.git # only for encoder
    sudo curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl # only for encoder
    sudo chmod a+rx /usr/local/bin/youtube-dl # only for encoder
    sudo a2enmod rewrite
    sudo phpenmod mbstring
    sudo systemctl restart apache2
    sudo mysql_secure_installation # set mysql-root-password

Rewrite-modules
'''''''''''''''

This is a important step.

We need to allow Apache to read .htaccess files located under the
directory. You can do this by editing the Apache configuration file:

Find the section ``<directory /var/www/html>`` and change
**AllowOverride None** to **AllowOverride All**

::

    sudo nano /etc/apache2/apache2.conf

After editing the above file your code should be like this:

::

    <Directory /var/www/>
              Options Indexes FollowSymLinks
              AllowOverride All
              Require all granted
      </Directory>

In order to use mod\_rewrite you can type the following command in the
terminal:

::

    sudo a2enmod rewrite

Restart apache2 after

::

    sudo /etc/init.d/apache2 restart

or

::

    sudo service apache2 restart

**The Encoder** We recommend that you use an YouPHPTube Encoder
privately, it is also available for free and open source and you can
download it
`here <https://github.com/DanielnetoDotCom/YouPHPTube-Encoder>`__ and
also we made some `installation
instructions <https://github.com/DanielnetoDotCom/YouPHPTube-Encoder/wiki/How-to-install-LAMP,--FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x---For-YouPHPTube-Encoder>`__.
But if you are limited in hardware or software resources feel free to
use our public encoder https://encoder.youphptube.com/

We hope you have fun! If you need help, have any question or Issue
please open an Issue on
https://github.com/DanielnetoDotCom/YouPHPTube/issues
