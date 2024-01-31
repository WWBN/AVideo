# First thing...

I thank God for graciously, through His mercy, giving me all the necessary knowledge acquired throughout my life and throughout the development of this project. It is only through His grace and provision that this was possible, and I am truly grateful for His presence every step of the way.

**For of Him, and through Him, and to Him, are all things: to whom be glory forever. Amen.**
`Apostle Paul in Romans 11:36`

<center>
    <img src="https://avideo.tube/website/assets/151/images/avideo_platform.png"/>
</center>

## Introduction to AVideo

AVideo is a versatile and advanced video streaming platform tailored for individual content creators, businesses, and developers alike. It stands out with its robust suite of features that enable users to host, manage, and monetize video content with remarkable efficiency. This introduction aims to shed light on the key functionalities of AVideo, highlighting how each feature can enhance user experience and content outreach. For a more detailed understanding, please follow the provided links.

## 🌟 Key Features of AVideo

1. **🎥 Video Playback and Management**: Experience top-tier video playback with AVideo, supporting self-hosted multiresolution videos for optimal viewing across devices. Enhance your content with [subtitles](https://github.com/WWBN/AVideo/wiki/Video-Transcription-for-the-SubtitleSwitcher-Plugin) and [AI-driven transcription](https://github.com/WWBN/AVideo/wiki/AI-Plugin) for increased engagement and accessibility. [Learn More](https://github.com/WWBN/AVideo/wiki/About-Video-Upload#3-embed-a-video-link).

2. **💰 Monetization Options**: Tap into new revenue streams with AVideo's versatile [subscription models](https://github.com/WWBN/AVideo/wiki/Subscription-Plugin) and [Pay-Per-View (PPV) plans](https://github.com/WWBN/AVideo/wiki/PayPerView-Plugin). Maximize the earning potential of your video content. [Learn More](https://github.com/WWBN/AVideo/wiki/Monetization:-How-To-Make-Money-on-AVideo-Platform).

3. **📡 Livestreaming and Interaction**: Engage your audience with AVideo's [live streaming](https://github.com/WWBN/AVideo/wiki/How-to-make-a-live-stream) capabilities. Record live sessions for later access and foster interaction with an integrated [chat feature](https://github.com/WWBN/AVideo/wiki/Chat2-Plugin).

4. **👥 User Channels and Playlists**: Enable users to create their own channels and playlists, encouraging organized and thematic content curation. [Learn More](https://github.com/WWBN/AVideo/wiki/Program-(Playlist)-to-series).

5. **🔄 Content Rebroadcasting**: Widen your audience with the [rebroadcasting](https://github.com/WWBN/AVideo/wiki/Rebroadcaster-Plugin) of VOD content and playlists, ensuring accessibility at any time.

6. **📢 Advertising and Promotion**: Craft bespoke [video ads](https://github.com/WWBN/AVideo/wiki/Ad-Server-Plugin) from your content or use [VAST video ads](https://github.com/WWBN/AVideo/wiki/Plugin:-GoogleAds_IMA---Videos-Ads-on-your-page) for extended reach.

7. **🔗 Third-Party App Integration**: Augment your platform's capabilities with AVideo's [API](https://github.com/WWBN/AVideo/wiki/AVideo-Platform-API) for developing connected third-party applications.

8. **☁️ Remote Video Storage**: Ensure secure and scalable video storage solutions with cloud services like S3, B2, FTP, and more. [Learn More](https://github.com/WWBN/AVideo/wiki/Storage-Options).

9. **🔒 Video Protection and Offline Viewing**: Protect your content with [HLS encryption](https://github.com/WWBN/AVideo/wiki/VideoHLS-Plugin) and provide [offline video saving](https://github.com/WWBN/AVideo/wiki/VideoOffline-Plugin) options for convenience. [Learn More](#).

## Your Comprehensive Video Streaming Solution

At AVideo, we provide more than just a platform; we offer a comprehensive solution for hosting, managing, monetizing, and expanding your video content. Embrace the future of video streaming and unlock the full potential of your content with AVideo. 

# How AVideo is organized

AVideo is divided into three distinct components: the streamer, the encoder, and the live server. The streamer is utilized to play and manage videos, while the encoder converts your videos into a web-compatible format. The live server, on the other hand, is used to broadcast live videos.

## Why do I need the Encoder?

There may be several reasons why you may consider installing the encoder, such as having a faster server compared to the public encoder server, or if you prefer a private method for encoding your videos

Additionally, if your server is on a private network and does not have a public IP address, or if it uses an IP address within the 10.0.0.0/8, 127.0.0.0/8 (Localhost), 172.16.0.0/12, or 192.168.0.0/16 range, it is mandatory to install an encoder in order for it to properly communicate with the streamer site.

## Agreement on the Purpose of Software Installation

This Software must be used for Good, never Evil. The use of this software for creating content related to sexually explicit material, pornography, or adult themes is strictly forbidden. Such usage goes against the values and principles of our platform and is not permitted under any circumstances.

# 🌐 Demonstration Sites

Explore our AVideo Platform through various demo sites, each showcasing different features and functionalities:

- **[AVideo Platform Full-Access Demo](http://demo.avideo.com/)**  
  Experience full access to our demo site, including admin privileges.  
  **Admin Access**:  
  - **User**: admin
  - **Password**: 123  
  **Non-Admin Access** (for commenting only):  
  - **User**: test
  - **Password**: test

- **[AVideo Platform Flix Demo](https://flix.avideo.com/)**  
  Discover the Flix Style site of AVideo Platform. Subscribe with real money via PayPal to access private videos.  
  **Test User Access**:  
  - **User**: test
  - **Password**: test

- **[AVideo Platform Gallery Demo](https://tutorials.avideo.com/)**  
  Explore our Video Gallery, which also serves as a tutorial site. Engage with the content through login, subscription, likes, dislikes, and comments. (Note: Uploading videos is not permitted.)

# 🖥️ Server Requirements

Ensure your server meets the following prerequisites to run the AVideo Platform efficiently. All required tools are freely available.

[![Minimum PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net/) - **PHP**: Version 7.4 or higher is required for optimal performance and security.

[![Minimum MySQL Version](https://img.shields.io/badge/MySQL-5.0%2B-blue)](https://www.mysql.com/) - **MySQL**: AVideo requires MySQL version 5.0 or higher to manage its databases effectively.

[![Minimum Apache Version](https://img.shields.io/badge/Apache-2.x%20%28mod__rewrite%29-blue)](https://httpd.apache.org/) - **Apache**: Utilize Apache web server version 2.x with mod_rewrite module enabled for URL rewriting capabilities.

[![GitHub release](https://img.shields.io/github/v/release/WWBN/AVideo?include_prereleases&label=AVideo&style=flat-square)](https://github.com/WWBN/AVideo/releases) - Stay up-to-date with the latest releases of AVideo.

For an in-depth look at the hardware requirements and additional server configurations, please visit our comprehensive guide: [AVideo Platform Hardware Requirements](https://github.com/WWBN/AVideo/wiki/AVideo-Platform-Hardware-Requirements).

# Crucial Advisory: Strictly Avoid Using Control Panels for Installation

**Important**: For the installation of the Streamer, Encoder, and Livestream components, it is imperative to use a Linux distribution, specifically Ubuntu, **without any type of control panel**. This includes avoiding panels like cPanel, Plesk, Webmin, VestaCP, and similar.

Control panels significantly interfere with the necessary system access and processes required for a successful installation. They restrict the installation of essential libraries and the compilation of critical software, such as Nginx for the Livestream component.

**Please be advised**: Installing our system on a server with any control panel is highly discouraged and is likely to result in installation failure. We cannot provide support or guarantee success in such scenarios. For a smooth and functional installation, it is essential to follow this guideline strictly.

# Installation Guide for AVideo on Ubuntu

Embarking on the installation of AVideo on your Ubuntu system? You're in the right place. Our comprehensive tutorials are tailored to guide you through every step of the installation process on various Ubuntu versions, including a Docker-based setup. 

🎬 **Video Tutorial**
- For a foundational understanding, check out our [Video Tutorial](https://tutorials.avideo.com/video/streamer-and-encoder). Though it's based on older versions of AVideo, it provides an excellent introduction to the installation process.

🐧 **Ubuntu-Specific Installation Guides**
- Tailor your installation to your specific Ubuntu version:
  - 📘 [Ubuntu 16.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x-For-AVideo-Platform-version-4.x-or-newer)
  - 📗 [Ubuntu 18.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x-for-AVideo-Platform-version-4.x-or-newer)
  - 📙 [Ubuntu 20.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-20.x-for-AVideo-Platform-version-11.x-or-newer)
  - 📔 [Ubuntu 22.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-22.x-for-AVideo-Platform-version-11.x-or-newer)

🐳 **Docker Installation**
- For a Docker-based setup, follow our [Docker Installation Guide](https://github.com/WWBN/AVideo/wiki/Running-AVideo-with-Docker) to streamline your experience.

These tutorials cover the entire scope of downloading, installing AVideo, and setting up required dependencies. By following them, you can efficiently prepare your Ubuntu system for AVideo.

# Usage

For administrative guidance, the [Admin Manual](https://github.com/WWBN/AVideo/wiki/Admin-manual) is your go-to resource, providing detailed instructions on managing and optimizing your AVideo platform.

# Errors and Troubleshooting

Encountered an issue? Our [error identification guide](https://github.com/WWBN/AVideo/wiki/How-to-find-errors-on-AVideo-Platform) is a valuable tool for troubleshooting and resolving common problems.

## AVideo Platform Certified Support

If you require specialized assistance with installation, consulting, or plugins, our team of certified AVideo Platform developers is at your service. Reach out to [Daniel Neto](https://youphp.tube/marketplace/) for professional support and expert consulting to ensure a seamless and effective AVideo installation and setup.
