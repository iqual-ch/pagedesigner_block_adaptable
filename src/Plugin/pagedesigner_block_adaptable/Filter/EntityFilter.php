<?php

namespace Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\Filter;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\pagedesigner_block_adaptable\Plugin\FilterPluginBase;
use Drupal\pagedesigner_block_adaptable\Plugin\views\filter\EntityFilterBase;

/**
 * Process entities of type pba_entity_filter.
 *
 * @PagedesignerFilter(
 *   id = "pagedesigner_filter_entity",
 *   name = @Translation("Entity filter plugin"),
 *   types = {
 *     "pba_entity_filter",
 *   }
 * )
 */
class EntityFilter extends FilterPluginBase {

  /**
   * {@inheritDoc}
   */
  public function build(array $filter) : array {
    return self::getRenderArray($filter);
  }

  /**
   * Returns a valid render array for filters for a content type.
   *
   * @param array $filter
   *   The filter definition.
   * 
   * @return array
   *   The render array, fallback to text field.
   */
  public static function getRenderArray(array $filter) {
    $renderArray = [
      'label' => $filter['field'],
      'type' => 'text',
    ];
    $entityType = (!empty($filter['entity_type'])) ? $filter['entity_type'] : NULL;
    if (empty($entityType)) {
      throw new \InvalidArgumentException("The entity_type key has to be set on the filter.");
    }
    $type = \Drupal::entityTypeManager()->getDefinition($entityType);
    if ($type instanceof ContentEntityType) {
      $options = [];
      $values = [];
      $bundles = EntityFilterBase::getBundles($filter);

      // Add sku on commerce variations.
      if ($entityType == "commerce_product_variation") {
        $data = EntityFilterBase::getData($type, $bundles, ['sku']);
        foreach ($data as $key => $record) {
          $options[$key] = $record->title . ' - ' . $record->sku;
        }
      }
      // Handle default cases (node, media, product, taxonomy terms etc.).
      else {
        $data = EntityFilterBase::getData($type, $bundles);
        foreach ($data as $key => $record) {
          $options[$key] = $record->{$type->getKey('label')};
        }
      }
      $values = array_keys($data);
      $renderArray = [
        'description' => t('Choose @label', ['@label' => $filter['expose']['label']])->__toString(),
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => $filter['pagedesigner_trait_type'],
        'name' => $filter['id'],
        'value' => $values,
      ];
      if ($filter['pagedesigner_trait_type'] == 'autocomplete') {
        $renderArray['additional'] = [
          'autocomplete' => [
            'entity_type' => $type->id(),
            'bundles' => $bundles
            ]
        ];
      }
    }
    return $renderArray;
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    $result = [];
    if ($filter['pagedesigner_trait_type'] == 'autocomplete') {
      if (!empty($value['id'])) {
        $value = [$value['id'] => $value['id']];
      }
      elseif (is_numeric(reset($value))) {
        $value = [reset($value) => reset($value)];
      }
      else {
        $value = [];
      }
    }
    $entityType = (!empty($filter['entity_type'])) ? $filter['entity_type'] : NULL;
    $type = \Drupal::entityTypeManager()->getDefinition($entityType);
    $bundles = EntityFilterBase::getBundles($filter);
    $validEntries = array_keys(EntityFilterBase::getData($type, $bundles));
    $hits = array_intersect(array_keys($value), $validEntries);
    $result = array_combine($hits, $hits);
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function serialize(array $value, array $filterDefinition = NULL): array {
    $values = [];
    $autocomplete = FALSE;
    $storageManager = NULL;
    if (!empty($filterDefinition['pagedesigner_trait_type']) && $filterDefinition['pagedesigner_trait_type'] == 'autocomplete') {
      $autocomplete = TRUE;
      $entityType = (!empty($filterDefinition['entity_type'])) ? $filterDefinition['entity_type'] : NULL;
      $storageManager = \Drupal::entityTypeManager()->getStorage($entityType);
    }
    foreach ($value as $key => $item) {
      if ($item) {
        if ($autocomplete) {
          $entity = $storageManager->load($key);
          $values = [
            'label' => $entity->label(),
            'value' => $entity->label() . ' (' . $key . ')',
            'id' => $key
           ];
        } else {
          $values[$key] = $key;  
        }
      }
      else {
        unset($values[$key]);
      }
    }
    return $values;
  }

}
