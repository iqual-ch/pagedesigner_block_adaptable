<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\Block;

use Drupal\views\Plugin\Block\ViewsBlock;

/**
 * Provides an adaptable views block.
 *
 * @Block(
 *   id = "adaptable_views_block",
 *   admin_label = @Translation("Adaptable Views Block"),
 *   category = @Translation("Adaptable Lists (Views)"),
 *   deriver = "Drupal\pagedesigner_block_adaptable\Plugin\Derivative\AdaptableViewsBlock"
 * )
 */
class AdaptableViewsBlock extends ViewsBlock {

  /**
   * The pagedesigner element to get the data from.
   *
   * @var \Drupal\pagedesigner\Entity\Element
   */
  protected $pagedesignerElement = NULL;

  /**
   * Set the pagedesigner element to get the data from.
   *
   * @param \Drupal\pagedesigner\Entity\Element $pagedesignerElement
   *   The pagedesigner element to get the data from.
   */
  public function setPagedesignerElement($pagedesignerElement) {
    $this->pagedesignerElement = $pagedesignerElement;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->pagedesignerElement != NULL) {
      // Store the original options.
      $filters = $this->view->getDisplay()->getOption('filters');
      $pager = $this->view->getDisplay()->getOption('pager');

      // Get the settings.
      $settings = json_decode($this->pagedesignerElement->field_block_settings->value, TRUE);

      // Alter the options for the build.
      if (!empty($settings['filters'])) {
        $this->alterFilters($settings['filters']);
      }
      if (!empty($settings['pager'])) {
        $this->alterPager($settings['pager']);
      }

      // Build the view.
      $build = parent::build();

      // Reset the options for the next build.
      $this->view->getDisplay()->overrideOption('filters', $filters);
      $this->view->getDisplay()->overrideOption('pager', $pager);
    }
    else {
      $build = parent::build();
    }
    return $build;
  }

  /**
   * Alter the filter definition before rendering the block.
   *
   * @param array $customFilters
   *   The custom filters.
   */
  protected function alterFilters($customFilters) {
    $filters = $this->view->getDisplay()->getOption('filters');
    $filterManager = \Drupal::service('plugin.manager.pagedesigner_block_adaptable_filter');

    foreach ($customFilters as $key => $filter) {
      if ($key == 'content_type') {
        $key = 'type';
      }
      if (isset($filters[$key])) {
        $filterPlugin = $filterManager->getInstance(['type' => $filters[$key]['plugin_id']])[0];
        if ($filters[$key]['plugin_id'] == 'numeric') {
          $filters[$key]['value']['value'] = $filterPlugin->patch($filter['value']);
        } else {
          $filters[$key]['value'] = $filterPlugin->patch($filter['value']);
        }
      }
      elseif ($key == 'content_type') {
        foreach ($filter['value'] as $filter_key => $item) {
          if ($item) {
            $filters[$key]['value'][$filter_key] = TRUE;
          }
          else {
            $filters[$key]['value'][$filter_key] = FALSE;
            $filters[$key]['value']['all'] = FALSE;
          }
        }
      }
    }
    $this->view->getDisplay()->overrideOption('filters', $filters);
  }

  /**
   *
   */
  protected function alterPager($customPager) {
    $pager = $this->view->getDisplay()->getOption('pager');
    foreach ($customPager as $key => $setting) {
      if (!empty($setting)) {
        $pager['options'][$key] = $setting;
      }
    }
    $this->view->getDisplay()->overrideOption('pager', $pager);
  }

}
