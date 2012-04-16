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
            try
            {
                echo $spotify->getPlayButtonFromPlaylistURI("spotify:user:erebore:playlist:788MOXyTfcUb1tdw4oC7KJ");
            }
            catch (MetaTuneException $ex)
            {
                die("<pre>Error\n" . $ex . "</pre>");
            }

    ?>
</body>
</html>