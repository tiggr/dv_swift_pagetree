<?php

$EM_CONF['dv_swift_pagetree'] = [
  'title'            => 'Faster Backend Pagetree (v11/v12)',
  'description'      => 'Performance-optimized backend page tree for large TYPO3 v11/v12 installations (> 10k pages)',
  'category'         => 'be',
  'author'           => 'Daniel Schöne',
  'author_company'   => 'davitec',
  'state'            => 'experimental',
  'version'          => '1.0.9',
  'constraints'      => [
    'depends'   => [
      'typo3' => '11.5.0-12.4.99',
    ],
    'conflicts' => [
    ],
    'suggests'  => [
    ],
  ],
];
