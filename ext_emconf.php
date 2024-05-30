<?php

$EM_CONF['dv_swift_pagetree'] = [
  'title'            => 'Faster Backend Pagetree (v11)',
  'description'      => 'Performance-optimized backend page tree for large TYPO3 v11 installations (> 10k pages)',
  'category'         => 'be',
  'author'           => 'Daniel Schöne',
  'author_company'   => 'davitec',
  'state'            => 'experimental',
  'internal'         => '',
  'uploadfolder'     => '0',
  'createDirs'       => '',
  'clearCacheOnLoad' => true,
  'version'          => '1.0.7',
  'constraints'      => [
    'depends'   => [
      'typo3' => '11.5.0-11.9.99',
    ],
    'conflicts' => [
    ],
    'suggests'  => [
    ],
  ],
];
