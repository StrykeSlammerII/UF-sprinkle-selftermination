<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Selftermination\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
//use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
//use UserFrosting\Sprinkle\Core\Facades\Seeder;

/**
 * Seeder for the default permissions.
 */
class SelfTerminationPermission extends BaseSeed
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // Get and save permissions
        $permissions = $this->getPermissions();
        $this->savePermissions($permissions);
	  
    }

    /**
     * @return array Permissions to seed
     */
    protected function getPermissions()
    {
        return [
            'self_termination' => new Permission([
                'slug'        => 'self_termination',
                'name'        => 'Self-termination',
                'conditions'  => '!is_master(self.id)',
                'description' => "Delete one's own account",
            ])
        ];
    }

    /**
     * Save permissions.
     *
     * @param array $permissions
     */
    protected function savePermissions(array &$permissions)
    {
        foreach ($permissions as $slug => $permission) {

            // Trying to find if the permission already exist
            $existingPermission = Permission::where(['slug' => $permission->slug, 'conditions' => $permission->conditions])->first();

            // Don't save if already exist, use existing permission reference
            if ($existingPermission == null) {
                $permission->save();
            } else {
                $permissions[$slug] = $existingPermission;
            }
        }
    }
}
