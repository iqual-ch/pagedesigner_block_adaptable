<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\filter;

use Drupal\Core\Language\LanguageInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filters by nid or status of project.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tid_views_filter")
 */
class NidViewsFilter extends InOperator {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Filter by nid');
    $this->definition['options callback'] = [$this, 'generateOptions'];
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $filters = $this->view->getDisplay()->getOption('filters');
    foreach ($filters as $key => $filter) {
      if ($filter['plugin_id'] == 'bundle') {
        if ($filter['field'] === 'type') {
          $bundleFilter = $filter;
        }
      }
    }
    if (isset($bundleFilter)) {
      $data = static::getData($bundleFilter, ['title'], $bundleFilter);
      $options = [];
      foreach ($data as $key => $record) {
        $options[$key] = $record->title;
      }
      return $options;
    }
    return [];
  }

  /**
   *
   */
  public static function getData(array $filter, array $fields, array $bundleFilter = NULL) {
    $options = [];
    $table = $filter['table'];
    $idField = $filter['entity_field'];
    $selectFields = $fields;
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
    if (!empty($bundleFilter)) {
      $bundles = [];
      foreach ($bundleFilter['value'] as $key => $option) {
        $bundles[] = $key;
      }
      $query->condition($bundleFilter['entity_field'], $bundles, 'IN');
    }
    $result = $query->execute()->fetchAll();
    foreach ($result as $record) {
      $options[$record->{$idField}] = $record;
    }
    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $this->query->addWhere('AND', 'node_field_data.nid', $this->value, 'IN');
    }
  }

}
