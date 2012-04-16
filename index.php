<?php
require_once("./lib/config.php");
?><!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <title>Metatune by mikaelbr</title>
    <link rel="stylesheet" href="stylesheets/styles.css">
    <link rel="stylesheet" href="stylesheets/pygment_trac.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="javascripts/main.js"></script>
    <!--[if lt IE 9]>
      <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

  </head>
  <body>

      <header>
        <h1>Metatune</h1>
        <p>PHP Wrapper for the Spotify Metadata API and the Spotify Play Button</p>
      </header>

      <div id="banner">
        <span id="logo"></span>

        <a href="https://github.com/mikaelbr/metatune" class="button fork"><strong>Fork On GitHub</strong></a>
        <div class="downloads">
          <span>Downloads:</span>
          <ul>
            <li><a href="https://github.com/mikaelbr/metatune/zipball/master" class="button">ZIP</a></li>
            <li><a href="https://github.com/mikaelbr/metatune/tarball/master" class="button">TAR</a></li>
          </ul>
        </div>

      </div><!-- end banner -->

    <div class="wrapper">
      <nav>
        <ul></ul>
      </nav>
      <section>

        <p>This is just demos of Metatune running. Look at <a href="http://mikaelbr.github.com/metatune">http://mikaelbr.github.com/metatune</a> for documentation and description</p>

<h1>Play Button</h1>

<div class="highlight">
<pre><span class="cp">&lt;?php</span>
    <span class="c1">// Get the metatune instance. </span>
    <span class="nv">$spotify</span> <span class="o">=</span> <span class="nx">MetaTune</span><span class="o">::</span><span class="na">getInstance</span><span class="p">();</span>
    <span class="k">try</span>
    <span class="p">{</span>
        <span class="c1">// Search for tracks</span>
        <span class="nv">$tracks</span> <span class="o">=</span> <span class="nv">$spotify</span><span class="o">-&gt;</span><span class="na">searcTrack</span><span class="p">(</span><span class="s2">"Black Keys Brothers"</span><span class="p"><span class="p">);</span>
        <span class="k">echo</span> <span class="nv">$spotify</span><span class="o">-&gt;</span><span class="na">getPlayButtonFromTracks</span><span class="p">(</span><span class="nv">$tracks</span>, <span class="s2">"Black Keys Brothers"</span><span class="p">);</span>
    <span class="p">}</span>
    <span class="k">catch</span> <span class="p">(</span><span class="nx">MetaTuneException</span> <span class="nv">$ex</span><span class="p">)</span>
    <span class="p">{</span>
        <span class="k">die</span><span class="p">(</span><span class="s2">"&lt;pre&gt;Error</span><span class="se">\n</span><span class="s2">"</span> <span class="o">.</span> <span class="nv">$ex</span> <span class="o">.</span> <span class="s2">"&lt;/pre&gt;"</span><span class="p">);</span>
    <span class="p">}</span>
<span class="cp">?&gt;</span><span class="x"></span>
</pre>
</div>


<h3>Result</h3>

<?php
    // Get the metatune instance. 
    $spotify = MetaTune::getInstance();
    try
    {
        // Need to be detailed, to have all tracks. (second argument true)
        $tracks = $spotify->searchTrack("Black Keys Brothers");
        echo $spotify->getPlayButtonFromTracks($tracks, "The Black Keys - Brothers");
    }
    catch (MetaTuneException $ex)
    {
        die("<pre>Error\n" . $ex . "</pre>");
    }
?>


<h1>Spotify Metadata API</h1>


<h2>Search for Track/Artist/Album</h2>

<div class="highlight">
<pre><span class="cp">&lt;?php</span>
<span class="c1">// We have the MetaTune-object; $spotify</span>

<span class="c1">// First we take a search</span>
<span class="nv">$trackResults</span> <span class="o">=</span> <span class="nv">$spotify</span><span class="o">-&gt;</span><span class="na">searchTrack</span> <span class="p">(</span><span class="s2">"Superfamily"</span><span class="p">);</span>

<span class="k">if</span><span class="p">(</span><span class="nb">count</span><span class="p">(</span><span class="nv">$trackResults</span><span class="p">)</span> <span class="o">&gt;</span> <span class="mi">0</span><span class="p">)</span> <span class="p">{</span>
    <span class="nv">$out</span> <span class="o">=</span> <span class="nb">count</span><span class="p">(</span><span class="nv">$trackResults</span><span class="p">)</span> <span class="o">.</span> <span class="s2">" results </span><span class="se">\n</span><span class="s2">&lt;ul&gt;"</span><span class="p">;</span>
    <span class="k">foreach</span><span class="p">(</span><span class="nv">$trackResults</span> <span class="k">as</span> <span class="nv">$track</span><span class="p">)</span> <span class="p">{</span>
          <span class="c1">// $track is now an object of the class Track. </span>
          <span class="nv">$out</span> <span class="o">.</span> <span class="s2">"</span><span class="se">\t</span><span class="s2">&lt;li&gt;</span><span class="si">{</span><span class="nv">$track</span><span class="si">}</span><span class="s2">&lt;/li&gt;</span><span class="se">\n</span><span class="s2">"</span><span class="p">;</span>
    <span class="p">}</span>
    <span class="k">echo</span> <span class="nv">$out</span> <span class="o">.</span> <span class="s2">"&lt;/ul&gt;"</span><span class="p">;</span>
<span class="p">}</span> <span class="k">else</span> <span class="p">{</span>
    <span class="k">echo</span> <span class="s2">"No results"</span><span class="p">;</span>
<span class="p">}</span>
<span class="cp">?&gt;</span><span class="x"></span>
</pre>
</div>

<h3>Results</h3>
<?php
// We have the MetaTune-object; $spotify
// First we take a search
$trackResults = $spotify->searchTrack("Superfamily");
if(count($trackResults) > 0) {
    $out = "<ul>";
    foreach($trackResults as $track) {
          // $track is now an object of the class Track. 
          $out .= "\t<li>{$track}</li>\n";
    }
    echo $out . "</ul>";
} else {
    echo "<p>No results</p>";
}
?>

<h3>Spotify advanced search syntax</h3>

<p>MetaTune supports all of Spotify's advanced search queries. See more information on <a href="http://www.spotify.com/no/about/features/advanced-search-syntax/">Spotify Advanced Search Syntax</a>.</p>

<h2>Lookup detailed/basic info about Track/Artist/Album</h2>

<p>Some times all you got is a Spotify URI, such as <code>spotify:track:4CwcvWeCi2rFcLPIJCOwXw</code>, and want to lookup detailed/basic information about a Track/Artist/Album.</p>

<div class="highlight">
<pre><span class="cp">&lt;?php</span>
<span class="c1">// We have the MetaTune-object; $spotify</span>

<span class="c1">// Get all information about a track!</span>
<span class="nv">$track</span> <span class="o">=</span> <span class="nv">$spotify</span><span class="o">-&gt;</span><span class="na">lookup</span><span class="p">(</span><span class="s2">"spotify:track:4CwcvWeCi2rFcLPIJCOwXw"</span><span class="p">);</span>

<span class="c1">// Print all results</span>
<span class="k">echo</span> <span class="s2">"&lt;pre&gt;"</span> <span class="o">.</span> <span class="nb">print_r</span><span class="p">(</span><span class="nv">$track</span><span class="p">,</span> <span class="mi">1</span><span class="p">)</span> <span class="o">.</span> <span class="s2">"&lt;/pre&gt;"</span><span class="p">;</span>
<span class="cp">?&gt;</span><span class="x"></span>
</pre>
</div>

<h3>Results</h3>
<?php
// We have the MetaTune-object; $spotify

// Get all information about a track!
$track = $spotify->lookup("spotify:track:4CwcvWeCi2rFcLPIJCOwXw");

// Print all results
echo "<pre>" . print_r($track, 1) . "</pre>";
?>

<pre><code>This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.
</code></pre>

<pre><code>SPOTIFY Disclaimer
This product uses a SPOTIFY API but is not endorsed, certified or otherwise
approved in any way by Spotify. Spotify is the registered trade mark of the
Spotify Group.
</code></pre>
      </section>
      <footer>
        <p>Project maintained by <a href="https://github.com/mikaelbr">mikaelbr</a></p>
        <p><small>Hosted on GitHub Pages &mdash; Theme by <a href="http://twitter.com/#!/michigangraham">mattgraham</a></small></p>
      </footer>
    </div>
    <!--[if !IE]><script>fixScale(document);</script><!--<![endif]-->
              <script type="text/javascript">
            var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
            document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
          </script>
          <script type="text/javascript">
            try {
              var pageTracker = _gat._getTracker("UA-16316654-3");
            pageTracker._trackPageview();
            } catch(err) {}
          </script>

  </body>
</html>