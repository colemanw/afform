<?php

namespace Civi\Api4\BasicAction;

use Civi\Api4\ActionInterface\GetInterface;
use Civi\Api4\Generic\ArrayRetrievalTrait;

/**
 * Class BasicGet
 * @package Civi\Api4\BasicAction
 *
 * BasicGet is a utility which allows any function (a "getter") to be
 * used as the implementation of an APIv4 "Get" action.
 *
 * The "getter" may simply return an array of all known elements. Optionally,
 * you *may* inspect the $request object and apply some pre-filters yourself.
 * But regardless, the results will be automatically filtered to apply
 * conventional APIv4 options (`where`, `select`, `limit`, etc).
 *
 * This is useful for small one-offs. If you need to develop something more
 * sophisticated, extend "Get" directly.
 */
class BasicGet extends \Civi\Api4\Action\Get implements GetInterface {

  use ArrayRetrievalTrait;

  /**
   * @var callable
   *   A function which finds a list of candidate records.
   *
   *   function(BasicGet $request) => array
   */
  private $getter;

  /**
   * Get constructor.
   * @param callable $getter
   */
  public function __construct($entity, callable $getter) {
    parent::__construct($entity);
    $this->getter = $getter;
  }

  public function getObjects() {
    $values = call_user_func($this->getter, $this);
    return $this->processArrayData($values);
  }

}
