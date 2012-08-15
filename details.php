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

            $spotify = MetaTune\MetaTune::getInstance();
            try {
                $content = $spotify->lookup($_GET['id']);
            } catch (MetaTuneException $ex) {
                die("<pre>Error\n" . $ex . "</pre>");
            }
            $artist = $content->getArtist();
            ?>
                        <ul>
                            <li><strong>Track:</strong> <a href="<?php echo $content->getURL() ?>"><?php echo $content->getTitle(); ?></a></li>
                            <li><strong>Artist:</strong> <a href="<?php echo (is_array($artist)) ? $artist[0]->getURL() : $artist->getURL(); ?>"><?php echo $content->getArtistAsString() ?></a></li>
                            <li><strong>Album:</strong> <a href="<?php echo $content->getAlbum()->getURL() ?>"><?php echo $content->getAlbum(); ?></a></li>
                            <li><strong>Duration:</strong> <?php echo $content->getLengthInMinutesAsString(); ?></li>
                            <li><strong>Popularity:</strong> <?php echo $content->getPopularityAsPercent(); ?>%</li>
                            <li><strong>Track Spotify URI:</strong> <?php echo $content->getURI(); ?></li>
                            <li><strong>Artist Spotify URI:</strong> <?php echo (is_array($artist)) ? $artist[0]->getURI() : $content->getArtist()->getURI(); ?></li>
                            <li><strong>Album Spotify URI:</strong> <?php echo $content->getAlbum()->getURI(); ?></li>
                        </ul>
            <hr />
            <?php
            try {
                $album = $spotify->lookup($content->getAlbum()->getURI());
                echo "<h3>Details, Album:</h3><pre>" . print_r($album, 1) . "</pre>";

                if (is_array($artist)) {
                    $artist = $spotify->lookup($artist[0]->getURI());
                    echo "<h3>Details, artist:</h3><pre>" . print_r($artist, 1) . "</pre>";
                } else {
                    $artist = $spotify->lookup($content->getArtist()->getURI());
                    echo "<h3>Details, artist:</h3><pre>" . print_r($artist, 1) . "</pre>";
                }
            } catch (MetaTuneException $ex) {
                die("<pre>Error \n" . $ex->getCode() . "\n" . $ex->getMessage() . "</pre>");
            }


            if (DEBUG) {
                $end = microtime();
                echo "<pre>Debug time: " . ($end - $start) . "</pre>";
            }
            ?>
        </div>
    </body>
</html>