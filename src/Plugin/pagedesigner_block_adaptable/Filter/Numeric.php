<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

/**
 * Process entities of type "numeric".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_numeric",
 *   name = @Translation("Numeric filter"),
 *   types = {
 *     "numeric",
 *   },
 * )
 */
class Numeric extends EntityFilter {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    $renderArray = [
      'label' => $filter['field'],
      'type' => 'text',
    ];
    if (empty($filter['pagedesigner_trait_type'])) {
      $filter['pagedesigner_trait_type'] = 'select';
    }
    // Handle reference fields (@deprecated in project:3.0.0).
    if (substr($filter['field'], -10) == '_target_id' && substr($filter['field'], 0, 6) == 'field_') {
      $bundle = substr($filter['field'], 6, -10);
      $items = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
        'type' => $bundle,
      ]);
      $options = [];
      $values = [];
      foreach ($items as $item) {
        if ($item != NULL) {
          $options[$item->id()] = $item->label();
        }
      }
      $renderArray = [
        'description' => t('Choose @label', ['@label' => $filter['expose']['label']]),
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => $filter['pagedesigner_trait_type'],
        'name' => $filter['id']['label'],
        'value' => $values,
      ];
    }
    else {
      $renderArray = parent::build($filter);
    }
    return $renderArray;
  }

}
