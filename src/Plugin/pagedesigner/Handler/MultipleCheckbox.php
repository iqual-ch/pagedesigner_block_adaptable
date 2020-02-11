<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\ui_patterns\Definition\PatternDefinitionField;

use Drupal\pagedesigner\Plugin\HandlerPluginBase;

/**
 * @PagedesignerHandler(
 *   id = "multiplecheckbox",
 *   name = @Translation("Multiple checkbox processor"),
 *   types = {
 *      "multiplecheckbox",
 *   }
 * )
 */
class MultipleCheckbox extends HandlerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function collectAttachments(array &$attachments) {
    $attachments['library'][] = 'pagedesigner_block_adaptable/pagedesigner';
  }

  /**
   * {@inheritDoc}
   */
  public function prepare(PatternDefinitionField &$field, &$fieldArray) {
    parent::prepare($field, $fieldArray);
    if (isset($field->toArray()['options'])) {
      foreach ($field->toArray()['options'] as $key => $option) {
        if (is_string($option)) {
          $option = t($option);
        }
        $fieldArray['options'][$key] = $option;
      }
    }
    if (isset($field->toArray()['value'])) {
      foreach ($field->toArray()['value'] as $key => $value) {
        $fieldArray['values'][$key] = $value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(Element $entity, &$build = []) {
    $build = $this->get($entity) + $build;
  }

  /**
   * {@inheritDoc}
   */
  public function get(Element $entity, &$result = '') {
    $result = $entity->field_content->value;
  }

  /**
   * {@inheritDoc}
   */
  public function generate($definition, $data, &$element = NULL) {
    parent::generate(['type' => 'content', 'name' => 'multiplecheckbox'], $data, $element = NULL);
  }

}
