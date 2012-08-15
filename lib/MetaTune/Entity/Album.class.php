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
 * A class for Albums. Holds information about name, URI/URL, release date,
 * popularity and might contain information about artist.
 *
 * @uses SpotifyItem
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelb@mikaelb.net>
 * @version 1.0
 * @package MetaTune
 */
class Album extends SpotifyItem {
    private $name;
    private $release;
    private $popularity;
    private $artist;
    private $tracks;
    private $territories;

    /**
     *
     * @param string $uri
     * @param string $name
     * @param string $release
     * @param Artist $artist
     * @param float $popularity
     */
    public function __construct($uri, $name, $release, Artist $artist = null, $popularity = 0.0, $tracks = array(), $territories = array()) {
        $this->uri = $uri;
        $this->name = $name;
        $this->release = $release;
        $this->popularity = $popularity;
        $this->artist = $artist;
        $this->tracks = $tracks;
        $this->territories = $territories;
    }

    /**
     * Get the Spotify URI to this album. Example:
     * spotify:album:0AF0eSjB3atcLpJ7gQBop5
     *
     * @return string
     */
    public function getURI() {
        return $this->uri;
    }

    /**
     * Set uri.
     * example: spotify:album:0AF0eSjB3atcLpJ7gQBop5
     * 
     * @param string $uri
     */
    public function setURI ($uri) {
        $this->uri = $uri;
    }

    /**
     * Get album name.
     *
     * @return string
     */
    public function getName () {
        return $this->name;
    }

    /**
     * Get all tracks
     *
     * @return Track[]
     */
    public function getTracks() {
        return $this->tracks;
    }

    /**
     * Set all tracks to the album.
     * 
     * @param Track[] $tracks
     */
    public function setTracks ($tracks) {
        $this->tracks = $tracks;
    }

    /**
     * Get all territories that this album is available in..
     *
     * @return string[]
     */
    public function getTerritories() {
        return $this->territories;
    }

    /**
     * Set all territories.
     * 
     * @param string[] $territories
     */
    public function setTerritories ($territories) {
        $this->territories = $territories;
    }

    /**
     * Returns TRUE if the album is available in the given territory,
     * or available worldwide.
     *
     * @param string $territory
     * @return boolean
     */
    public function isAvailable($territory) {
      return in_array('worldwide', $this->territories) || in_array($territory, $this->territories);
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
        return $this->spotifyBase . "album/" . $this->getID();
    }

    /**
     * Get the artist of this album. This artist can me "null"
     * @return Artist
     */
    public function getArtist() {
        return $this->artist;
    }

    /**
     * Set the artist of this album.
     *
     * @param Artist $artist
     */
    public function setArtist(Artist $artist) {
        $this->artist = $artist;
    }

    /**
     * Get a float representation of the popularity.
     * Example: 0.92
     *
     * @return float
     */
    public function getPopularity () {
        return $this->popularity;
    }

    /**
     * Get the popularity as percentage (int).
     * Example: 92
     *
     * @return int
     */
    public function getPopularityAsPercent () {
        return round($this->popularity*100);
    }

    /**
     * Check if $this is equal to $b.
     *
     * @param Album $b
     * @return bool
     */
    public function equals(SpotifyItem $b) {
        if(!($b instanceof Album)) {
            return false;
        }

        if($b->getURI() != $this->getURI() || $b->getName() != $this->getName()) {
            return false;
        }
        if($b->getArtist() != null && $this->getArtist() != null && !$this->getArtist()->equals($b->getArtist())) {
            return false;
        }

        $bTracks = $b->getTracks();
        if(count($this->tracks) != count($bTracks)) {
            return false;
        }

        if (count($this->tracks) > 0) {
            for($i = 0; $i < count($this->tracks); $i++) {
                if (!$this->tracks[$i]->equals($bTracks[$i])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get string representation of album.
     *
     * @return string
     */
    public function __toString() {
        return $this->getName() . "";
    }

    /**
     * Get XML representation of album.
     *
     * @return string
     */
    public function asXML () {
        $xml = new MBSimpleXMLElement('<album></album>');
        $xml->addAttribute("href", $this->getURI());
        $xml->addCData("name", $this->getName());

        $xml->addXMLElement(new MBSimpleXMLElement($this->artist->asXML()));

        $tracksElement = $xml->addChild("tracks");
        foreach ($this->tracks as $track) {
            $tracksElement->addXMLElement(new MBSimpleXMLElement($track->asXML()));
        }

        $xml->addChild("popularity", $this->popularity);
        $xml->addChild("released", $this->release);

        return $xml->asXML();

    }
}

