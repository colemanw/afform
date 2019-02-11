<?php

/*
 * This can't go in "Civi\Api4" because that'll probably screw up the entity scanner.
 * This can't go in "Civi\Api4\Action" because that'll probably screw up the action scanner.
 * This can't go in "Civi\Api4\Interface" because PHP disallows reserved words in namespaces.
 * So... I hope you like compound nouns...
 */
namespace Civi\Api4\ActionInterface;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;

/**
 * Interface ActionInterface
 * @package Civi\Api4\ActionInterface
 *
 * A "get" is an API action whose primary purpose is to fetch/filter/return
 * a list of records.
 *
 * This a soft/advisory interface because several methods are implemented
 * as magic functions.
 *
 */
interface ActionInterface {

  /**
   * Invoke api call.
   *
   * At this point all the params have been sent in and we initiate the api call & return the result.
   * This is basically the outer wrapper for api v4.
   *
   * @return Result|array
   * @throws UnauthorizedException
   */
  public function execute();

}
