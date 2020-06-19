<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable;

use Drupal\pagedesigner\Entity\Element;
use Drupal\ui_patterns\Definition\PatternDefinitionField;

/**
 * Provides the interface for defining pagedesigner block filters.
 */
interface FilterPluginInterface {

  /**
   * Returns a render array used for the filter field in the block.
   *
   * @param array $filter
   *   Original filter array.
   *
   * @return array
   *   The newly created render array.
   */
  public function build(array $filter);

  /**
   * Save the filter value.
   *
   * @param $value
   *   The value of the filter.
   */
  public function patch($value);

}
