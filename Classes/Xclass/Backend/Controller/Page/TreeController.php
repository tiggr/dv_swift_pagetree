<?php
declare(strict_types=1);
namespace Davitec\DvSwiftPagetree\Xclass\Backend\Controller\Page;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Core\Database\Query\Restriction\DocumentTypeExclusionRestriction;
use TYPO3\CMS\Core\Exception\Page\RootLineException;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class TreeController extends \TYPO3\CMS\Backend\Controller\Page\TreeController
{

  /**
   * Fetches all entry points for the page tree that the user is allowed to see
   *
   * @return array
   */
  protected function getAllEntryPointPageTrees(): array
  {
    $backendUser = $this->getBackendUser();

    $userTsConfig = $this->getBackendUser()->getTSConfig();
    $excludedDocumentTypes = GeneralUtility::intExplode(',', $userTsConfig['options.']['pageTree.']['excludeDoktypes'] ?? '', true);

    $additionalPageTreeQueryRestrictions = [];
    if (!empty($excludedDocumentTypes)) {
      foreach ($excludedDocumentTypes as $excludedDocumentType) {
        $additionalPageTreeQueryRestrictions[] = new DocumentTypeExclusionRestriction((int)$excludedDocumentType);
      }
    }

    $repository = GeneralUtility::makeInstance(PageTreeRepository::class, (int)$backendUser->workspace, [], $additionalPageTreeQueryRestrictions);

    $entryPoints = (int)($backendUser->uc['pageTree_temporaryMountPoint'] ?? 0);
    if ($entryPoints > 0) {
      $entryPoints = [$entryPoints];
    } else {
      $entryPoints = array_map('intval', $backendUser->returnWebmounts());
      $entryPoints = array_unique($entryPoints);
      if (empty($entryPoints)) {
        // use a virtual root
        // the real mount points will be fetched in getNodes() then
        // since those will be the "sub pages" of the virtual root
        $entryPoints = [0];
      }
    }
    if (empty($entryPoints)) {
      return [];
    }

    foreach ($entryPoints as $k => &$entryPoint) {
      if (in_array($entryPoint, $this->hiddenRecords, true)) {
        unset($entryPoints[$k]);
        continue;
      }

      if (!empty($this->backgroundColors) && is_array($this->backgroundColors)) {
        try {
          $entryPointRootLine = GeneralUtility::makeInstance(RootlineUtility::class, $entryPoint)->get();
        } catch (RootLineException $e) {
          $entryPointRootLine = [];
        }
        foreach ($entryPointRootLine as $rootLineEntry) {
          $parentUid = $rootLineEntry['uid'];
          if (!empty($this->backgroundColors[$parentUid]) && empty($this->backgroundColors[$entryPoint])) {
            $this->backgroundColors[$entryPoint] = $this->backgroundColors[$parentUid];
          }
        }
      }

      $entryPoint = $repository->getTree($entryPoint, function ($page) use ($backendUser) {
        // limit pages to 1st level if no temporary mount point is set
        if ((int)($backendUser->uc['pageTree_temporaryMountPoint']) === 0) {
          return $page['pid'] == 0;
        }
        // check each page if the user has permission to access it
        return $backendUser->doesUserHaveAccess($page, Permission::PAGE_SHOW);
      });
      if (!is_array($entryPoint)) {
        unset($entryPoints[$k]);
      }
    }

    return $entryPoints;
  }
}
