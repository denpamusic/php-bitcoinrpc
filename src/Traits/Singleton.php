<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

trait Singleton
{
    /**
     * Singleton instance.
     *
     * @var self
     */
    protected static $instance = null;

    /**
     * Gets singleton instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Clears singleton instance.
     *
     * @return void
     */
    public static function clearInstance(): void
    {
        self::$instance = null;
    }
}
