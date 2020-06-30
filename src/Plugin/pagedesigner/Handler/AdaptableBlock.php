<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\views\Views;
use Drupal\block\Entity\Block;
use Drupal\pagedesigner_block_adaptable\AdaptableBlockViewBuilder;
use Drupal\ui_patterns\Definition\PatternDefinitionField;
use Drupal\ui_patterns\Definition\PatternDefinition;
use Drupal\pagedesigner\Plugin\pagedesigner\HandlerPluginInterface;
use Drupal\Component\Plugin\PluginBase;

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
class AdaptableBlock extends PluginBase implements HandlerPluginInterface {

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
        $block = Block::load($pattern->getAdditional()['block']);
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
  protected function augmentDefinition(PatternDefinition &$pattern, Block &$block) {
    $pluginId = $block->getPluginId();
    if (strpos($pluginId, 'adaptable_views_block') !== 0) {
      return;
    }
    $viewInfo = explode('-', explode(':', $pluginId)[1]);
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
    $filterManager = \Drupal::service('plugin.manager.pagedesigner_block_adaptable_filter');

    $bundleFilter = NULL;
    // Expose the filters from the block so they can be
    // configurable in the pagedesigner for the editor.
    foreach ($filters as $key => $filter) {
      $filterPlugin = $filterManager->getInstance(['type' => $filter['plugin_id']])[0];
      if ($filter['plugin_id'] == 'bundle') {
        if ($filter['field'] === 'type') {
          $filter['bundle_filter'] = $filter;
        }
      }
      if ($filter['plugin_id'] == 'numeric') {
        $filter['filters'] = $filters;
      }
      if (empty($filter['exposed'])) {
        continue;
      }
      // Create multiple choice for the bundle plugin type.
      // Workaround for the content type, because there
      // can not be a key named 'type' in the definition.
      if ($filter['field'] === 'type') {
        $fields['content_type'] = $filterPlugin->build($filter);
      }
      else {
        $fields[$filter['field']] = $filterPlugin->build($filter);
      }
    }
    $pager = $view->getDisplay()->getOption('pager');
    if ($pager['type'] != 'none') {
      $fields['pager_items_per_page'] = [
        'description' => 'Select the number of items per page.',
        'label' => t('Items per page') . ' (' . t('Default: @value', ['@value' => $pager['options']['items_per_page']]) . ')',
        'type' => 'text',
        'name' => 'pager_items_per_page',
        'value' => '',
      ];
    }
    $fields['pager_offset'] = [
      'description' => 'Select the offset of items.',
      'label' => t('Offset') . ' (' . t('Default: @value', ['@value' => $pager['options']['offset']]) . ')',
      'type' => 'text',
      'name' => 'pager_offset',
      'value' => '',
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
      $settings = json_decode($entity->field_block_settings->value, true);
      $filterManager = \Drupal::service('plugin.manager.pagedesigner_block_adaptable_filter');
      if (!empty($settings['filters'])) {
        foreach ($settings['filters'] as $key => $item) {

          $filter = $filterManager->getInstance(['type' => $item['type']])[0];
          $fields[$key] = $filter->serialize($item['value']);
        }
      }
      if (!empty($settings['pager'])) {
        foreach ($settings['pager'] as $key => $item) {
          $fields['pager_' . $key] = $item['value'];
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
      $filterManager = \Drupal::service('plugin.manager.pagedesigner_block_adaptable_filter');

      foreach ($data['fields'] as $key => $value) {
        if (isset($filters[$key])) {
          $filter = $filterManager->getInstance(['type' => $filters[$key]['plugin_id']])[0];
          $view_filters[$key]['value'] = $filter->patch($value);
          $view_filters[$key]['type'] = $filters[$key]['plugin_id'];
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

      $pager = $display->getOption('pager');
      $pagerSettings = [];
      if (isset($data['fields']['pager_items_per_page'])) {
        $pagerSettings['items_per_page'] = $data['fields']['pager_items_per_page'];
      }
      if (isset($data['fields']['pager_offset'])) {
        $pagerSettings['offset'] = $data['fields']['pager_offset'];
      }

      $entity->field_block_settings->value = json_encode(['filters' => $view_filters, 'pager' => $pagerSettings]);
      $entity->save();
      \Drupal::service('cache_tags.invalidator')->invalidateTags($view->storage->getCacheTagsToInvalidate());
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
  public function restore(Element $entity) {
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
