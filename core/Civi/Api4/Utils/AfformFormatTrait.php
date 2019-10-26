<?php
namespace Civi\Api4\Utils;

/**
 * Class AfformFormatTrait
 * @package Civi\Api4\Utils
 *
 * @method $this setLayoutFormat(string $layoutFormat)
 * @method string getLayoutFormat()
 */
trait AfformFormatTrait {

  /**
   * @var string
   * @options html,shallow,deep
   */
  protected $layoutFormat = 'deep';

  /**
   * @param string $html
   * @return mixed
   * @throws \API_Exception
   */
  protected function convertHtmlToOutput($html) {
    if ($this->layoutFormat === 'html') {
      return $html;
    }
    $converter = new \CRM_Afform_ArrayHtml($this->layoutFormat !== 'shallow');
    return $converter->convertHtmlToArray($html);
  }

  /**
   * @param mixed $mixed
   * @return string
   * @throws \API_Exception
   */
  protected function convertInputToHtml($mixed) {
    if ($this->layoutFormat === 'html') {
      return $mixed;
    }
    $converter = new \CRM_Afform_ArrayHtml($this->layoutFormat !== 'shallow');
    return $converter->convertArraysToHtml($mixed);
  }

}
