<?php

/**
 * @file
 * Contains \Drupal\search_api\Plugin\SearchApi\Processor\Resources\Zl.
 */

namespace Drupal\search_api\Plugin\SearchApi\Processor\Resources;

class Zl implements unicodeListInterface {

  /**
   * {@inheritdoc}
   */
  public function getRegularExpression() {
    return '\x{21}\x{23}\x{25}';
  }
}