<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/rss/channel">
        <html>
            <head>
                <meta charset="UTF-8"/>
                <title><xsl:value-of select="title"/></title>
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; background: #f5f5f5; margin: 0; padding: 0; color: #222; }
                    .header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 20px; border-bottom: 1px solid #ddd; }
                    .header img { width: 96px; height: 96px; border-radius: 8px; object-fit: cover; }
                    .header h1 { margin: 0 0 6px 0; font-size: 22px; }
                    .header p { margin: 0; color: #555; }
                    .container { max-width: 900px; margin: 20px auto; padding: 0 15px; }
                    .notice { background: #fff8e1; border: 1px solid #ffe082; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; }
                    .item { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px 20px; margin-bottom: 12px; }
                    .item h2 { margin: 0 0 6px 0; font-size: 17px; }
                    .item .date { color: #888; font-size: 12px; margin-bottom: 8px; }
                    .item audio, .item a.download { display: block; margin-top: 8px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <xsl:if test="image/url">
                        <img src="{image/url}" alt="logo"/>
                    </xsl:if>
                    <div>
                        <h1><xsl:value-of select="title"/></h1>
                        <p><xsl:value-of select="description" disable-output-escaping="yes"/></p>
                    </div>
                </div>
                <div class="container">
                    <div class="notice">This is an RSS/Podcast feed. Subscribe to it using your favorite podcast app.</div>
                    <xsl:for-each select="item">
                        <div class="item">
                            <h2><xsl:value-of select="title"/></h2>
                            <div class="date"><xsl:value-of select="pubDate"/></div>
                            <div><xsl:value-of select="description" disable-output-escaping="yes"/></div>
                            <xsl:if test="enclosure/@url">
                                <audio controls="controls" preload="none">
                                    <source src="{enclosure/@url}" type="{enclosure/@type}"/>
                                </audio>
                                <a class="download" href="{enclosure/@url}">Download episode</a>
                            </xsl:if>
                        </div>
                    </xsl:for-each>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
