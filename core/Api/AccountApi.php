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
use ICT\Core\Conf;
use ICT\Core\CoreException;
use ICT\Core\Program;
use ICT\Core\User;
use stdClass;

class AccountApi extends Api
{

  /** @var string #interface_type */
  private $include_subfolder = true;

  /**
   * Create a new account
   *
   * @url POST /accounts
   */
  public function create($data = array(), $account_id = null)
  {
      $this->_authorize('account_create');
  
      if (isset($data['type']) && !empty($data['type'])) {
          $oAccount = Account::load($data['type']);
      } else {
          $oAccount = new Account();
      }
      $aSetting = $oAccount->settings;
      $oAccount = new Account();
      $oAccount->account_id = $account_id;
      $oAccount->set($data);
      if (isset($data['settings']) && !empty($data['settings'])) {
          $oAccount->settings = array_merge($aSetting, $oAccount->settings);
      }
      if ($oAccount->save()) {
          return $oAccount->account_id;
      } else {
          throw new CoreException(417, 'Account creation failed');
      }
  }
  
  /**
   * List all available accounts
   *
   * @url GET /accounts
   */
  public function list_view($query = array())
  {
    $this->_authorize('account_list');
    return Account::search((array)$query);
  }

  /**
   * Gets the account by id
   *
   * @url GET /accounts/$account_id
   */
  public function read($account_id)
  {
    $this->_authorize('account_read');

    $oAccount = Account::load($account_id);
    return $oAccount;
  }

  /**
   * Gets the provisioning information by account id
   *
   * @url GET /accounts/$account_id/provisioning
   */
  public function provisioning($account_id)
  {
    $this->_authorize('account_read');

    $oAccount = $this->read($account_id);

    $oProvisioning = new stdClass();
    $oProvisioning->username = $oAccount->username;
    $oProvisioning->password = $oAccount->passwd;
    $oProvisioning->callerid = $oAccount->phone;
    $aProvisioning = Conf::get('provisioning');
    foreach ($aProvisioning as $field => $value) {
      $oProvisioning->{$field} = $value;
    }
    $oProvisioning->dialplan = array(
      'agent_login' => '*'.$oAccount->phone,
      'voicemail' => '*78',
    );
    $oProvisioning->account = $oAccount; // all other account informations

    return $oProvisioning;
  }

  /**
   * Update existing account
   *
   * @url PUT /accounts/$account_id
   */
  public function update($account_id, $data = array())
  {
    $this->_authorize('account_update');

    $oAccount = Account::load($account_id);
    $aSetting = $oAccount->settings; 
    $oAccount->account_id = $account_id;
    $oAccount->set($data);
    if (isset($data['settings']) && !empty($data['settings'])) {
      $oAccount->settings = array_merge($aSetting, $oAccount->settings);
    }
    if ($oAccount->save()) {
      return $oAccount;
    } else {
      throw new CoreException(417, 'Account update failed');
    }
  }

  /**
   * Delete a account
   *
   * @url DELETE /accounts/$account_id
   */
  public function remove($account_id)
  {
    $this->_authorize('account_delete');

    $oAccount = Account::load($account_id);

    $result = $oAccount->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Account delete failed');
    }
  }

  /**
   * Subscribe to selected program
   *
   * @url PUT /accounts/$account_id/programs/$program_name
   */
  public function subscribe($account_id, $program_name)
  {
    $this->_authorize('account_update');
    $this->_authorize('program_create');
    $this->_authorize('program_execute');
    $oAccount = Account::load($account_id);
    $oProgram = Program::load($program_name);

    return $oAccount->install_program($oProgram);
  }

  /**
   * Unsubscribe from selected program
   *
   * @url DELETE /accounts/$account_id/programs
   * @url DELETE /accounts/$account_id/programs/$program_name
   */
  public function unsubscribe($account_id, $program_name = 'all')
  {
    $this->_authorize('account_update');
    $this->_authorize('program_delete');
    $oAccount = Account::load($account_id);

    return $oAccount->remove_program($program_name);
  }

  /**
   * Associate account to selected user
   *
   * @url PUT /accounts/$account_id/users/$user_id
   */
  public function associate($account_id, $user_id)
  {
    $this->_authorize('account_create'); // instead of updated association is more like account creation
    $this->_authorize('user_update');
    $oAccount = Account::load($account_id);
    $oAccount->dissociate();
    return $oAccount->associate($user_id);
  }

  /**
   * Unsubscribe from selected program
   *
   * @url DELETE /accounts/$account_id/users
   */
  public function dissociate($account_id)
  {
    $this->_authorize('account_delete');
    $this->_authorize('user_update');
    $oAccount = Account::load($account_id);

    return $oAccount->dissociate();
  }

  /**
   * Read setting associated with this account
   *
   * @url GET /accounts/$account_id/settings/$name
   */
  public function setting_read($account_id, $name)
  {
    $this->_authorize('account_read');
    $oAccount = Account::load($account_id);

    if (isset($oAccount->settings[$name])) {
      $oAccount->settings[$name];
      return $oAccount->save();
    }
    throw new CoreException(404, 'Setting not found');
  }

  /**
   * Save setting for this account
   *
   * @url PUT /accounts/$account_id/settings/$name
   */
  public function setting_write($account_id, $name, $data = array())
  {
    $this->_authorize('account_update');
    $oAccount = Account::load($account_id);
    $is_updated = false;

    if (is_array($data)) {
      if (isset($data['value'])) {
        $oAccount->settings[$name] = $data['value'];
        $is_updated = true;
      } elseif (isset($data['data'])) {
        $oAccount->settings[$name] = $data['data'];
        $is_updated = true;
      }
    } elseif (!empty($data)) {
      $oAccount->settings[$name] = $data;
      $is_updated = true;
    }
    if ($is_updated) {
      return $oAccount->save();
    }
    throw new CoreException(417, 'Account setting update failed! no value or data parameter set');
  }

  /**
   * Delete a setting from given account
   *
   * @url DELETE /accounts/$account_id/settings/$name
   */
  public function setting_delete($account_id, $name)
  {
    $this->_authorize('account_update');
    $oAccount = Account::load($account_id);
    unset($oAccount->settings[$name]);
    return $oAccount->save();
  }

  // include classes from Account folder
  protected static function rest_include()
  {
    if (property_exists (get_called_class(), 'include_subfolder')) {
      return 'Api/Account'; // Api class return sub api folder
    }
    // in child class return null
    return null;
  }

}
