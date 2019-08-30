<?php

$EM_CONF['dv_swift_pagetree'] = [
  'title'            => 'Faster Backend Pagetree (v9)',
  'description'      => 'Performance-optimized backend page tree for large TYPO3 v9 installations (> 10k pages)',
  'category'         => 'be',
  'author'           => 'Daniel Schöne',
  'author_company'   => 'davitec',
  'state'            => 'experimental',
  'internal'         => '',
  'uploadfolder'     => '0',
  'createDirs'       => '',
  'clearCacheOnLoad' => true,
  'version'          => '0.0.1',
  'constraints'      => [
    'depends'   => [
      'typo3' => '9.5.0-9.9.99',
    ],
    'conflicts' => [
    ],
    'suggests'  => [
    ],
  ],
];
