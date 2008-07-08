<?php
	
	class Server
	{
		
		function __construct()
		{
			
			$this->protocol = array_shift( explode( '/', strtolower( $_SERVER[ 'SERVER_PROTOCOL' ] ) ) );

			$domain = $this->domain = $_SERVER[ 'SERVER_NAME' ];
			
			$exp = explode( '.', $domain );
			
			$this->subdomain = ( count( $exp ) > 1 ) ? $exp[ 0 ] : '';

			$this->port = $_SERVER[ 'SERVER_PORT' ];

			$this->host = ( $this->port == 80 ) ? $this->protocol . '://' . $this->domain : $this->protocol . '://' . $this->domain . ':' . $this->port;

			$this->uri = $_SERVER[ 'REQUEST_URI' ];

			$exp = explode( '?', $this->uri );

			$this->querystring = ( count( $exp ) > 1 ) ? $exp[ 1 ] : '';

			$exp = explode( '/', $this->uri );

			$this->directory = $exp[ 1 ];

		}
		
	}

?>