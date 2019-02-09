<?php

namespace Civi\Api4\BasicAction;

use Civi\Api4\ActionInterface\UpdateInterface;
use Civi\Api4\Generic\ArrayRetrievalTrait;

/**
 * Class BasicUpdate
 * @package Civi\Api4\Action\Afform
 *
 * BasicUpdate is a utility which allows a pair of functions
 * (the `$getter` and `$saver`) to be used as the implementation
 * of an APIv4 "Update" action.
 *
 * The "getter" may simply return an array of all known elements. Optionally,
 * you *may* inspect the $request object and apply some pre-filters yourself.
 * But regardless, the results will be automatically filtered to apply
 * conventional APIv4 options (`where`, `select`, `limit`, etc).
 *
 * The "saver" should take a list of updated-field-values and save them.
 *
 * This is useful for small one-offs. If you need to develop something more
 * sophisticated, extend "Update" directly.
 */
class BasicUpdate extends \Civi\Api4\Action\Update implements UpdateInterface {

  use ArrayRetrievalTrait;

  /**
   * @var callable
   *   A function which finds a list of candidate records.
   *
   *   function(BasicUpdate $request) => array
   */
  private $getter;

  /**
   * @var callable
   *   A function which writes one record.
   *
   *   function(BasicUpdate $request, array $record) => array
   */
  private $saver;

  /**
   * BasicUpdate constructor.
   * @param string $entity
   * @param string $idCol
   * @param callable $getter
   * @param callable $doer
   */
  public function __construct($entity, $idCol, $getter, $doer) {
    parent::__construct($entity);
    $this->select = [$idCol];
    $this->getter = $getter;
    $this->saver = $doer;
  }

  public function getObjects() {
    $values = call_user_func($this->getter, $this);
    return $this->processArrayData($values);
  }

  protected function writeObject($record) {
    return call_user_func($this->saver, $this, $record);
  }

}
