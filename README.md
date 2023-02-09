# First thing...

I thank God for graciously, through His mercy, giving me all the necessary knowledge acquired throughout my life and throughout the development of this project. It is only through His grace and provision that this was possible, and I am truly grateful for His presence every step of the way.

**For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen.**
`Apostle Paul in Romans 11:36`

## Agreement on the Purpose of Software Installation

This Software must be used for Good, never Evil. The use of this software for creating content related to sexually explicit material, pornography, or adult themes is strictly forbidden. Such usage goes against the values and principles of our platform and is not permitted under any circumstances.

<center>
    <img src="https://avideo.tube/website/assets/151/images/avideo_platform.png"/>
</center>

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg?style=flat-square)](https://php.net/)
[![GitHub release](https://img.shields.io/github/v/release/WWBN/AVideo)](https://github.com/WWBN/AVideo/releases)

# Demo Sites

* <a href="https://flix.avideo.com/" target="_blank">AVideo Platform Flix Demo</a>
  - We provide you a AVideo Flix Style site sample. On this site you can subscribe (with real money on PayPal). this subscription will allow you to watch our private videos. There is an user that you can use to see how it works. user: test and pass: test.
* <a href="https://tutorials.avideo.com/" target="_blank">AVideo Platform Gallery Demo</a>
  - We've provided a sample Video Gallery site, which is also our tutorials site. On this sample you can login, subscribe, like, dislike and comment. but you can not upload videos.
* <a href="http://demo.avideo.com/" target="_blank">AVideo Platform Full-Access Demo</a>
  - We provide you a Demo site sample with full access to the admin account. You will need an admin password to upload and manage videos, it is by default. user: admin and pass: 123. Also there is a non admin user and password (Only for comments). user: test and pass: test.

# AVideo

AVideo is divided into three distinct components: the streamer, the encoder, and the live server. The streamer is utilized to play and manage videos, while the encoder converts your videos into a web-compatible format. The live server, on the other hand, is used to broadcast live videos.

## Why do I need the Encoder?

There may be several reasons why you may consider installing the encoder, such as having a faster server compared to the public encoder server, or if you prefer a private method for encoding your videos

Additionally, if your server is on a private network and does not have a public IP address, or if it uses an IP address within the 10.0.0.0/8, 127.0.0.0/8 (Localhost), 172.16.0.0/12, or 192.168.0.0/16 range, it is mandatory to install an encoder in order for it to properly communicate with the streamer site.

# Server Requirements

To run the AVideo Platform, it is necessary to have certain tools installed on your server. Fortunately, all of these tools are available for free. For a comprehensive list of the required tools, please refer to the following link: https://github.com/WWBN/AVideo/wiki/AVideo-Platform-Hardware-Requirements.

In summary, you will need:

- PHP version 7.4 or higher
- MySQL version 5.0 or higher
- Apache web server version 2.x with mod_rewrite enabled.

## Important Note: Please Avoid Using Servers with Control Panels

> It is important to note that while the Streamer component can be installed on any server, including Windows, the Encoder and Livestream components are recommended to be installed on a Linux distribution, specifically Ubuntu, without any control panel.

> The reason for this is that control panels such as cPanel, Plesk, Webmin, VestaCP, etc. may limit access to the root system, preventing the installation of necessary libraries and the compilation of certain software. Furthermore, the Livestream component requires the compilation of Nginx, which may not be possible with these control panels.

# Installation

[Video Tutorial](https://tutorials.avideo.com/video/streamer-and-encoder) (old but gives you a good idea)
[Ubuntu 16.04](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x-For-AVideo-Platform-version-4.x-or-newer)
[Ubuntu 18.04](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x-for-AVideo-Platform-version-4.x-or-newer)
[Ubuntu 20.04](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-20.x-for-AVideo-Platform-version-11.x-or-newer)
[Ubuntu 22.04](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-22.x-for-AVideo-Platform-version-11.x-or-newer)

## Separated live server

[Install Nginx](https://github.com/WWBN/AVideo/wiki/Set-up-my-own-Stream-Server)

## Notice:

We highly recommend you keep your instance of AVideo Platform updated to the latest release. If you have updated your instance to version 8.0, your old plugins will not work, just download and [install](https://github.com/WWBN/AVideo/wiki/How-To-Install-a-new-Plugin) them again and you should be fine.

# Usage

[Admin Manual](https://github.com/WWBN/AVideo/wiki/Admin-manual)

# Errors and troubleshooting

[Find errors](https://github.com/WWBN/AVideo/wiki/How-to-find-errors-on-AVideo-Platform)

## AVideo Platform Certified

### Installation help, consulting or support 

For assistance with installation, consultin or plugins, our team of certified AVideo Platform developers is here to help. We would be delighted to offer you our support and expertise to ensure a smooth and successful installation.

<a href="https://youphp.tube/marketplace/">Daniel Neto</a>

### AVideo Platform Mobile APP

<a href="https://play.google.com/store/apps/details?id=platform.avideo.com">Android</a>

# Docker

We've created a docker compose environment for easy development and production.

## Development

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
