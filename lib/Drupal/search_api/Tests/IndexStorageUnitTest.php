<?php
/**
 * @file
 * Contains \Drupal\search_api\Tests\IndexStorageUnitTest.
 */

namespace Drupal\search_api\Tests;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\simpletest\DrupalUnitTestBase;
use Drupal\search_api\Entity\Index;

/**
 * Class IndexStorageUnitTest
 *
 * Tests Search API Index entity storage
 *
 * @package Drupal\search_api\Test
 */
class IndexStorageUnitTest extends DrupalUnitTestBase {

  /**
   * Modules to enabled.
   *
   * @var array
   */
  public static $modules = array('search_api');

  /**
   * Search API Index storage controller.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface.
   */
  protected $controller;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Index storage unit tests',
      'description' => 'Test CRUD operation of Search API Index entity',
      'group' => 'Search API'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->controller = $this->container->get('entity.manager')->getStorage('search_api_index');
  }

  /**
   * Test all CRUD operations here as a queue of operations.
   */
  public function testIndexCRUD() {
    $this->assertTrue($this->controller instanceof ConfigEntityStorage, 'The Search API Index storage controller is loaded.');

    $index = $this->indexCreate();

    $this->indexLoad($index);
    $this->indexDelete($index);
  }

  /**
   * Tests Index creation.
   *
   * @return Index newly created instance of Index.
   */
  public function indexCreate() {
    $indexData = array(
      'machine_name' => $this->randomName(),
      'name' => $this->randomString(),
    );

    try {
      $entity = $this->controller->create($indexData);
    }
    catch (\Exception $e) {
      $this->fail($e->getMessage() . ' exception was thrown.');
    }

    $this->assertTrue($entity instanceof Index, 'The newly created entity is Search API Index.');

    $entity->save();

    return $entity;
  }

  /**
   * Test Index loading.
   *
   * @param $index Index
   */
  public function indexLoad($index) {
    try {
      $entity = $this->controller->load($index->id());
    }
    catch (\Exception $e) {
      $this->fail($e->getMessage() . ' exception was thrown.');
    }

    $this->assertIdentical($index->get('label'), $entity->get('label'));
  }

  /**
   * Test of deletion of given index.
   *
   * @param $index
   */
  public function indexDelete($index) {
    try {
      $this->controller->delete(array($index));
    }
    catch (\Exception $e) {
      $this->fail($e->getMessage() . ' exception was thrown.');
    }

    $entity = $this->controller->load($index->id());

    $this->assertFalse($entity);
  }
} 
