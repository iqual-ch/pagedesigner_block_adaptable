<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\display;

use Drupal\views\Plugin\views\display\Block;

/**
 * The plugin that handles a block.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "adaptable_block",
 *   title = @Translation("Pagedesigner adaptable Block"),
 *   help = @Translation("Display the view as a block."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_hook_block = FALSE,
 *   contextual_links_locations = {"adaptable_block"},
 *   admin = @Translation("Adaptable block")
 * )
 *
 * @see \Drupal\pagedesigner_block_adaptable\Plugin\Block\AdaptableViewsBlock
 * @see \Drupal\pagedesigner_block_adaptable\Plugin\Derivative\AdaptableViewsBlock
 */
class AdaptableBlock extends Block {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['block_category'] = ['default' => $this->t('Adaptable list (Views)')];
    return $options;
  }

}
