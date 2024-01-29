# First thing...

I thank God for graciously, through His mercy, giving me all the necessary knowledge acquired throughout my life and throughout the development of this project. It is only through His grace and provision that this was possible, and I am truly grateful for His presence every step of the way.

**For of Him, and through Him, and to Him, are all things: to whom be glory forever. Amen.**
`Apostle Paul in Romans 11:36`

<center>
    <img src="https://avideo.tube/website/assets/151/images/avideo_platform.png"/>
</center>

## Agreement on the Purpose of Software Installation

This Software must be used for Good, never Evil. The use of this software for creating content related to sexually explicit material, pornography, or adult themes is strictly forbidden. Such usage goes against the values and principles of our platform and is not permitted under any circumstances.

# Demonstration Sites

* <a href="http://demo.avideo.com/" target="_blank">AVideo Platform Full-Access Demo</a>
  - We provide you with a full-access demo site sample, which includes access to the admin account. To upload and manage videos, you will need the default admin password. User: admin and Password: 123. There is also a non-admin user and password (for commenting only). User: test and Password: test."
* <a href="https://flix.avideo.com/" target="_blank">AVideo Platform Flix Demo</a>
  - We offer you a demonstration of the AVideo Flix Style site. On this site, you can subscribe using real money through PayPal, which will grant you access to our private videos. We have provided a test user for you to experience how the site works. User: test and Password: test.
* <a href="https://tutorials.avideo.com/" target="_blank">AVideo Platform Gallery Demo</a>
  - We have also provided a sample Video Gallery site, which doubles as our tutorial site. In this demonstration, you can log in, subscribe, like, dislike, and comment, but uploading videos is not allowed.

# AVideo

## Introduction to AVideo

AVideo is an advanced video streaming platform, designed to cater to a broad spectrum of video hosting and streaming needs. Ideal for individual content creators, businesses, and developers, AVideo provides a comprehensive suite of features enabling users to host, manage, and monetize their video content efficiently. This introduction outlines AVideo's key functionalities, each underpinned by its capability to enhance user experience and content reach. For a more in-depth understanding of each feature, detailed links are provided.

## Key Features of AVideo

1. **Video Playback and Management**: AVideo excels in seamless video playback, supporting self-hosted multiresolution videos to ensure optimal viewing across different devices. Unique to AVideo, users can enhance their self-hosted videos with [subtitles](https://github.com/WWBN/AVideo/wiki/Video-Transcription-for-the-SubtitleSwitcher-Plugin) and [leverage AI](https://github.com/WWBN/AVideo/wiki/AI-Plugin) for accurate video transcription, enriching viewer engagement and accessibility. [Learn More](https://github.com/WWBN/AVideo/wiki/About-Video-Upload#3-embed-a-video-link)

2. **Monetization Options**: Diversify your revenue streams through AVideo's [subscription](https://github.com/WWBN/AVideo/wiki/Subscription-Plugin) models or [Pay-Per-View (PPV)](https://github.com/WWBN/AVideo/wiki/PayPerView-Plugin) plans. This functionality allows content creators and businesses to monetize their video content effectively. [Learn More](https://github.com/WWBN/AVideo/wiki/Monetization:-How-To-Make-Money-on-AVideo-Platform)

3. **Livestreaming and Interaction**: Engage your audience in real-time with AVideo's [live streaming feature](https://github.com/WWBN/AVideo/wiki/How-to-make-a-live-stream), which includes options to record live sessions for later access. Enhance the interactive experience with an integrated [chat function](https://github.com/WWBN/AVideo/wiki/Chat2-Plugin).

4. **User Channels and Playlists**: AVideo enables users to create individual channels and curate playlists, facilitating organized and thematic content sharing. [Learn More](https://github.com/WWBN/AVideo/wiki/Program-(Playlist)-to-series)

5. **Content Rebroadcasting**: The platform supports the [rebroadcasting](https://github.com/WWBN/AVideo/wiki/Rebroadcaster-Plugin) of both Video-On-Demand (VOD) content and playlists, ensuring your audience has access to your content at their convenience.

6. **Advertising and Promotion**: Utilize AVideo for creating bespoke [video ads from your content](https://github.com/WWBN/AVideo/wiki/Ad-Server-Plugin) or integrate [VAST video ads ](https://github.com/WWBN/AVideo/wiki/Plugin:-GoogleAds_IMA---Videos-Ads-on-your-page)for expanded reach.

7. **Third-Party App Integration**: Leverage AVideo's [API for developing](https://github.com/WWBN/AVideo/wiki/AVideo-Platform-API) third-party applications, enhancing the platform's integration and functionality across different services.

8. **Remote Video Storage**: Store your videos on various cloud services like S3, B2, FTP, and more, ensuring scalable and secure storage solutions. [Learn More](https://github.com/WWBN/AVideo/wiki/Storage-Options)

9. **Video Protection and Offline Viewing**: Protect your videos [from unauthorized downloads with HLS format and encryption](https://github.com/WWBN/AVideo/wiki/VideoHLS-Plugin). AVideo also offers options for [offline video saving](https://github.com/WWBN/AVideo/wiki/VideoOffline-Plugin), catering to viewers' convenience. [Learn More](#)

## Your Comprehensive Video Streaming Solution

AVideo is more than a platform; it's a comprehensive solution for hosting, managing, monetizing, and expanding your video content. Embrace the future of video streaming with AVideo. 

AVideo is divided into three distinct components: the streamer, the encoder, and the live server. The streamer is utilized to play and manage videos, while the encoder converts your videos into a web-compatible format. The live server, on the other hand, is used to broadcast live videos.

## Why do I need the Encoder?

There may be several reasons why you may consider installing the encoder, such as having a faster server compared to the public encoder server, or if you prefer a private method for encoding your videos

Additionally, if your server is on a private network and does not have a public IP address, or if it uses an IP address within the 10.0.0.0/8, 127.0.0.0/8 (Localhost), 172.16.0.0/12, or 192.168.0.0/16 range, it is mandatory to install an encoder in order for it to properly communicate with the streamer site.

# Server Requirements

[![Minimum PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net/)
[![Minimum MySQL Version](https://img.shields.io/badge/MySQL-5.0%2B-blue)](https://www.mysql.com/)
[![Minimum Apache Version](https://img.shields.io/badge/Apache-2.x%20%28mod__rewrite%29-blue)](https://httpd.apache.org/)
[![GitHub release](https://img.shields.io/github/v/release/WWBN/AVideo?include_prereleases&label=AVideo&style=flat-square)](https://github.com/WWBN/AVideo/releases)

To run the AVideo Platform, it is necessary to have certain tools installed on your server. Fortunately, all of these tools are available for free. For a comprehensive list of the required tools, please refer to the following link: https://github.com/WWBN/AVideo/wiki/AVideo-Platform-Hardware-Requirements.

In summary, you will need:

- PHP version 7.4 or higher
- MySQL version 5.0 or higher
- Apache web server version 2.x with mod_rewrite enabled.

# Crucial Advisory: Strictly Avoid Using Control Panels for Installation

**Important**: For the installation of the Streamer, Encoder, and Livestream components, it is imperative to use a Linux distribution, specifically Ubuntu, **without any type of control panel**. This includes avoiding panels like cPanel, Plesk, Webmin, VestaCP, and similar.

Control panels significantly interfere with the necessary system access and processes required for a successful installation. They restrict the installation of essential libraries and the compilation of critical software, such as Nginx for the Livestream component.

**Please be advised**: Installing our system on a server with any control panel is highly discouraged and is likely to result in installation failure. We cannot provide support or guarantee success in such scenarios. For a smooth and functional installation, it is essential to follow this guideline strictly.

# Installation Guide for AVideo on Ubuntu

Embarking on the installation of AVideo on your Ubuntu system? You're in the right place. Our comprehensive tutorials are tailored to guide you through every step of the installation process on various Ubuntu versions, including a Docker-based setup. 

- **Video Tutorial**: For a general overview, our [video tutorial](https://tutorials.avideo.com/video/streamer-and-encoder) provides foundational insights. While it's based on older versions, it's an excellent starting point for understanding the installation process.
- **Ubuntu-Specific Guides**: Depending on your Ubuntu version, please refer to the corresponding guide for detailed instructions:
  - [Ubuntu 16.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x-For-AVideo-Platform-version-4.x-or-newer)
  - [Ubuntu 18.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x-for-AVideo-Platform-version-4.x-or-newer)
  - [Ubuntu 20.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-20.x-for-AVideo-Platform-version-11.x-or-newer)
  - [Ubuntu 22.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-22.x-for-AVideo-Platform-version-11.x-or-newer)
- **Docker Installation**: For those preferring Docker, our [Docker guide](https://github.com/WWBN/AVideo/wiki/Running-AVideo-with-Docker) is designed to streamline your experience.

These tutorials cover the entire scope of downloading, installing AVideo, and setting up required dependencies. By following them, you can efficiently prepare your Ubuntu system for AVideo.

# Usage

For administrative guidance, the [Admin Manual](https://github.com/WWBN/AVideo/wiki/Admin-manual) is your go-to resource, providing detailed instructions on managing and optimizing your AVideo platform.

# Errors and Troubleshooting

Encountered an issue? Our [error identification guide](https://github.com/WWBN/AVideo/wiki/How-to-find-errors-on-AVideo-Platform) is a valuable tool for troubleshooting and resolving common problems.

## AVideo Platform Certified Support

If you require specialized assistance with installation, consulting, or plugins, our team of certified AVideo Platform developers is at your service. Reach out to [Daniel Neto](https://youphp.tube/marketplace/) for professional support and expert consulting to ensure a seamless and effective AVideo installation and setup.
