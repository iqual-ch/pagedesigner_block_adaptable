<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;

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
    $options = $this->loadOptionsAsTree($filter['vid']);
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
   * Loads the tree of a vocabulary.
   *
   * @param string $vocabulary
   *   Machine name.
   *
   * @return array
   *   Vocabulary as tree.
   */
  private function loadOptionsAsTree(string $vocabulary) {
    $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree($vocabulary);
    $tree = [];
    $weight = 0;
    foreach ($terms as $treeObject) {
      $this->addOptionToTree($tree, $treeObject, $vocabulary, $weight);
    }

    return $tree;
  }

  /**
   * Recursively appends a new option to the option tree.
   *
   * @param array $tree
   *   Reference to tree.
   * @param object $option
   *   Option as object.
   * @param string $vocabulary
   *   Vocabulary machine name.
   * @param int $weight
   *   Sorting options.
   */
  protected function addOptionToTree(array &$tree, $option, string $vocabulary, int &$weight) {

    if ($option->depth != 0) {
      return;
    }

    $tree[$option->tid] = [
      'label' => $option->name,
      'weight' => $weight++,
      'children' => [],
    ];

    $children = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadChildren($option->tid);
    if (!$children) {
      return;
    }

    $childTreeObjects = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree($vocabulary, $option->tid);

    foreach ($children as $child) {
      foreach ($childTreeObjects as $childTreeObject) {
        if ($childTreeObject->tid == $child->id()) {
          $this->addOptionToTree($tree[$option->tid]['children'], $childTreeObject, $vocabulary, $weight);
        }
      }
    }
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
          $result[$filter_key] = FALSE;
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
        $values[$key] = FALSE;
      }
    }
    return $values;
  }

}
