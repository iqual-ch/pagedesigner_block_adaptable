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
 * @ViewsFilter("nid_views_filter")
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
      return static::getOptions($bundleFilter);
    }
    return [];
  }

  /**
   *
   */
  public static function getOptions($bundleFilter) {
    $options = [];
    $langcode = \Drupal::languageManager()
      ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
      ->getId();
    if ($bundleFilter) {
      $bundles = [];
      foreach ($bundleFilter['value'] as $key => $option) {
        $bundles[] = $key;
      }
      $result = \Drupal::database()->query(
      "SELECT title, nid, langcode FROM (SELECT title, nid, langcode FROM node_field_data WHERE (langcode = :langcode OR default_langcode = 1) AND type in (:types[]) ORDER BY default_langcode ASC LIMIT 9999999) as sub GROUP BY nid ORDER BY title ASC",
      [
        ':langcode' => $langcode,
        ':types[]' => $bundles,
      ]);
    }
    else {
      $result = \Drupal::database()->query("SELECT title, nid, langcode FROM (SELECT title, nid, langcode FROM node_field_data WHERE (langcode = :langcode OR default_langcode = 1) ORDER BY default_langcode ASC LIMIT 9999999) as sub GROUP BY title ORDER BY nid ASC",
      [
        ':langcode' => $langcode,
      ]);
    }
    if ($result) {
      foreach ($result as $row) {
        if (!empty($row->title)) {
          $options[$row->nid] = $row->title;
        }
      }
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
