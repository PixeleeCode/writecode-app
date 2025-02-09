<?php

namespace App\DataFixtures\Provider;

use Faker\Provider\Base as BaseProvider;
use InvalidArgumentException;
use RuntimeException;

class PictureUserProvider extends BaseProvider
{
    /**
     * @return mixed
     */
    public static function picture(?string $dir = null, ?string $gender = 'women')
    {
        $randomInt = static::randomNumber(2, true);
        $url = "https://randomuser.me/api/portraits/$gender/$randomInt.jpg";

        return self::fetchImage($url, $dir);
    }

    private static function fetchImage(string $url, ?string $dir = null)
    {
        $dir = $dir ?? sys_get_temp_dir();
        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name.'.jpg';
        $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

        // save file
        if (function_exists('curl_exec')) {
            // use cURL
            $fp = fopen($filepath, 'wb');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $success = curl_exec($ch) && 200 === curl_getinfo($ch, CURLINFO_HTTP_CODE);
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);

                // could not contact the distant URL or HTTP error - fail silently.
                return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return str_replace($dir.'/', '', $filename);
    }
}
