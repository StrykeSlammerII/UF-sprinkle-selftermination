<?php

namespace SelfTermination\Sprinkle\Controller\User;

use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Admin\Controller\User\UserDeleteModal;


// code is the same as UserDeleteModal, just point it at a different page
// and use the self_termination permission slug
// and authenticate vs self; $user will be empty
class ConfirmTerminationAction extends UserDeleteModal {
    
    /** @var string Page template */
    protected string $template = 'modals/confirm-termination.html.twig';
    
    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
        if (!$this->authenticator->checkAccess('self_termination', ['user' => $this->authenticator->user()])) {
            throw new ForbiddenException();
        }
    }
}
