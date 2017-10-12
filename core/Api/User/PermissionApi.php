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

class PermissionApi extends Api
{

  /**
   * Create a new permission
   *
   * @url POST /permissions
   */
  public function create($data = array())
  {
    $this->_authorize('permission_create');

    $oPermission = new Permission();
    $this->set($oPermission, $data);

    if ($oPermission->save()) {
      return $oPermission->permission_id;
    } else {
      throw new CoreException(417, 'Permission creation failed');
    }
  }

  /**
   * List all available permissions
   *
   * @url GET /permissions
   */
  public function list_view($query = array())
  {
    $this->_authorize('permission_list');
    return Permission::search($query);
  }

  // no further api needed to update or delete permissions
}