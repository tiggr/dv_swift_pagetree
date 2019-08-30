<?php
declare(strict_types=1);

namespace Davitec\DvSwiftPagetree\Hook;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PagetreeCacheUpdateHooks
{

  /**
   * waits for DataHandler commands and looks for changed pages, if found further
   * changes take place to determine whether the cache needs to be updated
   *
   * @param string $status DataHandler operation status, either 'new' or 'update'
   * @param string $table The DB table the operation was carried out on
   * @param mixed $recordId The record's uid for update records, a string to look the record's uid up after it has been created
   * @param array $updatedFields Array of changed fields and their new values
   * @param DataHandler $dataHandler DataHandler parent object
   */
  public function processDatamap_afterDatabaseOperations(
    $status,
    $table,
    $recordId,
    array $updatedFields,
    DataHandler $dataHandler
  ) {
    if ($table === 'pages') {
      $this->clearPagetreeCache();
    }
  }

  /**
   * Checks whether the change requires an update of the treelist cache
   *
   * @param array $updatedFields Array of changed fields
   * @return bool TRUE if the treelist cache needs to be updated, FALSE if no update to the cache is required
   */
  protected function clearPagetreeCache()
  {
    $pageTreeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_pagetree');
    $pageTreeCache->flush();
  }

  /**
   * Waits for DataHandler commands and looks for deleted pages or swapped pages, if found
   * further changes take place to determine whether the cache needs to be updated
   *
   * @param string $command The TCE command
   * @param string $table The record's table
   * @param int $recordId The record's uid
   * @param array $commandValue The commands value, typically an array with more detailed command information
   * @param DataHandler $dataHandler The DataHandler parent object
   */
  public function processCmdmap_postProcess($command, $table, $recordId, $commandValue, DataHandler $dataHandler)
  {
    if ($table === 'pages') {
      $this->clearPagetreeCache();
    }
  }

  /**
   * waits for DataHandler commands and looks for moved pages, if found further
   * changes take place to determine whether the cache needs to be updated
   *
   * @param string $table Table name of the moved record
   * @param int $recordId The record's uid
   * @param int $destinationPid The record's destination page id
   * @param array $movedRecord The record that moved
   * @param array $updatedFields Array of changed fields
   * @param DataHandler $dataHandler DataHandler parent object
   */
  public function moveRecord_firstElementPostProcess(
    $table,
    $recordId,
    $destinationPid,
    array $movedRecord,
    array $updatedFields,
    DataHandler $dataHandler
  ) {
    if ($table === 'pages') {
      $this->clearPagetreeCache();
    }
  }

  /**
   * Waits for DataHandler commands and looks for moved pages, if found further
   * changes take place to determine whether the cache needs to be updated
   *
   * @param string $table Table name of the moved record
   * @param int $recordId The record's uid
   * @param int $destinationPid The record's destination page id
   * @param int $originalDestinationPid (negative) page id th page has been moved after
   * @param array $movedRecord The record that moved
   * @param array $updatedFields Array of changed fields
   * @param DataHandler $dataHandler DataHandler parent object
   */
  public function moveRecord_afterAnotherElementPostProcess(
    $table,
    $recordId,
    $destinationPid,
    $originalDestinationPid,
    array $movedRecord,
    array $updatedFields,
    DataHandler $dataHandler
  ) {
    if ($table === 'pages') {
      $this->clearPagetreeCache();
    }
  }

}
