<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules

return [
  'js' => [
    'ang/af.js',
    'ang/af/*.js',
    'ang/af/*/*.js',
  ],
  // 'css' => ['ang/af.css'],
  // 'partials' => ['ang/af'],
  'requires' => ['crmUtil'],
  'settings' => [],
  'basePages' => [],
  'exports' => [
    'af-entity' => 'AE',
    'af-fieldset' => 'A',
    'af-form' => 'AE',
  ],
];
