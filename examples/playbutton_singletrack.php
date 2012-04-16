<?php
require_once("../lib/config.php");
?><!doctype html>
<html>
<head>
    <title>Play Button: Auto Generated</title>
</head>
<body>
    <?php
            // Get the metatune instance. 
            $spotify = MetaTune::getInstance();
            try
            {
                $track = $spotify->lookup("spotify:track:4kO7mrAPfqIrsKwUOK5BFx");
                echo $spotify->getPlayButtonFromTrack($track);
            }
            catch (MetaTuneException $ex)
            {
                die("<pre>Error\n" . $ex . "</pre>");
            }

    ?>
</body>
</html>