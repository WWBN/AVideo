# YouPHPTube - Streamer
YouPHPTube! is an video-sharing website, It is an open source solution that is freely available to everyone. With YouPHPTube you can create your own video sharing site, YouPHPTube will help you import and encode videos from other sites like Youtube, Vimeo, etc. and you can share directly on your website. In addition, you can use Facebook or Google login to register users on your site. The service was created in march 2017.

<div align="center">
<img src="http://www.youphptube.com/img/prints/prints7.png">
<a href="http://demo.youphptube.com/" target="_blank">View Demo</a>
</div>

# Server Requirements

In order for you to be able to run YouPHPTube, there are certain tools that need to be installed on your server. Don't worry, they are all FREE. To have a look at complete list of required tools, click the link below.

- Linux (Kernel 2.6.32+)
- PHP 5.3+
- MySQL 5.0+
- Apache web server 2.x (with mod_rewrite enabled)

# What is new on this version?
Since version 4.x+ we separate the streamer website from the encoder website, so that we can distribute the application on different servers.
- The Streamer site, is the main front end and has as main function to attend the visitors of the site, through a layout based on the youtube experience, you can host the streamer site in any common internet host can host it (Windows or Linux).
- The Encoder site, will be better than the original encoder, the new encoder will be in charge of managing a media encoding queue. You can Donwload the encoder here: https://github.com/DanielnetoDotCom/YouPHPTube-Encoder. but to install it you will need ssh access to your server, usually only VPS servers give you that kind of access, that code uses commands that use the Linux shell and consume more CPU.
- I will have to install the encoder and the streamer?
No. We will be providing a public encoder, we will build the encoder in such a way that several streamers can use the same encoder. We are also providing source code for this, so you can install it internally and manage your own encoding priority.

<div align="center">
<img src="https://www.youphptube.com/img/architecture/SchemeV4.0.jpg">
<a href="https://github.com/DanielnetoDotCom/YouPHPTube-Encoder" target="_blank">Download Encoder</a>
</div>

# Older version
If you want the old version with Streamer and Encoder together (Version 3.4) download it <a href="https://github.com/DanielnetoDotCom/YouPHPTube/archive/3.4.zip">here</a>
