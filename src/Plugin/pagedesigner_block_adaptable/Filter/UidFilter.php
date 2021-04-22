<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

/**
 * Process entities of type "UID Filter".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_uid",
 *   name = @Translation("UID filter"),
 *   types = {
 *     "user_name"
 *   }
 * )
 */
class UidFilter extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) {
    $result = \Drupal::database()->query("SELECT name, uid FROM users_field_data");

    $options = [];
    $values = [];
    if ($result) {
      foreach ($result as $row) {
        if (!empty($row->name)) {
          $options[$row->uid] = $row->name;
          $values[$row->uid] = FALSE;
        }
      }
    }
    return [
      'description' => 'Choose user',
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
