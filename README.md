# First thing...

I thank God for graciously, through His mercy, giving me all the necessary knowledge acquired throughout my life and throughout the development of this project. It is only through His grace and provision that this was possible, and I am truly grateful for His presence every step of the way.

**For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen.**
`Apostle Paul in Romans 11:36`

## This Software must be used for Good, never Evil. The use of this software for creating content related to sexually explicit material, pornography, or adult themes is strictly forbidden. Such usage goes against the values and principles of our platform and is not permitted under any circumstances.

<img src="https://avideo.tube/website/assets/151/images/avideo_platform.png"/>

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg?style=flat-square)](https://php.net/)
[![GitHub release](https://img.shields.io/github/v/release/WWBN/AVideo)](https://github.com/WWBN/AVideo/releases)



### To see a demo select one below.
* <a href="https://flix.avideo.com/" target="_blank">AVideo Platform Flix Demo</a>
  - We provide you a AVideo Flix Style site sample. On this site you can subscribe (with real money on PayPal). this subscription will allow you to watch our private videos. There is an user that you can use to see how it works. user: test and pass: test.
* <a href="https://tutorials.wwbn.net/" target="_blank">AVideo Platform Gallery Demo</a>
  - We've provided a sample Video Gallery site, which is also our tutorials site. On this sample you can login, subscribe, like, dislike and comment. but you can not upload videos.
* <a href="http://demo.avideo.com/" target="_blank">AVideo Platform Full-Access Demo</a>
  - We provide you a Demo site sample with full access to the admin account. You will need an admin password to upload and manage videos, it is by default. user: admin and pass: 123. Also there is a non admin user and password (Only for comments). user: test and pass: test.

# Notice:

## We highly recommend you keep your instance of AVideo Platform updated to the latest release. If you have updated your instance to version 8.0, your old plugins will not work, just download them again and you should be fine.

## Important Information

> Streamer can be installed on any Server, including Windows, but the encoder and Livestream should work fine on any Linux distribution. However we recommend Ubuntu 20.04 without any kind of control panel.
> The problem with cPanel, Plesk, Webmin, VestaCP, etc. It's because we need full root access to install some libs, and maybe compile them. Another important point is that to make Livestream work, we need to compile Nginx and the control panels often prevent us from running the commands forcing the installation available only on your panel.

I don´t want to read I just want you to show me how to install!!

Ok, <a href="https://tutorials.wwbn.net/video/streamer-and-encoder">check this out!</a>

For text-based tutorials and the manual, <a href="https://github.com/WWBN/AVideo/wiki/Admin-manual"> look here</a>.

There, you can find some hints for troubleshooting as well.

### AVideo Platform Mobile APP
<a href="https://play.google.com/store/apps/details?id=platform.avideo.com">Android</a>

## AVideo Platform Certified
#### Need Help With Installation or Plugins? Feel free to ask us for help from the AVideo Platform Certified developers.

<a href="https://youphp.tube/marketplace/">Daniel Neto</a>

# AVideo - Streamer
AVideo! is an video-sharing website, It is an open source solution that is freely available to everyone. With AVideo you can create your own video sharing site, AVideo will help you import and encode videos from other sites like Youtube, Vimeo, etc. and you can share directly on your website. In addition, you can use Facebook or Google login to register users on your site. The service was created in march 2017.

<div align="center">
<img src="https://avideo.tube/website/assets/151/images/who-we-are.jpg">
<a href="http://demo.avideo.com/" target="_blank">View Demo</a>
</div>

# AVideo - Encoder
Go get it <a href="https://github.com/WWBN/AVideo-Encoder" target="_blank">here</a>

<div align="center">
<img src="https://avideo.tube/website/assets/151/images/encoder_img.png">
<a href="https://encoder1.wwbn.net/" target="_blank">View Public Encoder</a>
</div>

# Why do I need the Encoder?
You may want to install the encoder for a few reasons, such as, if you have a faster server than the public encoder server (which is likely to be the case), or if you'd like a private way of encoding your videos.

But, the installation is mandatory if you are using a private network. The public encoder will not have access to send the videos to your streamer site.

If your server does not have a public IP or uses an IP on some of these bands:
- 10.0.0.0/8
- 127.0.0.0/8 (Localhost)
- 172.16.0.0/12
- 192.168.0.0/16

Surely you need to install an encoder

# Server Requirements

In order for you to be able to run AVideo Platform, there are certain tools that need to be installed on your server. Don't worry, they are all FREE. To have a look at complete list of required tools, click the link below. https://github.com/WWBN/AVideo/wiki/AVideo-Platform-Hardware-Requirements

- PHP 7.3+
- MySQL 5.0+
- Apache web server 2.x (with mod_rewrite enabled)

## Docker

We've created a docker compose environment for easy development and production.

### Development

Either just build the current branch by cloning the repository and run

```bash
doker build -t avideo .
```

And run the image. It contains an Apache2 webserver exposing ports 80 and 443. 
We recommend using HTTPS on port 443 and ignore the HTTP port 80. The container
will create a self-signed certificate on startup. There are some environment
variables, that could be used to configure the environment

- `DB_MYSQL_HOST` - defines the database host name - default is `database`
- `DB_MYSQL_PORT` - defines the database port - default is `3306`
- `DB_MYSQL_NAME` - defines the database name - default is `avideo`
- `DB_MYSQL_USER` - defines the database user - default is `avideo`
- `DB_MYSQL_PASSWORD` - defines the database password - default is `avideo`
- `SERVER_NAME` - defines the virtualhost name for Apache - default is ` avideo.localhost`
- `ENABLE_PHPMYADMIN` - defines, if PHPMyAdmin should be exposed - default is `yes`
- `CREATE_TLS_CERTIFICATE` - defines, if the container should generate a self-signed certificate - default is `yes`
- `TLS_CERTIFICATE_FILE` - defines the location of the TLS certificate - default is `/etc/apache2/ssl/localhost.crt`
- `TLS_CERTIFICATE_KEY` - defines the location of the TLS private key - default is `/etc/apache2/ssl/localhost.key`
- `CONTACT_EMAIL` - defines the contact mail address - default is `admin@localhost`
- `SYSTEM_ADMIN_PASSWORD` - defines the system administrator passwort - default is `password`
- `WEBSITE_TITLE` - defines the website title - default is `AVideo`
- `MAIN_LANGUAGE` - defines the main language - default is `en_US`

If you don't want to rebuild the image during development, mount the git repository to
the path `/var/www/html/AVideo`. Then it using your local copy.

### Compose

We've also a simple docker compose environment to define the complete necessary 
environment. You can just use and customize the local `docker-compose.yml` file.

Beside the above defined docker image that can be build locally, the environment
contains a MariaDB database and a PhpMyAdmin to have an easy look into the 
database content.

```bash
docker-compose up --build -d
```

In production you should remove the phpmyadmin image by setting `ENABLE_PHPMYADMIN=no`.

Also we're working on a prebuild image. So you can use the image from [Docker hub](https://hub.docker.com/r/trickert76/avideo-platform/tags).

After a git clone command run this

composer update --prefer-dist --ignore-platform-reqs
