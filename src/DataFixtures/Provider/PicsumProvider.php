<?php

namespace App\DataFixtures\Provider;

use Faker\Provider\Base as BaseProvider;
use InvalidArgumentException;
use RuntimeException;

class PicsumProvider extends BaseProvider
{
    /**
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
     */
    public static function picsum(?string $dir = null, int $width = 640, int $height = 480, bool $gray = false, bool $sepia = false)
    {
        $url = "https://placeimg.com/{$width}/{$height}/any";

        if ($gray && !$sepia) {
            $url .= '/grayscale';
        }

        if ($sepia && !$gray) {
            $url .= '/sepia';
        }

        return self::fetchImage($url, $dir);
    }

    private static function fetchImage(string $url, ?string $dir = null)
    {
        $dir = $dir ?? sys_get_temp_dir();
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name.'.jpg';
        $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

        if (function_exists('curl_exec')) {
            $fp = fopen($filepath, 'wb');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $success = curl_exec($ch) && 200 === curl_getinfo($ch, CURLINFO_HTTP_CODE);
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);

                return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            $success = copy($url, $filepath);
        } else {
            return new RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return str_replace($dir.'/', '', $filename);
    }
}
