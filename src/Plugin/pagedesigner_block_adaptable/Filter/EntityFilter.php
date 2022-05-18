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
      $selection_settings = [
        'target_bundles' => ($bundles) ?: NULL,
        'sort' => ['field' => "_none", 'direction' => 'ASC'],
        'auto_create' => FALSE,
        'auto_create_bundle' => '',
        'match_operator' => 'CONTAINS',
        'match_limit' => 10
      ];
      $data = serialize($selection_settings) . $type->id() . 'default:' . $type->id();
      $selection_settings_key = Crypt::hmacBase64($data, \Drupal\Core\Site\Settings::getHashSalt());
      $key_value_storage = \Drupal::keyValue('entity_autocomplete');
      if (!$key_value_storage->has($selection_settings_key)) {
          $key_value_storage->set($selection_settings_key, $selection_settings);
      }
      $url = '/de/entity_reference_autocomplete/'. $type->id() . '/default:' . $type->id(). '/' . $selection_settings_key;
      $renderArray = [
        'description' => t('Choose @label', ['@label' => $filter['expose']['label']])->__toString(),
        'label' => $filter['expose']['label'],
        'options' => $options,
        'type' => 'autocomplete', // $filter['pagedesigner_trait_type'],
        'name' => $filter['id'],
        'value' => $values,
        'additional' => ['autocomplete_href' => $url],
      ];
    }
    return $renderArray;
  }

  /**
   * {@inheritDoc}
   */
  public function patch($filter, $value) {
    $result = [];
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
  public function serialize($value): array {
    $values = [];
    foreach ($value as $key => $item) {
      if ($item) {
        $values[$key] = $key;
      }
      else {
        unset($values[$key]);
      }
    }
    return $values;
  }

}
