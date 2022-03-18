<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\Core\Language\LanguageInterface;

/**
 * Process entities of type "NID Filter".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_nid",
 *   name = @Translation("NID filter"),
 *   types = {
 *     "nid_views_filter",
 *   }
 * )
 */
class NidFilter extends FilterPluginBase {

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
    $langcode = \Drupal::languageManager()
      ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
      ->getId();
    if ($bundleFilter) {
      $bundles = [];
      foreach ($bundleFilter['value'] as $key => $option) {
        $bundles[] = $key;
      }
      $result = \Drupal::database()->query(
        "SELECT title, nid, langcode FROM (SELECT title, nid, langcode FROM node_field_data WHERE (langcode = :langcode OR default_langcode = 1) AND type in (:types[]) ORDER BY default_langcode ASC) as sub GROUP BY nid ORDER BY title ASC",
        [
          ':langcode' => $langcode,
          ':types[]' => $bundles,
        ]);
    }
    else {
      $result = \Drupal::database()->query("SELECT title, nid, langcode FROM (SELECT title, nid, langcode FROM node_field_data WHERE (langcode = :langcode OR default_langcode = 1) ORDER BY default_langcode ASC) as sub GROUP BY nid ORDER BY title ASC",
      [
        ':langcode' => $langcode,
      ]);
    }
    $options = [];
    $values = [];
    if ($result) {
      foreach ($result as $row) {
        if (!empty($row->title)) {
          $options[$row->nid] = $row->title;
          $values[$row->nid] = FALSE;
        }
      }
    }
    return [
      'description' => 'Choose node',
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
