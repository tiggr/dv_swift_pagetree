<?php

namespace Davitec\DvSwiftPagetree\Xclass\Routing;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageSlugCandidateProvider extends \TYPO3\CMS\Core\Routing\PageSlugCandidateProvider
{
    /**
     * Enhanced version that respects site boundaries better
     * Filters pages to only include those within the current site tree
     */
    protected function getPagesFromDatabaseForCandidates(array $slugCandidates, int $languageId, array $excludeUids = []): array
    {
        // Get parent results first
        $pages = parent::getPagesFromDatabaseForCandidates($slugCandidates, $languageId, $excludeUids);
        
        // Filter to only include pages within the current site
        $rootPageUid = $this->site->getRootPageId();
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        
        $filteredPages = [];
        foreach ($pages as $page) {
            $pageIdInDefaultLanguage = (int)($languageId > 0 ? $page['l10n_parent'] : ($page['t3ver_oid'] ?: $page['uid']));
            
            try {
                $pageSite = $siteFinder->getSiteByPageId($pageIdInDefaultLanguage);
                if ($pageSite->getRootPageId() === $rootPageUid) {
                    $filteredPages[] = $page;
                }
            } catch (\Exception $e) {
                // Page not in any site, skip it
                continue;
            }
        }
        
        return $filteredPages;
    }
}
