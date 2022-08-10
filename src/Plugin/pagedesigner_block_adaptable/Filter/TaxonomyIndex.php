<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\Core\Language\LanguageInterface;

/**
 * Process entities of type "taxonomyIndex".
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_taxonomy_index",
 *   name = @Translation("TaxonomyIndex filter"),
 *   types = {
 *     "taxonomy_index_tid",
 *   },
 * )
 */
class TaxonomyIndex extends EntityFilter {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    if (empty($filter['pagedesigner_trait_type'])) {
      $filter['pagedesigner_trait_type'] = 'multiplecheckbox';
    }
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'taxonomy_term';
    }
    return parent::build($filter);
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    if (empty($filter['entity_type'])) {
      $filter['entity_type'] = 'taxonomy_term';
    }
    return parent::patch($filter, $value);
  }

}
