<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

/**
 * Process entities of type "boolean".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_boolean",
 *   name = @Translation("Boolean filter"),
 *   types = {
 *     "boolean",
 *   },
 * )
 */
class Boolean extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    $storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node');

    if (!empty($storage_definitions[$filter['field']])) {
      $label = (string) $storage_definitions[$filter['field']]->getLabel();
    }
    else {
      if (substr($filter['field'], -6, 6) == '_value') {
        $field_name = substr($filter['field'], 0, strlen($filter['field']) - 6);
      }
      if (!empty($field_name)) {
        $label = (string) $storage_definitions[$field_name]->getLabel();
      }
      else {
        $label = $filter['field'];
      }

    }
    return [
      'description' => 'Select an option',
      'label' => $label,
      'options' => [
        '1' => t('Yes'),
        '0' => t('No'),
      ],
      'type' => 'select',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    if (is_array($value)) {
      $value = reset($value);
    }
    return $value;
  }

}
