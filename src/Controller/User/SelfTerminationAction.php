<?php

namespace SelfTermination\Sprinkle\Controller\User;

//use Psr\Http\Message\ResponseInterface as Response;
//use Psr\Http\Message\ServerRequestInterface as Request;
//use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
//use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;

use UserFrosting\Sprinkle\Admin\Controller\User\UserDeleteAction;

/**
 * Processes the request to delete an existing user.
 *
 * Deletes the specified user, removing any existing associations.
 * Before doing so, checks that:
 * 1. You are not trying to delete the master account;
 * 2. You have permission to delete the target user's account.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: DELETE
 *
 * @throws ValidationException
 * @throws AccountNotFoundException If user is not found
 * @throws ForbiddenException       If user is not authorized to access page
 * @throws AccountException
 * edits by strike
 */
class SelfTerminationAction extends UserDeleteAction {
    
    use UserRedaction; //includes debug logger
    
    // below overrides route from Admin sprinkle, using (new) standardized RedactData routine.
    // [see https://learn.userfrosting.com/recipes/extending-the-user-model#override-just-a-few-controllers]
    
    // $user should be empty, as we're deleting ourself rather than a target
    protected function handle(UserInterface $user): void
    {
	  // Alias current user for convenience. Won't be null, since it's AuthGuarded.
        /** @var UserInterface $currentUser */
        $currentUser = $this->authenticator->user();
	  
        // Access-controlled page based on currentUser.
        $this->validateAccess($currentUser);

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($currentUser->id === $this->config->getInt('reserved_user_ids.master')) {
            $e = new AccountException();
            $e->setTitle('DELETE_MASTER');

            throw $e;
        }
	  
	  $userName = $currentUser->user_name;

	  $this->redactData($currentUser, $currentUser);

        $this->alert->addMessageTranslated('success', 'DELETION_SUCCESSFUL', [
            'user_name' => $userName,
        ]);
	  
	  // don't forget to logout the (now non-existant) user!
	  // leaving them logged in will cause "Account not found" errors all over the place for that client
	  $this->authenticator->logout();
    }
    
    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
	  $slug = 'self_termination';
	  
        if (!$this->authenticator->checkAccess($slug, ['user' => $user])) {
            throw new ForbiddenException();
        }
    }
    
}