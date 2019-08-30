<?php
declare(strict_types=1);

namespace Davitec\DvSwiftPagetree\Xclass\Backend\Tree\Repository;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTreeRepository extends \TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository
{

  protected function fetchAllPages(): array
  {
    if (!empty($this->fullPageTree)) {
      return $this->fullPageTree;
    }

    $pageTreeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_pagetree');
    if (false !== ($fullPageTree = $pageTreeCache->get('full'))) {
      return $this->fullPageTree = $fullPageTree;
    }

    $pageTreeCache->set('full', parent::fetchAllPages());

    return $this->fullPageTree;
  }
}
