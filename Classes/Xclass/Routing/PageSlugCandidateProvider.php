<?php

namespace Davitec\DvSwiftPagetree\Xclass\Routing;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageSlugCandidateProvider extends \TYPO3\CMS\Core\Routing\PageSlugCandidateProvider
{

    /**
     * Check for records in the database which matches one of the slug candidates.
     *
     * @IMPORTANT This method uses a custom stored procedure GetPageRootPageUid @ mysql server
     * @see ext_tables_GetPageRootPageUid_mysql5.sql and ext_tables_GetPageRootPageUid_mysql8.sql
     *      in root of extension
     *
     * @param array $slugCandidates
     * @param int $languageId
     * @param array $excludeUids when called recursively this is the mountpoint parameter of the original prefix
     * @return array[]|array
     * @throws SiteNotFoundException
     */
    protected function getPagesFromDatabaseForCandidates(array $slugCandidates, int $languageId, array $excludeUids = []): array
    {
        $workspaceId = (int)$this->context->getPropertyFromAspect('workspace', 'id');
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspaceId, true));

        $statement = $queryBuilder
            ->select('uid', 'sys_language_uid', 'l10n_parent', 'l18n_cfg', 'pid', 'slug', 'mount_pid', 'mount_pid_ol', 't3ver_state', 'doktype', 't3ver_wsid', 't3ver_oid')
            // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            // NOTE: added select to only query subpages of current site
            //       uses custom stored procedure
            ->addSelectLiteral('GetRootPageUid(uid) AS root_page_uid')
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'slug',
                    $queryBuilder->createNamedParameter(
                        $slugCandidates,
                        Connection::PARAM_STR_ARRAY
                    )
                )
            )
            // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            // NOTE: added restriction to only query subpages of current site using result of stored procedure
            ->having(
                $queryBuilder->expr()->eq(
                    'root_page_uid',
                    $queryBuilder->createNamedParameter($this->site->getRootPageId(), Connection::PARAM_INT)
                )
            )
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            // Exact match will be first, that's important
            ->orderBy('slug', 'desc')
            // versioned records should be rendered before the live records
            ->addOrderBy('t3ver_wsid', 'desc')
            // Sort pages that are not MountPoint pages before mount points
            ->addOrderBy('mount_pid_ol', 'asc')
            ->addOrderBy('mount_pid', 'asc')
            ->executeQuery();

        $pages = [];
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class, $this->context);
        $isRecursiveCall = !empty($excludeUids);

        while ($row = $statement->fetchAssociative()) {
            $mountPageInformation = null;
            $pageIdInDefaultLanguage = (int)($languageId > 0 ? $row['l10n_parent'] : ($row['t3ver_oid'] ?: $row['uid']));
            // When this page was added before via recursion, this page should be skipped
            if (in_array($pageIdInDefaultLanguage, $excludeUids, true)) {
                continue;
            }

            try {
                $isOnSameSite = $siteFinder->getSiteByPageId($pageIdInDefaultLanguage)->getRootPageId() === $this->site->getRootPageId();
            } catch (SiteNotFoundException $e) {
                // Page is not in a site, so it's not considered
                $isOnSameSite = false;
            }

            // If a MountPoint is found on the current site, and it hasn't been added yet by some other iteration
            // (see below "findPageCandidatesOfMountPoint"), then let's resolve the MountPoint information now
            if (!$isOnSameSite && $isRecursiveCall) {
                // Not in the same site, and called recursive, should be skipped
                continue;
            }
            $mountPageInformation = $pageRepository->getMountPointInfo($pageIdInDefaultLanguage, $row);

            // Mount Point Pages which are not on the same site (when not called on the first level) should be skipped
            // As they just clutter up the queries.
            if (!$isOnSameSite && !$isRecursiveCall && $mountPageInformation) {
                continue;
            }

            $mountedPage = null;
            if ($mountPageInformation) {
                // Add the MPvar to the row, so it can be used later-on in the PageRouter / PageArguments
                $row['MPvar'] = $mountPageInformation['MPvar'];
                $mountedPage = $pageRepository->getPage_noCheck($mountPageInformation['mount_pid_rec']['uid']);
                // Ensure to fetch the slug in the translated page
                $mountedPage = $pageRepository->getPageOverlay($mountedPage, $languageId);
                // Mount wasn't connected properly, so it is skipped
                if (!$mountedPage) {
                    continue;
                }
                // If the page is a MountPoint which should be overlaid with the contents of the mounted page,
                // it must never be accessible directly, but only in the MountPoint context. Therefore we change
                // the current ID and slug.
                // This needs to happen before the regular case, as the $pageToAdd contains the MPvar information
                if ((int)$row['doktype'] === PageRepository::DOKTYPE_MOUNTPOINT && $row['mount_pid_ol']) {
                    // If the mounted page was already added from above, this should not be added again (to include
                    // the mount point parameter).
                    if (in_array((int)$mountedPage['uid'], $excludeUids, true)) {
                        continue;
                    }
                    $pageToAdd = $mountedPage;
                    // Make sure target page "/about-us" is replaced by "/global-site/about-us" so router works
                    $pageToAdd['MPvar'] = $mountPageInformation['MPvar'];
                    $pageToAdd['slug'] = $row['slug'];
                    $pages[] = $pageToAdd;
                    $excludeUids[] = (int)$pageToAdd['uid'];
                    $excludeUids[] = $pageIdInDefaultLanguage;
                }
            }

            // This is the regular "non-MountPoint page" case (must happen after the if condition so MountPoint
            // pages that have been replaced by the Mounted Page will not be added again.
            if ($isOnSameSite && !in_array($pageIdInDefaultLanguage, $excludeUids, true)) {
                $pages[] = $row;
                $excludeUids[] = $pageIdInDefaultLanguage;
            }

            // Add possible sub-pages prepended with the MountPoint page slug
            if ($mountPageInformation) {
                /** @var array $mountedPage */
                $siteOfMountedPage = $siteFinder->getSiteByPageId((int)$mountedPage['uid']);
                $morePageCandidates = $this->findPageCandidatesOfMountPoint(
                    $row,
                    $mountedPage,
                    $siteOfMountedPage,
                    $languageId,
                    $slugCandidates
                );
                foreach ($morePageCandidates as $candidate) {
                    // When called previously this MountPoint page should be skipped
                    if (in_array((int)$candidate['uid'], $excludeUids, true)) {
                        continue;
                    }
                    $pages[] = $candidate;
                }
            }
        }
        return $pages;
    }
}
