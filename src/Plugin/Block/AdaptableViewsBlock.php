<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\Block;

use Drupal\views\Plugin\Block\ViewsBlock;

/**
 * Provides a filter views block.
 *
 * @Block(
 *   id = "adaptable_views_block",
 *   admin_label = @Translation("Adaptable Views Block"),
 *   category = @Translation("Adaptable Lists (Views)"),
 *   deriver = "Drupal\views\Plugin\Derivative\ViewsBlock"
 * )
 */
class AdaptableViewsBlock extends ViewsBlock {

  /**
   *
   */
  protected $pagedesignerElement = NULL;

  /**
   *
   */
  public function setPagedesignerElement($pagedesignerElement) {
    $this->pagedesignerElement = $pagedesignerElement;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->pagedesignerElement != NULL) {
      $origFilters = $view_filters = $this->view->getDisplay()->getOption('filters');
      foreach (json_decode($this->pagedesignerElement->field_block_settings->value, TRUE) as $key => $filter) {
        if ($key == 'content_type') {
          $key = 'type';
        }
        if (is_string($filter['value'])) {
          $view_filters[$key]['value'] = $filter['value'];
          continue;
        }
        foreach ($filter['value'] as $item => $enabled) {
          if ($enabled) {
            $view_filters[$key]['value'][$item] = $item;
          }
          else {
            unset($view_filters[$key]['value'][$item]);
            unset($view_filters[$key]['value']['all']);
          }
        }

      }
      $this->view->getDisplay()->overrideOption('filters', $view_filters);
      $build = parent::build();
      $this->view->getDisplay()->overrideOption('filters', $origFilters);
    }
    else {
      $build = parent::build();
    }
    return $build;
  }

}
