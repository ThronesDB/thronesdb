<?php

namespace AppBundle\Controller;

/**
 * Commonly used utility function for dealing with locale-specific twig template.
 * Mix this into the controllers as applicable.
 *
 * Trait LocaleAwareTemplating
 * @package AppBundle\Controller
 */
trait LocaleAwareTemplating
{
    /**
     * Finds and returns the appropriate locale-specific view path for a given language and template name.
     *
     * Example:
     * Passing in `about` as template base name, and `de` as language code will return
     * `AppBundle:Default:about.de.html.twig`.
     *
     * You can get the lang code for the currently active locale by invoking `Request::getLocale()` from a controller action.
     *
     * @param string $templateBaseName
     * @param string $langCode
     * @param string $bundleName
     * @param string $controllerName
     * @param string $format
     * @param string $engine
     * @return string
     * @todo: This is hinky, find a better solution. [ST 2019/01/12]
     */
    protected function getLocaleSpecificViewPath(
        $templateBaseName,
        $langCode = '',
        $bundleName = 'AppBundle',
        $controllerName = 'Default',
        $format = 'html',
        $engine = 'twig'
    ) {
        $tokens = [];
        $tokens[] = $templateBaseName;
        $tokens[] = $langCode;
        $tokens[] = $format;
        $tokens[] = $engine;

        // rm any blank strings from tokens list
        $tokens = array_values(array_filter($tokens, function($fragment) {
            return (trim($fragment) !== '');
        }));

        return "${bundleName}:${controllerName}:". implode('.', $tokens);
    }
}
