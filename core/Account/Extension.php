<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Extension
 *
 * @author nasir
 */
class Extension extends Account
{

  public $type = 'extension';

  public function capabilities()
  {
    return (Transmission::INBOUND | Transmission::OUTBOUND);
  }

  public function save()
  {
    parent::save();
    Voice::create_extension($this);
  }

  public function remove_program($program_name = 'all')
  {
    parent::remove_program($program_name);
  }

  public function delete()
  {
    parent::delete();
  }

}