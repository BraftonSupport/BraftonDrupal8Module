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
  public function createBraftonArticle() {

    $API_key = 'a3d8aaf8-be65-48bf-baf6-aabd4afeb8ca';
    $API_domain = 'http://api.contentlead.com';
    $connection = new ApiHandler($API_key, $API_domain);

    $article_array = $connection->getNewsHTML();
//    echo '<pre>';
//    var_dump($article_array[0]->getHeadline());
//    echo '</pre>';

    $article = $article_array[0];
    $title = $article->getHeadline();
    $body = $article->getText();
    $summary = $article->getExtract();
    $image = \Drupal\brafton_importer\BraftonImporterService::get_image_attributes($article);
    $brafton_id = $article->getId();
//    $x = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );

    $new_node_info = array(
      'type' => 'brafton_article',
      'title' => $title,
      'body' => array(
        'value' => $body,
        'summary' => $summary,
        'format' => 'full_html'
      ),
      'field_featured_image' => system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE ),
      'field_brafton_id' => $brafton_id
    );
/*
                $types = array(
                    'body'  => 'body',
                    'image' => 'field_brafton_image',
                    'tax'   => 'field_brafton_term'
                );
*/

    $new_node = \Drupal\node\Entity\Node::create($new_node_info);

   // Deprecated
  //  $new_node = entity_create('node', $new_node_info);


     //              $new_node->{$types['image']}[ $new_node->language ][0] = ( array ) system_retrieve_file( $image['url'],NULL,TRUE,FILE_EXISTS_REPLACE );
     //                       $new_node->{$types['image']}[ $new_node->language ][0]['alt'] = $image['alt'];
     //                       $new_node->{$types['image']}[ $new_node->language ][0]['title'] = $image['title'];


    $new_node->save();







  }

  /**
   * Gets image information from XML feed
   */
  public function get_image_attributes( $articleobj,$feedtype = NULL,$photoClient = NULL,$photos = NULL,$id = NULL )  {

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

}

// Closing php tag?
