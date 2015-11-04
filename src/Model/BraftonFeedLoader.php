<?php

/**
 * @file
 * Contains Drupal\brafton_importer\Model\BraftonFeedLoader
 */

namespace Drupal\brafton_importer\Model;

/**
 * The parent class for loading any Brafton XML feed.
 */
class BraftonFeedLoader {
    //put your properties here
 //   protected $feed;
    protected $brafton_config;
    protected $domain;

    /**
     * Constructor method: Sets initial properties when BraftonFeedLoader objectg is instantiated.
     *
     * @return void
     */
    public function __construct(){
        //use this function to get and set all need properties
        $this->brafton_config = \Drupal::configFactory()->getEditable('brafton_importer.settings');
        $this->domain = $this->brafton_config->get('brafton_importer.brafton_api_root');
    }

    /**
     * Checks whether a node with the same brafton ID exists in drupal database.
     *
     * @param int $brafton_id The Brafton ID.
     *
     * @return array $nids An array of node ids (nids) that have matching Brafton Id.
     */
    public function brafton_post_exists($brafton_id) {
      $query = \Drupal::entityQuery('node')
        ->condition('field_brafton_id', $brafton_id);
      $nids = $query->execute();
      return $nids;
    }

    /**
     * Displays list of imported articles.
     *
     * @param array $import_list Array containing titles, urls, number of articles imported.
     *
     * @return void
     */
    public function display_import_message($import_list) {

      $import_message = '<ul>';
      if ($import_list['items']) {
        foreach($import_list['items'] as $item) {
          $import_message .= "<li><a href='" . $item['url'] . "'>" . $item['title'] . "</a></li>";
        }
      }
      $import_message .+ "</ul>";
      drupal_set_message(t("You imported " . $import_list['counter'] . " articles:" . $import_message));
    }

  /**
   * Takes array of category names, creates the Drupal term if needed, returns Drupal tax term ids.
   *
   * @param array $name_array Array of category names (strings)
   *
   * @return array $cat_id_array Array of Drupal Tax term ids for individual article.
   */
  public function load_tax_terms($name_array) {
    $vocab = 'brafton_tax';
    $cat_id_array = array();
    foreach($name_array as $name) {
      $existing_terms = taxonomy_term_load_multiple_by_name($name, $vocab);
      // If term does not exist, create it.
      if ( empty($existing_terms) ) {
        // Creates new taxonomy term.
        $tax_info = array(
          'name' => $name,
          'vid' => $vocab,
        );
        $brafton_tax_term = \Drupal\taxonomy\Entity\Term::create($tax_info);
        $brafton_tax_term->save();
        $term_vid = $brafton_tax_term->id();
      }
      else {
        $term_vid = reset($existing_terms)->id();
      }
      $cat_id_array[] = $term_vid;
    }
    // returns array of unique term ids (vid).
    return $cat_id_array;
  }

}
