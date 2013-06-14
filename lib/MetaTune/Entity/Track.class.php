<?php
namespace MetaTune\Entity;

/**
 * A track from Spotify. Contains information about artist and album. 
 *
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
 * A class for Tracks. Holds information about everything a track needs; URI/URL,
 * title, artist, album, length (float and string), popularity and tracknr.
 *
 * @uses SpotifyItem
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */
class Track extends SpotifyItem {

    private $title;
    private $artist;
    private $album;
    private $length;
    private $popularity;
    private $trackNr;
    private $discNr;

    /**
     *
     * @param string $uri
     * @param string $title
     * @param Artist $artist
     * @param Album $album
     * @param float $length
     * @param float $popularity
     * @param int $trackNr
     */
    public function __construct($uri, $title, $artist, Album $album, $length = 0, $popularity = 0, $trackNr = 0, $discNr = 0) {
        $this->uri = $uri;
        $this->title = $title;
        $this->artist = $artist;
        $this->album = $album;
        $this->length = $length;
        $this->popularity = $popularity;
        $this->trackNr = $trackNr;
        $this->discNr = $discNr;
    }

    /**
     * Get track title.
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Get the Spotify URI to this track. Example:
     * spotify:track:5ScEEDGpKkkcCgYXzRRNlM
     *
     * @return string
     */
    public function getURI() {
        return $this->uri;
    }

    public function setURI($uri) {
        $this->uri = $uri;
    }

    /**
     * Get the artist of this track.
     *
     * @return Artist
     */
    public function getArtist() {
        return $this->artist;
    }

    /**
     * Get artists as string
     *
     * @return string
     */
    public function getArtistAsString() {
        if (is_array($this->artist)) {
            return implode(", ", $this->artist);
        }

        return $this->artist->__toString();
    }

    /**
     * Get the album this track is on.
     *
     * @return Album
     */
    public function getAlbum() {
        return $this->album;
    }

    /**
     * Get number of seconds of this tracks length
     * Example: 132.93
     *
     * @return float
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Get the duration of this song in a string format.
     * Example: 2:13
     *
     * @return String
     */
    public function getLengthInMinutesAsString() {
        $sec = round($this->length);
        $min = floor($sec / 60);
        $restSec = ($sec % 60);
        return (($min < 10) ? "0" . $min : $min) . ":" . (($restSec < 10) ? "0" . $restSec : $restSec);
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
     * Get track number of track (in album)
     *
     * @return int
     */
    public function getTrackNr() {
        return $this->trackNr;
    }

    /**
     * Get disc number of track (in album)
     *
     * @return int
     */
    public function getDiscNr() {
        return $this->discNr;
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
        return $this->spotifyBase . "track/" . parent::getID();
    }

    /**
     * Check if $this is equal to $b.
     *
     * @param Track $b
     * @return bool
     */
    public function equals(SpotifyItem $b) {

        if(!($b instanceof Track)) {
            return false;
        }

        if ($this->getURI() != $b->getURI()) {
            return false;
        }

        if (!is_array($this->getArtist())) {
            if (!$this->getArtist()->equals($b->getArtist())) {
                return false;
            }
        } else {
            $bArtists = $b->getArtist();
            for ($i = 0; $i < count($this->artist); $i++) {
                if (!$this->artist[$i]->equals($bArtists[i])) {
                    return false;
                }
            }
        }

        if (!$this->getAlbum()->equals($b->getAlbum())) {
                        return false;
        }
        return true;
    }

    /**
     * Get a string representation of track.
     * 
     * @return string
     */
    public function __toString() {
        return $this->title . ' - ' . $this->getArtistAsString() . " (" . $this->album . ")";
    }

    /**
     * Get XML representation of track.
     *
     * @return string
     */
    public function asXML() {
        $xml = new MBSimpleXMLElement('<track></track>');
        $xml->addAttribute("href", $this->getURI());
        $xml->addCData("name", $this->getTitle());

        // Add artists
        if ($this->artist != null) {
            if (is_array($this->artist)) {
                foreach ($this->artist as $artist) {
                    $xml->addXMLElement(new MBSimpleXMLElement($artist->asXML()));
                }
            } else {
                $xml->addXMLElement(new MBSimpleXMLElement($this->artist->asXML()));
            }
        }

        if ($this->album != null) {
            $xml->addXMLElement(new MBSimpleXMLElement($this->album->asXML()));
        }

        $xml->addChild("length", $this->length);
        $xml->addChild("popularity", $this->popularity);
        $xml->addChild("track-number", $this->trackNr);
        $xml->addChild("disc-number", $this->discNr);

        return $xml->asXML();
    }

}
