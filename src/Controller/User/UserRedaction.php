<?php

namespace SelfTermination\Sprinkle\Controller\User;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;

/**
 * Trait containing redaction code for deleting UF5 users.
 * Should be called by SelfTerminationAction
 * [so it doesn't really need to be a trait?
 *  as we're also overriding admin's UserDeleteAction with SelfTerminationAction]
 *
 * @author strike
 */
trait UserRedaction {

    #[\DI\Attribute\Inject]
    protected \UserFrosting\Sprinkle\Core\Log\DebugLoggerInterface $logger;
    
    /// TODO:  This does not yet remove entry from role_users table.
    /// If there are only a few users of a given role, this may help narrow down which user was which.
    /// Under some use cases, this may reveal more info than is intended.
    
    // Also please note: activity log for admin deletes RETAINS the original user name, "User Admin_Alice deleted the account for User_Bob"
    // Whereas self-deletion activity log only shows userID "User 15442 self-deleted."
    
    // Additional note for clarity: $targetUser and $currentUser will be the same when self-terminating
    protected function redactData(UserInterface $targetUser, UserInterface $currentUser)
    {
//	  $this->logger->debug("Target User:");
//	  $this->logger->debug($targetUser);
//	  $this->logger->debug("Current User:");
//	  $this->logger->debug($currentUser);
	  
	  $userName = $targetUser->user_name;

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($targetUser, $userName, $currentUser) {
			
		// append UID where needed so we don't have collisions with other 'redacted's
		$targetUser->email = "redacted".$targetUser->id."@test.com";
		$targetUser->user_name = "redacted".$targetUser->id; 
		$targetUser->first_name = "redacted";
		$targetUser->last_name = "redacted";
		// $user->password = ""; // field is varchar(255), non-nullable. Should become a (short?) random (garbage) set of characters that will NOT be matched by the password crypto method.
		// Blowfish results in 60 character output; 
		// Argon output can be set from 4 to (2^32)-1 bytes.
		    // PHP strings are 1 char = 1 byte...
			  // BUT there are a lot of non-ASCII bytes.
			  // bin2hex gives 2 hex characters for each binary byte, so that should work--right?
		    // ...so a 2char entry here should fail both Blowfish and Argon verifications
		    // using random bytes means that if somehow a single 2char "password" can be verified, it will only provide access to a subset of dead accounts, not all.
		    // ( In the case of a later password hash allowing for shorter outputs. Shorter is BAD, so it /shouldn't/ happen, but still. )
		
		$targetUser->password = bin2hex(random_bytes(1));
		$targetUser->save();
		
		// not deleting the activity log, as that only includes the username. Activity logs are retained subject to other constraints.
		$actor_id = $currentUser->id;
		$message = "User {$currentUser->user_name} redacted and deleted the account for {$userName}.";
		if ($targetUser == $currentUser) {
		    $message = "User " . $targetUser->id. " self-deleted.";
		}
		
		// do this before deleting $targetUser, so the transaction does not complete if the logger fails.
		$this->userActivityLogger->info($message, [
		    'type' => 'account_delete',
		    'user_id' => $actor_id,
		]);
		
            $targetUser->delete(); //soft delete		
		unset($targetUser);
        }); // end transaction
	  
    }   
}
