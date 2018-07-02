### Installation

#### LAMP

LAMP means Linux Apache Mysql and PHP. Those manuals are great if you set it up from scratch.

* [How to install LAMP, FFMPEG and Git on a fresh Ubuntu 16.x For YouPHPTube version 3.x or older](How-to-install-LAMP,--FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x---For-YouPHPTube-version-3.x-or-older) (YPT deprececated)
* [How to install LAMP, FFMPEG and Git on a fresh Ubuntu 16.x For YouPHPTube version 4.x or newer](How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x-For-YouPHPTube-version-4.x-or-newer)
* [How to install LAMP, FFMPEG and Git on a fresh Ubuntu 18.x for YouPHPTube version 4.x or newer](How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x-for-YouPHPTube-version-4.x-or-newer)
* [Install YouPHPTube in Debian 9.3](Install-YouPHPTube-in-Debian-9.3)

#### Nginx

Nginx is only experimental supported for the moment.

Since we have php-routing enable, this should be possible:

https://github.com/skipperbent/simple-php-router#setting-up-nginx

Thanks to patriclougheed, there are also two config-files for nginx (this solution surround the php-router above):

* [Streamer](https://gist.github.com/patriclougheed/706677ffe2459df3b6587e54fd4a0923)
* [Encoder](https://gist.github.com/patriclougheed/29a6d997a1371952e29bd8384ea9bf4e) (this is needed anyway, until php-router also reaches the encoder)

Both methods should work. You can find it's issue here: https://github.com/DanielnetoDotCom/YouPHPTube/issues/691

#### Microsofts IIS

This should work, but it's more a side-effect of implement simple-php-router than something else (eventualy it will never be tested?).

The web.config-file is already added, a direct copy-paste of this:

https://github.com/skipperbent/simple-php-router#setting-up-iis

#### Configuration

* [Advanced Customization Plugin](Advanced-Customization-Plugin)
* [GoogleAds_IMA-Video-Ads on your page](https://github.com/DanielnetoDotCom/YouPHPTube/wiki/Plugin:-GoogleAds_IMA---Videos-Ads-on-your-page)
* [Configure LDAP-Plugin](Configure-LDAP-Plugin)
* [Automatic Thumbs (JPG-GIF) on direct uploaded MP4 videos](Automatic-Thumbs-(JPG-GIF)-on-direct-uploaded-MP4-videos)
* [Setting up YouPHPTube to send emails](Setting-up-YouPHPTube-to-send-emails)
* [Use AWS S3 on YouPHPTube](Use-AWS-S3-on-YouPHPTube)
* [Redirect http to https](Redirect-http-to-https)

#### Paid plugins
We have some advanced functionality in form of plugins, we sell. Please have a look at https://easytube.club .

Some examples:
* Change resolutions
* Subtitles
* Secure videos folder
* VR360-Video
* Livesearch
* and many more...

### Update youphptube
* [How to update your YouPHPTube](How-to-Update-your-YouPHPTube)

### Performance
When you have a lot of users, your server can reach it's limits. To prevent this, there are some things possible.
* Use a host with mysqlnd enabled! We provide support for non-mysqlnd-host's, but we have a SQL-cache for mysqlnd only. Also, this is a fast, native driver (better performance anyway). If you are unshure if you have this, ask your hoster.
* Enable minify of javascript! This helps only, to reduce the bandwidth a little. Go to advanced customization-plugin and enable "Minify JS". Clear videos/cache after this!
* Enable the [Cache-Plugin](Cache-Plugin)
* Disable (Gallery- and Youphpflix-plugin) and not set gifs (use less bandwidth)

### Livestream
* [Setup streamer (video-tutorial)](https://tutorials.youphptube.com/video/10-min-youphptube-stream-server-installation)
* [Record Live Stream](Record-Live-Stream)
* [Configure NGINX Stream Resolutions](Configure-NGINX-Stream-Resolutions)

### Various
* [Howto Install a new Plugin](How-To-Install-a-new-Plugin)
* 
### Troubleshooting

Various things can cause problems. Here, you find steps that eventualy fix your problem. If it doesn't, please read [Check Ajax answer](Check-Ajax-answer) and [How to find errors on YouPHPTube](How-to-find-errors-on-YouPHPTube) for a usefull issue - this makes it easier for us to help you.

* Recheck, if all database-upgrades are done (**Menu -> Update version**)
* Clear the cache-folder (delete all files in **videos/cache/**)
* Ad-managment is broken? Try disable your adblocker
* [How to find errors on YouPHPTube](How-to-find-errors-on-YouPHPTube)
* [Check Ajax answer](Check-Ajax-answer)
* [Mysql Troubleshooting](Mysql-Troubleshooting)
* [Message when rewrite is not set / 404-Errors / install rewrite-modules](Message-when-rewrite-is-not-set)
* [Error while sending QUERY packet cpanel](Error-while-sending-QUERY-packet-cpanel)
* [How To Install a new Plugin](How-To-Install-a-new-Plugin)
* [youdtube dl failed to extract signature](youdtube-dl-failed-to-extract-signature)
* [Encoder-Error We could not found your streamer-site!](Encoder-Error-We-could-not-found-your-streamer-site!)

### Known problems
* If the chart is not counting videos, try disable the [Cache-Plugin](Cache-Plugin).