<?php

namespace Civi\Api4;

use Civi\Afform\PaletteRepo;
use Civi\Api4\BasicAction\BasicGet;
use Civi\Api4\ActionInterface\GetInterface;

/**
 * Class AfformPalette
 * @package Civi\Api4
 */
class AfformPalette {

  /**
   * @return GetInterface
   */
  public static function get() {
    // Decorate AfformRepo::getAll with APIv4 filtering.
    $repo = new PaletteRepo();
    return new BasicGet('AfformPalette', [$repo, 'getAll']);
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
