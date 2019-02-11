<?php

namespace Civi\Api4\ActionInterface;

/**
 * Interface GetInterface
 * @package Civi\Api4\ActionInterface
 *
 * A "get" is an API action whose primary purpose is to fetch/filter/return
 * a list of records.
 *
 * This a soft/advisory interface because several methods are implemented
 * as magic functions.
 *
 * @method $this addSelect(string $select)
 *   Add a field to return/display.
 * @method $this setSelect(array $selects)
 *   Set the list of fields to return/display.
 */
interface GetInterface extends ActionInterface, QueryInterface {
}
