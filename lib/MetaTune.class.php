<?php

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
 * @todo remove redundant information (recursive artist/track/album)?
 * @todo caching added - Alot of data cached? Flush capabillities?
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */
class MetaTune {
    const CACHE_DIR = 'lib/cache/'; // Cache directory (must be writable)
    const USE_CACHE = true; // Should caching be activated?
    const CACHE_PREFIX = "METATUNE_CACHE_"; // prefix for cache-files. 

    const SERVICE_BASE_URL_SEARCH = "http://ws.spotify.com/search/1/";
    const SERVICE_BASE_URL_LOOKUP = "http://ws.spotify.com/lookup/1/";

    // Holds instance
    private static $instance;

    // Singelton-patterned class. No need to make an instance of this object 
    // outside it self. 
    private function __construct() {
        
    }

    /**
     * Get new instance of this object.
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    /**
     * Prevents cloning
     * Cloning not allowed in a singelton patterned class
     */
    public function __clone() {
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
    public function generateXML($input) {
        if (!is_array($input) && $input instanceof SpotifyItem) {
            return $input->asXML();
        }

        // Empty array. No need for that. 
        if (count($input) < 1) {
            throw new MetaTuneException(1003);
        }

        if ($input[0] instanceof Artist) {
            $xml = new MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><artists></artists>');
        } else if ($input[0] instanceof Track) {
            $xml = new MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><tracks></tracks>');
        } else if ($input[0] instanceof Album) {
            $xml = new MBSimpleXMLElement('<?xml version = "1.0" encoding = "UTF-8"?><albums></albums>');
        } else {
            throw new MetaTuneException(1004);
        }

        foreach ($input as $item) {
            $xml->addXMLElement(new MBSimpleXMLElement($item->asXML()));
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
    public function parseXMLTracks($contents) {
        $xml = new SimpleXMLElement($contents);
        if (isset($xml->track)) {
            $tracks = array();
            foreach ($xml->track as $track) {
                $tracks[] = $this->extractTrackInfo($track);
            }

            return $tracks;
        }

        if (isset($xml->name)) {
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
    public function parseXMLArtist($contents) {
        $xml = new SimpleXMLElement($contents);
        if (isset($xml->artist)) {
            $artists = array();
            foreach ($xml->artist as $artist) {
                $artists[] = $this->extractArtistInfo($artist);
            }

            return $artists;
        }

        if (isset($xml->name)) {
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
    public function parseXMLAlbum($contents) {
        $xml = new SimpleXMLElement($contents);
        if (isset($xml->album)) {
            $albums = array();

            foreach ($xml->album as $album) {
                $albums[] = $this->extractAlbumInfo($album);
            }

            return $albums;
        }

        if (isset($xml->name)) {
            return $this->extractAlbumInfo($xml);
        }

        throw new MetaTuneException(1003);
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
     * @return array
     */
    public function searchTrack($name) {
        $url = self::SERVICE_BASE_URL_SEARCH . "track?q=" . $this->translateString($name);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $tracks = array();
        foreach ($xml->track as $track) {
            $tracks[] = $this->extractTrackInfo($track);
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
     * @return array
     */
    public function searchArtist($name) {
        $url = self::SERVICE_BASE_URL_SEARCH . "artist?q=" . $this->translateString($name);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $artists = array();
        foreach ($xml->artist as $artist) {
            $artists[] = $this->extractArtistInfo($artist);
        }

        return $artists;
    }

    /**
     * Search for an album at Spotify. 
     *
     * @throws MetaTuneException
     * @param string $name
     * @return array
     */
    public function searchAlbum($name) {
        $url = self::SERVICE_BASE_URL_SEARCH . "album?q=" . $this->translateString($name);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $albums = array();
        foreach ($xml->album as $album) {
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
     * @return mixed
     */
    public function lookup($spotifyURI) {
        $uriExtract = explode(":", $spotifyURI);
        if (count($uriExtract) < 2) {
            throw new MetaTuneException("404 Not Found");
        }

        switch ($uriExtract[1]) {
            case "artist":
                return $this->lookupArtistDetailed($spotifyURI);
                break;
            case "album":
                return $this->lookupAlbumDetailed($spotifyURI);
                break;
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
    public function lookupTrack($id) {

        if (substr($id, 0, 14) != "spotify:track:") {
            $id = "spotify:track:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $track = $this->extractTrackInfo($xml);
        $track->setURI($id);

        return $track;
    }

    /**
     * Get basic info about one artist. Argument takes a spotify URI or just the
     * id it self.
     *
     * This method will only get the basic information about an artist.
     * Will not get all artist's albums.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l</li>
     * <li>5ObUhLdIEbhEqVCYxzVQ9l</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $id
     * @return Artist
     */
    public function lookupArtist($id) {

        if (substr($id, 0, 15) != "spotify:artist:") {
            $id = "spotify:artist:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

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
     * @throws MetaTuneException
     * @param string $id
     * @return Artist
     */
    public function lookupArtistDetailed($id) {
        if (substr($id, 0, 15) != "spotify:artist:") {
            $id = "spotify:artist:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id) . "&extras=albumdetail";
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $artist = $this->extractArtistInfo($xml);
        $artist->setURI($id);

        return $artist;
    }

    /**
     * Get basic info about one album. Argument takes a spotify URI
     * or just the id it self.
     *
     * This method only gets basic album information. Will not contain the
     * detailed information like tracks in album.
     *
     * Example of $id:
     * <ul>
     * <li>spotify:album:1kjefoUShy8bZcwBEHtMWp</li>
     * <li>1kjefoUShy8bZcwBEHtMWp</li>
     * </ul>
     *
     * @throws MetaTuneException
     * @param string $id
     * @return Album
     */
    public function lookupAlbum($id) {
        if (substr($id, 0, 14) != "spotify:album:") {
            $id = "spotify:album:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id);
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $album = $this->extractAlbumInfo($xml);
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
     * @throws MetaTuneException
     * @param string $id
     * @return Album
     */
    public function lookupAlbumDetailed($id) {
        if (substr($id, 0, 14) != "spotify:album:") {
            $id = "spotify:album:" . $id;
        }

        $url = self::SERVICE_BASE_URL_LOOKUP . "?uri=" . ($id) . "&extras=trackdetail";
        $contents = $this->requestContent($url);
        $xml = new SimpleXMLElement($contents);

        $album = $this->extractAlbumInfo($xml, $id);
        $album->setURI($id);
        return $album;
    }

    /**
     * Translate string to a proper format to search in the Spotify API.
     *
     * @param string $string
     * @return string
     */
    private function translateString($string) {
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
    private function requestContent($url) {
        $headerdata = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept: application/xml\r\n"
            )
        );

        if (self::CACHE_DIR && self::USE_CACHE) {
            $delimiter = (substr(self::CACHE_DIR, -1) != "/") ? "/" : "";
            $filename = self::CACHE_DIR . $delimiter . self::CACHE_PREFIX . md5($url) . '.xml';
            if (file_exists($filename)) {
                $cacheContents = file_get_contents($filename);
                $matches = array();
                if (preg_match('/<!-- Last-Modified: ([^\n]+) -->\\z/', $cacheContents, $matches)) {
                    $headerdata['http']['header'] .= "If-Modified-Since: " . $matches[1] . "\r\n";
                }
            }
        }

        $headers = stream_context_create($headerdata);
        $contents = @file_get_contents($url, false, $headers);
        if (isset($http_response_header) && is_array($http_response_header)) {
            $errorCode = str_replace("HTTP/1.1 ", "", $http_response_header[0]);
            if ($errorCode != "200 OK" && $errorCode != "304 Not Modified") {
                throw new MetaTuneException($errorCode);
            }

            if ($errorCode == "304 Not Modified") {
                // If we're here, the cache header must have been set, so $cacheContents must have contents.
                $contents = $cacheContents;
            } else if (self::CACHE_DIR && self::USE_CACHE) {
                // cache data
                $lastChangedDate = "<!-- Last-Modified: " . str_replace("Last-Modified: ", "", $http_response_header[6]) . " -->";
                file_put_contents($filename, $contents . $lastChangedDate);
            }
        }
        return $contents;
    }

    /**
     * Extract artist information from a SimpleXMLElement.
     *
     * @param SimpleXMLElement $artist
     * @return Artist
     */
    private function extractArtistInfo(SimpleXMLElement $artist) {
        $artistId = $artist->attributes();
        $albums = array();
        if (isset($artist->albums->album)) {
            foreach ($artist->albums->album as $album) {
                $albums[] = $this->extractAlbumInfo($album);
            }
        }



        return new Artist((string) $artistId['href'], (string) $artist->name, (double) $artist->popularity, $albums);
    }

    /**
     * Extract information about a Track from a SimpleXMLElement.
     *
     * @param SimpleXMLElement  $track
     * @param Album $albumInput
     * @return Track
     */
    private function extractTrackInfo(SimpleXMLElement $track, Album $albumInput = null) {
        $artists = array();
        foreach ($track->artist as $artistl) {
            $artists[] = $this->extractArtistInfo($artistl);
        }
        if (count($artists) == 1) {
            $artists = $artists[0];
        }

        $artistAlbum = $artists;
        if (is_array($artists)) {
            $artistAlbum = $artists[0];
        }

        $cdnm = 0;
        if (isset($track->{"disc-number"})) {
            $cdnm = (int) $track->{"disc-number"};
        }

        if (!isset($albumInput) || $albumInput == null) {
            $albumId = $track->album->attributes();
            $album = new Album((string) $albumId['href'], (string) $track->album->name, (string) $track->album->released, $artistAlbum);
        } else {
            $album = $albumInput;
        }

        $trackId = $track->attributes();
        return new Track((string) $trackId['href'], (string) $track->name, $artists, $album, (double) $track->length, (double) $track->popularity, (int) $track->{"track-number"}, $cdnm);
    }

    /**
     * Extract album information from a SimpleXMLElement object.
     * 
     * @param SimpleXMLElement $album
     * @param string $id
     * @return Album
     */
    private function extractAlbumInfo(SimpleXMLElement $album, $id = null) {
        $artistUse = $album->artist;
        if (is_array($album->artist) || isset($album->artist[0])) {
            $artistUse = $album->artist[0];
        }
        $artist = $this->extractArtistInfo($artistUse);

        $albumId = $album->attributes();
        $albumURI = (string) $albumId['href'];
        if ($id != null && isset($id)) {
            $albumURI = $id;
        }

        $currentAlbum = new Album($albumURI, (string) $album->name, (string) $album->released, $artist, (double) $album->popularity);

        $tracks = array();
        if (isset($album->tracks->track)) {
            foreach ($album->tracks->track as $track) {
                $tracks[] = $this->extractTrackInfo($track, clone $currentAlbum);
            }
        }

        $currentAlbum->setTracks($tracks);

        return $currentAlbum;
    }

}

