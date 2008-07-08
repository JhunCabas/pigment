<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

	<xsl:output
		method="xml" 
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" 
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		omit-xml-declaration="yes"
		encoding="UTF-8" 
		indent="yes" />

	<xsl:template match="/">

		<xsl:variable name="host" as="xs:string" select="/page/resource/host" />
		
		<xsl:variable name="directory" as="xs:string" select="/page/resource/directory" />
		
		<xsl:variable name="version" as="xs:string" select="/page/resource/version" />
		
		<xsl:variable name="space" as="xs:string" select="/page/resource/space" />
		
		<xsl:variable name="spaceversion" as="xs:string" select="/page/resource/spaceversion" />
		
		<xsl:variable name="page" as="xs:string" select="/page/resource/page" />
		
		<xsl:variable name="stub" as="xs:string" select="/page/resource/stub" />
		
		<xsl:variable name="author" as="xs:string" select="/page/resource/page-author" />
		
		<xsl:variable name="description" as="xs:string" select="/page/resource/page-description" />
		
		<xsl:variable name="keywords" as="xs:string" select="/page/resource/page-keywords" />
		
		<xsl:variable name="icon" as="xs:string" select="/page/resource/page-icon" />
		
		<xsl:variable name="content" as="xs:string">
			
			<xsl:choose>

				<xsl:when test="/page/resource/stub != ''"> content <xsl:value-of select="$stub" disable-output-escaping="yes"/>-content</xsl:when>
				
				<xsl:when test="1 = 1"></xsl:when>
					
			</xsl:choose>
			
		</xsl:variable>
		
		<html xml:lang="en" lang="en">
			
			<head>
				
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
				
				<title><xsl:value-of select="/page/resource/page-title" disable-output-escaping="yes"/></title>
				
				<meta name="author" content="{$author}" />
				
				<meta name="description" content="{$description}" />
				
				<meta name="keywords" content="{$keywords}" />
				
				<xsl:choose>

					<xsl:when test="/page/resource/page-privacy = 'private'">

						<meta name='robots' content='noindex,nofollow' />

					</xsl:when>

					<xsl:when test="1 = 1">

						<meta name="robots" content="all" />

					</xsl:when>

				</xsl:choose>
				
				<link rel="stylesheet" type="text/css" href="{$host}/{$directory}/css/{$spaceversion}/index.css" />
				
				<script src="{$host}/{$directory}/js/{$spaceversion}/index.js" type="text/javascript"></script>
				
				<link rel="icon" href="{$icon}" type="image/x-icon" />
				
			</head>
			
			<body class="pigment">
				
				<div class="{$page}{$content}">
					
					<xsl:apply-templates select="page/content/heading"/>
					
					<xsl:if test="count(page/content/menu/item) &gt; 1">
						
						<ul class="menu">
						
							<xsl:apply-templates select="page/content/menu"/>
							
						</ul>
					
					</xsl:if>
					
					<xsl:if test="count(page/content/sidea/item) &gt; 0">
					
						<ul class="side sidea">
					
							<xsl:call-template name="side">
						
								<xsl:with-param name="side" select="/page/content/sidea" />
						
							</xsl:call-template>
						
						</ul>
						
					</xsl:if>
					
					<ul class="body">
						
						<xsl:apply-templates select="page/content/body"/>
					
					</ul>
					
					
					<xsl:if test="count(page/content/sideb/item) &gt; 0">
					
						<ul class="side sideb">
					
							<xsl:call-template name="side">
						
								<xsl:with-param name="side" select="/page/content/sideb" />
						
							</xsl:call-template>
						
						</ul>
						
					</xsl:if>
					
					<xsl:if test="count(page/content/footer/item) &gt; 0">
						
						<ul class="footer">
						
							<xsl:apply-templates select="page/content/footer"/>
							
						</ul>
					
					</xsl:if>
					
				</div>
				
			</body>
			
		</html>

	</xsl:template>

	<xsl:template match="heading/item">
		
		<xsl:variable name="host" as="xs:string" select="/page/resource/host" />
		
		<xsl:variable name="spaceversion" as="xs:string" select="/page/resource/spaceversion" />
		
		<xsl:choose>

			<xsl:when test="/page/resource/page = 'index'">
			
				<h1><xsl:value-of select="content" disable-output-escaping="yes"/></h1>
			
			</xsl:when>
			
			<xsl:when test="1 = 1">
				
				<h1><a href="."><xsl:value-of select="content" disable-output-escaping="yes"/></a></h1>
				
			</xsl:when>
				
		</xsl:choose>
		
		

	</xsl:template>
	
	<xsl:template match="menu/item">
		
		<li>
			
			<xsl:variable name="host" as="xs:string" select="/page/resource/host" />
			
			<xsl:variable name="spaceversion" as="xs:string" select="/page/resource/spaceversion" />
			
			<xsl:variable name="page" as="xs:string" select="stub" />
			
			<a href="{$page}"><xsl:value-of select="content" disable-output-escaping="yes"/></a>
			
		</li>

	</xsl:template>
	
	<xsl:template name="side">
		
		<xsl:param name="side" />
		
		<xsl:for-each select="$side/item">
			
			<xsl:variable name="id" as="xs:string" select="contentid" />

			<xsl:variable name="pageid" as="xs:string" select="pageid" />

			<xsl:variable name="order" as="xs:string" select="order" />

			<xsl:variable name="location" as="xs:string" select="location" />
			
			<li id="contentid_{$id}">

				<xsl:value-of select="content" disable-output-escaping="yes"/>

			</li>
			
		</xsl:for-each>	

	</xsl:template>
	
	<xsl:template match="body/item">
		
		<xsl:variable name="id" as="xs:string" select="contentid" />
		
		<xsl:variable name="pageid" as="xs:string" select="pageid" />
		
		<xsl:variable name="order" as="xs:string" select="order" />
		
		<xsl:variable name="location" as="xs:string" select="location" />
	
		<li id="contentid_{$id}">
			
			<xsl:value-of select="content" disable-output-escaping="yes"/>
			
		</li>

	</xsl:template>
	
	<xsl:template match="footer/item">
		
		<xsl:variable name="id" as="xs:string" select="contentid" />
		
		<xsl:variable name="pageid" as="xs:string" select="pageid" />
		
		<xsl:variable name="order" as="xs:string" select="order" />
		
		<xsl:variable name="location" as="xs:string" select="location" />
		
		<li id="contentid_{$id}">
			
			<xsl:value-of select="content" disable-output-escaping="yes"/>
			
		</li>

	</xsl:template>

</xsl:stylesheet>