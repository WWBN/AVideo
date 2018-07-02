Installation
~~~~~~~~~~~~

LAMP
^^^^

LAMP means Linux Apache Mysql and PHP. Those manuals are great if you
set it up from scratch.

-  `How to install LAMP, FFMPEG and Git on a fresh Ubuntu 16.x For
   YouPHPTube version 3.x or
   older <installUbuntu16Old.html>`__
   (YPT deprececated)
-  `How to install LAMP, FFMPEG and Git on a fresh Ubuntu 16.x For
   YouPHPTube version 4.x or
   newer <installUbuntu16.html>`__
-  `How to install LAMP, FFMPEG and Git on a fresh Ubuntu 18.x for
   YouPHPTube version 4.x or
   newer <installUbuntu18.html>`__
-  `Install YouPHPTube in Debian
   9.3 <Install-YouPHPTube-in-Debian-9.3>`__

Nginx
^^^^^

Nginx is only experimental supported for the moment.

Since we have php-routing enable, this should be possible:

https://github.com/skipperbent/simple-php-router#setting-up-nginx

Thanks to patriclougheed, there are also two config-files for nginx
(this solution surround the php-router above):

-  `Streamer <https://gist.github.com/patriclougheed/706677ffe2459df3b6587e54fd4a0923>`__
-  `Encoder <https://gist.github.com/patriclougheed/29a6d997a1371952e29bd8384ea9bf4e>`__
   (this is needed anyway, until php-router also reaches the encoder)

Both methods should work. You can find it's issue here:
https://github.com/DanielnetoDotCom/YouPHPTube/issues/691

Microsofts IIS
^^^^^^^^^^^^^^

This should work, but it's more a side-effect of implement
simple-php-router than something else (eventualy it will never be
tested?).

The web.config-file is already added, a direct copy-paste of this:

https://github.com/skipperbent/simple-php-router#setting-up-iis

Configuration
^^^^^^^^^^^^^

-  `Advanced Customization Plugin <Advanced-Customization-Plugin>`__
-  `GoogleAds\_IMA-Video-Ads on your
   page <https://github.com/DanielnetoDotCom/YouPHPTube/wiki/Plugin:-GoogleAds_IMA---Videos-Ads-on-your-page>`__
-  `Configure LDAP-Plugin <Configure-LDAP-Plugin>`__
-  `Automatic Thumbs (JPG-GIF) on direct uploaded MP4
   videos <Automatic-Thumbs-(JPG-GIF)-on-direct-uploaded-MP4-videos>`__
-  `Setting up YouPHPTube to send
   emails <Setting-up-YouPHPTube-to-send-emails>`__
-  `Use AWS S3 on YouPHPTube <Use-AWS-S3-on-YouPHPTube>`__
-  `Redirect http to https <Redirect-http-to-https>`__

Paid plugins
^^^^^^^^^^^^

We have some advanced functionality in form of plugins, we sell. Please
have a look at https://easytube.club .

Some examples: \* Change resolutions \* Subtitles \* Secure videos
folder \* VR360-Video \* Livesearch \* and many more...

Update youphptube
~~~~~~~~~~~~~~~~~

-  `How to update your YouPHPTube <How-to-Update-your-YouPHPTube>`__

Performance
~~~~~~~~~~~

When you have a lot of users, your server can reach it's limits. To
prevent this, there are some things possible. \* Use a host with mysqlnd
enabled! We provide support for non-mysqlnd-host's, but we have a
SQL-cache for mysqlnd only. Also, this is a fast, native driver (better
performance anyway). If you are unshure if you have this, ask your
hoster. \* Enable minify of javascript! This helps only, to reduce the
bandwidth a little. Go to advanced customization-plugin and enable
"Minify JS". Clear videos/cache after this! \* Enable the
`Cache-Plugin <Cache-Plugin>`__ \* Disable (Gallery- and
Youphpflix-plugin) and not set gifs (use less bandwidth)

Livestream
~~~~~~~~~~

-  `Setup streamer
   (video-tutorial) <https://tutorials.youphptube.com/video/10-min-youphptube-stream-server-installation>`__
-  `Record Live Stream <Record-Live-Stream>`__
-  `Configure NGINX Stream
   Resolutions <Configure-NGINX-Stream-Resolutions>`__

Various
~~~~~~~

-  `Howto Install a new Plugin <How-To-Install-a-new-Plugin>`__
-  .. rubric:: Troubleshooting
      :name: troubleshooting

Various things can cause problems. Here, you find steps that eventualy
fix your problem. If it doesn't, please read `Check Ajax
answer <Check-Ajax-answer>`__ and `How to find errors on
YouPHPTube <How-to-find-errors-on-YouPHPTube>`__ for a usefull issue -
this makes it easier for us to help you.

-  Recheck, if all database-upgrades are done (**Menu -> Update
   version**)
-  Clear the cache-folder (delete all files in **videos/cache/**)
-  Ad-managment is broken? Try disable your adblocker
-  `How to find errors on
   YouPHPTube <How-to-find-errors-on-YouPHPTube>`__
-  `Check Ajax answer <Check-Ajax-answer>`__
-  `Mysql Troubleshooting <Mysql-Troubleshooting>`__
-  `Message when rewrite is not set / 404-Errors / install
   rewrite-modules <Message-when-rewrite-is-not-set>`__
-  `Error while sending QUERY packet
   cpanel <Error-while-sending-QUERY-packet-cpanel>`__
-  `How To Install a new Plugin <How-To-Install-a-new-Plugin>`__
-  `youdtube dl failed to extract
   signature <youdtube-dl-failed-to-extract-signature>`__
-  `Encoder-Error We could not found your
   streamer-site! <Encoder-Error-We-could-not-found-your-streamer-site!>`__

Known problems
~~~~~~~~~~~~~~

-  If the chart is not counting videos, try disable the
   `Cache-Plugin <Cache-Plugin>`__.
