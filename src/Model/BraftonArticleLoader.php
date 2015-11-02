<?php

/**
 * @file
 * Contains Drupal\brafton_importer\Model\BraftonFeedLoader
 */

namespace Drupal\brafton_importer\Model;

use Drupal\brafton_importer\APIClientLibrary\ApiHandler;

/**
 * The class for articles. Child of BraftonFeedLoader.
 */
class BraftonArticleLoader extends BraftonFeedLoader{

    protected $API_key;

    /**
     * Constructor function. Is this necessary?
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->API_key = $this->brafton_config->get('brafton_importer.brafton_api_key');
    }

    /**
     * Method for loading feed. Because so basic, perhaps it should be property?
     *
     * @return object $feed of class ApiHandler
     */
    public function load_article_feed(){
        $feed = new ApiHandler($this->API_key, 'http://api.' . $this->domain);
        return $feed;
    }


    /**
     * Imports a single article.
     *
     * @param string $archive_url The local file url for an uploaded XML archive. Null for non-archive article importing.
     *
     * @return void
     */
    public function run_article_loop($archive_url){

      $counter = 0;
      $import_list = array('items' => array(), 'counter' => $counter);


      if ($archive_url) {
        $article_array = \Drupal\brafton_importer\APIClientLibrary\NewsItem::getNewsList( $archive_url,'html' );
      } else{
        $feed = $this->load_article_feed();
        $article_array = $feed->getNewsHTML();
      }

      foreach ($article_array as $article) {

        $brafton_id = $article->getId();
        $existing_posts = $this->brafton_post_exists($brafton_id);
        $overwrite = $this->brafton_config->get('brafton_importer.brafton_overwrite');
        if ( $overwrite == 1 && !empty($existing_posts) ) {
          $nid = reset($existing_posts);
          $new_node = \Drupal\node\Entity\Node::load($nid);
        }
        elseif (empty($existing_posts)) {
          $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_article'));
        }
        else {
          continue;
        }

        $publish_status = $this->brafton_config->get('brafton_importer.brafton_publish');
        $author_id = $this->get_author($article);
        $date = $this->get_publish_date($article);
        $categories = $this->get_taxonomy_terms($article);
        $title = $article->getHeadline();
        $body = $article->getText();
        $summary = $article->getExtract();
        $image = $this->get_image_attributes($article);

        $new_node->status = $publish_status;
        $new_node->title = $title;
        $new_node->uid = $author_id;
        $new_node->created = strtotime($date);
        $new_node->field_brafton_body = array(
          'value' => $body,
          'summary' => $summary,
          'format' => 'full_html'
        );
        $new_node->field_brafton_id = $brafton_id;
        $new_node->field_brafton_term = $categories;
        $new_node->field_brafton_image = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );
        $new_node->field_brafton_image->alt = $image['alt'];

        $new_node->save();

        $import_list['items'][] = array(
          'title' => $title,
          'url' => $new_node->url()
        );

        $counter = $counter + 1;

      }

      $import_list['counter'] = $counter;
      $import_message = '<ul>';
      if ($import_list['items']) {
        foreach($import_list['items'] as $item) {
          $import_message .= "<li><a href=''>" . $item['title'] . "</a></li>";
        }
      }

      $import_message .+ "</ul>";
      drupal_set_message(t("You imported " . $import_list['counter'] . " articles:" . $import_message));


    }

  /**
   * Gets needed Drupal taxonomy term IDs for associating with the new article.
   *
   * May be moved to feed_loader if we can get the method get passed the same info
   *
   * @param object $article An individual article from XML feed.
   *
   * @return array $cat_array Includes the needed Drupal taxonomy term IDs for associating with the new article.
   */
  public function get_taxonomy_terms($article) {


    if ( $this->brafton_config->get('brafton_importer.brafton_category_switch') == 'off' ) {
      return array();
    }

    $categories = $article->getCategories();

    $vocab = 'brafton_tax';

    $cat_array = array();

    foreach($categories as $category) {
      $name = $category->getName();

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
      $cat_array[] = $term_vid;
    }
    // returns array of unique term ids (vid).
    return $cat_array;
  }


}
