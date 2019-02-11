<?php
namespace Civi\Api4;

use Civi\Afform\TagRepo;
use Civi\Api4\BasicAction\BasicGet;
use Civi\Api4\ActionInterface\GetInterface;

/**
 * Class AfformTag
 * @package Civi\Api4
 */
class AfformTag {

  /**
   * @return GetInterface
   */
  public static function get() {
    // Decorate TagRepo::getAll with APIv4 filtering.
    $repo = new TagRepo();
    return new BasicGet('AfformTag', [$repo, 'getAll']);
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
