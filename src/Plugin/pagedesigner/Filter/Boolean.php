<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
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
  public function build(array $filter) {
    $label = (string) \Drupal::service('entity.manager')->getFieldStorageDefinitions('node')[$filter['field']]->getLabel();
    return [
      'description' => 'Select an option',
      'label' => $label,
      'options' => [
        '1' => 'Yes',
        '0' => 'No',
      ],
      'type' => 'select',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function patch($value) {
    return $value;
  }

}
