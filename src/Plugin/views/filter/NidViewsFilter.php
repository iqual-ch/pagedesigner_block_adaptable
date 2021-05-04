<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

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
