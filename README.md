# Installation and Requirements
## Requirements 

 * PHP 5
 * Enabled fopen wrappers.
 * Read/Write access to server (For caching)

## How to install

 * Download latest version of MetaTune
 * Upload `/lib` and all it's contents to your web server. 
 * Edit `/lib/config.php` to fit yout need. 
 * Look at some of the examples or read the FeatureList for help using MetaTune. 


## Features
Some features includes:

 * Search for Track/Artist/Album
 * Lookup detailed/basic info about Track/Artist/Album
 * Exceptions for error handling. 
 * Caching metadata.
 * XML Import/Export for quick save/load.

Look at [the Wiki](https://github.com/mikaelbr/metatune/wiki/Features-of-MetaTune---Spotify-Metadata-API-PHP-Wrapper) for usage and full support. 

---

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.

---

SPOTIFY Disclaimer
This product uses a SPOTIFY API but is not endorsed, certified or otherwise
approved in any way by Spotify. Spotify is the registered trade mark of the
Spotify Group.

---
# Usage
You can pretty much do anything in the Spotify Metadata API with this library, and then some. Below you'll find all the features and some short example of how to use them. 

In common for all the features, it requires a object of the MetaTune-class.
```php
<?php
$spotify = MetaTune::getInstance();
?>
```

## Search for Track/Artist/Album

You can search for everything in MetaTune. Not only tracks, but albums and artists aswell. In this example however, I will limit myself to just showing search for artists. The code would essentially be the same.

<table>
<tr><th>Method overview</th><th>Description</th></tr>
<tr><td>searchTrack()</td><td>Search for tracks. Will return an array of tracks</td></tr>
<tr><td>searchArtist()</td><td>Search for artists. Will return an array of artists</td></tr>
<tr><td>searchAlbum()</td><td>Search for albums. Will return an array of albums</td></tr>
</table>

```php
<?php
// We have the MetaTune-object; $spotify

// First we take a search
$trackResults = $spotify->searchTrack ("Superfamily");

if(count($trackResults) > 0) {
    $out = count($trackResults) . " results \n<ul>";
    foreach($trackResults as $track) {
          // $track is now an object of the class Track. 
          $out . "\t<li>{$track}</li>\n";
    }
    echo $out . "</ul>";
} else {
    echo "No results";
}
?>
```

### Spotify advanced search syntax

MetaTune supports all of Spotify's advanced search queries. See more information on [Spotify Advanced Search Syntax](http://www.spotify.com/no/about/features/advanced-search-syntax/).

## Lookup detailed/basic info about Track/Artist/Album

Some times all you got is a Spotify URI, such as ```spotify:track:4CwcvWeCi2rFcLPIJCOwXw```, and want to lookup detailed/basic information about a Track/Artist/Album. 

<table>
<tr><th>Method overview</th><th>Description</th></tr>
<tr><td>lookupTrack()</td><td>Get all information about a track. Popularity, duration, artist, album, number and disc in album</td></tr>
<tr><td>lookupArtist()</td><td>Get basic information about an artist. This includes name and popularity</td></tr>
<tr><td>lookupArtistDetailed()</td><td>Get detailed information about an artist. This includes basic info + all the artist's albums</td></tr>
<tr><td>lookupAlbum()</td><td>Get basic info about an album. This includes release date, popularity, name.</td></tr>
<tr><td>lookupAlbumDetailed()</td><td>Get detailed information about an album. This includes basic info + all the album's tracks.</td></tr>
<tr><td>lookup()</td><td>Uses the Spotify URI to determine what to look up.</td></tr>
</table>

```php
<?php
// We have the MetaTune-object; $spotify

// Get all information about a track!
$track = $spotify->lookup("spotify:track:4CwcvWeCi2rFcLPIJCOwXw");

// Print all results
echo "<pre>" . print_r($track, 1) . "</pre>";
?>
```

## Exceptions

But what if the Spotify URI is in a wrong format or is not found? Then we have exceptions to tell us. Lets try to use exceptions in the example from lookup. 

```php
<?php
// We have the MetaTune-object; $spotify

try {
   // Get all information about a track!
   $track = $spotify->lookup("spotify:track:WRONG_URI");
   // all lookup-methods throws a MetaTuneException. We'll try to catch one. 

   // Print all results
   echo "<pre>" . print_r($track, 1) . "</pre>";
} catch (MetaTuneException $ex) {
   echo "Could not retrive information: " . $ex->getMessage();
}
?>
```

## Caching

All access to the Spotify-servers will be cached as long as certain constants are set in the MetaTune class. Caching is activated per default.

The caching works by saving files locally on your server with this format: ```path/to/dir/<YOUR_PREFIX>_<MD5("THE_SEARCH_QUERY")>.xml```

Search query is stripped of some non-alphanumerical signs and trimmed. 

The Spotify servers only send information on requests as long as ```If-Modified-Since``` header field doesn't kick in. If a cache exists of a search the header field would be appended to the file request, and Spotify won't send you any data.  All this happens automaticly as long as you have caching activated. 

To activate/deactivate or change cache settings in any way, you can do this at the top of MetaTune

```php
<?php
    const CACHE_DIR = 'lib/cache/'; // Cache directory (must be writable)
    const USE_CACHE = false; // Should caching be activated?
    const CACHE_PREFIX = "METATUNE_CACHE_"; // prefix for cache-files. 
?>
```


## XML Import/Export

With XML import and/or export you can easily create XML structure of your searches. All ready to be saved to files and/or database (XML-types). 

Here's an example of usage:
 
```php
<?php
// ***Test for array of tracks***
$trackList = $spotify->searchTrack("Superfamily");
$tracksXML = $spotify->generateXML($trackList);
            
// This should now be the same as $trackList
$tracksImport = $spotify->parseXMLTracks($tracksXML);
// Demo print to check correct content
echo "<pre>" . print_r($tracksImport, 1) . "</pre>";
?>
```

In this case ```$tracksXML``` will contain something like:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<tracks>
	<track href="spotify:track:3BbfQLpcj0BfjM5rq8Ioj9"><![CDATA[]]>
		<name><![CDATA[The Radio Has Expressed Concerns About What You Did Last Night]]></name>
		<artist href="spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l"><![CDATA[]]>
			<name><![CDATA[Superfamily]]></name>
			<albums><![CDATA[]]></albums>
			<popularity><![CDATA[0]]></popularity>
		</artist>
		<album href="spotify:album:2PABf16wrJQau7JuDTqTzx"><![CDATA[]]>
			<name><![CDATA[The Radio Has Expressed Concerns About What You Did Last Night]]></name>
			<artist href="spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l"><![CDATA[]]>
				<name><![CDATA[Superfamily]]></name>
				<albums><![CDATA[]]></albums>
				<popularity><![CDATA[0]]></popularity>
			</artist>
			<tracks><![CDATA[]]></tracks>
			<popularity><![CDATA[0]]></popularity>
			<released><![CDATA[2007]]></released>
		</album>
		<length><![CDATA[245.013]]></length>
		<popularity><![CDATA[0.56061]]></popularity>
		<track-number><![CDATA[1]]></track-number>
		<disc-number><![CDATA[0]]></disc-number>
	</track>
	
	[. . .]

</tracks>
```

And ```$tracksImport``` will contain your original data:

```php
Array
(
    [0] => Track Object
        (
            [uri:Track:private] => spotify:track:3BbfQLpcj0BfjM5rq8Ioj9
            [title:Track:private] => The Radio Has Expressed Concerns About What You Did Last Night
            [artist:Track:private] => Artist Object
                (
                    [uri:Artist:private] => spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l
                    [name:Artist:private] => Superfamily
                    [popularity:Artist:private] => 0
                    [albums:Artist:private] => Array
                        (
                        )

                    [spotifyBase] => http://open.spotify.com/
                )

            [album:Track:private] => Album Object
                (
                    [uri:Album:private] => spotify:album:2PABf16wrJQau7JuDTqTzx
                    [name:Album:private] => The Radio Has Expressed Concerns About What You Did Last Night
                    [release:Album:private] => 2007
                    [popularity:Album:private] => 0
                    [artist:Album:private] => Artist Object
                        (
                            [uri:Artist:private] => spotify:artist:5ObUhLdIEbhEqVCYxzVQ9l
                            [name:Artist:private] => Superfamily
                            [popularity:Artist:private] => 0
                            [albums:Artist:private] => Array
                                (
                                )

                            [spotifyBase] => http://open.spotify.com/
                        )

                    [tracks:Album:private] => Array
                        (
                        )

                    [spotifyBase] => http://open.spotify.com/
                )

            [length:Track:private] => 245.013
            [popularity:Track:private] => 0.56061
            [trackNr:Track:private] => 1
            [discNr:Track:private] => 0
            [spotifyBase] => http://open.spotify.com/
        )
	
	[. . . ]

)
```

See more examples of XML import/export in xmltest.php