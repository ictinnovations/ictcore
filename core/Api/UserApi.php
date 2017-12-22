<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\User;
use ICT\Core\User\Permission;
use ICT\Core\User\Role;

class UserApi extends Api
{

  /**
   * Create a new user
   *
   * @url POST /users
   */
  public function create($data = array())
  {
    $this->_authorize('user_create');

    $oUser = new User();
    $this->set($oUser, $data);

    if ($oUser->save()) {
      return $oUser->user_id;
    } else {
      throw new CoreException(417, 'User creation failed');
    }
  }

  /**
   * List all available users
   *
   * @url GET /users
   */
  public function list_view($query = array())
  {
    $this->_authorize('user_list');
    return User::search((array)$query);
  }

  /**
   * Gets the user by id
   *
   * @url GET /users/$user_id
   */
  public function read($user_id)
  {
    $this->_authorize('user_read');

    $oUser = new User($user_id);
    return $oUser;
  }

  /**
   * Update existing user
   *
   * @url PUT /users/$user_id
   */
  public function update($user_id, $data = array())
  {
    $this->_authorize('user_update');

    $oUser = new User($user_id);
    $this->set($oUser, $data);

    if ($oUser->save()) {
      return $oUser;
    } else {
      throw new CoreException(417, 'User update failed');
    }
  }

  /**
   * Create a new user
   *
   * @url DELETE /users/$user_id
   */
  public function remove($user_id)
  {
    $this->_authorize('user_delete');

    $oUser = new User($user_id);

    $result = $oUser->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'User delete failed');
    }
  }

  /**
   * Allow / authorize user for a certain permission
   *
   * @url PUT /users/$user_id/permissions/$permission_id
   */
  public function allow($user_id, $permission_id)
  {
    $this->_authorize('user_update');
    $this->_authorize('permission_create');

    $oUser = new User($user_id);
    $oPermission = new Permission($permission_id);
    $oUser->permission_assign($oPermission);
    return $oUser->save();
  }

  /**
   * Disallow / prevent a user form using a certain permission
   *
   * @url DELETE /users/$user_id/permissions/$permission_id
   */
  public function disallow($user_id, $permission_id)
  {
    $this->_authorize('user_update');
    $this->_authorize('permission_delete');

    $oUser = new User($user_id);
    $oPermission = new Permission($permission_id);
    $oUser->permission_unassign($oPermission);
    return $oUser->save();
  }

  /**
   * Assign a role to user
   *
   * @url PUT /users/$user_id/roles/$role_id
   */
  public function assign($user_id, $role_id)
  {
    $this->_authorize('user_update');
    $this->_authorize('role_update');

    $oUser = new User($user_id);
    $oRole = new Role($role_id);
    $oUser->role_assign($oRole);
    return $oUser->save();
  }

  /**
   * Remove certain role from user
   *
   * @url DELETE /users/$user_id/roles/$role_id
   */
  public function unassign($user_id, $role_id)
  {
    $this->_authorize('user_update');
    $this->_authorize('role_update');

    $oUser = new User($user_id);
    $oRole = new Role($role_id);
    $oUser->role_unassign($oRole);
    return $oUser->save();
  }

  protected static function rest_include()
  {
    return 'Api/User';
  }
}
