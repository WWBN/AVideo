# First thing...

I thank God for graciously, through His mercy, giving me all the necessary knowledge acquired throughout my life and throughout the development of this project. It is only through His grace and provision that this was possible, and I am truly grateful for His presence every step of the way.

> **For of Him, and through Him, and to Him, are all things: to whom be glory forever. Amen.**
> `Apostle Paul in Romans 11:36`

<p align="center">
  <img src="https://avideo.tube/website/assets/151/images/avideo_platform.png"/>
</p>

## Introduction to AVideo

AVideo is a versatile and advanced video streaming platform tailored for individual content creators, businesses, and developers alike. It stands out with its robust suite of features that enable users to host, manage, and monetize video content with remarkable efficiency. This introduction aims to shed light on the key functionalities of AVideo, highlighting how each feature can enhance user experience and content outreach. For a more detailed understanding, please follow the provided links.

## 🌟 Key Features of AVideo

1. **🔒 Advanced Security & Content Protection**: Safeguard your video content with AVideo’s [encrypted HLS streaming](https://github.com/WWBN/AVideo/wiki/VideoHLS-Plugin), protecting both on-demand and live streams. Encryption keys are securely managed to ensure only authorized players can access your content, offering a strong defense against unauthorized access.

2. **📡 Secure Livestreaming with Recording**: Host live events with confidence using AVideo’s [secure livestreaming](https://github.com/WWBN/AVideo/wiki/How-to-make-a-live-stream) capabilities, backed by encrypted HLS protection. Engage viewers in real-time, record live streams for future access, and enhance interaction through integrated [chat features](https://github.com/WWBN/AVideo/wiki/Chat2-Plugin) for a more immersive experience.

3. **🔄 Restreaming & Multi-Platform Broadcasting**: Extend your livestream’s reach by rebroadcasting content across multiple platforms simultaneously. [Restreaming capabilities](https://github.com/WWBN/AVideo/wiki/Live-Plugin#restream) make it easy to connect with audiences wherever they are.

4. **📋 User-Generated Channels & Playlists**: Empower users to create custom channels and playlists, helping organize and promote thematic content curation. Boost engagement and community-building by letting viewers personalize their viewing experience.

5. **💰 Monetization Options**: Maximize revenue with AVideo’s flexible [subscription](https://github.com/WWBN/AVideo/wiki/Subscription-Plugin) and [Pay-Per-View](https://github.com/WWBN/AVideo/wiki/PayPerView-Plugin) options. Expand monetization opportunities, allowing users to support premium content and exclusive live events.

6. **📢 Ad Integration & Promotion**: Increase revenue with targeted [video ad placements](https://github.com/WWBN/AVideo/wiki/Ad-Server-Plugin) and support for [VAST and VMAP ads](https://github.com/WWBN/AVideo/wiki/Plugin:-GoogleAds_IMA---Videos-Ads-on-your-page), enhancing your platform's profitability and reach.

7. **☁️ Scalable Cloud Storage**: Rely on secure and scalable storage solutions with options like S3, B2, FTP, and more, ensuring seamless video delivery even during high traffic peaks. [Learn More](https://github.com/WWBN/AVideo/wiki/Storage-Options).

8. **🔗 Third-Party Integration & API**: Extend platform capabilities by connecting third-party apps with AVideo’s [API](https://github.com/WWBN/AVideo/wiki/AVideo-Platform-API), offering flexibility for tailored integrations and custom development.

9. **📥 Offline Viewing & Secure Downloads**: Allow viewers to download and watch videos offline with AVideo’s [offline video saving](https://github.com/WWBN/AVideo/wiki/VideoOffline-Plugin) feature, while maintaining strict [content protection](https://github.com/WWBN/AVideo/wiki/VideoHLS-Plugin#download-protection) to prevent unauthorized distribution. 

## Your Comprehensive Video Streaming Solution

At AVideo, we provide more than just a platform; we offer a comprehensive solution for hosting, managing, monetizing, and expanding your video content. Embrace the future of video streaming and unlock the full potential of your content with AVideo. 

# 📚 How AVideo is Organized

AVideo is a comprehensive platform, divided into three key components:

- **Streamer**: The core component for playing and managing videos. It acts as the main interface for users to interact with video content.
- **Encoder**: This tool converts your videos into a web-compatible format, ensuring they are ready for streaming on various devices and platforms.
- **Live Server**: Specifically designed for broadcasting live videos, this component is essential for real-time streaming capabilities.

## 🔍 Why Do I Need the Encoder?

Installing your own encoder can be beneficial for several reasons:

- **Faster Performance**: Having your own encoder might provide faster processing compared to using a public encoder server.
- **Privacy**: If privacy in video processing is a concern, a private encoder ensures that your content remains confidential.
- **Network Compatibility**: In cases where your server is on a private network without a public IP address or uses an IP within specific ranges (10.0.0.0/8, 127.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16), having your own encoder is essential for proper communication with the streamer site.

## 📜 Agreement on the Purpose of Software Installation

AVideo is dedicated to promoting positive and ethical content creation. As such, we firmly stipulate that:

- This Software must be used for Good, never for Evil.
- The creation of content related to sexually explicit material, pornography, or adult themes using this software is strictly prohibited.
- Any such usage is against the values and principles of our platform and is not permitted under any circumstances.

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

[![Minimum PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net/) - **PHP**: Version 8.0 or higher is required for optimal performance and security.

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
- For a foundational understanding, check out our [Video Tutorial](https://tutorials.avideo.com/video/10/streamer-and-encoder). Though it's based on older versions of AVideo, it provides an excellent introduction to the installation process.

🐧 **Ubuntu-Specific Installation Guides**
- Tailor your installation to your specific Ubuntu version:
  - 📘 [Ubuntu 16.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-16.x-For-AVideo-Platform-version-4.x-or-newer)
  - 📗 [Ubuntu 18.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-18.x-for-AVideo-Platform-version-4.x-or-newer)
  - 📙 [Ubuntu 20.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-20.x-for-AVideo-Platform-version-11.x-or-newer)
  - 📔 [Ubuntu 22.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-22.x-for-AVideo-Platform-version-11.x-or-newer)
  - 📒 [Ubuntu 24.04 Guide](https://github.com/WWBN/AVideo/wiki/How-to-install-LAMP,-FFMPEG-and-Git-on-a-fresh-Ubuntu-24.x-for-AVideo-Platform)

🐳 **Docker Installation**
- For a Docker-based setup, follow our [Docker Installation Guide](https://github.com/WWBN/AVideo/wiki/Running-AVideo-with-Docker) to streamline your experience.

These tutorials cover the entire scope of downloading, installing AVideo, and setting up required dependencies. By following them, you can efficiently prepare your Ubuntu system for AVideo.

# 📘 Usage

For comprehensive administrative guidance, refer to the [Admin Manual](https://github.com/WWBN/AVideo/wiki/Admin-manual). This resource provides detailed instructions on how to manage and optimize your AVideo platform effectively.

# 🛠️ Errors and Troubleshooting

Encountered an issue? Don't worry! Our [error identification guide](https://github.com/WWBN/AVideo/wiki/How-to-find-errors-on-AVideo-Platform) is designed to help you troubleshoot and resolve common problems efficiently.

## 🌟 AVideo Platform Certified Support

Require specialized assistance? Our team of certified AVideo Platform developers is here to help. For professional support and expert consulting on installation, consulting, or plugins, reach out to [Daniel Neto](https://youphp.tube/marketplace/). We're committed to ensuring a seamless and effective AVideo installation and setup.
