<?php
namespace Codex\Core\Addons;

use MyCLabs\Enum\Enum;

class AddonType extends Enum
{
    const DOCUMENT = 'document';
    const FILTER = 'filter';
    const HOOK = 'hook';

    public static function make($name)
    {
        return new static($name);
    }
}
