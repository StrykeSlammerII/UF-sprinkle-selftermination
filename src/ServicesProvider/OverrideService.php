<?php

namespace SelfTermination\Sprinkle\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;

use UserFrosting\Sprinkle\Admin\Controller\User\UserDeleteAction;
use SelfTermination\Sprinkle\Controller\SelfTerminationAction;
use UserFrosting\Sprinkle\Admin\Routes\UsersRoutes;
use SelfTermination\Sprinkle\Routes\OverrideRoutes;


class OverrideService implements ServicesProviderInterface
{
    public function register(): array
    {
	  // overwrite the Admin Sprinkle's method, to add redaction.
        return [
		UserDeleteAction::class => \DI\autowire(SelfTerminationAction::class),
		UsersRoutes::class => \DI\autowire(OverrideRoutes::class),
        ];
    }
}