<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner\Plugin\FieldHandlerBase;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\views\filter\NidViewsFilter;

/**
 * Process entities of type "UID Filter".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_uid",
 *   name = @Translation("UID filter"),
 *   types = {
 *     "user_name"
 *   }
 * )
 */
class UidFilter extends EntityFilter {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    if (empty($filter['pagedesigner_trait_type'])) {
      $filter['pagedesigner_trait_type'] = 'multiplecheckbox';
    }
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'user';
    }
    return parent::build($filter);
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'user';
    }
    return parent::patch($filter, $value);
  }

}
