<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\commerce_product\Entity\ProductVariation;
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

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) {
    if (isset($filter['bundle_filter'])) {
      $bundleFilter = $filter['bundle_filter'];
    }
    else {
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
        $result = \Drupal::database()->query("SELECT title, nid FROM node_field_data WHERE type in (:types[])", [
          ':types[]' => $bundles,
        ]);
      }
      else {
        $result = \Drupal::database()->query("SELECT title, nid FROM node_field_data");
      }
      $options = [];
      $values = [];
      if ($result) {
        // while($row = $result->fetchObj()) {.
        foreach ($result as $row) {
          if (!empty($row->label)) {
            $options[$row->nid] = $row->title;
            $values[] = $row->nid;
          }
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
      $items = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple();
      $options = [];
      $values = [];
      foreach ($items as $item) {
        if ($item != NULL) {
          $options[$item->id()] = $item->label();
          // Support for the commerce variation entity type.
          if ($entity_type == 'commerce_product_variation') {
            /** @var ProductVariation $variation */
            $variation = ProductVariation::load($item->id());
            // If it is a variation, get the label and SKU from the variation
            // instead of only the product label, since it will be the same for
            // each variation.
            $options[$item->id()] = $variation->label() . ' - ' . $variation->getSku();
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
    return ['value' => $value];
  }

}
