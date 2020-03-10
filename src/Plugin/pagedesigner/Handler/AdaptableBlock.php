<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\views\Views;
use Drupal\block\Entity\Block as BlockEntity;
use Drupal\pagedesigner_block\Plugin\pagedesigner\Handler\Block;
use Drupal\pagedesigner_block_adaptable\AdaptableBlockViewBuilder;
use Drupal\ui_patterns\Definition\PatternDefinitionField;
use Drupal\ui_patterns\Definition\PatternDefinition;

/**
 * Process entities of type "block".
 *
 * Adds the ability to adapt the underlying view.
 *
 * @PagedesignerHandler(
 *   id = "adaptable_block",
 *   name = @Translation("Adaptable block handler"),
 *   types = {
 *      "block"
 *   },
 *   weight = 1000,
 * )
 */
class AdaptableBlock extends Block {

  /**
   * {@inheritdoc}
   */
  public function collectAttachments(array &$attachments) {
  }

  /**
   * {@inheritDoc}
   */
  public function collectPatterns(array &$patterns) {
    foreach ($patterns as $pattern) {
      if (isset($pattern->getAdditional()['block'])) {
        $block = BlockEntity::load($pattern->getAdditional()['block']);
        $this->augmentDefinition($pattern, $block);
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function adaptPatterns(array &$patterns) {
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(PatternDefinitionField &$field, array &$fieldArray) {
  }

  /**
   *
   */
  protected function augmentDefinition(PatternDefinition &$pattern, BlockEntity &$block) {
    if (strpos($block->getPlugin()->pluginId, 'adaptable_views_block') !== 0) {
      return;
    }
    $viewInfo = explode('-', explode(':', $block->getPlugin()->pluginId)[1]);
    $view = Views::getView($viewInfo[0]);
    if ($view == NULL) {
      return;
    }
    $view->setDisplay($viewInfo[1]);
    $display = $view->getDisplay();
    if ($display == NULL) {
      return;
    }
    $fields = $pattern->getFields();
    $filters = $view->getDisplay()->getOption('filters');

    $bundleFilter = NULL;
    // Expose the filters from the block so they can be
    // configurable in the pagedesigner for the editor.
    foreach ($filters as $key => $filter) {
      if (empty($filter['exposed'])) {
        continue;
      }
      // Create multiple choice for the bundle plugin type.
      if ($filter['plugin_id'] == 'bundle') {
        // Workaround for the content type, because there
        // can not be a key named 'type' in the definition.
        if ($filter['field'] === 'type') {
          $bundleFilter = $filter;
          $options = [];
          $values = [];
          foreach ($filter['value'] as $key => $option) {
            if (\Drupal::entityTypeManager()->getStorage('node_type')->load($option) != NULL) {
              $options[$key] = \Drupal::entityTypeManager()->getStorage('node_type')->load($option)->label();
              $values[$key] = TRUE;
            }
          }
          $fields['content_type'] = [
            'description' => 'Choose type',
            'label' => 'Type',
            'options' => $options,
            'type' => 'multiplecheckbox',
            'name' => 'content_type',
            'value' => $values,
          ];
        }
        else {
          $values = [];
          $fields[$filter['field']] = [
            'description' => 'Choose options',
            'label' => $filter['field'],
            'options' => $filter['value'],
            'type' => 'multiplecheckbox',
            'name' => $filter['field'],
            'value' => $values,
          ];
        }
      }
      // Create select for the boolean plugin type.
      if ($filter['plugin_id'] == 'boolean') {
        $label = (string) \Drupal::service('entity.manager')->getFieldStorageDefinitions('node')[$filter['field']]->getLabel();
        $fields[$filter['field']] = [
          'description' => 'Select an option',
          'label' => $label,
          'options' => [
            '1' => 'Yes',
            '0' => 'No',
          ],
          'type' => 'select',
        ];
      }
      // Create a text field for the string plugin type.
      if ($filter['plugin_id'] == 'string') {
        $label = (string) \Drupal::service('entity.manager')->getFieldStorageDefinitions('node')[$filter['field']]->getLabel();
        $fields[$filter['field']] = [
          'label' => $label,
          'type' => 'text',
        ];
      }
      if ($filter['plugin_id'] == 'taxonomy_index_tid') {
        $label = \Drupal::service('entity_type.manager')->getStorage('taxonomy_vocabulary')->load($filter['vid'])->label();
        $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadTree($filter['vid']);
        $options = [];
        $values = [];
        foreach ($terms as $option) {
          $term = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->load($option->tid);
          if ($term != NULL) {
            $options[$option->tid] = $term->label();
            $values[$option->tid] = TRUE;
          }
        }
        $fields[$filter['field']] = [
          'description' => 'Choose ' . $filter['vid'],
          'label' => $label,
          'options' => $options,
          'type' => 'multiplecheckbox',
          'name' => $filter['field'],
          'value' => $values,
        ];
      }
      if ($filter['plugin_id'] == 'numeric') {
        if ($filter['field'] == 'nid') {
          $nodes = [];
          if ($bundleFilter) {
            $bundles = [];
            foreach ($bundleFilter['value'] as $key => $option) {
              $bundles[] = $key;
            }
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
              'type' => $bundles,
            ]);
          }
          else {
            $nids = \Drupal::entityQuery('node')->execute();
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
          }
          // $nids = \Drupal::service('entity_type.manager')->getStorage('node')->load($filter['vid'])->label();
          $options = [];
          $values = [];
          foreach ($nodes as $node) {
            if ($node != NULL) {
              $options[$node->id()] = $node->label();
            }
          }
          $fields[$filter['field']] = [
            'description' => 'Choose node',
            'label' => $filter['expose']['label'],
            'options' => $options,
            'type' => 'select',
            'name' => $filter['field'],
            'value' => $values,
          ];
        }
      }
    }
    $pager = $view->getDisplay()->getOption('pager');
    if ($pager['type'] != 'none') {
      $fields['items_per_page'] = [
        'description' => 'Select the number of items per page.',
        'label' => 'Items per page',
        'type' => 'text',
        'name' => 'items_per_page',
        'value' => $pager['options']['items_per_page'],
      ];
    }
    $fields['offset'] = [
      'description' => 'Select the offset of items.',
      'label' => 'Offset',
      'type' => 'text',
      'name' => 'offset',
      'value' => $pager['options']['offset'],
    ];
    $pattern->setFields($fields);
  }

  /**
   * {@inheritdoc}
   */
  public function get(Element $entity, string &$result = '') {
  }

  /**
   * {@inheritdoc}
   */
  public function getContent(Element $entity, array &$list = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function serialize(Element $entity, &$result = []) {
    $fields = [];
    if ($entity->hasField('field_block_settings') && !$entity->field_block_settings->isEmpty()) {
      $settings = json_decode($entity->field_block_settings->value, TRUE);
      if (!empty($settings['filters'])) {
        foreach ($settings['filters'] as $key => $item) {
          $fields[$key] = $item['value'];
        }
      }
      if (!empty($settings['pager'])) {
        foreach ($settings['pager'] as $key => $item) {
          $fields[$key] = $item['value'];
        }
      }
    }
    $result = [
      'fields' => $fields,
    ] + $result;
  }

  /**
   * {@inheritdoc}
   */
  public function describe(Element $entity, array &$result = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function generate($definition, array $data, Element &$entity = NULL) {
    $this->patch($entity, $data);
  }

  /**
   * {@inheritDoc}
   */
  public function patch(Element $entity, array $data) {
    $block = $entity->field_block->entity;
    if ($block != NULL && !empty($data['fields'])) {
      $view_filters = [];
      $viewInfo = explode('-', explode(':', $block->get('plugin'))[1]);
      $view = Views::getView($viewInfo[0]);
      // Check if there is a view for the block.
      if ($view == NULL) {
        return;
      }
      $view->setDisplay($viewInfo[1]);
      $display = $view->getDisplay();
      if ($display == NULL) {
        return;
      }
      // Take the filter data and apply it to the field of the entity.
      $filters = $display->getOption('filters');
      foreach ($data['fields'] as $key => $value) {
        if (isset($filters[$key])) {
          if ($filters[$key]['plugin_id'] === 'boolean' || $filters[$key]['plugin_id'] === 'string') {
            $view_filters[$key]['value'] = $value;
          }
          elseif ($filters[$key]['plugin_id'] === 'numeric') {
            $view_filters[$key]['value'] = $value;
          }
          elseif ($filters[$key]['plugin_id'] === 'bundle') {
            foreach ($value as $filter_key => $item) {
              if ($item) {
                $view_filters[$key]['value'][$filter_key] = TRUE;
              }
              else {
                $view_filters[$key]['value'][$filter_key] = FALSE;
              }
            }
          }
          elseif ($filters[$key]['plugin_id'] == 'taxonomy_index_tid') {
            foreach ($value as $filter_key => $item) {
              if ($item) {
                $view_filters[$key]['value'][$filter_key] = TRUE;
              }
              else {
                $view_filters[$key]['value'][$filter_key] = FALSE;
              }
            }
          }
        }
        elseif ($key == 'content_type') {
          foreach ($value as $filter_key => $item) {
            if ($item) {
              $view_filters[$key]['value'][$filter_key] = TRUE;
            }
            else {
              $view_filters[$key]['value'][$filter_key] = FALSE;
              $view_filters[$key]['value']['all'] = FALSE;
            }
          }
        }
      }
      $pagerSettings = [];
      if (!empty($data['fields']['items_per_page'])) {
        $pagerSettings['items_per_page'] = $data['fields']['items_per_page'];
      }
      if (!empty($data['fields']['offset'])) {
        $pagerSettings['offset'] = $data['fields']['offset'];
      }
      $entity->field_block_settings->value = json_encode(['filters' => $view_filters, 'pager' => $pagerSettings]);
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function copy(Element $entity, Element $container = NULL, Element &$clone = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function delete(Element $entity, bool $remove = FALSE) {
  }

  /**
   * {@inheritdoc}
   */
  public function render(Element $entity, array &$build = []) {
    if ($entity->field_block->entity == NULL) {
      return;
    }
    if ($entity->hasField('field_block_settings') && !$entity->field_block_settings->isEmpty() && strpos($entity->field_block->entity->getPluginId(), 'adaptable_views_block') === 0) {
      $manager = \Drupal::entityTypeManager();
      $definition = $manager->getDefinition('block');
      $handler = $manager->createHandlerInstance('\Drupal\pagedesigner_block_adaptable\AdaptableBlockViewBuilder', $definition);
      $build = AdaptableBlockViewBuilder::lazyBuilderWithElement($entity->field_block->target_id, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function renderForPublic(Element $entity, array &$build = []) {
  }

  /**
   * {@inheritdoc}
   */
  public function renderForEdit(Element $entity, array &$build = []) {
  }

  /**
   * {@inheritdoc}
   */
  public function publish(Element $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function unpublish(Element $entity) {
  }

}
