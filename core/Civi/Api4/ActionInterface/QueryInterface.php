<?php

namespace Civi\Api4\ActionInterface;

/**
 * Interface QueryInterface
 * @package Civi\Api4\ActionInterface
 *
 * A "query" is an API action that relies on fetching/filtering a list of
 * records. For example, "get()" and "update()" are queries - but "create()"
 * is not.
 *
 * This a soft/advisory interface because several methods are implemented
 * as magic functions.
 *
 * @method $this addWhere(string $field, string $op, mixed $value)
 * @method $this addClause(string $operator, string $condition1)
 * @method $this addOrderBy(string $field, string $direction)
 * @method $this setWhere(array $wheres)
 * @method $this setOrderBy(array $order)
 * @method $this setLimit(int $limit)
 * @method $this setOffset(int $offset)
 */
interface QueryInterface extends ActionInterface {

}
