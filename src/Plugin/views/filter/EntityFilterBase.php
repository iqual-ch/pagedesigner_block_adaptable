<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\filter;

/**
 * @file
 */

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Language\LanguageInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Undocumented class
 */
class EntityFilterBase extends FilterPluginBase {

  /**
   * Extract the bundles from the filter.
   *
   * @param array $filter
   *   The filter definition.
   *
   * @return array
   *   The bundles retrieved from the filter.
   */
  public static function getBundles(array $filter) {
    $bundles = [];
    // Get vid for taxonomy terms.
    if (!empty($filter['vid'])) {
      $bundles[$filter['vid']] = $filter['vid'];
    }
    // Get bundles from filter settings.
    elseif (!empty($filter['pagedesigner_bundles'])) {
      foreach ($filter['pagedesigner_bundles'] as $bundle) {
        if (is_string($bundle)) {
          $bundles[$bundle] = $bundle;
        }
      }
    }
    // Get bundles from type filter on view (@deprecated in project:3.0.0).
    elseif (!empty($filter['filters']['type'])) {
      $bundles = $filter['filters']['type']['value'];
    }
    return $bundles;
  }

  /**
   * Gets the entity data based on the filter.
   *
   * @param \Drupal\Core\Entity\ContentEntityType $type
   *   The content entity type.
   * @param array $bundles
   *   Bundles to limit the result to.
   * @param array $fields
   *   List of additional fields to query.
   *
   * @return array
   *   The records, keyed by entity id.
   */
  public static function getData(ContentEntityType $type, array $bundles = NULL, array $fields = []) {
    $options = [];
    $table = $type->getDataTable();
    $idField = $type->getKey('id');
    $selectFields = $fields;
    $selectFields[] = $type->getKey('label');
    $selectFields[] = $idField;
    $langcode = \Drupal::languageManager()
      ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
      ->getId();
    $database = \Drupal::database();
    $query = $database->select($table, 'u');
    $query->fields('u', $selectFields);
    $orGroup = $query->orConditionGroup()
      ->condition('langcode', $langcode, 'LIKE')
      ->condition('default_langcode', 1);
    $query->condition($orGroup);
    $query->orderBy('default_langcode', 'DESC');
    foreach ($fields as $field) {
      $query->orderBy($field, 'ASC');
    }
    if (!empty($bundles)) {
      $query->condition($type->getKey('bundle'), $bundles, 'IN');
    }
    $result = $query->execute()->fetchAll();
    foreach ($result as $record) {
      $options[$record->{$idField}] = $record;
    }
    return $options;
  }
}
