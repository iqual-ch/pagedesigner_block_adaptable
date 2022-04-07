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
    $entityType = (!empty($filter['entity_type'])) ? $filter['entity_type'] : NULL;
    if ($entityType != NULL) {
      $options = [];
      $values = [];
      $bundleFilter = NULL;
      if ($entityType == 'taxonomy_term') {
        if (!empty($filter['filters']['vid'])) {
          $bundleFilter = $filter['filters']['vid'];
        }
        $filter['entity_field'] = 'tid';
        $data = NidViewsFilter::getData($filter, ['name'], $bundleFilter);
        foreach ($data as $key => $record) {
          $options[$key] = $record->name;
        }
      }
      else {
        if (!empty($filter['filters']['type'])) {
          $bundleFilter = $filter['filters']['type'];
        }
        if ($entityType == "commerce_product_variation") {
          $data = NidViewsFilter::getData($filter, ['title', 'sku'], $bundleFilter);
          foreach ($data as $key => $record) {
            $options[$key] = $record->title . ' - ' . $record->sku;
          }
        }
        else {
          $data = NidViewsFilter::getData($filter, ['title'], $bundleFilter);
          foreach ($data as $key => $record) {
            $options[$key] = $record->title;
          }
        }
        $values = array_keys($data);
        return [
          'description' => t('Choose @label', ['@label' => $filter['expose']['label']]),
          'label' => $filter['expose']['label'],
          'options' => $options,
          'type' => 'select',
          'name' => $filter['field'],
          'value' => $values,
        ];
      }
    }
    elseif (substr($filter['field'], -10) == '_target_id' && substr($filter['field'], 0, 6) == 'field_') {
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
      return [
        'description' => t('Choose @label', ['@label' => $filter['expose']['label']]),
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'select',
        'name' => $filter['field'],
        'value' => $values,
      ];
    }
    return [
      'label' => $filter['field'],
      'type' => 'text',
    ];
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
