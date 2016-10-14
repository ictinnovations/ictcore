<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class PermissionApi extends Api
{

  /**
   * Create a new permission
   *
   * @url POST /permission/create
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
   * @url GET /permission/list
   * @url POST /permission/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('permission_list');
    return Permission::search($data);
  }

  // no further api needed to update or delete permissions
}