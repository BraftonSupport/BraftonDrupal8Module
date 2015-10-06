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

  public function import_videos() {

    $this->video_client = new AdferoVideoClient($this->video_url, $this->public_key, $this->private_key);

    $this->client = new AdferoClient($this->video_url, $this->public_key, $this->private_key);

    $this->photo_client = new AdferoPhotoClient($this->photo_url);

    $this->video_client_outputs = $this->video_client->videoOutputs();
    debug($this->video_client_outputs);
    $feeds = $this->client->Feeds();
    $feed_list = $feeds->ListFeeds(0,10);
    $this->feed_id = $feed_list->items[$this->feed_number]->id;

    $articles = $this->client->Articles();
    $this->article_list = $articles->ListForFeed($feed_list->items[$this->feed_number]->id, 'live', 0, 100);






  }

}
