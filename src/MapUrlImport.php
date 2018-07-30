<?php

namespace Drupal\map_url;


class MapUrlImport {

  /**
   * Path of CSV file to import.
   *
   * @var null|string
   */
  private $file;

  /**
   * Path of module.
   *
   * @var string
   */
  private $module_path;

  public function __construct($file = NULL) {
    $this->module_path = drupal_get_path('module', 'map_url');
    if (!empty($file)) {
      if (file_exists($file)) {
        $this->file = $file;
      }
      else {
        \Drupal::logger('MapUrlImport')->error(t('The path @path is not found.', [
          '@path' => $file,
        ]));
        $this->file = NULL;
      }
    }
    else {
      $this->file = $this->module_path . '/include/url.csv';
    }
  }

  /**
   * Gets all values from CSV file.
   *
   * @return array|null
   */
  public function read() {
    if (!empty($this->file)) {
      $csv = [];
      $row = 0;
      $column = ['ID', 'URL', 'Title', 'Name', 'Parent'];

      if (($handle = fopen($this->file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
          array_walk($data, function (&$element, $key) use (&$csv, $row, $column) {
            if ($column[$key] == 'Title') {
              $element = utf8_encode($element);
            }
            $csv[$row][$column[$key]] = $element;
          });
          $row++;
        }
        fclose($handle);
        array_shift($csv);
      }

      return $csv;
    }
    return NULL;
  }

  /**
   * Gets a built arrays ordered by parent.
   *
   * @return array|null
   */
  public function get() {
    $csv = $this->read();
    if (empty($csv)) {
      return NULL;
    }

    // Get all parent URL.
    $parents = array_filter($csv, function ($v) {
      return empty($v['Parent']);
    });

    $filtered = [];
    foreach ($parents as $parent) {
      $filtered[$parent['ID']]['URL'] = $parent['URL'];
      $filtered[$parent['ID']]['Title'] = $parent['Title'];
      $filtered[$parent['ID']]['Name'] = $parent['Name'];
      $filtered[$parent['ID']]['children'] = array_filter($csv, function ($v) use ($parent) {
        return $v['Parent'] == $parent['ID'];
      });
    }

    return $filtered;
  }
}
