<?php

use UserFrosting\Sprinkle\Core\Util\NoCache;

use Slim\App;

// both the admin-side override and the user-side self-termination-request use new code
$app->group('/api/users', function () {
    // override Admin sprinkle delete_user
    $this->delete('/u/{user_name}', 'UserFrosting\Sprinkle\Selftermination\Controller\SelfTerminationController:delete');
    
})->add('authGuard')->add(new NoCache());

// from the user's self-termination form.
$app->delete('/selftermination', 'UserFrosting\Sprinkle\Selftermination\Controller\SelfTerminationController:selfTermination')
	  ->add('authGuard')->add(new NoCache());

// modal confirmation form
$app->group('/modals/users', function () {
    $this->get('/confirm-termination', 'UserFrosting\Sprinkle\Selftermination\Controller\SelfTerminationController:getModalConfirmTermination');
})->add('authGuard')->add(new NoCache());
