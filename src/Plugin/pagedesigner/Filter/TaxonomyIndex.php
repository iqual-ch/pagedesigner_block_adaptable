<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

/**
 * Process entities of type "taxonomyIndex".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_taxonomy_index",
 *   name = @Translation("TaxonomyIndex filter"),
 *   types = {
 *     "taxonomy_index_tid",
 *   },
 * )
 */
class TaxonomyIndex extends FilterPluginBase {

  public function build(array $filter) {
    $label = \Drupal::service('entity_type.manager')->getStorage('taxonomy_vocabulary')->load($filter['vid'])->label();
    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree($filter['vid']);
    $options = [];
    $values = [];
    foreach ($terms as $option) {
      $term = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->load($option->tid);
      if ($term != NULL) {
        $options[$option->tid] = $term->label();
        $values[$option->tid] = TRUE;
      }
    }
    return [
      'description' => 'Choose ' . $filter['vid'],
      'label' => $label,
      'options' => $options,
      'type' => 'multiplecheckbox',
      'name' => $filter['field'],
      'value' => $values,
    ];
  }

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
