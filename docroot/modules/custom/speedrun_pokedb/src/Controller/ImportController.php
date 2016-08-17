<?php

namespace Drupal\speedrun_pokedb\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportController extends ControllerBase {

  use DependencySerializationTrait;

  /**
   * @var string
   */
  protected $dir;

  /**
   * ImportController constructor.
   *
   * @param string $dir
   *   Path to the speedrun_pokedb module.
   */
  public function __construct($dir) {
    $this->dir = $dir;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')->getModule('speedrun_pokedb')->getPath()
    );
  }

  /**
   * Initializes batch job to import all Pokemon.
   */
  public function start() {
    $db = file_get_contents($this->dir . '/db.json');
    $db = Json::decode($db);

    $batch = array(
      'title' => $this->t('Importing Pokemon'),
    );

    $types = array();
    foreach ($db as $pokemon) {
      $types = array_merge($types, $pokemon['type']);
    }
    foreach (array_unique($types) as $type) {
      $batch['operations'][] = array([$this, 'importType'], [$type]);
    }

    foreach ($db as $pokemon) {
      $batch['operations'][] = array([$this, 'importPokemon'], [$pokemon]);
    }

    batch_set($batch);
    return batch_process('admin/content');
  }

  /**
   * Imports a Pokemon type as a taxonomy term.
   *
   * @param string $label
   *   The Pokemon type.
   * @param array $context
   *   The batch job context.
   */
  public function importType($label, array &$context) {
    $this->entityTypeManager()
      ->getStorage('taxonomy_term')
      ->create([
        'name' => $label,
        'vid' => 'type',
      ])
      ->save();
  }

  /**
   * Imports a Pokemon image.
   *
   * @param string $image
   *   The file name.
   *
   * @return \Drupal\file\FileInterface
   *   The imported file entity, or NULL if the file was not saved.
   */
  protected function importImage($image) {
    $image = basename($image, '.jpg');
    $image = preg_replace('/[^\w\d\-]+/', NULL, $image);
    $image .= '.jpg';

    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager()
      ->getStorage('file')
      ->create([
        'filename' => $image,
        'uri' => 'public://' . $image,
        'status' => FILE_STATUS_PERMANENT,
      ]);

    if ($file->save()) {
      file_unmanaged_copy($this->dir . '/images/' . $image, $file->getFileUri());
      return $file;
    }
  }

  public function importPokemon(array $pokemon, array &$context) {
    $node = $this->entityTypeManager()
      ->getStorage('node')
      ->create([
        'type' => 'pokemon',
        'title' => $pokemon['name'],
        'field_abilities' => $pokemon['abilities'],
        'field_growth_rate' => $pokemon['growth_rate'],
        'field_japanese_name' => $pokemon['japanese_name'],
        'field_species' => html_entity_decode($pokemon['species']),
        'moderation_state' => 'published',
      ]);

    $image = $this->importImage($pokemon['image']);
    if ($image) {
      $node->field_image = $image;
    }

    $match = array();
    preg_match('/(\d+(\.\d+)?)m/', $pokemon['height'], $match);
    if (isset($match[1])) {
      $node->field_height = (float) $match[1];
    }

    $match = array();
    preg_match('/(\d+(\.\d+)?)\s*kg/', $pokemon['weight'], $match);
    if (isset($match[1])) {
      $node->field_weight = (float) $match[1];
    }

    foreach ($pokemon['type'] as $i => $type) {
      $terms = $this->entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $type]);

      $node->field_type[$i] = reset($terms);
    }

    $node->save();
  }

}
