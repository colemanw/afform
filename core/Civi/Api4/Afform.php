<?php

namespace Civi\Api4;

use Civi\Afform\AfformRepo;
use Civi\Api4\Action\GetActions;
use Civi\Api4\ActionInterface\QueryInterface;
use Civi\Api4\ActionInterface\GetInterface;
use Civi\Api4\ActionInterface\UpdateInterface;
use Civi\Api4\BasicAction\BasicBulkTask;
use Civi\Api4\BasicAction\BasicGet;
use Civi\Api4\BasicAction\BasicUpdate;

/**
 * Class Afform
 * @package Civi\Api4
 */
class Afform {

  /**
   * @return GetInterface
   */
  public static function get() {
    // Decorate AfformRepo::getAll with APIv4 filtering.
    $repo = new AfformRepo();
    return new BasicGet('Afform', [$repo, 'getAll']);
  }

  /**
   * @return GetActions
   */
  public static function getActions() {
    return new GetActions('Afform');
  }

  /**
   * @return QueryInterface
   */
  public static function revert() {
    $repo = new AfformRepo();
    return new BasicBulkTask('Afform', 'name',
      [$repo, 'getAll'],
      [$repo, 'revert']);
  }

  /**
   * @return UpdateInterface
   */
  public static function update() {
    $repo = new AfformRepo();
    return new BasicUpdate('Afform', 'name',
      [$repo, 'getAll'],
      [$repo, 'save']);
  }

  /**
   * @return array
   */
  public static function permissions() {
    return [
      "meta" => ["access CiviCRM"],
      "default" => ["administer CiviCRM"],
    ];
  }

}
