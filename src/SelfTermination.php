<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace SelfTermination\Sprinkle;

//use SelfTermination\Sprinkle\Bakery\HelloCommand;
//use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\BakeryRecipe;
//use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
//use UserFrosting\Theme\AdminLTE\AdminLTE;
use SelfTermination\Sprinkle\ServicesProvider\OverrideService;
use SelfTermination\Sprinkle\Database\Seeds\SelfTerminationPermission;
use SelfTermination\Sprinkle\Routes\MyRoutes;
use SelfTermination\Sprinkle\Routes\OverrideRoutes;

class SelfTermination implements
    SprinkleRecipe,
//    BakeryRecipe,
    SeedRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'SelfTermination Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
//            Core::class,
//            Account::class,
            Admin::class,
//		AdminLTE::class
        ];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @return string[]
     */
    public function getRoutes(): array
    {
        return [
            MyRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions files.
     *
     * @return string[]
     */
    public function getServices(): array
    {
        return [
            OverrideService::class,
        ];
    }
    
    public function getSeeds(): array
    {
        return [
            SelfTerminationPermission::class,
        ];
    }
}
