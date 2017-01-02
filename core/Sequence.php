<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ReflectionClass;

class Sequence
{

  /** @var Request $oRequest  */
  public $oRequest = null;

  /** @var Response $oResponse  */
  public $oResponse = null;

  /** @var Token $oToken  */
  public $oToken = null;

  public function __construct(Request &$oRequest = null, Response &$oResponse = null)
  {
    if (!empty($oRequest)) {
      $this->oRequest = $oRequest;
    } else {
      $this->oRequest = new Request();
    }
    if (!empty($oResponse)) {
      $this->oResponse = $oResponse;
    } else {
      $this->oResponse = new Response();
    }
    $this->oToken = new Token();
  }

  public function &response_create($spool_id, $application_id, $application_data)
  {
    $this->oResponse = new Response();

    $this->oResponse->spool_id = $spool_id;
    $this->oResponse->application_id = $application_id;
    $this->oResponse->application_data = $this->oToken->render_template($application_data);

    return $this->oResponse;
  }

  public function token_create($oObject)
  {
    if (method_exists($oObject, 'load_token')) {
      if (empty($this->oToken)) {
        $this->oToken = $oObject->load_token();
      } else {
        $this->oToken->merge($oObject->load_token());
      }
    } else if (method_exists($oObject, 'token_get')) {
      $reflectionObject = new ReflectionClass($oObject);
      $class_name = strtolower($reflectionObject->getShortName());
      $this->oToken->add($class_name, $oObject->token_get());
      $reflectionParent = $reflectionObject->getParentClass();
      if ($reflectionParent) {
        $parent_class_name = strtolower($reflectionParent->getShortName());
        $this->oToken->add($parent_class_name, $oObject->token_get());
      }
    }
  }

}