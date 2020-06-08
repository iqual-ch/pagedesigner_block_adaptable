<?php

namespace Drupal\pagedesigner_block_adaptable\Service;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Handler manager for pagedesigner filter plugins.
 *
 * @see \Drupal\Core\Archiver\Annotation\Archiver
 * @see \Drupal\Core\Archiver\ArchiverInterface
 * @see plugin_api
 */
class FilterPluginManager extends DefaultPluginManager implements FallbackPluginManagerInterface {

  /**
   * Constructs a RendererPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/pagedesigner_block_adaptable/Filter',
      $namespaces,
      $module_handler,
      'Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner_block_adaptable\FilterPluginInterface',
      'Drupal\pagedesigner_block_adaptable\Annotation\PagedesignerFilter'
    );
    $this->alterInfo('pagedesigner_filter_info');
    $this->setCacheBackend($cache_backend, 'pagedesigner_filter_info_plugins');
  }

  /**
   * Overrides PluginManagerBase::getInstance().
   *
   * @param array $options
   *   An array with the following key/value pairs:
   *   - id: The id of the plugin.
   *   - type: The type of the pattern field.
   *
   * @return \Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\FilterPluginInterface[]
   *   A list of Filter objects.
   */
  public function getInstance(array $options) {
    $filters = [];
    $definitions = [];
    $configuration = [];
    $type = $options['type'];
    $allDefinitions = $this->getDefinitions();
    foreach ($allDefinitions as $plugin_id => $definition) {
      if (in_array($type, $definition['types'])) {
        $definitions[$plugin_id] = $definition;
      }
    }
    if (count($definitions)) {
      uasort($definitions, ['\Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      foreach ($definitions as $plugin_id => $definition) {
        $filters[] = $this
          ->createInstance($plugin_id, $configuration);
      }
    }
    if (empty($filters)) {
      $filters[] = $this
        ->createInstance($this->getFallbackPluginId($type), $configuration);
    }
    return $filters;
  }

  /**
   * Returns all filters.
   *
   * @return \Drupal\pagedesigner_block_adaptable\Plugin\pagedesigner\FilterPluginInterface[]
   *   A list of Filter objects.
   */
  public function getFilters() {
    $filters = [];
    $configuration = [];
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $filters[] = $this
        ->createInstance($plugin_id, $configuration);
    }
    return $filters;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'pagedesigner_filter_string';
  }

}
