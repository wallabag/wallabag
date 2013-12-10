<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" /> 
<xsl:variable name="title" select="/rss/channel/title"/>		
<xsl:template match="/">
<html>
  <head>
    <title><xsl:value-of select="$title"/> (full-text feed)</title>
    <style type="text/css">
    @import url(css/feed.css);
    </style>
  </head>
  <body>
    <div id="explanation">
      <h1><xsl:value-of select="$title"/> <span class="small"> (full-text feed)</span></h1>
      <p>You are viewing an auto-generated full-text <acronym title="Really Simple Syndication">RSS</acronym> feed. RSS feeds allow you to stay up to date with the latest news and features you want from websites. To subscribe to it, you will need a News Reader or other similar device.</p>
      <p>Below is the latest content available from this feed.</p>
    </div>
    
    <div id="content">
    <ul>
      <xsl:for-each select="rss/channel/item">
      <div class="article">
        <li><a href="{link}" rel="bookmark"><xsl:value-of disable-output-escaping="yes" select="title"/></a>
			<div><xsl:value-of disable-output-escaping="yes" select="description" /></div>
		</li>        
      </div>
      </xsl:for-each>
      </ul>
    </div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>