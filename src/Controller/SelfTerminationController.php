<?php

/*
 * Code is fully owned and copyrighted Strike & Co. Keep your fingers off my stuff.
 */

namespace UserFrosting\Sprinkle\Selftermination\Controller;

//use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
//use UserFrosting\Sprinkle\Account\Facades\Password;
//use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
//use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;

use UserFrosting\Sprinkle\Admin\Controller\UserController;

use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Description of UserController
 *
 * @author strike
 */
class SelfTerminationController extends UserController {
    
    // below overrides same route from Admin sprinkle, using (new) standardized RedactData routine.
    // [see https://learn.userfrosting.com/recipes/extending-the-user-model#override-just-a-few-controllers]
    
    
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
     * @param Request  $request
     * @param Response $response
     * @param string[] $args
     *
     * @throws NotFoundException   If user is not found
     * @throws ForbiddenException  If user is not authorized to access page
     * @throws BadRequestException
     */
    public function delete(Request $request, Response $response, array $args)
    {
        $user = $this->getUserFromParams($args);

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException();
        }

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'delete_user', [
            'user' => $user,
        ])) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($user->id == $config['reserved_user_ids.master']) {
            $e = new BadRequestException();
            $e->addUserMessage('DELETE_MASTER');

            throw $e;
        }
	  
	  $userName = $user->user_name;

	  $this->redactData($user, $currentUser);

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'DELETION_SUCCESSFUL', [
            'user_name' => $userName,
        ]);

        return $response->withJson([], 200);
    }
    

    /**
     * Processes the request to delete an existing user.
     *
     * Deletes the specified user, removing any existing associations.
     * Before doing so, checks that:
     * 1. You are not trying to delete the master account;
     * 2. You have permission to delete the target user's account.
     * This route requires authentication.
     *
     * 
     * This should redirect to the main page on success.
     * 
     * Request type: DELETE
     *
     * @param Request  $request
     * @param Response $response
     * @param string[] $args
     *
     * @throws NotFoundException   If user is not found
     * @throws ForbiddenException  If user is not authorized to access page
     * @throws BadRequestException
     */
    public function selfTermination(Request $request, Response $response, array $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
  
        // Access-controlled page

	  if (!$authorizer->checkAccess($currentUser, 'self_termination')) {

		throw new ForbiddenException();
	  }

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($currentUser->id == $config['reserved_user_ids.master']) {
            $e = new BadRequestException();
            $e->addUserMessage('DELETE_MASTER');

            throw $e;
        }

	  $userName = $currentUser->user_name;
	  
	  $this->redactData($currentUser, $currentUser);
	  
	  // also delete user id from session cookie, so we don't hit Authentication errors later.
	  $session = $this->ci->session;
	  unset($session['account.current_user_id']);
	  

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;

        $ms->addMessageTranslated('success', 'DELETION_SUCCESSFUL', [
            'user_name' => $userName,
        ]);

	  // the user may never see the alert ;-; but at least we can try. If it's stored in the session (rather than cache) then this should work.
	  
        return $response->withHeader('UF-Redirect', '/');
    }

    /// TODO:  This does not yet remove entry from role_users table.
    /// If there are only a few users of a given role, this may help narrow down which user was which.
    /// Under some use cases, this may reveal more info than is intended.
    
    // Also please note: activity log for admin deletes RETAINS the original user name, "User Admin_Alice deleted the account for User_Bob"
    // Whereas self-deletion activity log only shows userID "User 15442 self-deleted."
    
    private function redactData($user, $currentUser)
    {
	  $userName = $user->user_name;

        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($user, $userName, $currentUser) {
			
		// append UID where needed so we don't have collisions with other 'redacted's
		$user->email = "redacted".$user->id."@test.com";
		$user->user_name = "redacted".$user->id; 
		$user->first_name = "redacted";
		$user->last_name = "redacted";
		// $user->password = ""; // field is varchar(255), non-nullable. Should become a (short?) random (garbage) set of characters that will NOT be matched by the password crypto method.
		// Blowfish results in 60 character output; 
		// Argon output can be set from 4 to (2^32)-1 bytes.
		    // PHP strings are 1 char = 1 byte...
			  // BUT there are a lot of non-ASCII bytes.
			  // bin2hex gives 2 hex characters for each binary byte, so that should work--right?
		    // ...so a 2char entry here should fail both Blowfish and Argon verifications
		    // using random bytes means that if somehow a single 2char "password" can be verified, it will only provide access to a subset of dead accounts, not all.
		    // ( In the case of a later password hash allowing for shorter outputs. Shorter is BAD, so it /shouldn't/ happen, but still. )
		
		$user->password = bin2hex(random_bytes(1));
		
		$user->save();
		
		// not deleting the activity log, as that only includes the username. Activity logs are retained, subject to the usual time constraints.
		
		if ($user == $currentUser) {
		    $message = "User {$user->id} self-deleted.";
		} else {
		    $message = "User {$currentUser->user_name} deleted the account for {$userName}.";
		}
		
            // Create activity record
            $this->ci->userActivityLogger->info($message, [
                'type'    => 'account_delete',
                'user_id' => $currentUser->id,
            ]);

            $user->delete(); //soft delete		
		unset($user);
        });
    }
    
    // similarly edited from parent class
    /**
     * Renders the modal form to confirm self-deletion.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     *
     * @param Request  $request
     * @param Response $response
     * @param string[] $args
     *
     * @throws NotFoundException   If user is not found
     * @throws ForbiddenException  If user is not authorized to access page
     * @throws BadRequestException
     */
    public function getModalConfirmTermination(Request $request, Response $response, array $args)
    {
	  // user is current user; no need to have other params
//        // GET parameters
//        $params = $request->getQueryParams();
//
//        $user = $this->getUserFromParams($params);
//
//        // If the user doesn't exist, return 404
//        if (!$user) {
//            throw new NotFoundException();
//        }

        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;

        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
	  
        // Access-controlled page
	  // (check whether we are permitted to delete ourselves)

	  if (!$authorizer->checkAccess($currentUser, 'self_termination')) {

		throw new ForbiddenException();
	  }

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($currentuser->id == $config['reserved_user_ids.master']) {
            $e = new BadRequestException();
            $e->addUserMessage('DELETE_MASTER');

            throw $e;
        }

        return $this->ci->view->render($response, 'modals/confirm-termination.html.twig');
    }
    
    
}