<?php

class StaticPHP
{
    public function __construct( String $path_to_input_directory, String $path_to_output_directory )
    {
        $this->emptyDirectory( $path_to_output_directory );
        $this->processDirectory( $path_to_input_directory, $path_to_output_directory );
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

    private function processPHP( $path_to_input_file, $path_to_output_file )
    {
        if( ! is_file( $path_to_input_file ) )
            return;
        
        echo "Processing PHP File: " . $path_to_input_file . "\n";

        ob_start();
    
        include( $path_to_input_file );
        
        $input_file_contents = ob_get_contents();
        ob_end_clean();
        
        echo "Outputting HTML File: "  . $path_to_output_file . "\n";

        @chmod( $path_to_output_file, 0755 );
        $open_output_file_for_writing = fopen( $path_to_output_file, "w" );
        fputs( $open_output_file_for_writing, $input_file_contents, strlen( $input_file_contents ) );
        fclose( $open_output_file_for_writing );
    }
}

$path_to_input_directory = "." . DIRECTORY_SEPARATOR . "input";
$path_to_output_directory = "." . DIRECTORY_SEPARATOR . "output";

if( $argc > 0 && $argv[ 0 ] == basename( __FILE__ ) )
{
    unset( $argv[ 0 ] );
    $argv = array_values( $argv );
    $argc--;
}

if( $argc >= 0 )
{
    if( isset( $argv[ 0 ] ) )
        $path_to_input_directory = $argv[ 0 ];
    if( isset( $argv[ 1 ] ) )
        $path_to_output_directory = $argv[ 1 ];
}

new StaticPHP( $path_to_input_directory, $path_to_output_directory );
