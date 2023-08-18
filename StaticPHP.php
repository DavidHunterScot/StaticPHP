<?php

class StaticPHP
{
	private $source_dir_path;
	private $output_dir_path;
	private $items_to_ignore;
	private $friendly_urls;
	private $metaDataDelimiter;

	public function __construct( String $path_to_input_directory, String $path_to_output_directory, array $items_to_ignore = [], bool $friendly_urls = false, String $metaDataDelimiter = "---" )
	{
		$this->source_dir_path = $path_to_input_directory;
		$this->output_dir_path = $path_to_output_directory;
		$this->items_to_ignore = $items_to_ignore;
		$this->friendly_urls = $friendly_urls;
		$this->metaDataDelimiter = $metaDataDelimiter;

		$this->source_dir_path = str_replace( [ "\\", "/" ], DIRECTORY_SEPARATOR, $this->source_dir_path );
		$this->output_dir_path = str_replace( [ "\\", "/" ], DIRECTORY_SEPARATOR, $this->output_dir_path );

		$this->emptyDirectory( $this->output_dir_path );
		$this->processDirectory( $this->source_dir_path, $this->output_dir_path );
	}
	
	private function emptyDirectory( String $path_to_directory )
	{
		if( ! is_dir( $path_to_directory ) )
			return;
		
		echo "Emptying Directory: " . $path_to_directory . "\n";
		
		$directory_items = scandir( $path_to_directory );
		
		if( count( $directory_items ) == 2 )
			echo "Directory Already Empty.\n";
		
		foreach( $directory_items as $directory_item )
		{
			if( $directory_item == "." || $directory_item == ".." )
				continue;
			
			$path_to_directory_item = $path_to_directory . DIRECTORY_SEPARATOR . $directory_item;
			
			if( is_dir( $path_to_directory_item ) )
			{
				$this->emptyDirectory( $path_to_directory_item );
				echo "Removing Directory: " . $path_to_directory_item . "\n";
				rmdir( $path_to_directory_item );
				continue;
			}
			
			if( is_file( $path_to_directory_item ) )
			{
				echo "Deleting File: " . $path_to_directory_item . "\n";
				unlink( $path_to_directory_item );
				continue;
			}
		}
		
		if( count( $directory_items ) > 2 )
			echo "Done. \n";
	}
	
	private function processDirectory( String $path_to_input_directory, String $path_to_output_directory )
	{
		if( ! is_dir( $path_to_input_directory ) )
			die( "Directory does not exist: " . $path_to_input_directory );
        	
		echo "Processing Directory: " . $path_to_input_directory . "\n";
		
		$directory_items = scandir( $path_to_input_directory );
		
		if( ! is_dir( $path_to_output_directory ) && count( $directory_items ) > 2 )
		{
			echo "Created New Directory: " . $path_to_output_directory . "\n";
			mkdir( $path_to_output_directory );
		}
		
		if( count( $directory_items ) == 2 )
		{
			echo "Input directory is empty. Nothing to process.\n";
		}
		
		foreach( $directory_items as $directory_item )
		{
			if( $directory_item == "." || $directory_item == ".." )
				continue;
			
			$path_to_input_directory_item = $path_to_input_directory . DIRECTORY_SEPARATOR . $directory_item;
			$path_to_output_directory_item = $path_to_output_directory . DIRECTORY_SEPARATOR . $directory_item;
			
			if( is_array( $this->items_to_ignore ) )
			{
				foreach( $this->items_to_ignore as $item_to_ignore )
				{
					if( $item_to_ignore != "" && strpos( $directory_item, $item_to_ignore ) !== false )
					{
						echo "Ignoring Directory Item: " . $path_to_input_directory_item . "\n";
						continue( 2 );
					}
				}
			}
			
			if( is_dir( $path_to_input_directory_item ) )
			{
				$this->processDirectory( $path_to_input_directory_item, $path_to_output_directory_item );
			}
			
			if( is_file( $path_to_input_directory_item ) && substr( $directory_item, -4 ) == ".php" )
			{
				$path_to_output_directory_item = substr( $path_to_output_directory_item, 0, -4 ) . ".html";
				
				$this->processPHP( $path_to_input_directory_item, $path_to_output_directory_item );
				continue;
			}

			if( is_file( $path_to_input_directory_item ) && substr( $directory_item, -5 ) == ".html" )
			{
				$this->processHTML( $path_to_input_directory_item, $path_to_output_directory_item );
				continue;
			}
			
			if( is_file( $path_to_input_directory_item ) )
			{
				echo "Copying File: " . $path_to_input_directory_item . " to " . $path_to_output_directory_item . "\n";
				copy( $path_to_input_directory_item, $path_to_output_directory_item );
			}
		}
		
		if( count( $directory_items ) > 2 )
		{
			echo "Done.\n";
		}
	}
	
	private function processMetaData( String $delimiter, String $input_contents, array &$metadata, String &$output_contents )
	{
		if( ! isset( $metadata['staticphp_path'] ) )
			$metadata['staticphp_path'] = __DIR__;
		
        $input_lines = explode( "\n", $input_contents );
		
		if( count( $input_lines ) > 0 && trim( $input_lines[ 0 ] ) == $delimiter )
		{
			echo "Processing MetaData...\n\n";
			
			unset( $input_lines[ 0 ] );
			
			for( $line_number = 1; $line_number <= count( $input_lines ); $line_number++ )
			{
				$input_line = trim( $input_lines[ $line_number ] );
				
				unset( $input_lines[ $line_number ] );
				
				if( $input_line == $delimiter )
					break;
				
				if( ! strpos( $input_line, ":" ) )
					continue;
				
				$data = explode( ":", $input_line, 2 );
				
				$metadata_key = trim( $data[ 0 ] );
				$metadata_value = trim( $data[ 1 ] );
				
				echo "Setting MetaData Key: " . $metadata_key . "\n";
				echo "with matching value: " . $metadata_value . "\n\n";
				$metadata[ $metadata_key ] = $metadata_value;
			}
			
			$output_contents = join( "\r\n", $input_lines );
			
			echo "End of MetaData.\n\n";
		}
	}
	
	private function processMetaDataPlaceHolders( String $delimiter, String $input_contents, array $metadata, String &$output_contents, String $prefix = 'metadata' )
	{
		echo "Processing MetaData PlaceHolders...\n";
		$pattern = '/' . $delimiter . '\s*' . $prefix . '\.(\S+)\s*' . $delimiter . '/';
		
		$output_contents = preg_replace_callback
		(
			$pattern,
			function( $matches ) use ( $metadata )
			{
				$key = $matches[ 1 ];
				
				if( array_key_exists( $key, $metadata ) )
				{
					$value = $metadata[ $key ];
					echo "Replacing " . $key . " with " . $value . "\n";
					return $value;
				}
				else
				{
					return $matches[ 0 ];
				}
			},
			$input_contents
		);
		echo "Done.\n\n";
	}
	
	private function processLayoutMetaData( array &$metadata, string $metaDataDelimiter, string &$layout_contents )
	{
		// Check if a base layout path is defined in metadata.
		if( isset( $metadata['layout'] ) )
		{
			// Get full path to layout file assuming it is relative to StaticPHP.
			$layout_path = __DIR__ . DIRECTORY_SEPARATOR . $metadata['layout'];
			
			if( $layout_path && is_file( $layout_path ) )
			{
				echo "Processing layout file: " . $layout_path . "\n";
				
				// Get contents of base layout file.
				$layout_contents = file_get_contents( $layout_path );
				// Get layout metadata.
				$layout_metadata = array();
				$this->processMetaData( $metaDataDelimiter, $layout_contents, $layout_metadata, $layout_contents );
				// Update metadata with a merged version of the layout metadata and the current metadata, giving priority to current where conflicting keys exist.
				$metadata = array_merge( $layout_metadata, $metadata );
			}
		}
	}
	
	private function processContentPlaceHolder( array $metadata, string &$file_contents, string $layout_contents )
	{
		// Check for a content placeholder defined in metadata (usually layout metadata).
		if( isset( $metadata['content_placeholder'] ) && trim( $metadata['content_placeholder'] ) )
		{
			echo "Replacing content placeholder with page content...\n";
			// Update current page content with the layout content, replacing the placeholder with the content of current page.
			$file_contents = str_replace( trim( $metadata['content_placeholder'] ), $file_contents, $layout_contents );
		}
	}
	
	private function processTemporaryFile( string $path_to_file, string &$file_contents, array $metadata )
	{
		$temp_file_path = tempnam( dirname( $path_to_file ), "staticphp_" );
		echo "Creating temporary file (" . $temp_file_path . ")...\n";
		file_put_contents( $temp_file_path, $file_contents );
		
		echo "Including temporary file...\n";
        
		ob_start();
		
		include $temp_file_path;
		$file_contents = ob_get_contents();
		
		ob_end_clean();
		
		echo "Removing temporary file...\n";
		unlink( $temp_file_path );
	}
	
	private function processOutputPath( string &$path_to_output_file, array $metadata, bool $friendly_urls, string $custom_output_path = null )
	{
		// Check if output file is index.html and skip further processing.
		if( basename( $path_to_output_file ) == "index.html" )
			return;
		
		// Is a custom output path defined?
		if( isset( $metadata['custom_output_path'] ) || $custom_output_path )
		{
			if( isset( $metadata['custom_output_path'] ) )
			{
				$path_to_output_file = $metadata['custom_output_path'];
				return;
			}
			
			$path_to_output_file = $custom_output_path;
			return;
		}

		// No custom output path defined, check for friendly URLs in metadata and give it priority.
		if( isset( $metadata['friendly_urls'] ) )
		{
			if( $metadata['friendly_urls'] == "true" )
				$friendly_urls = true;
			if( $metadata['friendly_urls'] == "false" )
				$friendly_urls = false;
		}
		
		// Check if friendly URLs are enabled.
		if( $friendly_urls )
		{
			// Check if a directory matching the output filename minus the extension exists.
			if( ! is_dir( substr( $path_to_output_file, 0, -5 ) ) )
			{
				// Create a directory matching the output filename minus the extension.
				mkdir( substr( $path_to_output_file, 0, -5 ) );
			}
			
			// Set path to output file to that of a directory with the same name minus extension with an index.html file inside it.
			$path_to_output_file = substr( $path_to_output_file, 0, -5 ) . DIRECTORY_SEPARATOR . "index.html";
		}
	}
	
	private function outputFile( string $path_to_file, string $file_contents )
	{
		echo "Outputting File: "  . $path_to_file . "\n";
		
		@chmod( $path_to_file, 0755 );
		$open_file_for_writing = fopen( $path_to_file, "w" );
		fputs( $open_file_for_writing, $file_contents, strlen( $file_contents ) );
		fclose( $open_file_for_writing );
	}
	
	private function processPHP( $path_to_input_file, $path_to_output_file )
	{
		if( ! isset( $staticphp_path ) )
			$staticphp_path = __DIR__;

		if( ! is_file( $path_to_input_file ) )
			return;
		
		echo "Processing PHP File: " . $path_to_input_file . "\n";
		
		ob_start();
		
		include $path_to_input_file;
		$input_file_contents = ob_get_contents();
		
		ob_end_clean();
		
		$metadata = array();
		
		$this->processMetaData( $this->metaDataDelimiter, $input_file_contents, $metadata, $input_file_contents );
		
		$layout_contents = "";
		$this->processLayoutMetaData( $metadata, $this->metaDataDelimiter, $layout_contents );

		if( isset( $metadata['layout'] ) && $metadata['layout'] && substr( $metadata['layout'], -4 ) == ".php" )
			$this->processTemporaryFile( $metadata['layout'], $layout_contents, $metadata );
		
		$this->processContentPlaceHolder( $metadata, $input_file_contents, $layout_contents );
		
		$this->processMetaDataPlaceHolders( $this->metaDataDelimiter, $input_file_contents, $metadata, $input_file_contents );

		$input_file_contents = $this->processFunctionalBlocks( $input_file_contents );

		if( ! isset( $friendly_urls ) )
			$friendly_urls = $this->friendly_urls;
		
		if( isset( $custom_output_path ) )
			$this->processOutputPath( $path_to_output_file, $metadata, $friendly_urls, $custom_output_path );
		else
			$this->processOutputPath( $path_to_output_file, $metadata, $friendly_urls );
		
		$this->outputFile( $path_to_output_file, $input_file_contents );
	}

	private function processHTML( $path_to_input_file, $path_to_output_file )
	{
		if( ! is_file( $path_to_input_file ) )
			return;

		echo "Processing HTML File: " . $path_to_input_file . "\n";

		$input_file_contents = file_get_contents( $path_to_input_file );

		$metadata = array();

		$this->processMetaData( $this->metaDataDelimiter, $input_file_contents, $metadata, $input_file_contents );

		$layout_contents = "";
		$this->processLayoutMetaData( $metadata, $this->metaDataDelimiter, $layout_contents );

		if( isset( $metadata['layout'] ) && $metadata['layout'] && substr( $metadata['layout'], -4 ) == ".php" )
			$this->processTemporaryFile( $metadata['layout'], $layout_contents, $metadata );

		$this->processContentPlaceHolder( $metadata, $input_file_contents, $layout_contents );

		$this->processMetaDataPlaceHolders( $this->metaDataDelimiter, $input_file_contents, $metadata, $input_file_contents );

		$input_file_contents = $this->processFunctionalBlocks( $input_file_contents );

		if( ! isset( $friendly_urls ) )
			$friendly_urls = $this->friendly_urls;

		if( isset( $custom_output_path ) )
			$this->processOutputPath( $path_to_output_file, $metadata, $friendly_urls, $custom_output_path );
		else
			$this->processOutputPath( $path_to_output_file, $metadata, $friendly_urls );
		
		$this->outputFile( $path_to_output_file, $input_file_contents );
	}

	private function processFunctionalBlocks( String $content )
	{
		echo "Processing Functional Blocks...\n";

		$delimiter = $this->metaDataDelimiter;

		$pattern = '/' . preg_quote( $delimiter ) . ' (\w+)\(([^)]*)\) ' . preg_quote( $delimiter ) . '\s*(.*?)\s*' . preg_quote( $delimiter ) . ' end\1 ' . preg_quote( $delimiter ) . '/s';

		$output = preg_replace_callback(
			$pattern, function( $matches ) use ( $delimiter )
			{
				$funcName = $matches[ 1 ];
				$paramStr = $matches[ 2 ];
				$blockContent = $matches[ 3 ];

				switch( $funcName )
				{
					case 'loop':
						$blockOutput = $this->processLoopFunctionalBlock( $this->parseFunctionalBlockParameters( $paramStr ), $blockContent );

						if( $blockOutput !== null && $blockOutput !== "" )
						{
							return $blockOutput; // Replaced Content
						}

						break;
				}

				return $matches[ 0 ]; // Original Content
			},
			$content
		);

		echo "...Functional Blocks Processed.\n";

		return $output;
	}

	private function processLoopFunctionalBlock( array $params, String $loopContent )
	{
		if( ! isset( $params[ 'dir' ] ) || ! is_dir( $params[ 'dir' ] ) )
		{
			return null;
		}

		echo "Processing Loop Functional Block...\n";

		$dir = __DIR__ . DIRECTORY_SEPARATOR . $params[ 'dir' ];

		$dir = str_replace( [ "\\", "/" ], DIRECTORY_SEPARATOR, $dir );

		$output = array();

		$output = $this->processLoopDir( $dir, $params, $loopContent, $output );

		if( isset( $params[ 'json' ] ) )
		{
			$jsonFilePath = str_replace( [ "\\", "/" ], DIRECTORY_SEPARATOR, $params[ 'json' ] );
			
			$jsonFilePathParts = explode( DIRECTORY_SEPARATOR, $jsonFilePath );
			
			$currentJsonFilePath = "";
			
			for( $cjfp = 0; $cjfp < count( $jsonFilePathParts ) -1; $cjfp++ )
			{
				$currentJsonFilePath .= $jsonFilePathParts[ $cjfp ] . DIRECTORY_SEPARATOR;
				
				if( ! is_dir( $currentJsonFilePath ) && $cjfp != count( $jsonFilePathParts ) -1 )
				{
					mkdir( $currentJsonFilePath );
				}
			}
			
			echo "Outputting JSON File: " . $jsonFilePath . "\n";
			file_put_contents( $jsonFilePath, json_encode( $output ) );
			echo "JSON File Complete.\n";
		}

		$output_str = "";

		foreach( $output as $output_item )
		{
			$output_str .= $output_item['outputContent'];
		}

		echo "...Loop Functional Block Processed.\n";

		return $output_str;
	}

	private function processLoopDir( String $dirPath, array $params, String $loopContent, array $output = array() )
	{
		if( ! is_dir( $dirPath ) )
			return;

		echo "Processing Loop Directory: " . $dirPath . "\n";

		$dirContents = scandir( $dirPath );

		if( isset( $params['sort'] ) && $params['sort'] == "ascending" )
		{
			$dirContents = sort( $dirContents );
		}
		elseif( isset( $params['sort'] ) && $params['sort'] == "descending" )
		{
			$dirContents = rsort( $dirContents );
		}

		foreach( $dirContents as $dirItem )
		{
			if( $dirItem === '.' || $dirItem === '..' )
			{
				continue;
			}

			$dirItemPath = $dirPath . DIRECTORY_SEPARATOR . $dirItem;

			if( is_dir( $dirItemPath ) )
			{
				$output = $this->processLoopDir( $dirItemPath, $params, $loopContent, $output );
				continue;
			}

			if( ! is_file( $dirItemPath ) )
			{
				continue;
			}

			if( substr( $dirItem, -4 ) !== ".php" && substr( $dirItem, -5 ) !== ".html" )
			{
				continue;
			}

			if( is_array( $this->items_to_ignore ) )
			{
				foreach( $this->items_to_ignore as $item_to_ignore )
				{
					if( $item_to_ignore != "" && strpos( $dirItemPath, $item_to_ignore ) !== false )
					{
						echo "Loop Ignoring Item: " . $dirItemPath . "\n";
						continue( 2 );
					}
				}
			}

			
			$fileContents = file_get_contents( $dirItemPath );
			
			$thisLoopContent = $loopContent;
			
			$metadata = array();
			
			$this->processMetaData( $this->metaDataDelimiter, $fileContents, $metadata, $fileContents );

			unset( $metadata['staticphp_path'] );
			
			$fileOutputPath = str_replace( [ $this->source_dir_path, ".php" ], [ $this->output_dir_path, ".html" ], $dirItemPath );

			$fileURI = $fileOutputPath;
			
			$this->processOutputPath( $fileURI, $metadata );

			$fileURI = str_replace( $this->output_dir_path, "", $fileURI );

			$fileURI = str_replace( [ "\\", "index.html" ], [ "/", "" ], $fileURI );
			
			$metadata[ 'uri' ] = $fileURI;
			
			$this->processMetaDataPlaceHolders( $this->metaDataDelimiter, $loopContent, $metadata, $thisLoopContent, 'loop' );
			
			if( isset( $params[ 'content_placeholder' ] ) && $params[ 'content_placeholder' ] )
			{
				$thisLoopContent = str_replace( $params[ 'content_placeholder' ], $fileContents, $thisLoopContent );
			}

			$toOutput['metadata'] = $metadata;
			$toOutput['fileContents'] = $fileContents;
			$toOutput['outputContent'] = $thisLoopContent;

			$output[] = $toOutput;

			echo "...Loop Directory Processed.\n";
		}

		return $output;
	}

	private function parseFunctionalBlockParameters( String $paramStr )
	{
		$params = [];
		$paramPairs = explode( ',', $paramStr );
		
		foreach( $paramPairs as $pair )
		{
			list( $key, $value ) = explode( '=', $pair, 2 );
			$params[ trim( $key ) ] = trim( trim( $value ), "'\"" );
		}
		
		return $params;
	}
}

if( isset( $argv[ 0 ] ) && basename( $argv[ 0 ] ) == basename( __FILE__ ) )
{
	$path_to_input_directory = "." . DIRECTORY_SEPARATOR . "input";
	$path_to_output_directory = "." . DIRECTORY_SEPARATOR . "output";
	$items_to_ignore = [];
	$friendly_urls = false;
	$metaDataDelimiter = "---";
	
	unset( $argv[ 0 ] );
	$argv = array_values( $argv );
	$argc--;
	
	if( $argc >= 0 )
	{
		if( isset( $argv[ 0 ] ) )
			$path_to_input_directory = $argv[ 0 ];
		if( isset( $argv[ 1 ] ) )
			$path_to_output_directory = $argv[ 1 ];
		if( isset( $argv[ 2 ] ) && strlen( $argv[ 2 ] ) > 0 )
			$items_to_ignore = [ $argv[ 2 ] ];
		if( isset( $argv[ 3 ] ) )
			$friendly_urls = $argv[ 3 ] == "true" ? true : false;
		if( isset( $argv[ 4 ] ) )
			$metaDataDelimiter = $argv[ 4 ];
	}
	
	new StaticPHP( $path_to_input_directory, $path_to_output_directory, $items_to_ignore, $friendly_urls, $metaDataDelimiter );
}

