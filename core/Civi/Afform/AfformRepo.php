<?php
namespace Civi\Afform;

use Civi\Api4\ActionInterface\QueryInterface;

class AfformRepo {

  /**
   * @param QueryInterface $queryDefn
   * @return array
   */
  public function getAll($queryDefn) {
    /** @var \CRM_Afform_AfformScanner $scanner */
    $scanner = \Civi::service('afform_scanner');
    $converter = new \CRM_Afform_ArrayHtml();

    $where = $queryDefn->getWhere();
    if (count($where) === 1 && $where[0][0] === 'name' && $where[0][1] == '=') {
      $names = [$where[0][2]];
    }
    else {
      $names = array_keys($scanner->findFilePaths());
    }

    $values = [];
    foreach ($names as $name) {
      $record = $scanner->getMeta($name);
      $layout = $scanner->findFilePath($name, 'aff.html');
      if ($layout) {
        // FIXME check for file existence+substance+validity
        $record['layout'] = $converter->convertHtmlToArray(file_get_contents($layout));
      }
      $values[] = $record;
    }

    return $values;
  }

  /**
   * Write a record as part of a create/update action.
   *
   * @param UpdateInterface|... $request
   * @param array $record
   *   The record to write to the DB.
   * @return array
   *   The record after being written to the DB (e.g. including newly assigned "id").
   * @throws \API_Exception
   */
  public function save($request, $record) {
    /** @var \CRM_Afform_AfformScanner $scanner */
    $scanner = \Civi::service('afform_scanner');
    $converter = new \CRM_Afform_ArrayHtml();

    if (empty($record['name']) || !preg_match('/^[a-zA-Z][a-zA-Z0-9\-]*$/', $record['name'])) {
      throw new \API_Exception("Afform.create: name is a mandatory field. It should use alphanumerics and dashes.");
    }
    $name = $record['name'];

    // FIXME validate all field data.
    $updates = _afform_fields_filter($record);

    // Create or update aff.html.
    if (isset($updates['layout'])) {
      $layoutPath = $scanner->createSiteLocalPath($name, 'aff.html');
      \ CRM_Utils_File::createDir(dirname($layoutPath));
      file_put_contents($layoutPath, $converter->convertArrayToHtml($updates['layout']));
      // FIXME check for writability then success. Report errors.
    }

    // Create or update *.aff.json.
    $orig = \Civi\Api4\Afform::get()
      ->setCheckPermissions($request->getCheckPermissions())
      ->addWhere('name', '=', $name)
      ->execute();

    if (isset($orig[0])) {
      $meta = _afform_fields_filter(array_merge($orig[0], $updates));
    }
    else {
      $meta = $updates;
    }
    unset($meta['layout']);
    unset($meta['name']);
    if (!empty($meta)) {
      $metaPath = $scanner->createSiteLocalPath($name, \CRM_Afform_AfformScanner::METADATA_FILE);
      // printf("[%s] Update meta %s: %s\n", $name, $metaPath, print_R(['updates'=>$updates, 'meta'=>$meta], 1));
      \CRM_Utils_File::createDir(dirname($metaPath));
      file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT));
      // FIXME check for writability then success. Report errors.
    }

    // We may have changed list of files covered by the cache.
    $scanner->clear();

    // FIXME if `server_route` changes, then flush the menu cache.
    // FIXME if asset-caching is enabled, then flush the asset cache.

    return $updates;
  }

  /**
   * @param QueryInterface $request
   * @param array $afform
   * @return array
   * @throws \API_Exception
   */
  public function revert($request, $afform) {
    $scanner = \Civi::service('afform_scanner');
    $files = [
      \CRM_Afform_AfformScanner::METADATA_FILE,
      \CRM_Afform_AfformScanner::LAYOUT_FILE
    ];

    foreach ($files as $file) {
      $metaPath = $scanner->createSiteLocalPath($afform['name'], $file);
      if (file_exists($metaPath)) {
        if (!@unlink($metaPath)) {
          throw new \API_Exception("Failed to remove afform overrides in $file");
        }
      }
    }

    // We may have changed list of files covered by the cache.
    $scanner->clear();

    // FIXME if `server_route` changes, then flush the menu cache.
    // FIXME if asset-caching is enabled, then flush the asset cache

    return $afform;
  }

}
