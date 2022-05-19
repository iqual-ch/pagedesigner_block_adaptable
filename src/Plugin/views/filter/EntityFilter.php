<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Numeric filter for entity references.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("pba_entity_filter")
 */
class EntityFilter extends EntityFilterBase {

  /**
   * Overrides \Drupal\views\Plugin\views\HandlerBase::init().
   *
   * Provide some extra help to get the operator/value easier to use.
   *
   * This likely has to be overridden by filters which are more complex
   * than simple operator/value.
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['pagedesigner_trait_type'] = ['default' => 'multiplecheckbox'];
    $options['pagedesigner_multiselect'] = ['default' => FALSE];
    $options['pagedesigner_required'] = ['default' => TRUE];
    $options['pagedesigner_bundles'] = ['default' => []];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    unset($form['expose_button']);
    $form['pagedesigner_trait_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Pagedesigner trait type'),
      '#description' => $this->t('The pagedesigner trait type: select (default), autocomplete, multiple checkboxes.'),
      '#default_value' => $this->options['pagedesigner_trait_type'],
      '#options'  => [
        'select' => $this->t('Select'),
        'autocomplete' => $this->t('Autocomplete'),
        'multiplecheckbox' => $this->t('Checkboxes'),
      ],
      '#required' => TRUE,
    ];

    // $form['pagedesigner_required'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Make selection mandatory in pagedesigner.'),
    //   '#default_value' => $this->options['pagedesigner_required'],
    // ];

    // $form['pagedesigner_multiselect'] = [
    //   '#type' => 'checkbox',
    //   '#title' => $this->t('Allow multiple items to be selected (only for multiple checkbox).'),
    //   '#default_value' => $this->options['pagedesigner_multiselect'],
    //   '#states' => [
    //     'disabled' => [
    //       ':input[name="options[pagedesigner_trait_type]"]' =>
    //       [
    //         ['value' => 'select'], ['value' => 'autocomplete'],
    //       ],
    //     ],
    //   ],
    // ];

    $entityType = $this->view->getBaseEntityType()->id();

    if ($this->options['relationship'] != 'none') {
      /** @var \Drupal\views\Plugin\views\display\DisplayPluginBase $handler */
      $handler = $this->displayHandler->getHandler('relationship', $this->options['relationship']);
      $entityType = $handler->definition['entity type'];
    }
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entityType);
    $bundleOptions = [];
    if (!$bundles[$entityType]) {
      foreach ($bundles as $id => $bundle) {
        $bundleOptions[$id] = $bundle['label'];
      }
      $form['pagedesigner_bundles'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Bundles'),
        '#description' => $this->t('Select the bundles for entity selection in pagedesigner. If empty, all bundles will be matched.'),
        '#default_value' => $this->options['pagedesigner_bundles'],
        '#options'  => $bundleOptions,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (!empty($this->value)) {
      $table = $this->table;
      $field = $this->options["entity_field"];
      // $table
      if ($this->options['relationship'] != 'none') {
        /** @var \Drupal\views\Plugin\views\display\DisplayPluginBase $handler */
        $handler = $this->displayHandler->getHandler('relationship', $this->options['relationship']);
        $table = $handler->table;
        $field = $handler->field;
      }
      $fieldString = $table . '.' . $field;
      $this->query->addWhere('AND', $fieldString, $this->value, 'IN');
    }
  }

}
