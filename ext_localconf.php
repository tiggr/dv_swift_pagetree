<?php
if (!defined('TYPO3_MODE')) {
  die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree'])) {
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree'] = [];
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree']['groups'] = ['all'];
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree']['backend'] = \TYPO3\CMS\Core\Cache\Backend\ApcuBackend::class;
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pagetree']['options'] = [
    'defaultLifetime' => 86400,
  ];
}

// Registering hooks for the pagetree cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Davitec\DvSwiftPagetree\Hook\PagetreeCacheUpdateHooks::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \Davitec\DvSwiftPagetree\Hook\PagetreeCacheUpdateHooks::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][] = \Davitec\DvSwiftPagetree\Hook\PagetreeCacheUpdateHooks::class;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Tree\\Repository\\PageTreeRepository'] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\Tree\Repository\PageTreeRepository::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Controller\\Page\\TreeController'] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\Controller\Page\TreeController::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider::class] = [
  'className' => \Davitec\DvSwiftPagetree\Xclass\Backend\ContextMenu\ItemProviders\PageProvider::class,
];

