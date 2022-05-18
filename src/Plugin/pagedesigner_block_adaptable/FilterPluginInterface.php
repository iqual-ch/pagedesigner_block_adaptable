<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable;

/**
 * Provides the interface for defining pagedesigner block filters.
 */
interface FilterPluginInterface {

  /**
   * Returns a render array used for the filter in the view.
   *
   * @param array $filter
   *   The filter definition.
   *
   * @return array
   *   The render array.
   */
  public function build(array $filter) : array;

  /**
   * Save the filter value.
   *
   * @param array $filter
   *   The filter definition.
   * @param mixed $value
   *   The value of the filter.
   *
   * @return mixed
   *   The resulting data to save.
   */
  public function patch(array $filter, $value);

  /**
   * Serializes the filter value.
   *
   * @param array $value
   *   The value to serialize.
   *
   * @return array
   *   The serialized value.
   */
  public function serialize(array $value) : array;

}
