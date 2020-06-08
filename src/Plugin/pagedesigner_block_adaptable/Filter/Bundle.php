<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

/**
 * Process entities of type "bundle".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_bundle",
 *   name = @Translation("Bundle filter"),
 *   types = {
 *     "bundle",
 *   },
 * )
 */
class Bundle extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) {
    // Workaround for the content type, because there
    // can not be a key named 'type' in the definition.
    if ($filter['field'] === 'type') {
      $bundleFilter = $filter;
      $options = [];
      $values = [];
      foreach ($filter['value'] as $key => $option) {
        if (\Drupal::entityTypeManager()->getStorage('node_type')->load($option) != NULL) {
          $options[$key] = \Drupal::entityTypeManager()->getStorage('node_type')->load($option)->label();
          $values[$key] = TRUE;
        }
      }
      return [
        'description' => 'Choose type',
        'label' => 'Type',
        'options' => $options,
        'type' => 'multiplecheckbox',
        'name' => 'content_type',
        'value' => $values,
      ];
    }
    else {
      $values = [];
      return [
        'description' => 'Choose options',
        'label' => $filter['field'],
        'options' => $filter['value'],
        'type' => 'multiplecheckbox',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function patch($value) {
    $result = [];
    foreach ($value as $filter_key => $item) {
      if ($item) {
        $result[$filter_key] = TRUE;
      }
      else {
        $result[$filter_key] = FALSE;
      }
    }
    return $result;
  }

}
