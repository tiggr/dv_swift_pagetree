<?php
if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\TreeController::class] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\Controller\Page\TreeController::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider::class] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\ContextMenu\ItemProviders\PageProvider::class,
];

