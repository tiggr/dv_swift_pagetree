<?php
declare(strict_types=1);

namespace Davitec\DvSwiftPagetree\Xclass\Backend\ContextMenu\ItemProviders;

class PageProvider extends \TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider
{

  public function __construct(string $table, string $identifier, string $context = '')
  {
    parent::__construct($table, $identifier, $context);

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
  }
}
