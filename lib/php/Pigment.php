<?php
	
	class Pigment
	{
		
		function __construct( $args = array() )
		{
			
			// parent::__construct( $args ); //no parent
			
			include "Server.php";
			
			$server = new Server();
			
			$this->format = 'default';
			
			if ( !empty( $server->querystring ) ) { $this->querystring = $server->querystring; }
			
			if ( isset( $this->querystring ) ) {
				
				if ( strstr( $this->querystring, 'urlrewrite=' ) ) {
					
					$this->redirect( '.' );
					
				}
				
				if ( isset( $_REQUEST[ 'object' ] ) ) {
					
					$this->format = 'object';

				} else if ( isset( $_REQUEST[ 'xml' ] ) ) {

					$this->format = 'xml';

				} else if ( isset( $_REQUEST[ 'log' ] ) ) {

					$this->format = 'log';

				}
				
			}
			
			$this->urlrewrite = $_REQUEST[ 'urlrewrite' ];
			
			$this->root = dirname( dirname( dirname( __FILE__ ) ) );
			
			$file = $this->root . "/lib/ini/preferences.ini";
			
			$this->preferences = parse_ini_file( $file );
			
			$this->parseUri( $server );
			
			$this->parseRequest();
			
			$this->parseRequestType();
			
			if ( $this->format === 'log' ) {
				
				$this->log();
				
			} else {
				
				$this->render();
				
			}
			
		}
		
		public function log()
		{
			
			// log hit, crunch later: datetime | space | page | ip | useragent | os | acceptlanguage | referrer | uniqueid
			
		}
		
		public function render()
		{
			
			eval( "\$this->content = \$this->parse$this->requesttype();" );
			
			$this->checkFor404();
			
			if ( $this->format === 'object' ) { $this->printObject( $this ); }
			
			else { 
				
				ob_start();

					$file = $this->root . '/lib/xml/pigment.xml';

					echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";

					if ( is_file( $file ) ) { include $file; }

					$xml = ob_get_contents();

				ob_end_clean();
			
			}
			
			if ( $this->format === 'xml' ) { 
				
				$type = $this->getHeader( 'xml' );
				
				header( "Content-Type: $type" );

				echo $xml;
				
			} else if ( $this->format === 'default' ) {
				
				if ( $this->requesttype === 'Content' ) {
					
					$type = $this->getHeader( 'xml' );

					$file = "$this->root/lib/xsl/$this->version.xsl";

					if ( !is_file( $file ) ) { $file = "$this->root/lib/xsl/default.xsl"; }

					$xmldoc = DOMDocument::loadXML( $xml );

					$xsldoc = new DomDocument;

					$xsldoc->load( $file );

					$proc = new XSLTProcessor();

					$proc->registerPHPFunctions();

					$proc->importStyleSheet( $xsldoc );
					
					$output = $proc->transformToXML( $xmldoc );
					
					$selector = '</html>';
					
					if ( strstr( $output, $selector ) ) {
						
						$exp = explode( $selector, $output );
						
						$uri = $_SERVER[ 'REQUEST_URI' ];
						
						$delim = strstr( $uri, '?' ) ? '&' : '?';
						
						$track = '<object type="text/html" width="1" height="1" data="'.$uri.$delim.'log"></object>';
						
						$output = implode( $track . '</html>', $exp );
						
					}
					
				} else {
					
					$output = $this->content;
					
				}
				
			}
			
			if ( isset( $output ) ) {
				
				if ( isset( $this->is404 ) && $this->is404 === true ) {
					
					header(' ', true, 404);
					
				} else {
					
					header( "Content-Type: $this->header" );
					
				}
				
				echo $output;
				
				if ( $this->preferences[ 'cache' ] && !isset( $this->querystring ) && !isset( $this->is404 ) ) {
					
					$this->cache( $output );
					
				}
				
			}
			
		}
		
		public function checkFor404()
		{
			
			if ( count( $this->content ) === 0 ) {
				
				$heading404 = $this->getInclude( '404heading', 'htm' );
				
				$body404 = $this->getInclude( '404body', 'htm' );
				
				$this->content[ 'heading' ] = array();
				
				$this->content[ 'heading' ][ 0 ] = array();
				
				$inc = $heading404;
				
				if ( is_file( $inc ) ) { 

					ob_start();

					include $inc;

					$this->content[ 'heading' ][ 0 ][ 'content' ] = ob_get_contents();

					ob_end_clean();

				}
				
				$this->content[ 'body' ] = array();
				
				$this->content[ 'body' ][ 0 ] = array();
				
				$inc = $body404;
				
				if ( is_file( $inc ) ) { 

					ob_start();

					include $inc;

					$this->content[ 'body' ][ 0 ][ 'content' ] = ob_get_contents();

					ob_end_clean();

				}
				
				$this->is404 = true;
				
				$this->header = $this->getHeader( '-1' );
				
			}
			
		}
		
		public function parseRequestType()
		{
			
			$t = array_pop( explode( '/', $this->uri ) );
			
			if ( strstr( $t, 'index.' ) ) {
				
				$this->requesttype = 'Asset';
				
				$this->libraries = $this->stub;
				
				unset( $this->stub );
				
				$this->extension = $this->page;
				
				unset( $this->page );
				
			} else {
				
				$this->requesttype = 'Content';
				
				if ( empty( $this->page ) ) {
					
					$this->page = 'index';
					
				}
				
				$this->extension = 'html';
				
			}
			
			if ( isset( $this->domainpreferences[ 'page' ] ) && isset( $this->page ) && $this->page == 'index' ) {
				
				$page = $this->domainpreferences[ 'page' ];
				
				$url = "$this->host/$page";
				
				$this->redirect( $url );
				
			}
			
			$this->header = $this->getHeader( $this->extension );
			
			$this->mediatype = $this->getMediaType();
			
			if ( $this->mediatype === 'text' && $this->requesttype === 'Asset' ) {
				
				$this->libraries = explode( ',', $this->libraries );
				
			} else if ( $this->requesttype === 'Asset' ) {
				
				$this->directives = $this->libraries;
				
				unset( $this->libraries );
				
			}
			
		}
		
		public function getMediaType()
		{
		
			$mediatype = 'text';
			
			$e = $this->extension;
			
			if ( $e == 'jpg' || $e == 'png' || $e == 'gif' ) {
				
				$mediatype = 'image';
				
			} else if ( $e == 'swf' ) {
				
				$mediatype = 'media';
				
			}
			
			return $mediatype;
			
		}
		
		public function parseRequest()
		{
			
			$t = explode( '/', $this->uri );
			
			$this->spaceversion = $t[ 0 ];
			
			if ( strstr( $this->spaceversion, '.' ) ) {
				
				$tt = explode( '.', $this->spaceversion );
				
				$this->space = $tt[ 0 ];

				$this->version = $tt[ 1 ];
				
			} else {

				$this->space = $this->spaceversion;

				$this->version = 'default';

			}
			
			$this->page = !isset( $t[ 1 ] ) ? 'index' : $t[ 1 ];
			
			if ( isset( $t[ 2 ] ) && !empty( $t[ 2 ] ) ) { $this->stub = $t[ 2 ]; }
			
			if ( isset( $t[ 3 ] ) && !empty( $t[ 3 ] ) ) { $this->parameters = $t[ 3 ]; }
			
			$t = null;
			
			if ( isset( $this->parameters ) && strstr( $this->parameters, ';' ) ) {
				
				$t = $this->parameters;
				
			} else if ( isset( $this->stub ) && strstr( $this->stub, ';' ) ) {
				
				$t = $this->stub;
				
				unset( $this->stub );
				
			} else if ( isset( $this->page ) && strstr( $this->page, ';' ) ) {

				$t = $this->page;
				
				$this->page = 'index';

			}
			
			if ( $t != null ) { $this->parseParameters( $t ); } else { unset( $this->parameters ); }
			
			if ( strstr( $this->page, '.' ) || ( strlen( $this->page ) === 4 && intval( $this->page ) ) ) {
				
				$this->pagetype = 'archive';
				
				$t = explode( '.', $this->page );
				
				$this->year = $t[ 0 ];
				
				if ( isset( $t[ 1 ] ) ) { $this->month = $t[ 1 ]; }
				
				if ( isset( $t[ 2 ] ) ) { $this->day = $t[ 2 ]; }
				
			} else {
				
				$this->pagetype = 'page';
				
			}
			
			
			
			
			if ( isset( $this->stub ) && !empty( $this->stub ) ) {
				
				if ( $this->pagetype === 'archive' ) {
					
					$this->page = $this->stub;
					
					unset( $this->stub );
					
				}
				
			} else {
				
				if ( $this->pagetype === 'archive' ) {
					
					$this->page = 1;
					
				}
				
			}
			
			
		}
		
		public function parseParameters( $parameters )
		{
			
			$t = explode( ',', $parameters );

			$this->parameters = array();
			
			foreach ( $t as $i => $item ) {

				$tt = explode( ';', $item );

				$this->parameters[ $tt[ 0 ] ] = $tt[ 1 ];

			}
			
			//parmeter overrides
			
			if ( isset( $this->parameters[ 'space' ] ) ) $this->space = $this->parameters[ 'space' ];
			
			if ( isset( $this->parameters[ 'version' ] ) ) $this->version = $this->parameters[ 'version' ];
			
			if ( isset( $this->parameters[ 'page' ] ) ) $this->page = $this->parameters[ 'page' ];
			
			if ( isset( $this->parameters[ 'stub' ] ) ) $this->stub = $this->parameters[ 'stub' ];
			
		}
		
		public function parseUri( $server )
		{
			
			$t = explode( '/', $this->urlrewrite );
			
			$this->protocol = $server->protocol;
			
			if ( !empty( $server->subdomain ) ) { $this->subdomain = $server->subdomain; }
			
			$this->port = $server->port;
			
			$this->host = $server->host;
			
			$this->domain = array_shift( $t );
			
			$file = $this->root . "/lib/ini/domain;$this->domain.ini";
			
			$this->domainpreferences = parse_ini_file( $file );
			
			$this->acceptlanguage = array_shift( $t );
			
			$tt = ( !isset( $this->domainpreferences[ 'root' ] ) ) ? $this->preferences[ 'space' ] : $this->domainpreferences[ 'root' ];
			
			$ttt = explode( '/', $tt );
			
			$this->uri = array_shift( $ttt ) . '/' . implode( '/', $t );
			
			$this->directory = implode( '/', $ttt );
			
		}
		
		public function redirect( $path )
		{
			
			$location = "Location: $path";

			header( $location );
			
			exit;
			
		}
		
		public function getHeader( $extension )
		{
			
			// http://en.wikipedia.org/wiki/Internet_media_type
			
			$header = 'text/html; charset=UTF-8';
			
			switch ( $extension ) {
				
				case '-1':
					
					$header = 'HTTP/1.0 404 Not Found';
					
					break;
				
				case 'txt':
					
					$header = 'text/xml';
					
					break;
				
				case 'js':
					
					$header = 'application/x-javascript';
					
					break;
					
				case 'css':
				
					$header = 'text/css';
				
					break;
					
				case 'xml':

					$header = 'text/xml';

					break;
					
				case 'html':
				
					$header = 'text/html; charset=UTF-8';
				
					break;
					
				case 'php':

					$header = 'application/x-httpd-php';

					break;
					
				case 'phps':

					$header = 'application/x-httpd-php-source';

					break;
					
				case 'sit':

					$header = 'application/x-stuffit';

					break;
					
				case 'tar':

					$header = 'application/x-tar';

					break;
					
				case 'gif':

					$header = 'image/gif';

					break;
					
				case 'jpg':

					$header = 'image/jpeg';

					break;
					
				case 'png':

					$header = 'image/png';

					break;
					
				case 'tiff':

					$header = 'image/tiff';

					break;
				
			}
			
			return $header;
			
		}
		
		public function printObject( $obj )
		{
			
			echo '<textarea cols="100" rows="100">';
			
			$clone = clone $obj;
			
			if ( $obj === $this ) {
				
				$clone->preferences[ 'dbuser' ] = '****';

				$clone->preferences[ 'dbpassword' ] = '****';
				
			}
			
			print_r( $clone );

			echo '</textarea>';
			
		}
		
		public function parseContent()
		{
			
			$content = array();
			
			$ini = ( $this->pagetype === 'archive' ) ? "$this->space;$this->year.ini" : "$this->space;$this->page.ini";
			
			$file = $this->root . '/lib/ini/'. $ini;
			
			if ( is_file( $file ) ) {
				
				if ( $this->pagetype === 'page' ) {
					
					$page = $data = parse_ini_file( $file );
					
					$zones = 'heading,sidea,body,sideb,footer';

					$zones = explode( ',', $zones );

					foreach ( $zones as $zi => $zone ) {
						
						if ( isset( $page[ $zone ] ) ) {

							$content[ $zone ] = array();

							$expl = explode( ',', $page[ $zone ] );

							foreach ( $expl as $i => $item ) {

								$inc = $this->getInclude( $item, 'htm' );

								if ( is_file( $inc ) ) { 

									ob_start();

									include $inc;

									$content[ $zone ][ $i ][ 'index' ] = $i;

									$content[ $zone ][ $i ][ 'stub' ] = $item;

									$content[ $zone ][ $i ][ 'content' ] = ob_get_contents();

									ob_end_clean();

								}

							}

						}
						
					}
					
				} else { //parse content for archive
					
					$index = 0;
					
					$archive = $data = parse_ini_file( $file );
					
					if ( $archive[ 'monthindex' ] ) {
						
						if ( isset( $this->month ) ) {
							
							$m = array();
							
							array_push( $m, $this->month );
							
						} else {
							
							$m = explode( ',', $archive[ 'monthindex' ] );
							
						}
						
						foreach ( $m as $i => $item ) {

							if ( $archive[ 'month' . $item ] ) {
								
								if ( isset( $this->day ) ) {
									
									$d = array();
									
									array_push( $d, $this->day );
									
								} else {
									
									$d = explode( ',', $archive[ 'month' . $item ] );
									
								}
								
								foreach ( $d as $ii => $iitem ) {
									
									if ( $archive[ 'day' . $item . '_' . $iitem ] ) {
										
										$e = explode( ',', $archive[ 'day' . $item . '_' . $iitem ] );
										
										foreach ( $e as $iii => $iiitem ) {
											
											$inc = $this->getInclude( $iiitem, 'htm' );
											
											if ( is_file( $inc ) ) { 

												ob_start();

												include $inc;

												$content[ 'body' ][ $index ][ 'index' ] = $index;

												$content[ 'body' ][ $index ][ 'stub' ] = $iiitem;
												
												$content[ 'body' ][ $index ][ 'year' ] = $this->year;
												
												$content[ 'body' ][ $index ][ 'month' ] = $item;
												
												$content[ 'body' ][ $index ][ 'day' ] = $iitem;

												$content[ 'body' ][ $index ][ 'content' ] = ob_get_contents();
												
												$index++;

												ob_end_clean();

											}
											
										}
										
									}
									
								}

							}

						}
						
					}
					
					$zones = 'heading,sidea,sideb,footer';

					$zones = explode( ',', $zones );

					foreach ( $zones as $zi => $zone ) {
						
						if ( isset( $archive[ $zone ] ) ) {

							$content[ $zone ] = array();

							$expl = explode( ',', $archive[ $zone ] );

							foreach ( $expl as $i => $item ) {

								$inc = $this->getInclude( $item, 'htm' );

								if ( is_file( $inc ) ) { 

									ob_start();

									include $inc;

									$content[ $zone ][ $i ][ 'index' ] = $i;

									$content[ $zone ][ $i ][ 'stub' ] = $item;

									$content[ $zone ][ $i ][ 'content' ] = ob_get_contents();

									ob_end_clean();

								}

							}

						}
						
					}
					
				}
				
				$this->meta = $data;
				
				$itemtotal = intval( $data[ 'itemtotal' ] );
				
				$pagenum = intval( $this->page );
				
				if ( !$pagenum ) {
					
					if ( isset( $this->stub ) && intval( $this->stub ) ) {
						
						$pagenum = intval( $this->stub );
						
					} else {
						
						$pagenum = 1;
						
					}
					
				}
				
				$pagetotal = ceil( count( $content[ 'body' ] ) / $itemtotal );
				
				$max = $pagenum * $itemtotal;
				
				$first = $max - $itemtotal;
				
				$entries = array();
				
				for ( $i = $first; $i < $max; $i++ ) {
					
					if ( isset( $content[ 'body' ][ $i ] ) ) {
						
						array_push( $entries, $content[ 'body' ][ $i ] );
						
					}
					
				}
				
				$content[ 'body' ] = $entries;
				
				$content[ 'page' ] = array();
				
				for ( $i = 0; $i < $pagetotal; $i++ ) {
					
					$p = array();
					
					$p[ 'index' ] = $i + 1;
					
					$p[ 'total' ] = $pagetotal;
					
					array_push( $content[ 'page' ], $p );
					
				}
				
				$this->parseMenu( $content );
				
			}
			
			return $content;
			
		}
		
		public function parseMenu( &$content )
		{
			
			$dir = $this->root . '/lib/ini';
			
			$files = scandir( $dir );
			
			global $space;
			
			$space = $this->space;
			
			function match( $var ) { 
				
				return strstr( $var, $GLOBALS['space'] ) != false && !strstr( $var, 'domain;' );
				
			}
			
			$pages = array_filter( $files, "match" );
			
			if ( count( $pages ) > 0 ) {
				
				$content[ 'menu' ] = array();
				
			}
			
			foreach ( $pages as $i => $item ) {
				
				$file = $this->root . '/lib/ini/' . $item;
				
				$pageData = parse_ini_file( $file );
				
				if ( !( $pageData[ 'order' ] === '0' ) ) {
					
					$ary = array();
					
					$ary[ 'index' ] = $pageData[ 'order' ];
					
					$ary[ 'stub' ] = $pageData[ 'stub' ];
					
					$ary[ 'content' ] = $pageData[ 'label' ];
					
					array_push( $content[ 'menu' ], $ary );
					
				};
				
			}
			
		}
		
		public function parseAsset()
		{
			
			$content = '';
			
			$this->mediatype = $this->getMediaType();
			
			if ( $this->mediatype != 'text' ) {
				
				$this->directives = $this->libraries;
				
				unset( $this->libraries );
				
			}
			
			if ( isset( $this->libraries ) ) {
				
				foreach ( $this->libraries as $i => $item ) {
					
					$inc = $this->getInclude( $item, $this->extension );
					
					if ( is_file( $inc ) ) { 
				
						ob_start();
				
						include $inc;
						
						$content .= ob_get_contents();
				
						ob_end_clean();
				
					}
				
				}
				
			}
			
			return $content;
			
		}
		
		public function getInclude( $lib, $ext )
		{
			
			$i = $this->root . '/lib/' . $ext . '/' . $lib . '.' . $ext;
			
			$iv = $this->root . '/lib/' . $ext . '/' . $lib . '.' . $this->version . '.' . $ext;
			
			$inc = $i;
			
			if ( isset( $this->version ) && $this->version != 'default' && is_file( $iv ) ) {
				
				$inc = $iv;
				
			}
			
			$t = explode( ',', $this->acceptlanguage );
			
			if ( $t[ 0 ] != $this->preferences[ 'defaultlanguage' ] || !is_file( $inc ) ) {
				
				foreach ( $t as $i => $item ) {

					$i = null;

					$lang = array_shift( explode( ';', $item ) );

					$ivl = $this->root . '/lib/' . $ext . '/' . $lib . '.' . $this->version . ';' . $lang . '.' . $ext;

					$il = $this->root . '/lib/' . $ext . '/' . $lib . ';' . $lang . '.' . $ext;

					if ( is_file( $ivl ) ) {

						$i = $ivl;

					} else if ( is_file( $il ) ) {

						$i = $il;

					}

					if ( $i != null ) {

						$inc = $i;

						break;

					}

				}
				
			}
			
			return $inc;
			
		}
		
		public function cache( $output )
		{
			
			$selector = '/index.';
			
			if ( strstr( $this->urlrewrite, $selector ) ) {
				
				$t = explode( $selector, $this->urlrewrite );
				
				$dir = array_shift( $t );
				
			} else if ( $this->requesttype === 'Content' ) {
				
				$dir = $this->urlrewrite;
				
			}
			
			$ext = $this->extension;
			
			$public = $this->root . '/public/cache/';
			
			chdir( $public );
			
			if ( !is_dir( $dir ) ) { mkdir( $dir, 0755, true ); }
			
			if ( isset( $ext ) ) {
				
				$file = "$dir/index.$ext";
				
				$file_handle = fopen( $file, 'w' ) or die( "can't open file" );
				
				fwrite( $file_handle, $output );
				
				fclose( $file_handle );
				
			}
			
		}
		
	}

?>