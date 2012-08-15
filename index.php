<?php
// This is just a demo. Code is way to ugly for use. 
require_once("lib/config.php");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="no-NB">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="keywords" content="Spotify Metadata API, Mikael Brevik, PHP Lib, Spotify" />
        <meta name="description" content="MetaTune examples" />
        <title>MetaTune - Demo</title>
        <link type="text/css" rel="stylesheet" href="./demo.css" />
    </head>
    <body>
        <div id="wrapper">
            <form action="" method="post">
                <fieldset>
                    <legend>Advanced search:</legend>
                    <label for="track">Track
                        <input type="text" id="track" name="track" value="<?php echo isset($_POST['track']) ? htmlspecialchars(stripslashes($_POST['track'])) : ''; ?>" />
                    </label>
                    <label for="artist">Artist
                        <input type="text" id="artist" name="artist" value="<?php echo isset($_POST['artist']) ? htmlspecialchars(stripslashes($_POST['artist'])) : ''; ?>" />
                    </label>
                    <label for="album">Album
                        <input type="text" id="artist" name="album" value="<?php echo isset($_POST['album']) ? htmlspecialchars(stripslashes($_POST['album'])) : ''; ?>" />
                    </label>
                    <hr />
                    <p><input type="submit" name="submit" value="Søk" /><input type="hidden" name="checkTime" value="<?php echo time(); ?>" /></p>
                </fieldset>
            </form>


            <?php



           if (DEBUG) {
                $start = microtime();
            }

            function printResult($response) {
                if (count($response) < 1) {
                    echo "No results";
                    die();
                }
                if (isset($response['errorid'])) {
                    echo "<pre>Error: " . $response['errorid'] . "\nMsg: " . $response['errormsg'] . "</pre>";
                    die();
                }
                $out = "";
                $out .= "<p>Found " . count($response) . " items.</p>\n";
                $out .= "<ul>\n";
                foreach ($response as $content) {
                    $out .= "\t<li><a href=\"" . $content->getURL() . "\">" . $content . "</a></li>\n";
                }
                $out .= "</ul>\n";
                return $out;
            }

            function songResult($response) {

                if (count($response) < 1) {
                    echo "No results";
                    die();
                }
                if (isset($response['errorid'])) {
                    echo "<pre>Error: " . $response['errorid'] . "\nMsg: " . $response['errormsg'] . "</pre>";
                    die();
                }
                $out = "";
                $out .= "<p>Found " . count($response) . " items.</p>\n";
                $out .= "<ul>\n";
                foreach ($response as $content) {
                    $out .= "\t<li>[<a href=\"details.php?id=" . $content->getURI() . "\">details</a>] :: <a href=\"" . $content->getURL() . "\">" . $content . "</a></li>\n";
                }
                $out .= "</ul>\n";
                return $out;
            }

            if (isset($_POST['checkTime']) && $_POST['checkTime'] > time() - 60 * 5) {

                // Initiate the MetaTune object.
                $spotiy = MetaTune\MetaTune::getInstance();
                $out = '<div class="masterResult">';                
                if (!empty($_POST['artist'])) {
                    // Get a list of artists
                    $response = $spotiy->searchArtist($_POST['artist']);
                    $out .= "<div class=\"resultBox\"><h2>Artists</h2>";
                    $out .= printResult($response);
                    $out .= "</div>";
                }

                if (!empty($_POST['album'])) {
                    // Get a list of albums from search
                    $response = $spotiy->searchAlbum($_POST['album']);
                    $out .= "<div class=\"resultBox\"><h2>Albums</h2>";
                    $out .= printResult($response);
                    $out .= "</div>";
                }
                if (!empty($_POST['track'])) {
                    // Search and get a list of tracks/song. 
                    $response = $spotiy->searchTrack($_POST['track']);
                    
                    $out .= "\t<div class=\"last\">\n\t<h2>Tracks</h2>\n";
                    $out .= songResult($response);
                    $out .= "\t</div>\n";
                }
                $out .= "</div>\n";
                echo $out;
            }

            if (DEBUG) {
                $end = microtime();
                echo "<pre>Debug time: " . ($end - $start) . "</pre>";
            }
            ?>
        </div>
    </body>
</html>