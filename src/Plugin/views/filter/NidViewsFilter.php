<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Filters by nid or status of project.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("nid_views_filter")
 * @deprecated in project:2.1.0 and is removed from project:3.0.0 Use
 *   pba_entity_filter instead.
 * @see 
 */
class NidViewsFilter extends EntityFilterBase {

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
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $this->query->addWhere('AND', 'node_field_data.nid', $this->value, 'IN');
    }
  }

}
