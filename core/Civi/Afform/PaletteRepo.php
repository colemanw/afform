<?php
namespace Civi\Afform;

use Civi\Api4\ActionInterface\QueryInterface;

class PaletteRepo {

  /**
   * @param QueryInterface $queryDefn
   * @return array
   */
  public function getAll($queryDefn) {
    return [
      [
        'id' => 'Parent:afl-name',
        'entity' => 'Parent',
        'title' => 'Name',
        'template' => '<afl-name contact-id="entities.parent.id" afl-label="Name"/>',
      ],
      [
        'id' => 'Parent:afl-address',
        'entity' => 'Parent',
        'title' => 'Address',
        'template' => '<afl-address contact-id="entities.parent.id" afl-label="Address"/>',
      ],
    ];
  }

}
