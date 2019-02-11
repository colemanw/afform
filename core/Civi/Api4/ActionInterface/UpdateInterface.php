<?php

namespace Civi\Api4\ActionInterface;

/**
 * Interface UpdateInterface
 * @package Civi\Api4\ActionInterface
 *
 * An "update is an API action which looks up records and modifies
 * a list of properties/values.
 *
 * This a soft/advisory interface because several methods are implemented
 * as magic functions.
 *
 * @method $this setValues(array $values) Set all field values from an array of key => value pairs.
 * @method $this addValue($field, $value) Set field value to update.
 * @method $this setReload(bool $reload) Specify whether complete objects will be returned after saving.
 */
interface UpdateInterface extends ActionInterface, QueryInterface {
}
