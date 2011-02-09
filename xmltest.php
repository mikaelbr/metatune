<?php
require_once("lib/config.php");
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
            if (DEBUG) {
                $start = microtime();
            }

            $spotify = MetaTune::getInstance();

            //Test Single track lookup
            $trackSingle = $spotify->lookupTrack("spotify:track:3BbfQLpcj0BfjM5rq8Ioj9");
            $trackXML = $spotify->generateXML($trackSingle);
            // This should be the same as $trackSingle
            $trackImport = $spotify->parseXMLTracks($trackXML);
            var_dump($trackSingle->equals($trackImport)); // Test confirms: bool(true)

            echo "<br />";
            //Test Single artist lookup
            $artistSingle = $spotify->lookupArtist("spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l");
            $artistXML = $spotify->generateXML($artistSingle);
            // This should be the same as $artistSingle
            $artistImport = $spotify->parseXMLArtist($artistXML);
            var_dump($artistSingle->equals($artistImport)); // Test confirms: bool(true)


            echo "<br />";
            //Test Single album lookup
            $albumSingle = $spotify->lookupAlbum("spotify:album:1kjefoUShy8bZcwBEHtMWp");
            $albumXML = $spotify->generateXML($albumSingle);
            // This should be the same as $albumSingle
            $albumImport = $spotify->parseXMLAlbum($albumXML);
            var_dump($albumSingle->equals($albumImport)); // Test confirms: bool(true)

            if (DEBUG) {
                $end = microtime();
                echo "<pre>Debug time: " . ($end - $start) . "</pre>";
            }

            // ***Test for array of tracks***
            $trackList = $spotify->searchTrack("Superfamily");
            $tracksXML = $spotify->generateXML($trackList);
            
            // This should now be the same as $trackList
            $tracksImport = $spotify->parseXMLTracks($tracksXML);
            // Demo print to check correct content
            echo "<pre>" . print_r($tracksImport, 1) . "</pre>";

            // ***Test for array of artists***
            $artistList = $spotify->searchArtist("Of");
            $artistsXML = $spotify->generateXML($artistList);
            // This should now be the same as $artistsList
            $artistsImport = $spotify->parseXMLArtist($artistsXML);
            // Demo print to check correct content
            echo "<pre>" . print_r($artistsImport, 1) . "</pre>";

            // *** Test for array of albums ***
            $albumList = $spotify->searchAlbum("The");
            $albumsXML = $spotify->generateXML($albumList);
            // This should now be the same as $albumList
            $albumsImport = $spotify->parseXMLAlbum($albumsXML);
            // Demo print to check correct content
            echo "<pre>" . print_r($albumsImport, 1) . "</pre>";

            ?>
        </div>
    </body>
</html>