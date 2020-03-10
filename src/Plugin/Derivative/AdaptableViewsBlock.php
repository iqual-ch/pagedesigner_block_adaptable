<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\Derivative;

use Drupal\views\Plugin\Derivative\ViewsBlock;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides block plugin definitions for all adaptable Views block displays.
 *
 * @see \Drupal\pagedesigner_block_adaptable\Plugin\Block\AdaptableViewsBlock
 */
class AdaptableViewsBlock extends ViewsBlock {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Check all Views for block displays.
    foreach ($this->viewStorage->loadMultiple() as $view) {
      // Do not return results for disabled views.
      if (!$view->status()) {
        continue;
      }
      $executable = $view->getExecutable();
      $executable->initDisplay();
      foreach ($executable->displayHandlers as $display) {
        /** @var \Drupal\views\Plugin\views\display\DisplayPluginInterface $display */
        // Add a block plugin definition for each block display.
        if (isset($display) && $display->definition['id'] == 'adaptable_block') {
          $delta = $view->id() . '-' . $display->display['id'];

          $admin_label = $display->getOption('block_description');
          if (empty($admin_label)) {
            if ($display->display['display_title'] == $display->definition['title']) {
              $admin_label = $view->label();
            }
            else {
              // Allow translators to control the punctuation. Plugin
              // definitions get cached, so use TranslatableMarkup() instead of
              // t() to avoid double escaping when $admin_label is rendered
              // during requests that use the cached definition.
              $admin_label = new TranslatableMarkup('@view: @display', ['@view' => $view->label(), '@display' => $display->display['display_title']]);
            }
          }

          $this->derivatives[$delta] = [
            'category' => $display->getOption('block_category'),
            'admin_label' => $admin_label,
            'config_dependencies' => [
              'config' => [
                $view->getConfigDependencyName(),
              ],
            ],
          ];

          // Look for arguments and expose them as context.
          foreach ($display->getHandlers('argument') as $argument_name => $argument) {
            /** @var \Drupal\views\Plugin\views\argument\ArgumentPluginBase $argument */
            if ($context_definition = $argument->getContextDefinition()) {
              $this->derivatives[$delta]['context_definitions'][$argument_name] = $context_definition;
            }
          }

          $this->derivatives[$delta] += $base_plugin_definition;
        }
      }
    }
    return $this->derivatives;
  }

}
