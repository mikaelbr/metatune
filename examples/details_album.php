<?php
require_once("../lib/config.php");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="no-NB">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="keywords" content="Spotify Metadata API, Mikael Brevik, PHP Lib, Spotify" />
        <meta name="description" content="MetaTune examples" />
        <title>MetaTune - Demo Details</title>
        <link type="text/css" rel="stylesheet" href="./demo.css" />
    </head>
    <body>
        <div id="wrapper">
            <?php
            // Get the metatune instance. 
            $spotify = MetaTune::getInstance();
            try
            {
                // Lookup spesific album. In this case "Hurry Up, We're Dreaming by M83"
                // First argument is the spotify ID, **second argument defines if we want to show details or not**.
                // So if the second argument equals true, all tracks will be included.  
                $album = $spotify->lookupAlbum("spotify:album:6MBuQugGuX7VMBX0uiBnAQ", true);
                
                // Album will now contain a Album object, *with* all tracks. 

                // Print basic details about album. $album->getArtist() will contain the artist object. 
                $out = '<h1>Album: «' . $album->getName() . '» <small>(by ' . $album->getArtist()->getName() . ')</small></h1>' . "\n";
                $out .= '<h2>Tracks:</h2>' . "\n" . '<ul>' . "\n";

                // $album->getTracks() return an array of all track objects. 
                foreach ($album->getTracks() as $track) {
                    $out .= "\t" . '<li>' . $track->getTitle() . '</li>' . "\n";
                }

                $out .= "</ul>";

                echo $out;
            }
            catch (MetaTuneException $ex)
            {
                die("<pre>Error\n" . $ex . "</pre>");
            }
            ?>
        </div>
    </body>
</html>