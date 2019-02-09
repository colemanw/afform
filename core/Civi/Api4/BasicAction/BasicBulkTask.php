<?php

namespace Civi\Api4\BasicAction;

use Civi\Api4\ActionInterface\QueryInterface;
use Civi\Api4\Generic\Result;

class BasicBulkTask extends BasicGet implements QueryInterface {

  /**
   * @var callable
   *   Function(BasicBulkTask $request, array $record) => array
   *
   *   The callable is given one record at a time.
   *
   *   The return value will passed back as part of the result.
   */
  private $doer;

  /**
   * BasicUpdate constructor.
   * @param string $entity
   * @param string $idCol
   * @param callable $getter
   * @param callable $doer
   */
  public function __construct($entity, $idCol, $getter, $doer) {
    parent::__construct($entity, $getter);
    $this->select = [$idCol];
    $this->doer = $doer;
  }

  public function _run(Result $result) {
    if (empty($this->where)) {
      // throw new \API_Exception(sprintf("%s.%s requries \"where\"", $result->entity, $result->action));
      throw new \API_Exception("Missing required filters (\"where\")");
    }

    $getResult = clone $result;
    parent::_run($getResult);
    foreach ($getResult as $resultKey => $record) {
      $result[$resultKey] = call_user_func($this->doer, $this, $record);
    }
  }

}
