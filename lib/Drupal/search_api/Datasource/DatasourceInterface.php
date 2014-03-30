<?php
/**
 * @file
 * Contains \Drupal\search_api\Datasource\DatasourceInterface.
 */

namespace Drupal\search_api\Datasource;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\search_api\Plugin\IndexPluginInterface;

/**
 * Describes a datasource.
 */
interface DatasourceInterface extends IndexPluginInterface {

  /**
   * Retrieves the properties exposed by the underlying complex data type.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   An associative array of property data types, keyed by the property name.
   */
  public function getPropertyDefinitions();

  /**
   * Loads an item.
   *
   * @param mixed $id
   *   The ID of an item.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface|NULL
   *   The loaded item if it could be found, NULL otherwise.
   */
  public function load($id);

  /**
   * Loads multiple items.
   *
   * @param array $ids
   *   An array of item IDs.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface[]
   *   An associative array of loaded items, keyed by their IDs.
   */
  public function loadMultiple(array $ids);

  /**
   * Retrieves a URL at which the item can be viewed on the web.
   *
   * @param mixed $item
   *   An item of this DataSource's type.
   *
   * @return array|null
   *   Either an array containing the 'path' and 'options' keys used to build
   *   the URL of the item, and matching the signature of url(), or NULL if the
   *   item has no URL of its own.
   *
   */
  public function getItemUrl($item);

  /**
   * Starts tracking for this index.
   */
  public function startTracking();

  /**
   * Stops tracking for this index.
   */
  public function stopTracking();

  /**
   * Track IDs as inserted.
   *
   * @param array $ids
   *   An array of item IDs.
   *
   * @return boolean
   *   TRUE if successful, otherwise FALSE.
   */
  public function trackInsert(array $ids);

  /**
   * Track IDs as updated.
   *
   * @param array $ids
   *   An array of item IDs, or NULL to mark all items as changed.
   *
   * @return boolean
   *   TRUE if successful, otherwise FALSE.
   */
  public function trackUpdate(array $ids = NULL);

  /**
   * Track IDs as indexed.
   *
   * @param array $ids
   *   An array of item IDs.
   *
   * @return boolean
   *   TRUE if successful, otherwise FALSE.
   */
  public function trackIndexed(array $ids);

  /**
   * Track IDs as deleted.
   *
   * @param array|NULL $ids
   *   An array of item IDs.
   */
  public function trackDelete(array $ids = NULL);

  /**
   * Clear all tracked items.
   *
   * @return boolean
   *   TRUE if successful, otherwise FALSE.
   */
  public function clear();

  /**
   * Get a list of IDs that need to be indexed.
   *
   * If possible, completely unindexed items should be returned before items
   * that were indexed but later changed. Also, items that were changed longer
   * ago should be favored.
   *
   * @param integer $limit
   *   Optional. The maximum number of items to return. Negative values mean
   *   "unlimited". Defaults to all changed items.
   *
   * @return array
   *   An array of item IDs that need to be indexed for the given index.
   */
  public function getRemainingItems($limit = -1);

  /**
   * Retrieves the number of indexed items.
   *
   * @return int
   *   The number of indexed items.
   */
  public function getIndexedItemsCount();

  /**
   * Retrieves the number of changed items.
   *
   * @return int
   *   The number of changed items.
   */
  public function getRemainingItemsCount();

  /**
   * Retrieves the total number of items that have to be indexed.
   *
   * @return int
   *   The total number of items that have to be indexed.
   */
  public function getTotalItemsCount();

  /**
   * Returns view mode info for this item type.
   *
   * @return array
   *   An associative array, keyed by the view mode names, and with the values
   *   being associative arrays with at least the following keys:
   *   - label: A human-readable label for the view mode.
   */
  public function getViewModes();

  /**
   * Returns the render array for the provided item and view mode.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   The item to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the item.
   * @param string|null $langcode
   *   (optional) For which language the item should be rendered. Defaults to
   *   the language the item has been loaded in.
   *
   * @return array
   *   A render array for the item.
   *
   * @throws \InvalidArgumentException
   *   Can be thrown when the set of parameters is inconsistent.
   */
  public function viewItem(ComplexDataInterface $item, $view_mode, $langcode = NULL);

  /**
   * Returns the render array for the provided items and view mode.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface[] $items
   *   The items to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the items.
   * @param string|null $langcode
   *   (optional) For which language the items should be rendered. Defaults to
   *   the language each item has been loaded in.
   *
   * @return array
   *   A render array for the items.
   *
   * @throws \InvalidArgumentException
   *   Can be thrown when the set of parameters is inconsistent.
   */
  public function viewMultipleItems(array $items, $view_mode, $langcode = NULL);

}
