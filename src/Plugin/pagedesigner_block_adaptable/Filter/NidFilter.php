<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\views\filter\NidViewsFilter;

/**
 * Process entities of type "NID Filter".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_nid",
 *   name = @Translation("NID filter"),
 *   types = {
 *     "nid_views_filter",
 *   }
 * )
 */
class NidFilter extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) {
    if (isset($filter['bundle_filter'])) {
      $bundleFilter = $filter['bundle_filter'];
    }
    else {
      if (!empty($filter['filters']['type'])) {
        $bundleFilter = $filter['filters']['type'];
      }
      else {
        $bundleFilter = NULL;
      }
    }
    $options = NidViewsFilter::getOptions($bundleFilter);
    $values = array_keys($options);
    return [
      'description' => 'Choose node',
      'label' => $filter['expose']['label'],
      'options' => $options,
      'type' => 'multiplecheckbox',
      'name' => $filter['field'],
      'value' => $values,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function patch($value) {
    $result = [];
    foreach ($value as $filter_key => $item) {
      $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($filter_key);
      if ($node != NULL) {
        if ($item) {
          $result[$filter_key] = $filter_key;
        }
        else {
          $result[$filter_key] = FALSE;
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function serialize($value) {
    $values = [];
    foreach ($value as $key => $item) {
      if ($item != FALSE) {
        $values[$key] = TRUE;
      }
      else {
        $values[$key] = FALSE;
      }
    }
    return $values;
  }

}
