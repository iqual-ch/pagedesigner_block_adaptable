<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
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
class TaxonomyIndex extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) {
    $label = \Drupal::service('entity_type.manager')->getStorage('taxonomy_vocabulary')->load($filter['vid'])->label();
    $terms = \Drupal::entityQuery('taxonomy_term')->condition('vid', $filter['vid'])->execute();
    $options = [];
    $values = [];
    $langcode = \Drupal::languageManager()
      ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
      ->getId();
    foreach ($terms as $option) {
      $result = \Drupal::database()->query(
        "SELECT name (SELECT name, tid FROM taxonomy_term_field_data WHERE tid = :tid and (langcode = :langcode OR default_langcode = 1) ORDER BY default_langcode ASC) as sub GROUP BY tid",
        [
          ':tid' => $option,
          ':langcode' => $langcode,
        ]
      );
      $term = $result->fetch();
      if ($term) {
        $options[$option] = $term->name;
        $values[$option] = FALSE;
      }
    }
    return [
      'description' => 'Choose ' . $filter['vid'],
      'label' => $label,
      'options' => $options,
      'type' => 'multiplecheckbox',
      'name' => $filter['field'],
      'value' => $values,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function patch($value) {
    $result = [];
    foreach ($value as $filter_key => $item) {
      $term = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->load($filter_key);
      if ($term != NULL) {
        if ($item) {
          $result[$filter_key] = $filter_key;
        }
        else {
          unset($result[$filter_key]);
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function serialize($value) {
    $values = [];
    foreach ($value as $key => $item) {
      if ($item != FALSE) {
        $values[$key] = TRUE;
      }
      else {
        unset($values[$key]);
      }
    }
    return $values;
  }

}
