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
		
		<html xml:lang="en" lang="en">
			
			<head>
				
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
				
				<title>404 Not Found</title>
				
			</head>
			
			<body class="pigment">
				
				<div>
					<h1>
						<span>Not Found</span>
					</h1>
					<div>The requested URI "" was not found.</div>
				</div>
				
			</body>
			
		</html>

	</xsl:template>

</xsl:stylesheet>