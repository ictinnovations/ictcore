<?php

namespace ICT\Core\Api\User;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\User\Permission;
use ICT\Core\User\Role;

class RoleApi extends Api
{

  /**
   * Create a new role
   *
   * @url POST /role/create
   */
  public function create($data = array())
  {
    $this->_authorize('role_create');

    $oRole = new Role();
    $this->set($oRole, $data);

    if ($oRole->save()) {
      return $oRole->role_id;
    } else {
      throw new CoreException(417, 'Role creation failed');
    }
  }

  /**
   * List all available roles
   *
   * @url GET /role/list
   * @url POST /role/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('role_list');
    return Role::search($data);
  }

  /**
   * Gets the role by id
   *
   * @url GET /role/$role_id
   */
  public function read($role_id)
  {
    $this->_authorize('role_read');

    $oRole = new Role($role_id);
    return $oRole;
  }

  /**
   * Update existing role
   *
   * @url POST /role/$role_id/update
   * @url PUT /role/$role_id/update
   */
  public function update($role_id, $data = array())
  {
    $this->_authorize('role_update');

    $oRole = new Role($role_id);
    $this->set($oRole, $data);

    if ($oRole->save()) {
      return $oRole;
    } else {
      throw new CoreException(417, 'Role update failed');
    }
  }

  /**
   * Create a new role
   *
   * @url GET /role/$role_id/delete
   * @url DELETE /role/$role_id/delete
   */
  public function remove($role_id)
  {
    $this->_authorize('role_delete');

    $oRole = new Role($role_id);

    $result = $oRole->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Role delete failed');
    }
  }

  /**
   * Allow / authorize role for a certain permission
   *
   * @url GET /role/$role_id/allow/$permission_id
   */
  public function allow($role_id, $permission_id)
  {
    $this->_authorize('role_update');
    $this->_authorize('permission_update');

    $oRole = new Role($role_id);
    $oPermission = new Permission($permission_id);
    $oRole->permission_assign($oPermission);
    return $oRole->save();
  }

  /**
   * Disallow / prevent a role form using a certain permission
   *
   * @url GET /role/$role_id/disallow/$permission_id
   */
  public function disallow($role_id, $permission_id)
  {
    $this->_authorize('role_update');
    $this->_authorize('permission_update');

    $oRole = new Role($role_id);
    $oPermission = new Permission($permission_id);
    $oRole->permission_unassign($oPermission);
    return $oRole->save();
  }

}