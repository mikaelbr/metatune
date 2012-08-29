<?php
namespace MetaTune;

/**
 * MetaTune - The ultimate PHP Wrapper to the Spotify Metadata API
 *
 * Copyright (C) 2010  Mikael Brevik
 *
 * <pre>
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see {@link http://www.gnu.org/licenses/}.
 * </pre>
 *
 * This is the controller class.
 * With an object of this class you may search or lookup detailed information
 * about a song on Spotify.
 *
 * Caching is activated per default. To deactivate id, change USE_CACH to false.
 * Change cache directory in the CACHE_DIR constant.
 *
 * If the Spotify URLS should change somehow, you can alter these in the constants
 * SERVICE_BASE_URL_SEARCH and SERVICE_BASE_URL_LOOKUP.
 * 
 * Example:
 * <code>
 * $spotify = MetaTune::getInstance();
 * $song = $spotify->searchTrack("Superfamily - The Dive");
 * echo "First result: " . $song[0]->getTitle() . " by " . 
 *  $song[0]->getArtistAsString();
 * // Will result in:
 * // First result: The Dive by Superfamily
 * </code>
 *
 * <b>SPOTIFY Disclaimer</b>
 * <i>This product uses a SPOTIFY API but is not endorsed, certified or otherwise
 * approved in any way by Spotify. Spotify is the registered trade mark of the
 * Spotify Group.</i>
 *
 * @todo caching added - Alot of data cached? Flush capabillities?
 * @copyright Mikael Brevik 2011
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */

class MetaTune {
    const CACHE_DIR = '../cache/'; // Cache directory (must be writable) relative to this file
    const USE_CACHE = true; // Should caching be activated?
    const CACHE_PREFIX = "METATUNE_CACHE_"; // prefix for cache-files. 

    const SERVICE_BASE_URL_SEARCH = "http://ws.spotify.com/search/1/";
    const SERVICE_BASE_URL_LOOKUP = "http://ws.spotify.com/lookup/1/";
    const PLAYBUTTON_BASE_URL = "https://embed.spotify.com/?uri=";

    public $autoAddTracksToPlayButton = false;

    private $list = array();

    // Holds instance
    private static $instance;

    // Singelton-patterned class. No need to make an instance of this object 
    // outside it self. 
    private function __construct()
    {
        $delimiter = (substr(self::CACHE_DIR, 0, 1) != "/") ? "/" : "";
        $cacheDir = dirname(__FILE__) . $delimiter . self::CACHE_DIR;
        if ((!is_dir($cacheDir) || !is_writable($cacheDir)) && self::USE_CACHE) {
            throw new \Exception("No writable cache dir found: " . $cacheDir);
        }

        Utils\CacheRequest::$useCache = self::USE_CACHE;
        Utils\CacheRequest::$cacheDir = $cacheDir;
    }

    /**
     * Get new instance of this object.
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    /**
     * Prevents cloning
     * Cloning not allowed in a singelton patterned class
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Generates a XML string from your array or object of artists, albums
     * or tracks.
     *
     * @throws MetaTuneException
     * @param mixed $input
     * @return string
     */
    public function generateXML($input)
    {
        if (!is_array($input) && $input instanceof SpotifyItem)
        {
            return $input->asXML();
        }

        // Empty array. No need for that. 
        if (count($input) < 1)
        {
            throw new MetaTuneException(1003);
        }

        if ($input[0] instanceof Artist)
        {
            $xml = new Utils\MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><artists></artists>');
        }
        else if ($input[0] instanceof Track)
        {
            $xml = new Utils\MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><tracks></tracks>');
        }
        else if ($input[0] instanceof Album)
        {
            $xml = new Utils\MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><albums></albums>');
        }
        else
        {
            throw new MetaTuneException(1004);
        }

        foreach ($input as $item)
        {
            $xml->addXMLElement(new Utils\MBSimpleXMLElement($item->asXML()));
        }

        return $xml->asXML();
    }

    /**
     * Generates a array or object of tracks from provided XML document.
     *
     * @throws MetaTuneException
     * @param string $contents
     * @return mixed
     */
    public function parseXMLTracks($contents)
    {
        $xml = new \SimpleXMLElement($contents);
        if (isset($xml->track))
        {
            $tracks = array();
            foreach ($xml->track as $track)
            {
                $tracks[] = $this->extractTrackInfo($track);
            }

            return $tracks;
        }

        if (isset($xml->name))
        {
            return $this->extractTrackInfo($xml);
        }

        throw new MetaTuneException(1001);
    }

    /**
     * Generates a array or object of (an) artist(s) from provided XML document.
     *
     * @throws MetaTuneException
     * @param string $contents
     * @return mixed
     */
    public function parseXMLArtist($contents)
    {
        $xml = new \SimpleXMLElement($contents);
        if (isset($xml->artist))
        {
            $artists = array();
            foreach ($xml->artist as $artist)
            {
                $artists[] = $this->extractArtistInfo($artist);
            }

            return $artists;
        }

        if (isset($xml->name))
        {
            return $this->extractArtistInfo($xml);
        }

        throw new MetaTuneException(1002);
    }

    /**
     * Generates a array or object of (an) album(s) from provided XML document.
     *
     * Spotify advanced search can be used.
     *
     * Do not filter or encode input in any way. (i.e. htmlspecialchars)
     *
     * 
     * @throws MetaTuneException
     * @param string $contents
     * @return mixed
     */
    public function parseXMLAlbum($contents)
    {
        $xml = new \SimpleXMLElement($contents);
        if (isset($xml->album))
        {
            $albums = array();

            foreach ($xml->album as $album)
            {
                $albums[] = $this->extractAlbumInfo($album);
            }

            return $albums;
        }

        if (isset($xml->name))
        {
            return $this->extractAlbumInfo($xml);
        }

        throw new MetaTuneException(1003);
    }

    private function addPageSuffix($page) 
    {
        if ($page <= 1 || !is_numeric($page)) return "";
        return "&page=" . (int) $page;
    }

    /**
     * Search for a spesific track.
     *
     * If everything works it will return an array with all the results as Track
     * objects.
     *
     * Spotify advanced search can be used.
     *
     * Do not filter or encode input in any way. (i.e. htmlspecialchars)
     *
     * @throws MetaTuneException
     * @param string $name
     * @param int $page
     * @return array
     */
    public function searchTrack($name, $page = 1)
    {
        $url = self::SERVICE_BASE_URL_SEARCH . "track?q=" . $this->translateString($name) . $this->addPageSuffix($page);
        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $tracks = array();
        foreach ($xml->track as $track)
        {
            $tracks[] = $this->extractTrackInfo($track);
        }

        if ($this->autoAddTracksToPlayButton) {
            $this->appendTracksToTrackList($tracks);
        }

        return $tracks;
    }

    /**
     * Search in all the artists at Spotifys database.
     *
     * Spotify advanced search can be used.
     *
     * Do not filter or encode input in any way. (i.e. htmlspecialchars)
     *
     * @throws MetaTuneException
     * @param string $name
     * @param int $page
     * @return array
     */
    public function searchArtist($name, $page = 1)
    {
        $url = self::SERVICE_BASE_URL_SEARCH . "artist?q=" . $this->translateString($name) . $this->addPageSuffix($page);
        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $artists = array();
        foreach ($xml->artist as $artist)
        {
            $artists[] = $this->extractArtistInfo($artist);
        }

        return $artists;
    }

    /**
     * Search for an album at Spotify. 
     *
     * @throws MetaTuneException
     * @param string $name
     * @param int $page
     * @return array
     */
    public function searchAlbum($name, $page = 1)
    {
        $url = self::SERVICE_BASE_URL_SEARCH . "album?q=" . $this->translateString($name) . $this->addPageSuffix($page);
        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $albums = array();
        foreach ($xml->album as $album)
        {
            $albums[] = $this->extractAlbumInfo($album);
        }

        return $albums;
    }

    /**
     * A wrapper method for the lookup***() methods. Give in a Spotify URI,
     * and that will be used to chose the correct method. For artists and albums
     * this method will return the detailed information.
     *
     * Example of $spotifyURI
     * <ul>
     * <li>spotify:track:1f58xau99Rdn0hhcJTwRhz</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $spotifyURI
     * @param boolean $details
     * @return mixed
     */
    public function lookup($spotifyURI, $details = true)
    {
        $uriExtract = explode(":", $spotifyURI);
        if (count($uriExtract) < 2)
        {
            throw new MetaTuneException("404 Not Found");
        }

        switch ($uriExtract[1])
        {
            case "artist":
                return $this->lookupArtist($spotifyURI, $details);
            case "album":
                return $this->lookupAlbum($spotifyURI, $details);
            default:
                return $this->lookupTrack($spotifyURI);
        }
    }

    /**
     * Get full info about one track. Argument takes a spotify URI or just the
     * id it self.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:track:1f58xau99Rdn0hhcJTwRhz</li>
     * <li>1f58xau99Rdn0hhcJTwRhz</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $id
     * @return Track
     */
    public function lookupTrack($id)
    {

        if (substr($id, 0, 14) != "spotify:track:")
        {
            $id = "spotify:track:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);
        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $track = $this->extractTrackInfo($xml);
        $track->setURI($id);

        // Add, if activated, track to play button list. 
        if ($this->autoAddTracksToPlayButton) {
            $this->list[] = $track;
        }

        return $track;
    }

    /**
     * Get basic info about one artist. Argument takes a spotify URI or just the
     * id it self.
     *
     * If param $details is false:
     * This method will only get the basic information about an artist.
     * Will not get all artist's albums.
     *
     * If param $details is true:
     * This method will return an artist with all it's albums.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l</li>
     * <li>5ObUhLdIEbhEqVCYxzVQ9l</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $id
     * @param boolean $details
     * @return Artist
     */
    public function lookupArtist($id, $details = false)
    {

        if (substr($id, 0, 15) != "spotify:artist:")
        {
            $id = "spotify:artist:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);

        if ($details)
        {
            $url .= "&extras=albumdetail";
        }

        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $artist = $this->extractArtistInfo($xml);
        $artist->setURI($id);

        return $artist;
    }

    /**
     * Get full (detailed) info about one artist. Argument takes a spotify URI
     * or just the id it self.
     *
     * This method will return an artist with all it's albums.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l</li>
     * <li>5ObUhLdIEbhEqVCYxzVQ9l</li>
     * </ul>
     *
     * @deprecated
     * @throws MetaTuneException
     * @param string $id
     * @return Artist
     */
    public function lookupArtistDetailed($id)
    {
        return $this->lookupArtist($id, true);
    }

    /**
     * Get basic info about one album. Argument takes a spotify URI
     * or just the id it self.
     *
     * If $details is false only basic album information will be fetched. 
     * Otherwise all details will be shown. (Tracks)
     *
     * Example of $id:
     * <ul>
     * <li>spotify:album:1kjefoUShy8bZcwBEHtMWp</li>
     * <li>1kjefoUShy8bZcwBEHtMWp</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $id
     * @param boolean $details
     * @return Album
     */
    public function lookupAlbum($id, $details = false)
    {
        if (substr($id, 0, 14) != "spotify:album:")
        {
            $id = "spotify:album:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);

        if ($details)
        {
            $url .= "&extras=trackdetail";
        }

        $contents = $this->requestContent($url);
        $xml = new \SimpleXMLElement($contents);

        $album = $this->extractAlbumInfo($xml, $id);
        $album->setURI($id);
        return $album;
    }

    /**
     * Get all (detailed) info about one album. Argument takes a spotify URI
     * or just the id it self.
     *
     * This method gets detailed album information. Will contain the
     * detailed information like tracks.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:album:1kjefoUShy8bZcwBEHtMWp</li>
     * <li>1kjefoUShy8bZcwBEHtMWp</li>
     * </ul>
     *
     * @deprecated
     * @throws MetaTuneException
     * @param string $id
     * @return Album
     */
    public function lookupAlbumDetailed($id)
    {
        return $this->lookupAlbum($id, true);
    }

    /**
     * Translate string to a proper format to search in the Spotify API.
     *
     * @param string $string
     * @return string
     */
    private function translateString($string)
    {
        // Replace "-" in regular search but leave it on "tag"-searches
        // such as "genre:brit-pop" or "label:deutsche-grammophon"
        $string = preg_replace("/(^[^a-z\:]+\-|[\_\(\)])/ui", " ", (trim($string)));

        // replace multiple whitespaces with a single one.
        $string = preg_replace("/\s{2,}/", " ", ($string));
        return urlencode((trim($string)));
    }

    /**
     * Request content from the URL given as argument. This method will
     * cache data if CACHE_DIR and USE_CACHE is set.
     *
     * @throws MetaTuneException
     * @param string $url
     * @return string
     */
    private function requestContent($url)
    {
        $cacheDelimiter = (substr(self::CACHE_DIR, 0, 1) != "/") ? "/" : "";
        $delimiter = (substr(self::CACHE_DIR, -1) != "/") ? "/" : "";
        $filename = dirname(__FILE__) . $cacheDelimiter . self::CACHE_DIR . $delimiter . self::CACHE_PREFIX . md5($url) . '.xml';
        
        return Utils\CacheRequest::request($url, $filename);
    }

    /**
     * Extract artist information from a SimpleXMLElement.
     *
     * @param SimpleXMLElement $artist
     * @return Artist
     */
    private function extractArtistInfo(\SimpleXMLElement $artist)
    {
        $artistId = $artist->attributes();
        $albums = array();
        if (isset($artist->albums->album))
        {
            foreach ($artist->albums->album as $album)
            {
                $albums[] = $this->extractAlbumInfo($album);
            }
        }



        return new Entity\Artist((string) $artistId['href'], (string) $artist->name, (double) $artist->popularity, $albums);
    }

    /**
     * Extract information about a Track from a SimpleXMLElement.
     *
     * @param SimpleXMLElement  $track
     * @param Album $albumInput
     * @return Track
     */
    private function extractTrackInfo(\SimpleXMLElement $track, Entity\Album $albumInput = null)
    {
        $artists = array();
        foreach ($track->artist as $artistl)
        {
            $artists[] = $this->extractArtistInfo($artistl);
        }
        if (count($artists) == 1)
        {
            $artists = $artists[0];
        }

        $artistAlbum = $artists;
        if (is_array($artists))
        {
            $artistAlbum = $artists[0];
        }

        $cdnm = 0;
        if (isset($track->{"disc-number"}))
        {
            $cdnm = (int) $track->{"disc-number"};
        }

        if (!isset($albumInput) || $albumInput == null)
        {
            $albumId = $track->album->attributes();
            $album = new Entity\Album((string) $albumId['href'], (string) $track->album->name, (string) $track->album->released, $artistAlbum);
            $territories = explode(' ', (string) $track->album->availability->territories);
            $album->setTerritories($territories);
        }
        else
        {
            $album = $albumInput;
        }

        $trackId = $track->attributes();
        return new Entity\Track((string) $trackId['href'], (string) $track->name, $artists, $album, (double) $track->length, (double) $track->popularity, (int) $track->{"track-number"}, $cdnm);

    }

    /**
     * Extract album information from a SimpleXMLElement object.
     * 
     * @param SimpleXMLElement $album
     * @param string $id
     * @return Album
     */
    private function extractAlbumInfo(\SimpleXMLElement $album, $id = null)
    {
        $artistUse = $album->artist;
        if (is_array($album->artist) || isset($album->artist[0]))
        {
            $artistUse = $album->artist[0];
        }
        $artist = $this->extractArtistInfo($artistUse);

        $albumId = $album->attributes();
        $albumURI = (string) $albumId['href'];
        if ($id != null && isset($id))
        {
            $albumURI = $id;
        }

        $currentAlbum = new Entity\Album($albumURI, (string) $album->name, (string) $album->released, $artist, (double) $album->popularity);

        $territories = explode(' ', (string) $album->availability->territories);
        $currentAlbum->setTerritories($territories);

        $tracks = array();
        if (isset($album->tracks->track))
        {
            foreach ($album->tracks->track as $track)
            {
                $tracks[] = $this->extractTrackInfo($track, clone $currentAlbum);
            }
        }

        $currentAlbum->setTracks($tracks);

        return $currentAlbum;
    }

    public $playButtonHeight = 330;
    public $playButtonWidth = 250;
    public $playButtonTheme = "black";
    public $playButtonView = "list";

    /**
     * Append single track or array of tracks to the autogenerated list.
     * 
     * @param mixed $track array of Track or a single Track object
     * @return array The list of tracks in the autogenerated list.
     */
    public function appendTracksToTrackList ($track) 
    {
        if (!is_array ($track)) 
        {
            return $this->addTrackToList(array($track));
        }
        $this->list = array_merge($this->list, $track);
        return $this->list;
    }

    /**
     * Get the list autogenerated through search/lookup.
     * Will only be filled if the autoAddTracksToPlayButton variable is true. 
     * 
     * @return array The list of tracks in the autogenerated list.
     */
    public function getAutoGeneratedList ($track)
    {
        return $this->list;
    }

    /**
     * Reset the autogenerated list. Empties it completely.
     *
     * @return void
     */
    public function emptyTrackList () 
    {
        $this->list = array();
    }

    /**
     * Generate HTML for a Play Button from an album object. 
     *
     * @param Album $album Metatune album object.
     * @return string HTML 
     */
    public function getPlayButtonFromAlbum(Album $album) 
    {   
        if (!$album)
        {
            throw new MetaTuneException("No album given to play button");
        }

        $playlistName = $album->getName();
        return $this->getPlayButtonFromTracks($album->getTracks(), $playlistName);
    }

    /**
     * Generate HTML for a Play Button from an album object. 
     *
     * @param Album $album Metatune album object.
     * @return string HTML 
     */
    public function getPlayButtonFromTrack(Track $track) 
    {
        if (!$track)
        {
            throw new MetaTuneException("No track given to play button");
        }

        return $this->generatePlayButton($track->getURI());
    }

    /**
     * Wrapper for the getPlayButtonFromTracks() method, but only tries
     * for the auto generated list. 
     * 
     * @param string $title Title for the play button list.
     * @return string HTML
     */
    public function getPlayButtonAutoGenerated($title = "Playlist") 
    {
        return $this->getPlayButtonFromTracks(array(), $title);
    }

     /**
     *  Generate HTML for a Play Button from an URI to a playlist. 
     *  If $tracks == array(), the method tries to look at the auto 
     *  generated list (based on searches and lookups.)
     *  
     *  @example $metatune->getPlayButtonFromPlaylistURI("spotify:user:erebore:playlist:788MOXyTfcUb1tdw4oC7KJ");
     *  @param string $uri Spotify URI for a playlist.
     *  @return string HTML 
     */
    public function getPlayButtonFromTracks(array $tracks = array(), $title = "Playlist")
    {
        if (count($tracks) == 0 && count($this->list) > 0)
        {
            return $this->getPlayButtonFromTracks($this->list, $title);
        }

        if (count($tracks) == 1)
        {
            return $this->getPlayButtonFromTrack($tracks[0]);
        }

        if (count($tracks) == 0)
        {
            throw new MetaTuneException("No tracks given for the play button.");
        }

        if (strlen($title) < 1)
        {
            $title = "Playlist";
        }

        $stringTracks = "";
        foreach ($tracks as $track)
        {
            if ($track instanceof Track)
            {   
                $stringTracks .= str_replace("spotify:track:", "", $track->getURI()) . ",";
            }
        }
        $stringTracks = substr($stringTracks, 0, -1); // remove trailing ,
        $src = "spotify:trackset:" . $title . ":" . $stringTracks;
        return $this->generatePlayButton($src);
    }

    /**
     *  Generate HTML for a Play Button from an URI to a playlist. 
     *  
     *  @example $metatune->getPlayButtonFromPlaylistURI("spotify:user:erebore:playlist:788MOXyTfcUb1tdw4oC7KJ");
     *  @param string $uri Spotify URI for a playlist.
     *  @return string HTML 
     */
    public function getPlayButtonFromPlaylistURI($uri) 
    {
        if (substr($uri, 0, 13) != "spotify:user:")
        {
            $uri = "spotify:user:" . $uri;
        }

        return $this->generatePlayButton($uri);
    }

    /**
     * Used as a helper method to generate the iframe
     * based on settings and given argument $src. 
     */
    private function generatePlayButton($src) 
    {
        $this->validateSettings();

        $template = '<iframe src="%s%s&theme=%s&view=%s" width="%d" height="%d" frameborder="0" allowtransparency="true"></iframe>';
        
        return sprintf($template, 
                        self::PLAYBUTTON_BASE_URL, 
                        $src, 
                        $this->playButtonTheme, 
                        $this->playButtonView, 
                        $this->playButtonWidth,
                        $this->playButtonHeight
                      );
    }

    /**
     * Helper method to validate settings given.
     * If the settings doesn't match the criteria, settings are set 
     * back to default. 
     */
    private function validateSettings () 
    {

        if (!in_array($this->playButtonTheme, array("black", "white")))
        {
            $this->playButtonTheme = "black";
        }

        if (!in_array($this->playButtonView, array("list", "coverart")))
        {
            $this->playButtonView = "list";
        }

        if ($this->playButtonHeight < 80 || $this->playButtonHeight > 720)
        {
            $this->playButtonHeight = 330;
        }

        if ($this->playButtonWidth < 250 || $this->playButtonWidth > 640)
        {
            $this->playButtonWidth = 250;
        }

    }


}

