<?php

/**
 * @file
 * Contains Drupal\brafton_importer\BraftonImporterService
 */

namespace Drupal\brafton_importer;

use Drupal\brafton_importer\APIClientLibrary\ApiHandler;

//require_once dirname(__FILE__) . '/APIClientLibrary/ApiHandler.php';

class BraftonImporterService {

  protected $demo_value;

  public function __construct() {
    $this->demo_value = 'Upchuk';
  }

  public function getDemoValue() {
    return $this->demo_value;
  }


 /**
   * Creates a Brafton article.
   */
  public static function createBraftonArticle() {

    $config = \Drupal::configFactory()->getEditable('brafton_importer.settings');
    $API_key = $config->get('brafton_importer.brafton_api_key');
    $API_domain = $config->get('brafton_importer.brafton_api_root');

    $connection = new ApiHandler($API_key, $API_domain);

    $article_array = $connection->getNewsHTML();

    foreach($article_array as $article) {

      // $results = $connection->query("SELECT uid, name FROM {users_field_data} WHERE status=1");

      // $db_connection = \Drupal\Core\Database\Database::getConnection();
      // $result = $db_connection->query("SELECT ")

      $categories = \Drupal\brafton_importer\BraftonImporterService::set_article_categories($article);
      $title = $article->getHeadline();
      $body = $article->getText();
      $summary = $article->getExtract();
      $image = \Drupal\brafton_importer\BraftonImporterService::get_image_attributes($article);
      $brafton_id = $article->getId();

      $new_node_info = array(
        'type' => 'brafton_article',
        'title' => $title,
        'field_brafton_body' => array(
          'value' => $body,
          'summary' => $summary,
          'format' => 'full_html'
        ),
        'field_brafton_image' => system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE ),
        'field_brafton_id' => $brafton_id,
        'field_brafton_term' => $categories,
      );

      $new_node = \Drupal\node\Entity\Node::create($new_node_info);
      $new_node->field_brafton_image->alt = $image['alt'];

      $new_node->save();
    }



  }

  /**
   * Gets image information from XML feed
   */
  public static function get_image_attributes( $articleobj,$feedtype = NULL,$photoClient = NULL,$photos = NULL,$id = NULL )  {

    if( $feedtype == 'video' )  {
      $thisPhotos = $photos->ListForArticle( $id,0,100 );
      $photoId = $photos->Get( $thisPhotos->items[0]->id )->sourcePhotoId;
      $image_info = array(
        'url' => $photoClient->Photos()->GetLocationUrl( $photoId )->locationUri,
        'alt' => $photos->Get( $thisPhotos->items[0]->id )->fields['caption'],
        'title' => $photos->Get( $thisPhotos->items[0]->id )->fields['caption'],
      );
      return $image_info;
    }
    else {

      //Grabs the image attributes from the feed.

      $images = $articleobj->getPhotos();
      if( !empty( $images ) ) {
        $image_array = $images[0];
        if( $image_array )  {
          $image_large = $image_array->getLarge();
          $image_info = array(
            'url' => $image_large->getUrl(),
            'alt' => $image_array->getAlt(),
            'title' => $image_array->getCaption(),
          );
          return $image_info;
        }
        else {
          $image_info = NULL;
          return $image_info;
        }
      }
    }

  }

  /**
   * Imports categories for an article.
   */
  public static function set_article_categories($article) {
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

// Closing php tag?
