<?php

namespace LeKoala\MicroFramework;

use SilverStripe\ORM\DatabaseAdmin;

/**
 * Avoid errors on dev build without db
 */
class MicroDatabaseAdmin extends DatabaseAdmin
{
    public function doBuild($quiet = false, $populate = true, $testMode = false)
    {
        if (!MicroKernel::usesDatabase()) {
            echo '<p><b>Skipping database</b></p>';
            return;
        }
        return parent::doBuild($quiet, $populate, $testMode);
    }
}
