<?php

namespace SelfTermination\Sprinkle\Routes;

use Slim\App;

use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;
use UserFrosting\Sprinkle\Admin\Middlewares\UserInjector;

use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use Slim\Routing\RouteCollectorProxy;

use SelfTermination\Sprinkle\Controller\AppController;
use SelfTermination\Sprinkle\Controller\User\SelfTerminationAction;
use SelfTermination\Sprinkle\Controller\User\UserRedactAction;
use SelfTermination\Sprinkle\Controller\User\ConfirmTerminationAction;

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
/*
	  // 4 base routes for the skeleton:
	  $app->get('/', [AppController::class, 'pageIndex'])->setName('index');
	  $app->get('/about', [AppController::class, 'pageAbout'])->setName('about');
        $app->get('/legal', [AppController::class, 'pageLegal'])->setName('legal');
        $app->get('/privacy', [AppController::class, 'pagePrivacy'])->setName('privacy');
*/

    // from the user's self-termination form.
	  $app->delete('/selftermination', SelfTerminationAction::class)
		->add(AuthGuard::class)->add(NoCache::class);

    // modal confirmation form
	  $app->group('/modals/users', function (RouteCollectorProxy $group) {
		$group->get('/confirm-termination', ConfirmTerminationAction::class);
	  })->add(AuthGuard::class)->add(NoCache::class);

/*
    // overwrite DELETE api/users/u/{user_name}
	  $app->group('/api/users', function (RouteCollectorProxy $group) {
            $group->delete('/u/{user_name}', UserRedactAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.delete');
	  })->add(AuthGuard::class)->add(NoCache::class);
*/
    }
}
