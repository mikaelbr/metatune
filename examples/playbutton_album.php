<?php
require_once("../lib/config.php");
?><!doctype html>
<html>
<head>
    <title>Play Button: Album</title>
</head>
<body>
    <?php
        // Get the metatune instance. 
        $spotify = MetaTune::getInstance();
        $spotify->playButtonHeight = 330; // Set height to show list

        try
        {
            $album = $spotify->lookupAlbum("spotify:album:6MBuQugGuX7VMBX0uiBnAQ", true);
            echo $spotify->getPlayButtonFromAlbum($album);
        }
        catch (MetaTuneException $ex)
        {
            die("<pre>Error\n" . $ex . "</pre>");
        }

    ?>
</body>
</html>