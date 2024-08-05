<?php

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;
use TYPO3\CMS\Backend\Controller\Page\TreeController;
use TYPO3\CMS\Core\Routing\PageSlugCandidateProvider;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TreeController::class] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\Controller\Page\TreeController::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][PageProvider::class] = [
    'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\ContextMenu\ItemProviders\PageProvider::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][PageSlugCandidateProvider::class] = [
    'className' => \Davitec\DvSwiftPagetree\Xclass\Routing\PageSlugCandidateProvider::class,
];
