<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\User;
use ICT\Core\User\Permission;

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
   * Update user passwd
   *
   * @url PUT /users/$user_id/password
   */
  public function update_password($user_id, $data = array())
  {
    $this->_authorize('user_password');

    $oUser = new User($user_id);
    $oUser->password = $data['password'];

    if ($oUser->save()) {
      return $oUser;
    } else {
      throw new CoreException(417, 'User password update failed');
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
   * Permission list of user
   *
   * @url GET /users/$user_id/permissions
   */
  public function permission_list_view($user_id, $query = array())
  {
    $this->_authorize('user_list');
    $this->_authorize('permission_list');

    $oUser = new User($user_id);
    return $oUser->search_permission((array)$query);
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
    $oUser->permission_assign($permission_id);
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
    $oUser->permission_unassign($permission_id);
    return $oUser->save();
  }

  /**
   * Role list of user
   *
   * @url GET /users/$user_id/roles
   */
  public function role_list_view($user_id, $query = array())
  {
    $this->_authorize('user_list');
    $this->_authorize('role_list');

    $oUser = new User($user_id);
    return $oUser->search_role((array)$query);
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
    $oUser->role_assign($role_id);
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
    $oUser->role_unassign($role_id);
    return $oUser->save();
  }

  protected static function rest_include()
  {
    return 'Api/User';
  }

  /**
   * List all account assigned to this user
   *
   * @url GET /users/$user_id/accounts
   */
  public function account_list($user_id, $query = array())
  {
    $this->_authorize('user_read');
    $this->_authorize('account_list');

    $filter = (array)$query;
    $filter['created_by'] = $user_id;
    return Account::search($filter);
  }
}
