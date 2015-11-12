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
    protected $overwrite;
    protected $publish_status;
    protected $category_switch;
    protected $API_key;
    protected $errors;

    /**
     * Constructor method: Sets initial properties when BraftonFeedLoader objectg is instantiated.
     *
     * @return void
     */
    public function __construct(){
        //use this function to get and set all need properties
        $this->brafton_config = \Drupal::configFactory()->getEditable('brafton_importer.settings');
        $this->domain = $this->brafton_config->get('brafton_importer.brafton_api_root');
        $this->overwrite = $this->brafton_config->get('brafton_importer.brafton_overwrite');
        $this->publish_status = $this->brafton_config->get('brafton_importer.brafton_publish');
        $this->category_switch = $this->brafton_config->get('brafton_importer.brafton_category_switch');
        $this->API_key = $this->brafton_config->get('brafton_importer.brafton_api_key');
        $this->errors = new BraftonErrorReport($this->API_key, $this->domain, $this->brafton_config->get('brafton_importer.brafton_debug_mode'));
    }

    /**
     * Checks whether a node with the same brafton ID exists in drupal database.
     *
     * @param int $brafton_id The Brafton ID.
     *
     * @return array $nids An array of node ids (nids) that have matching Brafton Id.
     */
    public function brafton_post_exists($brafton_id) {
      $loop_section = $this->errors->get_section();
      $this->errors->set_section('Checking for existing Brafton ID ' . $brafton_id);

      $query = \Drupal::entityQuery('node')
        ->condition('field_brafton_id', $brafton_id);
      $nids = $query->execute();

      $this->errors->set_section($loop_section);
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
      $loop_section = $this->errors->get_section();
      $this->errors->set_section('Display import message');

      $import_message = '<ul>';
      if ($import_list['items']) {
        foreach($import_list['items'] as $item) {
          $import_message .= "<li><a href='" . $item['url'] . "'>" . $item['title'] . "</a></li>";
        }
      }
      $import_message .+ "</ul>";
      drupal_set_message(t("You imported " . $import_list['counter'] . " articles:" . $import_message));

      $this->errors->set_section($loop_section);
    }

  /**
   * Takes array of category names, creates the Drupal term if needed, returns Drupal tax term ids.
   *
   * @param array $name_array Array of category names (strings)
   *
   * @return array $cat_id_array Array of Drupal Tax term ids for individual article.
   */
  public function load_tax_terms($name_array) {
    $loop_section = $this->errors->get_section();
    $this->errors->set_section('Load tax terms');

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
    $this->errors->set_section($loop_section);
    return $cat_id_array;
  }

  /**
   *  Gets the author of the article based on configs.
   *
   * @param string $byline The byline author from the XML feed.
   *
   * @return int $author_id The drupal user ID for the author.
   */
  public function get_author($byline) {
    $loop_section = $this->errors->get_section();
    $this->errors->set_section('Getting Byline.');

    // static existing drupal user chosen.
    if ($this->article_author_id != 0) {
      $author_id = $this->article_author_id;
    }
    // user selects Dynamic Authorship
    else {
    //  $byline = 'juicy';
      // if byline exists
      if (!empty($byline)) {
        $user = user_load_by_name($byline);
        // if user exists
        if ($user) {
          return $user->id();
        }
        else {
          //create user programatically
          $password = user_password(8);
          $fields = array(
              'name' => $byline,
              'mail' => $byline.rand().'@example.com',
              'pass' => $password,
              'status' => 1,
              'init' => 'email address',
              'roles' => array(
                DRUPAL_AUTHENTICATED_RID => 'authenticated user',
              ),
            );
          $new_user = \Drupal\user\Entity\User::create($fields);
          $new_user->save();
          $author_id = $new_user->id();
        }
      }
      // if byline is chosen but doesn't exist, choose first user.
      else {
        $author_id = 1;
      }
    }
    $this->errors->set_section($loop_section);
    return $author_id;
  }



}
