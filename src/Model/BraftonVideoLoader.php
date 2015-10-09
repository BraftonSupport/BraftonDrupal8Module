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

  public function get_video_feed() {

    $this->video_client = new AdferoVideoClient($this->video_url, $this->public_key, $this->private_key);
    $this->client = new AdferoClient($this->video_url, $this->public_key, $this->private_key);
    $this->photo_client = new AdferoPhotoClient($this->photo_url);
    $this->video_client_outputs = $this->video_client->videoOutputs();

    $this->photos = $this->client->ArticlePhotos();

    $feeds = $this->client->Feeds();
    $feed_list = $feeds->ListFeeds(0,10);
    $this->feed_id = $feed_list->items[$this->feed_number]->id;

    $this->articles = $this->client->Articles();
    $this->article_list = $this->articles->ListForFeed($this->feed_id, 'live', 0, 100);
  }

  // Loops through each video article and grabs data.
  public function run_loop() {

    foreach($this->article_list->items as $article) {
      $brafton_id = $article->id;
      $this_article = $this->articles->Get($brafton_id);

      $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_video'));

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

    }

  }

  public function import_videos() {
    $this->get_video_feed();
    $this->run_loop();
  }

}
