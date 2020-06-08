<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

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

  public function build(array $filter) {
    if (isset($filter['bundle_filter'])) {
      $bundleFilter = $filter['bundle_filter'];
    }else {
      $bundleFilter = NULL;
    }
    if (isset($filter['filters'])) {
      $filters = $filter['filters'];
    }
    else {
      $filters = [];
    }
    if ($filter['field'] == 'nid') {
      $nodes = [];
      if ($bundleFilter) {
        $bundles = [];
        foreach ($bundleFilter['value'] as $key => $option) {
          $bundles[] = $key;
        }
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
          'type' => $bundles,
        ]);
      }
      else {
        $nids = \Drupal::entityQuery('node')->execute();
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
      }
      $options = [];
      $values = [];
      foreach ($nodes as $node) {
        if ($node != NULL) {
          $options[$node->id()] = $node->label();
          $values[] = $node->id();
        }
      }
      return [
        'description' => 'Choose node',
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'select',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }elseif ($filter['field'] == 'tid_raw') {
      if( isset($filters['vid']) ){
        $options = [];
        $values = [];
        foreach ( $filters['vid']['value'] as $vid => $vocabulary ) {
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
      }else{
        return [
          'label' => $filter['field'],
          'type' => 'text',
        ];
      }
    } elseif (substr($filter['field'], -3) == '_id') {
      $entity_type = $filter['entity_type'];
      $label = substr($filter['field'],0,-3);
      $items = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple();
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
    else {
      return [
        'label' => $filter['field'],
        'type' => 'text',
      ];
    }
  }

  public function patch($value) {
    return $value;
  }

}
