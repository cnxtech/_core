<?php
/**
 * Part of the Sebwite PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Codex\Core\Addon\Traits;


use Codex\Core\Contracts\Addon\Bootable;
use Codex\Core\Contracts\Addon\Hookable;
use Codex\Core\Exception\ContractMissingException;

trait BootableTrait
{

    protected static $booted = [ ];

    /**
     * Check if the model needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        $class = get_class($this);

        if ( !class_implements($class, Hookable::class) ) {
            throw ContractMissingException::in($this)->missingContract(Hookable::class);
        }
        if ( !class_implements($class, Bootable::class) ) {
            throw ContractMissingException::in($this)->missingContract(Bootable::class);
        }

        if ( !isset(static::$booted[ $class ]) ) {
            static::$booted[ $class ] = true;

            $this->emit('booting', false);

            static::boot();

            $this->emit('booted', false);
        }
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the model.
     *
     * @return void
     */
    protected static function bootTraits()
    {
        foreach ( class_uses_recursive(get_called_class()) as $trait ) {
            if ( method_exists(get_called_class(), $method = 'boot' . class_basename($trait)) ) {
                forward_static_call([ get_called_class(), $method ]);
            }
        }
    }

    /**
     * Clear the list of booted models so they will be re-booted.
     *
     * @return void
     */
    public static function clearBooted()
    {
        static::$booted = [ ];
    }

    public function __wakeup()
    {
        $this->bootIfNotBooted();
    }

    public static function booting($callback)
    {
        static::listen('booting', $callback);

        return static::class;
    }

    public static function booted($callback)
    {
        static::listen('booted', $callback);

        return static::class;
    }
}
