<?php

declare(strict_types=1);

namespace Davitec\DvSwiftPagetree\Xclass\Backend\ContextMenu\ItemProviders;

class PageProvider extends \TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider
{
    protected function canRender(string $itemName, string $type): bool
    {
        // Add custom items before checking if rendering is possible
        $this->itemsConfiguration = [
            'mountAsTreeRoot' => [
                'label'          => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.tempMountPoint',
                'iconIdentifier' => 'actions-pagetree-mountroot',
                'callbackAction' => 'mountAsTreeRoot',
            ],
            'divider0'        => [
                'type' => 'divider',
            ],
        ] + $this->itemsConfiguration;
        
        return parent::canRender($itemName, $type);
    }
}
