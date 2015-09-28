<?php

/**
 * @file
 * Contains Drupal\brafton_importer\Model\BraftonFeedLoader
 */

namespace Drupal\brafton_importer\Model;

use Drupal\brafton_importer\APIClientLibrary\ApiHandler;

class BraftonFeedLoader {
    //put your properties here
 //   protected $feed;
    protected $brafton_config;
    protected $API_key;
    protected $API_domain;

    public function __construct(){
        //use this function to get and set all need properties
        $this->brafton_config = \Drupal::configFactory()->getEditable('brafton_importer.settings');
        $this->API_key = $this->brafton_config->get('brafton_importer.brafton_api_key');
        $this->API_domain = $this->brafton_config->get('brafton_importer.brafton_api_root');
    }

    public function load_feed(){
        //method for loading the feed itself

        $feed = new ApiHandler($this->API_key, $this->API_domain);

        return $feed;
    }

    public function upload_image(){

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

    public function brafton_post_exists($brafton_id) {
      $query = \Drupal::entityQuery('node')
        ->condition('field_brafton_id', $brafton_id);
      $nids = $query->execute();
      return $nids;
    }

    public function get_publish_date($article) {
      $date_setting = $this->brafton_config->get('brafton_importer.brafton_publish_date');
      switch($date_setting) {
        case 'published':
          $date = $article->getPublishDate();
          break;
        case 'created':
          $date = $article->getCreatedDate();
          break;
        case 'lastmodified':
          $date = $article->getLastModifiedDate();
          break;
        default:
          $date = $article->getPublishDate();
      }
      return $date;
    }

    public function get_author($article) {
      $author_id = $this->brafton_config->get('brafton_importer.brafton_author');
      // static existing drupal user chosen.
      if ($author_id != 0) {
        return $author_id;
      }
      // user selects Dynamic Authorship
      else {
      //  $byline = $article->getByLine();
        $byline = 'juicy';
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
            return $new_user->id();
          }
        }
        else {
          return $author_id;
        }
      }
    }

}
