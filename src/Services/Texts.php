<?php


namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Parsedown;

/**
 * Class Texts
 * @package App\Services
 */
class Texts
{
    protected HTMLPurifier $purifier;

    protected Parsedown $parsedown;

    /**
     * Texts constructor.
     * @param string $kernelRootDirectory
     */
    public function __construct($kernelRootDirectory)
    {
        $config = HTMLPurifier_Config::create(array('Cache.SerializerPath' => $kernelRootDirectory));
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'data-code', 'Text');
        $this->purifier = new HTMLPurifier($config);
        $this->parsedown = new Parsedown();
    }

    /**
     * Returns a substring of $string that is $max_length length max and doesn't split
     * a word or a html tag
     *
     * @param string $string
     * @param int $max_length
     * @return string
     */
    public function truncate($string, $max_length)
    {
        $response = '';
        $token = '';

        $string = preg_replace('/\s+/', ' ', $string);

        while (strlen($token.$string) > 0 && strlen($response.$token) < $max_length) {
            $response = $response.$token;
            $matches = [];

            if (preg_match('/^(<.+?>)(.*)/', $string, $matches)) {
                $token = $matches[1];
                $string = $matches[2];
            } elseif (preg_match('/^([^\s]+\s*)(.*)/', $string, $matches)) {
                $token = $matches[1];
                $string = $matches[2];
            } else {
                $token = $string;
                $string = '';
            }
        }
        if (strlen($token) > 0) {
            $response = $response . '[&hellip;]';
        }

        return $response;
    }

    /**
     * Returns the processed version of a markdown text
     * @param string $string
     * @return string
     */
    public function markdown($string)
    {
        return $this->purify($this->imgResponsive($this->transform($string)));
    }

    /**
     * removes any dangerous code from a HTML string
     * @param string $string
     * @return string
     */
    public function purify($string)
    {
        return $this->purifier->purify($string);
    }

    /**
     * turns a Markdown string into a HTML string
     * @param string $string
     * @return string
     */
    public function transform($string)
    {
        return $this->parsedown->text($string);
    }

    /**
     * adds class="img-responsive" to every <img> tag
     * @param string $string
     * @return string
     */
    public function imgResponsive($string)
    {
        return preg_replace('/<img/', '<img class="img-responsive"', $string);
    }

    /**
     * Transforms the string into a valid filename, lower-case, no spaces, pure ASCII, etc.
     * @param string $filename
     * @return string
     */
    public function slugify($filename)
    {
        $filename = mb_ereg_replace('[^\w\-]', '-', $filename);
        $filename = iconv('utf-8', 'us-ascii//TRANSLIT', $filename);
        $filename = preg_replace('/[^\w\-]/', '', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        $filename = trim($filename, '-');
        $filename = strtolower($filename);
        return $filename;
    }
}
