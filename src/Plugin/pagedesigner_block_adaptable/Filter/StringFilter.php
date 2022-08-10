<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

/**
 * Process entities of type "string".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_string",
 *   name = @Translation("String filter"),
 *   types = {
 *     "string",
 *   },
 * )
 */
class StringFilter extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    if (!empty(\Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')[$filter['field']])) {
      $label = (string) \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')[$filter['field']]->getLabel();
    }
    else {
      $label = t('Set value');
    }

    return [
      'label' => $label,
      'type' => 'text',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    return $value;
  }

}
