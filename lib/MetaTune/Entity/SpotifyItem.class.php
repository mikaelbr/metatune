<?php
namespace MetaTune\Entity;

/**
 * Parent class of all different Spotify items.
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
 * Parent class of all spotify medium-classes (Artist, Track, Album).
 * Contains vital methods for these kinds of classes.
 *
 * @abstract
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */
abstract class SpotifyItem {

    // The start of the HTTP URL to open a medium.
    public $spotifyBase = "http://open.spotify.com/";

    protected $uri;

    /**
     * A must have method to implement for all extended classes. Used
     * to find a HTTP URL from a Spotify Medium.
     */
    abstract public function getURL();

    /**
     * A method all SpotifyItems must have to be able to export information.
     */
    abstract public function asXML();

    /**
     * Used to check equality of a SpotifyClass (or it's children) class.
     * @param SpotifyItem $b
     */
    abstract public function equals(SpotifyItem $b);

    /**
     * This function/method is used to extract the proper ID from
     * a spotify URI. Must be in the format : "spotify:<medium>:<ID>"
     *
     * @param string $id
     * @return string
     */
    public function getID() {
        $uriSplit = explode(":", $this->uri);
        return $uriSplit[2];
    }

}
