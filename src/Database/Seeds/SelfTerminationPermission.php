<?php

namespace SelfTermination\Sprinkle\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
//use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;


/**
 * Seeder for the default permissions.
 */
class SelfTerminationPermission implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run():void
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
        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
            }
        }
    }
}
