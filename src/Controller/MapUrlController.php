<?php

namespace Drupal\map_url\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;

class MapUrlController extends ControllerBase {

  /**
   * Return a Map URL.
   */
  public function index() {
    // Loads all terms by vocabulary.
    $vid = 'sas_normativa';
    $output = [];
    // Gets only father terms.
    $query = \Drupal::database()
      ->select('taxonomy_term_data', 'td')
      ->fields('td', ['tid']);
    $query->join('taxonomy_term_hierarchy', 'th', 'td.tid = th.tid');
    $query->condition('td.vid', $vid)
      ->condition('th.parent', 0);
    $parent_tids = $query->execute()->fetchCol();

    foreach ($parent_tids as $tid) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = Term::load($tid);
      $term_url = current($term->get('field_map_url')->getValue());
      $url = filter_var($term_url['uri'], FILTER_VALIDATE_URL) ? $term_url['uri'] : '#';

      $output[$term->id()] = [
        'name' => $term->getName(),
        'url' => $url,
      ];

      // Check if parent has children.
      $children = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vid, $term->id());
      if (!empty($children)) {
        foreach ($children as $child) {
          /** @var \Drupal\taxonomy\Entity\Term $term_child */
          $term_child = Term::load($child->tid);
          $term_url = current($term_child->get('field_map_url')->getValue());
          $url = filter_var($term_url['uri'], FILTER_VALIDATE_URL) ? $term_url['uri'] : '#';
          $output[$term->id()]['children'][] = [
            'name' => $term_child->getName(),
            'url' => $url,
          ];
        }
      }
    }

    return [
      '#theme' => 'map_url',
      '#terms' => $output,
      '#attached' => [
        'library' => ['map_url/map_url'],
      ],
    ];
  }

}
