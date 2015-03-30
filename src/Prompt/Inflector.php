<?php

namespace CL\ComposerInit\Prompt;

class Inflector
{
    /**
     * Makes an underscored or dashed phrase human-readable.
     *
     *     $str = $inflector->humanize('kittens-are-cats'); // "kittens are cats"
     *     $str = $inflector->humanize('dogs_as_well');     // "dogs as well"
     *
     * @param  string $str phrase to make human-readable
     * @return string
     */
    public function humanize($string)
    {
        return preg_replace('/[_-]+/', ' ', trim($string));
    }

    /**
     * @param  string $str
     * @return string
     */
    public function title($string)
    {
        return ucwords(self::humanize($string));
    }

    /**
     * @param  string $str
     * @return string
     */
    public function titlecase($string)
    {
        return str_replace(' ', '', self::title($string));
    }

    /**
     * @param  string $str
     * @return string
     */
    public function initials($string)
    {
        $title = self::title($string);

        $initials = str_replace(' ', '', preg_replace('/[a-z]/', '', $title));

        if (strlen($initials) == 1) {
            $initials .= strtoupper($title[1]);
        }

        return $initials;
    }
}