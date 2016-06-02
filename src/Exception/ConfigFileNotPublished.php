<?php
/**
 * Part of the Codex Project PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Codex\Exception;


/**
 * This is the class ConfigFileNotPublished.
 *
 * @package        Codex\Core\Next;
 * @author         Sebwite
 * @copyright      Copyright (c) 2015, Sebwite. All rights reserved
 *
 */
class ConfigFileNotPublished extends CodexException
{
    public function filePath($path)
    {
        return new static("Config file [{$path}] not published");
    }
}
