<?php

namespace SelfTermination\Sprinkle\Controller\User;

use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Admin\Controller\User\UserDeleteModal;


// code is the same as UserDeleteModal, just point it at a different page
// and use the self_termination permission slug
/**
 * Renders the modal form to confirm user deletion.
 *
 * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
 * This page requires authentication.
 * Request type: GET
 *
 * @throws ValidationException
 * @throws AccountNotFoundException If user is not found
 * @throws ForbiddenException       If user is not authorized to access page
 * @throws AccountException         If trying to delete the master account
 */
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
        if (!$this->authenticator->checkAccess('self_termination', ['user' => $user])) {
            throw new ForbiddenException();
        }
    }
}