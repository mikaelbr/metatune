<?php

namespace MetaTune\Utils;


class CacheRequest {

    public static $cacheDir;
    public static $useCache;

    public static function request($url, $file, $hours = 24)
    {

        $content = self::getCache($file, $hours);

        if ($content)
        {
            return $content;
        }

        $content = self::getCurlData($url);

        self::saveCache($file, $content);

        return $content;
    }

    private static function getCurlData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    } 

    private static function getCache($file, $hours = 24) 
    {
        if (!self::$useCache || !is_dir(self::$cacheDir))
        {
            return false;
        }

        if (!self::$useCache || !file_exists($file))
        {
            return false;
        }


        $currentTime = time(); 
        $expireTime = $hours * 60 * 60; 
        $fileTime = filemtime($file);

        if ($currentTime - $expireTime < $fileTime) 
        {
            return file_get_contents($file);
        } 
        
        // try to remove old file
        unlink($file);
        return false; // Expired. Get new.
    }


    private static function saveCache($filename, $content)
    {
        if (self::$useCache && is_dir(self::$cacheDir))
        {
            return file_put_contents($filename, $content);
        }
    }

}