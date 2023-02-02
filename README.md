# Welcome to StaticPHP!

A simple Static Site Generator (SSG) written in PHP.

It converts PHP files into HTML files containing the processed output.

## What is a Static Site?

A static site is one where the content does not change unless the owner of that website changes it themselves. It also does not require any server-side processing, making it extremely fast and secure. If you build your static site files using StaticPHP on your own computer before deployment, then you don't even need PHP installed on the server, meaning one less thing to maintain, and more time to develop your site.

## Why use StaticPHP?

StaticPHP gives you the benefits of using PHP to code your site, with the speed and security you expect from a static site.

The traditional PHP web page is built on request, as usually there is some kind of processing required to generate the correct content. A static site does not require any processing per request, because the content only changes when the site owner deploys changes. StaticPHP lets you eliminate PHP usage per request because the site is built ahead of time, and therefore all the server needs to do is send the content as a response to the request.

## Getting Started

It is super easy to get started with StaticPHP for your next website project.

Before you use StaticPHP, make sure you create your source directory (e.g. `src`) and put your website files in there, including your PHP files.

### Use the StaticPHP Launcher (Recommended)

The recommended and easiest way to use StaticPHP is with the Launcher.

The launcher will ensure you are always running the latest StaticPHP version upon each execution, and allow you to save your build configuration for easy execution in future.

1. Download the latest StaticPHP Launcher file from [GitHub](https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP-Launcher.php).
   - https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP-Launcher.php.

2. Modify the configurable options in that file to suit your project.
   - `$path_to_source_files` = Set this to the path where you have put your source files. A good name for the source directory is `src` but this is up to you.
   - `$path_to_public_files` = Set this to the path where you wish your generated output files. A good name for the public files is `public` but this is up to you.
   - `$paths_to_ignore` = Modify this array to include elements that will form parts of paths you wish to ignore.
   - `$friendly_urls` = Setting friendly URLs to false will turn PHP files into their respective HTML files. Setting friendly URLs to true will turn PHP files into their respective directories with an index.html file inside.

3. Execute the StaticPHP Launcher with the following command: `php StaticPHP-Launcher.php`

4. Check the output log for errors and fix them. Remember to re-execute StaticPHP Launcher after each modification of the source files or the output files may not reflect the changes.

### Use the Command Line

1. Download the latest StaticPHP file from [GitHub](https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP.php).
   - https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP.php

2. Execute StaticPHP with the following command `php StaticPHP.php PATH_TO_SOURCE_FILES PATH_TO_PUBLIC_FILES PATH_TO_IGNORE FRIENDLY_URLS_TRUE_OR_FALSE`
   - The above command explained:
     - First part is you wanting to run PHP.
     - The second part is the file you wish PHP to execute.
     - The rest are parameters/arguments with information for StaticPHP. All should be self explanatory, but note the following.
       - `PATH_TO_IGNORE` is treated as a string, and therefore can only contain one element.
       - `FRIENDLY_URLS_TRUE_OR_FALSE` is treated as a boolean, so only true or false is acceptable, anything else will be treated as false.

3. Check the output log for errors and fix them. Remember to re-execute StaticPHP after each modification of the source files or the output files may not reflect the changes.

### Use your own Custom PHP Script

1. Download the latest StaticPHP file from [GitHub](https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP.php).
   - https://raw.githubusercontent.com/DavidHunterScot/StaticPHP/master/StaticPHP.php

2. In your custom PHP file, include the `StaticPHP.php` file and create a new instance with the build configuration as parameters/arguments.
   ```php
    include __DIR__ . DIRECTORY_SEPARATOR . 'StaticPHP.php';

    $path_to_source_files = __DIR__ . DIRECTORY_SEPARATOR . 'SOURCE-FILES';
    $path_to_public_files = __DIR__ . DIRECTORY_SEPARATOR . 'PUBLIC-FILES';
    $paths_to_ignore = array( "IGNORE-FILES" );
    $friendly_urls = true;
    
    new StaticPHP( $path_to_source_files, $path_to_public_files, $paths_to_ignore, $friendly_urls );
   ```

3. Execute your custom script with the following command `php CUSTOM-PHP-SCRIPT.php` (where CUSTOM-PHP-SCRIPT is the actual filename of your custom script).

4. Check the output log for errors and fix them. Remember to re-execute your custom script after each modification of the source files or the output files may not reflect the changes.

## Deployment

Your static website has been built. What now?

1. Visit your new static site locally.
   - Change directory to the one containing your public output files (e.g. `cd public`)
   - Run PHP's built-in web server with `php -S localhost:80`. (80 represents the port the web server will run on. Change it accordingly if needed.)
   - Open your internet browsing software (e.g. Firefox, Chrome, Edge, etc) and go to http://localhost. (You will need to append `:PORT` if you chose a port other than 80 where PORT is the actual port number.)
   - Verify the site is as intended, and make any other changes you need. Remember to rebuild using one of the above methods after each change you make, or the static site may not reflect those changes.

2. Your new static site is now ready for deployment.
   - Upload the site to the server via SFTP or some other method and place it at the path where your site will be served from.
   - Optionally, if you wish to use Git to track the changes to your site, it is recommended to just commit the source files and the launcher, as the generated output files can be regenerated based on the source files.
     - Use .gitignore file to ignore the directory where your public output files are located. (If you named your directory `public` then add a line with just that on it and git will ignore that entire directory in future)
     - Make sure when you `git add` that you exclude the public output directory on your first commit, as it may not be ignored until the .gitignore file has been commited to the respository.

Congratulations! Your new site has been made and is now live. Go to your website by entering your domain or hostname into your internet browser and enjoy your new site.
