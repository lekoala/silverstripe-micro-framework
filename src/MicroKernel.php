<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Core\CoreKernel;
use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;

/**
 * This updated kernel allows the following features
 *
 * - Do not require a valid database to run
 * - Ensure isFlushed is available (private by default)
 */
class MicroKernel extends CoreKernel
{
    /**
     * Indicates whether the Kernel has been flushed on boot
     * Unitialized before boot
     *
     * @var bool
     */
    private $flush;

    public function __construct($basePath)
    {
        parent::__construct($basePath);
    }

    public function boot($flush = false)
    {
        $this->flush = $flush;

        $this->bootPHP();
        $this->bootManifests($flush);
        $this->bootErrorHandling();
        if (self::usesDatabase()) {
            $this->bootDatabaseEnvVars();
        }
        $this->bootConfigs();
        if (self::usesDatabase()) {
            $this->bootDatabaseGlobals();
        }
        if (self::usesDatabase()) {
            $this->validateDatabase();
            Security::force_database_is_ready(true);
        }

        $this->booted = true;
    }

    /**
     * @return bool
     */
    public static function usesDatabase()
    {
        return Environment::getEnv('SS_DATABASE_NAME') ? true : false;
    }

    /**
     * Returns whether the Kernel has been flushed on boot
     *
     * @return bool|null null if the kernel hasn't been booted yet
     */
    public function isFlushed()
    {
        return $this->flush;
    }
}
