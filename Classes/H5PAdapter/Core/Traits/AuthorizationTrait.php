<?php

namespace Sandstorm\NeosH5P\H5PAdapter\Core\Traits;

/**
 * Handles everything to-do with permissions for users to execute a certain task.
 */
trait AuthorizationTrait
{
    /**
     * Is the current user allowed to update libraries?
     *
     * @return boolean
     *  TRUE if the user is allowed to update libraries
     *  FALSE if the user is not allowed to update libraries
     */
    public function mayUpdateLibraries()
    {
        // TODO: Implement mayUpdateLibraries() method.
    }

    /**
     * Check if user has permissions to an action
     *
     * @method hasPermission
     * @param  [H5PPermission] $permission Permission type, ref H5PPermission
     * @param  [int]           $id         Id need by platform to determine permission
     * @return boolean
     */
    public function hasPermission($permission, $id = NULL)
    {
        // TODO: Implement hasPermission() method.
    }
}
