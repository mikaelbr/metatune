<?php
namespace MetaTune;

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
 * Error handling for MetaTune. Extends normal Exceptions
 *
 * @copyright Mikael Brevik 2010
 * @author Mikael Brevik <mikaelbre@gmail.com>
 * @version 1.0
 * @package MetaTune
 */
class MetaTuneException extends Exception {

    public function __construct($codeMsg, $code = 0, Exception $previous = null) {

        switch ($codeMsg) {
            // Spotify API Spesific errors
            case "400 Bad Request":
            case 400:
                $this->code = 400;
                $this->message = "The request was not understood. Used for example when a required parameter was omitted.";
                break;
            case "403 Forbidden":
            case 403:
                $this->code = 403;
                $this->message = "The rate limiting has kicked in.";
                break;
            case "406 Not Acceptable":
            case 406:
                $this->code = 406;
                $this->message = "The requested format isn’t available.";
                break;
            case "503 Service Unavailable":
            case 503:
                $this->code = 503;
                $this->message = "The API is temporarily unavailable.";
                return array("errorid" => "503 Service Unavailable", "errormsg" => "");
                break;
            case "500 Internal Server Error":
            case 500:
                $this->code = 500;
                $this->message = "The server encountered an unexpected problem. Should not happen.";
                break;
            // Library spesific errors
            case "1001 Bad Tracks XML Format":
            case 1001:
                $this->code = 1001;
                $this->message = "The XML-document given is not in a proper format for tracks.";
                break;
            case "1002 Bad Artist XML Format":
            case 1002:
                $this->code = 1002;
                $this->message = "The XML-document given is not in a proper format for artists.";
                break;
            case "1003 Bad Album XML Format":
            case 1003:
                $this->code = 1003;
                $this->message = "The XML-document given is not in a proper format for albums.";
                break;
            case 1004:
                $this->code = 1004;
                $this->message = "The provided array or object is not recognized as a SpotifyItem.";
                break;
            default:
                $this->code = 404;
                $this->message = "The requested resource was not found. Also used if a format is requested using the url and the format isn’t available.";
        }

        // make sure everything is assigned properly
        parent::__construct($this->message, $this->code);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

