<?php

/**
 * @file
 * Contains \Drupal\search_api\Tests\Plugin\Processor\HtmlFilterTest.
 */

namespace Drupal\search_api\Tests\Plugin\Processor;

use Drupal\search_api\Plugin\SearchApi\Processor\HTMLFilter;
use Drupal\search_api\Utility\Utility;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the HtmlFilter processor plugin.
 *
 * @group Drupal
 * @group search_api
 */
class HtmlFilterTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'HTML filter processor test',
      'description' => 'Test if HTML Filter processor works.',
      'group' => 'Search API',
    );
  }

  /**
   * Get an accessible method of HTMLFilter using reflection.
   */
  public function getAccessibleMethod($methodName) {
    $class = new \ReflectionClass('Drupal\search_api\Plugin\SearchApi\Processor\HTMLFilter');
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);
    return $method;
  }

  /**
   * Tests processFieldValue method with title fetching enabled.
   *
   * @dataProvider titleConfigurationDataProvider
   */
  public function testTitleConfiguration($passedString, $expectedValue, $titleConfig) {
    $processor = new HTMLFilter(array('tags' => array(), 'title' => $titleConfig, 'alt' => FALSE), 'html_filter', array());

    $processFieldValueMethod = $this->getAccessibleMethod('processFieldValue');
    $processFieldValueMethod->invokeArgs($processor, array(&$passedString, 'text'));

    $this->assertEquals($expectedValue, $passedString);

  }

  /**
   * Data provider for testTitleConfiguration().
   */
  public function titleConfigurationDataProvider() {
    return array(
      array('word', 'word', FALSE),
      array('word', 'word', TRUE),
      array('<div>word</div>', 'word', TRUE),
      array('<div title="TITLE">word</div>', 'TITLE word', TRUE),
      array('<div title="TITLE">word</div>', 'word', FALSE),
      array('<div data-title="TITLE">word</div>', 'word', TRUE),
      array('<div title="TITLE">word</a>', 'TITLE word', TRUE),
    );
  }

  /**
   * Tests processFieldValue method with alt fetching enabled.
   *
   * @dataProvider altConfigurationDataProvider
   */
  public function testAltConfiguration($passedString, $expectedValue, $altBoost) {
    $processor = new HTMLFilter(array('tags' => array('img' => '2'), 'title' => FALSE, 'alt' => $altBoost), 'html_filter', array());

    $processFieldValueMethod = $this->getAccessibleMethod('processFieldValue');
    $processFieldValueMethod->invokeArgs($processor, array(&$passedString, 'text'));

    $this->assertEquals($expectedValue, $passedString);
  }

  /**
   * Data provider method for testAltConfiguration()
   */
  public function altConfigurationDataProvider() {
    return array(
      array('word', array(Utility::createTextToken('word')), FALSE),
      array('word', array(Utility::createTextToken('word')), TRUE),
      array('<img src="href" />word', array(Utility::createTextToken('word')), TRUE),
      array('<img alt="ALT"/> word', array(Utility::createTextToken('ALT', 2), Utility::createTextToken('word')), TRUE),
      array('<img alt="ALT" /> word', array(Utility::createTextToken('word')), FALSE),
      array('<img data-alt="ALT"/> word', array(Utility::createTextToken('word')), TRUE),
      array('<img src="href" alt="ALT" title="Bar" /> word </a>', array(Utility::createTextToken('ALT', 2), Utility::createTextToken('word')), TRUE),
    );
  }

  /**
   * Tests processFieldValue method with tag provided fetching enabled.
   *
   * @dataProvider tagConfigurationDataProvider
   */
  public function testTagConfiguration($passedString, $expectedValue, array $tagsConfig) {
    $processor = new HTMLFilter(array('tags' => $tagsConfig, 'title' => TRUE, 'alt' => TRUE), 'html_filter', array());

    $processFieldValueMethod = $this->getAccessibleMethod('processFieldValue');
    $processFieldValueMethod->invokeArgs($processor, array(&$passedString, 'text'));
    $this->assertEquals($expectedValue, $passedString);
  }

  /**
   * Data provider method for testTagConfiguration()
   */
  public function tagConfigurationDataProvider() {
    $complex_test = array(
      '<h2>Foo Bar <em>Baz</em></h2>

<p>Bla Bla Bla. <strong title="Foobar">Important:</strong> Bla.</p>
<img src="/foo.png" alt="Some picture" />
<span>This is hidden</span>',
      array(
        Utility::createTextToken('Foo Bar', 3.0),
        Utility::createTextToken('Baz', 4.5),
        Utility::createTextToken('Bla Bla Bla.', 1.0),
        Utility::createTextToken('Foobar Important:', 2.0),
        Utility::createTextToken('Bla.', 1.0),
        Utility::createTextToken('Some picture', 0.5),
      ),
      array(
        'em' => 1.5,
        'strong' => 2.0,
        'h2' => 3.0,
        'img' => 0.5,
        'span' => 0,
      ),
    );
    $tags_config = array('h2' => '2');
    return array(
      array('h2word', 'h2word', array()),
      array('h2word', array(Utility::createTextToken('h2word')), $tags_config),
      array('foo bar <h2> h2word </h2>', array(Utility::createTextToken('foo bar'), Utility::createTextToken('h2word', 2.0)), $tags_config),
      array('foo bar <h2>h2word</h2>', array(Utility::createTextToken('foo bar'), Utility::createTextToken('h2word', 2.0)), $tags_config),
      array('<div>word</div>', array(Utility::createTextToken('word', 2)), array('div' => 2)),
      $complex_test,
    );
  }

  /**
   * Tests whether strings are correctly handled.
   *
   * String field handling should be completely independent of configuration.
   *
   * @dataProvider stringProcessingDataProvider
   */
  public function testStringProcessing(array $config) {
    $processor = new HTMLFilter($config, 'html_filter', array());

    $passedString = '<h2>Foo Bar <em>Baz</em></h2>

<p>Bla Bla Bla. <strong title="Foobar">Important:</strong> Bla.</p>
<img src="/foo.png" alt="Some picture" />
<span>This is hidden</span>';
    $expectedValue = preg_replace('/\s+/', ' ', strip_tags($passedString));

    $processFieldValueMethod = $this->getAccessibleMethod('processFieldValue');
    $processFieldValueMethod->invokeArgs($processor, array(&$passedString, 'string'));
    $this->assertEquals($expectedValue, $passedString);
  }

  /**
   * Provides a few sets of HTML filter configuration.
   *
   * @return array
   *   An array of argument arrays for testStringProcessing(), where each array
   *   contains a HTML filter configuration as the only value.
   */
  public function stringProcessingDataProvider() {
    $configs = array();
    $configs[] = array(array());
    $config['tags'] = array(
      'h2' => 2.0,
      'span' => 4.0,
      'strong' => 1.5,
      'p' => 0,
    );
    $configs[] = array($config);
    $config['title'] = TRUE;
    $configs[] = array($config);
    $config['alt'] = TRUE;
    $configs[] = array($config);
    unset($config['tags']);
    $configs[] = array($config);
    return $configs;
  }

}
