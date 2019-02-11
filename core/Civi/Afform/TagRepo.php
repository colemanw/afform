<?php

namespace Civi\Afform;

use Civi\Api4\ActionInterface\QueryInterface;

class TagRepo {

  /**
   * @param QueryInterface $queryDefn
   * @return array
   */
  public function getAll($queryDefn) {
    return [
      [
        'name' => 'afl-entity',
        'attrs' => ['entity-name', 'matching-rule', 'assigned-values'],
      ],
      [
        'name' => 'afl-name',
        'attrs' => ['contact-id', 'afl-label'],
      ],
      [
        'name' => 'afl-contact-email',
        'attrs' => ['contact-id', 'afl-label'],
      ],
    ];
  }

}
