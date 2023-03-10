<?php

class StaticPHP
{
    public function __construct( String $path_to_input_directory, String $path_to_output_directory, array $items_to_ignore = [], bool $friendly_urls = false )
    {
        $this->emptyDirectory( $path_to_output_directory );
        $this->processDirectory( $path_to_input_directory, $path_to_output_directory, $items_to_ignore, $friendly_urls );
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

    private function processDirectory( String $path_to_input_directory, String $path_to_output_directory, array $items_to_ignore, bool $friendly_urls = false )
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

            if( is_string( $items_to_ignore ) )
            {
                $items_to_ignore = explode( ";", $items_to_ignore );
            }

            if( is_array( $items_to_ignore ) )
            {
                foreach( $items_to_ignore as $item_to_ignore )
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
                $this->processDirectory( $path_to_input_directory_item, $path_to_output_directory_item, $items_to_ignore, $friendly_urls );
            }
            
            if( is_file( $path_to_input_directory_item ) && substr( $directory_item, -4 ) == ".php" )
            {
                $path_to_output_directory_item = substr( $path_to_output_directory_item, 0, -4 ) . ".html";

                $this->processPHP( $path_to_input_directory_item, $path_to_output_directory_item, $friendly_urls );
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

    private function processPHP( $path_to_input_file, $path_to_output_file, bool $friendly_urls )
    {
        if( ! is_file( $path_to_input_file ) )
            return;
        
        echo "Processing PHP File: " . $path_to_input_file . "\n";

        ob_start();
    
        include( $path_to_input_file );
        
        $input_file_contents = ob_get_contents();
        ob_end_clean();

        if( isset( $custom_output_path ) )
        {
            $path_to_output_file = $custom_output_path;
        }
        else if( $friendly_urls && substr( $path_to_output_file, strrpos( $path_to_output_file, DIRECTORY_SEPARATOR ) ) != DIRECTORY_SEPARATOR . "index.html" )
        {
            if( ! is_dir( substr( $path_to_output_file, 0, -5 ) ) )
            {
                mkdir( substr( $path_to_output_file, 0, -5 ) );
            }
            
            $path_to_output_file = substr( $path_to_output_file, 0, -5 ) . DIRECTORY_SEPARATOR . "index.html";
        }
        
        echo "Outputting HTML File: "  . $path_to_output_file . "\n";

        @chmod( $path_to_output_file, 0755 );
        $open_output_file_for_writing = fopen( $path_to_output_file, "w" );
        fputs( $open_output_file_for_writing, $input_file_contents, strlen( $input_file_contents ) );
        fclose( $open_output_file_for_writing );
    }
}

if( isset( $argv[ 0 ] ) && basename( $argv[ 0 ] ) == basename( __FILE__ ) )
{
    $path_to_input_directory = "." . DIRECTORY_SEPARATOR . "input";
    $path_to_output_directory = "." . DIRECTORY_SEPARATOR . "output";
    $items_to_ignore = [];
	$friendly_urls = false;

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
    }

    new StaticPHP( $path_to_input_directory, $path_to_output_directory, $items_to_ignore, $friendly_urls );
}
