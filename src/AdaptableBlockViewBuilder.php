<?php

namespace Drupal\pagedesigner_block_adaptable;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\block\BlockViewBuilder;
use Drupal\block\Entity\Block;

/**
 * Provides a Block view builder.
 */
class AdaptableBlockViewBuilder extends BlockViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected static function buildPreRenderableBlockWithElement($entity, ModuleHandlerInterface $module_handler, $element) {
    $build = parent::buildPreRenderableBlock($entity, $module_handler);
    $build['#pagedesigner_element'] = $element;
    return $build;
  }

  /**
   *
   */
  public static function lazyBuilderWithElement($block_id, $element) {
    return static::buildPreRenderableBlockWithElement(Block::load($block_id), \Drupal::service('module_handler'), $element);
  }

  /**
   * #pre_render callback for building a block.
   *
   * Renders the content using the provided block plugin, and then:
   * - if there is no content, aborts rendering, and makes sure the block won't
   *   be rendered.
   * - if there is content, moves the contextual links from the block content to
   *   the block itself.
   */
  public static function preRender($build) {
    $build['#block']->getPlugin()->setPagedesignerElement($build['#pagedesigner_element']);
    return parent::preRender($build);
  }

}
