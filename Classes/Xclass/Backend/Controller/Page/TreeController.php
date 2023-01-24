<?php
declare(strict_types=1);
namespace Davitec\DvSwiftPagetree\Xclass\Backend\Controller\Page;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

class TreeController extends \TYPO3\CMS\Backend\Controller\Page\TreeController
{
    /**
     * Returns JSON representing page tree - ONLY 1ST LEVEL IS FETCHED FOR ROOT
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function fetchDataAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeConfiguration($request);

        $items = [];
        if (!empty($request->getQueryParams()['pid'])) {
            // Fetching a part of a page tree
            $entryPoints = $this->getAllEntryPointPageTrees((int)$request->getQueryParams()['pid']);
            $mountPid = (int)($request->getQueryParams()['mount'] ?? 0);
            $parentDepth = (int)($request->getQueryParams()['pidDepth'] ?? 0);
            $this->levelsToFetch = $parentDepth + $this->levelsToFetch;
            foreach ($entryPoints as $page) {
                $items = array_merge($items, $this->pagesToFlatArray($page, $mountPid, $parentDepth));
            }
        } else {
            $entryPoints = $this->getAllEntryPointPageTrees();
            foreach ($entryPoints as $page) {
                $items = array_merge($items, $this->pagesToFlatArray($page, (int)$page['uid'], 1));
            }
        }

        return new JsonResponse($items);
    }
}
