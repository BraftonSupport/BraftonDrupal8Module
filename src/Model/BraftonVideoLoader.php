<?php

/**
 * @file
 * Contains Drupal\brafton_importer\Model\BraftonVideoLoader
 */

namespace Drupal\brafton_importer\Model;

use Drupal\brafton_importer\RCClientLibrary\AdferoArticlesVideoExtensions\AdferoVideoClient;
use Drupal\brafton_importer\RCClientLibrary\AdferoArticles\AdferoClient;
use Drupal\brafton_importer\RCClientLibrary\AdferoPhotos\AdferoPhotoClient;

/**
 * The class wrapper for Videos.
 */
class BraftonVideoLoader extends BraftonFeedLoader {

  protected $private_key;
  protected $public_key;
  protected $feed_number;
  protected $video_url;
  protected $photo_url;

  public function __construct() {
    parent::__construct();
    $this->private_key = $this->brafton_config->get('brafton_importer.brafton_video_private_key');
    $this->public_key = $this->brafton_config->get('brafton_importer.brafton_video_public_key');
    $this->feed_number = $this->brafton_config->get('brafton_importer.brafton_video_feed_number');
    $this->video_url = 'http://livevideo.api.' . $this->domain . '/v2/';
    $this->photo_url = 'http://pictures. ' . $this->domain . '/v2/';
  }

  public function import_categories($brafton_id) {
    if ( $this->brafton_config->get('brafton_importer.brafton_category_switch') == 'off' ) {
      return array();
    }

    //$categories = $this->categories->ListForFeed( $this->feed_list->items[ $this->feed_number ]->id,0,100 )->items;
    //$category_id = $this->categories->ListForArticle( $brafton_id,0,100 )->items[0]->id;

    //If the video article has categories...
    $cat_array = $this->categories->ListForArticle( $brafton_id,0,100 )->items;
    if(!empty($cat_array)) {
      foreach($cat_array as $cat) {
        //$category_id = $cat->id;
        $category = $this->categories->Get( $cat->id );
        debug($category);
      }

    }
    //$category = $this->categories->Get( $category_id );



  }

  public function get_video_feed() {

    $this->video_client = new AdferoVideoClient($this->video_url, $this->public_key, $this->private_key);
    $this->client = new AdferoClient($this->video_url, $this->public_key, $this->private_key);
    $this->photo_client = new AdferoPhotoClient($this->photo_url);
    $this->video_client_outputs = $this->video_client->videoOutputs();

    $this->photos = $this->client->ArticlePhotos();

    $feeds = $this->client->Feeds();
    $this->feed_list = $feeds->ListFeeds(0,10);
    $this->feed_id = $this->feed_list->items[$this->feed_number]->id;

    $this->articles = $this->client->Articles();
    $this->article_list = $this->articles->ListForFeed($this->feed_id, 'live', 0, 100);

    $this->categories = $this->client->Categories();
  }

  // Loops through each video article and grabs data.
  public function run_loop() {

    foreach($this->article_list->items as $article) {
      $brafton_id = $article->id;
      $existing_posts = $this->brafton_post_exists($brafton_id);
      $overwrite = $this->brafton_config->get('brafton_importer.brafton_overwrite');
      if ( $overwrite == 1 && !empty($existing_posts) ) {
        $nid = reset($existing_posts);
        $new_node = \Drupal\node\Entity\Node::load($nid);
      }
      else {
        $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_video'));
      }



 //     if (empty($existing_posts)) {

        $this->import_categories($brafton_id);

        $this_article = $this->articles->Get($brafton_id);

        $new_node->uid = $this->brafton_config->get('brafton_importer.brafton_author');
        $new_node->title = $this_article->fields['title'];
        $new_node->field_brafton_body = array(
          'value' => $this_article->fields['content'],
          'summary' => $this_article->fields['extract'],
          'format' => 'full_html'
        );
        $new_node->status = $this->brafton_config->get('brafton_importer.brafton_publish');
        $new_node->created = strtotime( $this_article->fields['lastModifiedDate'] );
        $new_node->field_brafton_id = $brafton_id;

        $new_node->save();
//      }

    }

  }

  public function import_videos() {
    $this->get_video_feed();



    $this->run_loop();
  }

}
