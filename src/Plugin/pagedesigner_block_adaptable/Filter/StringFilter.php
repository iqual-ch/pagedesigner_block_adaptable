<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
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

  public function build(array $filter) {
    $label = (string) \Drupal::service('entity.manager')->getFieldStorageDefinitions('node')[$filter['field']]->getLabel();
    return [
      'label' => $label,
      'type' => 'text',
    ];
  }

  public function patch($value) {
    return $value;
  }

}
