<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\views\filter\NidViewsFilter;

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
class NidFilter extends EntityFilter {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    if (empty($filter['pagedesigner_trait_type'])) {
      $filter['pagedesigner_trait_type'] = 'multiplecheckbox';
    }
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'node';
    }
    return parent::build($filter);
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'node';
    }
    return parent::patch($filter, $value);
  }

}
