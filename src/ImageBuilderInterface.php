<?php

namespace Michaelr0\LOIA;

interface MakeImageBuilder
{
    /**
     * Get an ImageBuilder instance for a particular Openwrt version.
     */
    public static function make(string $version): ImageBuilder;
}
