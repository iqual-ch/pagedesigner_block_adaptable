<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\FilterPluginInterface;

/**
 * Base implementation for pagedesigner block filters.
 */
abstract class FilterPluginBase extends PluginBase implements FilterPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function serialize($value) {
    return $value;
  }
}
