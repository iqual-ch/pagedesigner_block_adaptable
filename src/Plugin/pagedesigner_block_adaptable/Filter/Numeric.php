<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\views\filter\NidViewsFilter;

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
class Numeric extends FilterPluginBase {

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
    if (isset($filter['filters'])) {
      $filters = $filter['filters'];
    }
    else {
      $filters = [];
    }
    if ($filter['field'] == 'nid') {
      $options = NidViewsFilter::getOptions($bundleFilter);
      $values = array_keys($options);
      return [
        'description' => 'Choose node',
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'select',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }
    elseif (substr($filter['field'], -10) == '_target_id' && substr($filter['field'], 0, 6) == 'field_') {
      $bundle = substr($filter['field'], 6, -10);
      $label = $filter['expose']['label'];
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
      return [
        'description' => 'Choose ' . $label,
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'select',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }
    elseif ($filter['field'] == 'tid_raw') {
      if (isset($filters['vid'])) {
        $options = [];
        $values = [];
        foreach ($filters['vid']['value'] as $vid => $vocabulary) {
          $terms = \Drupal::service('entity_type.manager')
            ->getStorage('taxonomy_term')
            ->loadTree($vid);

          foreach ($terms as $term) {
            if ($term != NULL) {
              $options[$term->tid] = $term->name;
            }
          }
        }
        return [
          'description' => 'Choose term',
          'label' => $filter['expose']['label'],
          'options' => $options,
          'type' => 'select',
          'name' => $filter['field'],
          'value' => $values,
        ];
      }
      else {
        return [
          'label' => $filter['field'],
          'type' => 'text',
        ];
      }
    }
    elseif (substr($filter['field'], -3) == '_id') {
      $entity_type = $filter['entity_type'];
      $label = substr($filter['field'], 0, -3);
      $options = [];
      $values = [];
      if (strpos($entity_type, 'commerce_product') == 0) {
        $database = \Drupal::database();
        $table_name = $entity_type . '_field_data';
        $query = $database->select($table_name, 'u');
        if ($entity_type == "commerce_product") {
          $query->fields('u', ['product_id', 'title']);
          $result = $query->execute();
          foreach ($result as $record) {
            $options[$record->product_id] = $record->title;
          }
        }
        if ($entity_type == "commerce_product_variation") {
          $query_fields = ['variation_id', 'title', 'sku'];
          $query->fields('u', ['variation_id', 'title', 'sku']);
          $result = $query->execute();
          foreach ($result as $record) {
            $options[$record->variation_id] = $record->title . ' - ' . $record->sku;
          }
        }
      }
      else {
        $items = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple();
        foreach ($items as $item) {
          if ($item != NULL) {
            $options[$item->id()] = $item->label();
          }
        }
      }
      
      return [
        'description' => 'Choose ' . $label,
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'select',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }
    else {
      return [
        'label' => $filter['field'],
        'type' => 'text',
      ];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function patch($value) {
    return $value;
  }

  /**
   * {@inheritDoc}
   */
  public function serialize($value) {
    return [$value];
  }

}
