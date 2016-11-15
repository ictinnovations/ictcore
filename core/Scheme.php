<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Scheme
{

  /** @var Application[] $applicationCache  */
  private $applicationCache = array();

  /** @var array $conditionCache  */
  private $conditionCache = array();

  /** @var Action[] $actionCache  */
  private $actionCache = array();

  /** @var Application $appFirst  */
  private $appFirst = null;

  /** @var Application $appLast  */
  private $appLast = null;

  /** @var Application $appCurrent  */
  private $appCurrent = null;

  /** @var string $inspCurrent  */
  private $inspCurrent = null;

  /** @var string $condCurrent  */
  private $condCurrent = null;

  public function compile(Program &$oProgram, Sequence &$oSequence)
  {
    Corelog::log("Compiling porgram", Corelog::LOGIC);
    foreach ($this->applicationCache as $appName => $oApplication) {
      $data = array_merge($oApplication::$requiredParameter, $oApplication->data);
      // First application must have ORDER_INIT weight
      if ($this->appFirst->name == $appName) {
        $oApplication->weight = Application::ORDER_INIT;
      }
      // make sure no default value left, so REPLACE_EMPTY in token replacement
      $oApplication->data = $oSequence->oToken->token_replace($data, Token::REPLACE_EMPTY);
      $oApplication->program_id = $oProgram->program_id;
      $oApplication->save();
      $oApplication->deploy($oProgram);
      foreach ($this->actionCache[$appName] as $result_type => $oAction) {
        $oAction->application_id = $oApplication->application_id;
      }
    }
    foreach ($this->actionCache as $appName => $listAction) {
      foreach ($listAction as $result_type => $oAction) {
        $oAction->save();
      }
    }
  }

  private function node_create()
  {
    $oScheme = new Scheme();
    $oScheme->applicationCache = &$this->applicationCache;
    $oScheme->conditionCache = &$this->conditionCache;
    $oScheme->actionCache = &$this->actionCache;

    $oScheme->appFirst = &$this->appFirst;
    $oScheme->appLast = &$this->appLast;

    // use values, avoid reference
    $oScheme->appCurrent = $this->appCurrent;
    $oScheme->inspCurrent = $this->inspCurrent;
    $oScheme->condCurrent = $this->condCurrent;

    return $oScheme;
  }

  public function locate($appName)
  {
    $oNode = $this->node_create();
    $oNode->appCurrent = &$this->applicationCache[$appName];
    $oNode->inspCurrent = &$this->conditionCache[$appName];
    // Note: after first application inspCurrent can be null
    if (!empty($oNode->inspCurrent)) { // TODO, drop this if no use
      $oNode->condCurrent = &$this->conditionCache[$appName][$oNode->inspCurrent];
    } else {
      $oNode->condCurrent = null;
    }
    return $oNode;
  }

  public function condition($inspect, $match)
  {
    $this->inpect($inspect);
    return $this->match($match);
  }

  public function inspect($inspect = 'result')
  {
    $appName = $this->appCurrent->name;
    $oApp = $this->appCurrent;
    if (!in_array($inspect, $oApp::$supportedResult)) {
      throw new CoreException("500", "Current application does not support result type : $inspect");
    }
    $this->conditionCache[$appName] = $inspect;
    return $this->locate($appName);
  }

  public function match($match = 'default')
  {
    $appName = $this->appCurrent->name;
    $oApp = $this->appCurrent;
    if (!empty($this->inspCurrent)) {
      throw new CoreException("500", "Invalid or missing inspect variable, can't continue");
    } else if (!in_array($this->inspCurrent, $oApp::$supportedResult)) {
      throw new CoreException("500", "Current application does not support inspecting : $this->inspCurrent");
    } else {
      $supportedInspect = (array) $oApp::$supportedResult[$this->inspCurrent];
      if (!in_array($match, $supportedInspect)) {
        throw new CoreException("500", "Current result does not support this condition : $match");
      }
    }
    $this->conditionCache[$appName][$this->inspCurrent] = $match;
    return $this->locate($appName);
  }

  public function add(Application &$oApplication, $is_default = false)
  {
    $appName = $oApplication->name;

    $this->applicationCache[$appName] = &$oApplication;
    $this->conditionCache[$appName] = array();
    $this->actionCache[$appName] = array();

    // if this is first application then set appFirst variable
    if (empty($this->appFirst)) {
      $this->appFirst = &$this->applicationCache[$appName];
      // Or link it with previouse application
    } else {
      $this->link($appName, $is_default);
    }
    $this->appLast = &$this->applicationCache[$appName];

    $this->appCurrent = &$this->applicationCache[$appName];
    $this->inspCurrent = null; // no condition for new applications

    return $this->locate($appName);
  }

  protected function link($subApp, $is_default = false)
  {
    $parentApp = $this->appCurrent->name;
    $oParent = $this->applicationCache[$parentApp];

    if (empty($this->conditionCache[$parentApp])) {
      $this->conditionCache[$parentApp] = $oParent::$defaultCondition;
    }

    foreach ($this->conditionCache[$parentApp] as $result_type => $result_value) {
      $oAction = $this->action_create($parentApp, $result_type);
      $oAction->data = array($result_type => $result_value);
      $oAction->nextApplication = &$this->applicationCache[$subApp];
      break;
    }

    // if given, make current app as default and reset all other actions under this application
    if ($is_default) {
      foreach ($this->actionCache[$parentApp] as $result_type2 => $oAction) {
        if ($result_type == $result_type2) {
          $oAction->is_default = true;
        } else {
          $oAction->is_default = false;
        }
      }
    }
  }

  protected function &action_create($appName, $result_type)
  {
    $oAction = new Action();
    $this->actionCache[$appName][$result_type] = $oAction;
    return $this->actionCache[$appName][$result_type];
  }

}