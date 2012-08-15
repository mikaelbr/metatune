<?php
namespace MetaTune\Entity;

/**
 * MetaTune - A PHP Wrapper to the Spotify Metadata API
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
 * A class for Artists. Holds information about name, popularity and URL/URI
 *
 * @uses SpotifyItem
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */
class Artist extends SpotifyItem {

    private $name;
    private $popularity;
    private $albums;

    /**
     *
     * @param string $uri
     * @param string $name
     * @param float $popularity
     * @param Album[] $albums
     */
    public function __construct($uri, $name, $popularity = 0.0, $albums = array()) {
        $this->uri = $uri;
        $this->name = $name;
        $this->popularity = $popularity;
        $this->albums = $albums;
    }

    /**
     * Get the name of the artist.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the Spotify URI to this artist. Example:
     * spotify:artist:0mXTJETA4XUa12MmmXxZJh
     *
     * @return string
     */
    public function getURI() {
        return $this->uri;
    }

    public function setURI($uri) {
        return $this->uri = $uri;
    }

    /**
     * Not all systems can open a Spotify URI. This method returns a HTTP URL
     * that links to the current artist/track/album.
     *
     * Example: http://open.spotify.com/artist/0mXTJETA4XUa12MmmXxZJh
     *
     * @return string
     */
    public function getURL() {
        return $this->spotifyBase . "artist/" . $this->getID();
    }

    /**
     * Get a float representation of the popularity.
     * Example: 0.92
     *
     * @return float
     */
    public function getPopularity() {
        return $this->popularity;
    }

    /**
     * Get the popularity as percentage (int).
     * Example: 92
     *
     * @return int
     */
    public function getPopularityAsPercent() {
        return round($this->popularity * 100);
    }

    /**
     * Get an array of all albums this artist has. Might be an empty array.
     *
     * @return Album[]
     */
    public function getAlbum() {
        return $this->albums;
    }

    /**
     * Check if $this is equal to $b.
     *
     * @param Artist $b
     * @return bool
     */
    public function equals(SpotifyItem $b) {

        if(!($b instanceof Artist)) {
            return false;
        }

        if ($b->getName() != $this->getName()) {
            return false;
        }
        if ($b->getURI() != $this->getURI()) {
            return false;
        }

        $bAlbums = $b->getAlbum();
        if (count($bAlbums) != count($this->albums)) {
            return false;
        }

        if (count($this->albums) > 0) {
            
            for($i = 0; $i < count($this->albums); $i++) {
                if (!$this->albums[$i]->equals($bAlbums[$i])) {
                    
                    return false;
                }
            }
        }
        

        return true;
    }

    /**
     * Get string representation of artist.
     *
     * @return string
     */
    public function __toString() {
        return $this->getName() . "";
    }

    /**
     * Get XML representation of artist.
     *
     * @return string
     */
    public function asXML() {
        $xml = new MBSimpleXMLElement('<artist></artist>');
        $xml->addAttribute("href", $this->getURI());
        $xml->addCData("name", $this->getName());

        $albumXML = $xml->addChild("albums");
        foreach ($this->albums as $album) {
            $albumXML->addXMLElement(new MBSimpleXMLElement($album->asXML()));
        }

        $xml->addChild("popularity", $this->popularity);

        return $xml->asXML();
    }

}

