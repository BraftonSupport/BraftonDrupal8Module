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
  protected $video_client;
  protected $client;
  protected $photo_client;
  protected $video_client_outputs;
  protected $photos;
  protected $feed_list;
  protected $feed_id;
  protected $articles;
  protected $article_list;
  protected $categories;
  protected $video_date_setting;
  protected $video_author_id;
  protected $cta_option;
  protected $pause_cta_text;
  protected $end_cta_title;
  protected $end_cta_subtitle;
  protected $end_cta_link;
  protected $end_cta_text;
  protected $pause_asset_id;
  protected $end_background_image_url;
  protected $end_asset_id;
  protected $button_image_url;

  public function __construct() {
    parent::__construct();
    $this->private_key = $this->brafton_config->get('brafton_importer.brafton_video_private_key');
    $this->public_key = $this->brafton_config->get('brafton_importer.brafton_video_public_key');
    $this->feed_number = $this->brafton_config->get('brafton_importer.brafton_video_feed_number');
    $this->video_url = 'http://livevideo.api.' . $this->domain . '/v2/';
    $this->photo_url = 'http://pictures.' . $this->domain . '/v2/';
    $this->video_date_setting = $this->brafton_config->get('brafton_importer.brafton_video_publish_date');
    $this->video_author_id = $this->brafton_config->get('brafton_importer.brafton_video_author');
    $this->cta_option = $this->brafton_config->get('brafton_importer.brafton_cta_switch');
  }

  /**
   * Gets the category names for a video article
   *
   * @param int $brafton_id The unique Brafton ID.
   *
   * @return array $name_array The array of category names.
   */
  public function get_video_tax_names($brafton_id) {
    if ( $this->category_switch == 'off' ) {
      return array();
    }
    $name_array = array();
    $cat_list = $this->categories->ListForArticle( $brafton_id,0,100 )->items;
    foreach($cat_list as $cat) {
      $cat_name = $this->categories->Get( $cat->id )->name;
      $name_array[] = $cat_name;
    }
    return $name_array;
  }

  /**
   * Loads useful API endpoints into properties of object.
   *
   * @return void
   */
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

  /**
   * Loads CTA info as class properties.
   *
   * @return void
   */
  public function get_cta_info() {
    $this->pause_cta_text = $this->brafton_config->get( 'brafton_importer.brafton_video_pause_cta_text' );
    $this->pause_cta_link = $this->brafton_config->get( 'brafton_importer.brafton_video_pause_cta_link' );
    $this->end_cta_title = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_title' );
    $this->end_cta_subtitle = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_subtitle' );
    $this->end_cta_link = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_link' );
    $this->end_cta_text = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_text' );
    $this->pause_asset_id = $this->brafton_config->get('brafton_importer.brafton_video_pause_cta_asset_gateway_id');
    $this->end_background_image_url = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_background_url');
    $this->end_asset_id = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_asset_gateway_id');
    $this->button_image_url = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_button_image_url');
  }

  /**
   * Generates source tags for video.
   *
   * @param string $src The url path
   * @param int $resolution The height of the video
   *
   * @return string Full source tag for video.
   */
  public function generate_source_tag($src, $resolution) {
      $tag = '';
      $ext = pathinfo($src, PATHINFO_EXTENSION);
      return sprintf('<source src="%s" type="video/%s" data-resolution="%s" />', $src, $ext, $resolution );
  }

  /**
   * Creates embed code for video
   *
   * @param int $brafton_id The unique Brafton ID.
   *
   * @return string $embed_code The full embed code containing the video.
   */
  public function create_embed($brafton_id) {
    $this_article = $this->articles->Get($brafton_id);

    $presplash = $this_article->fields['preSplash'];
    $postsplash = $this_article->fields['postSplash'];

    $video_list = $this->video_client_outputs->ListForArticle($brafton_id,0,10);
    $list = $video_list->items;
    $embed_code = sprintf( "<video id='video-%s' class=\"ajs-default-skin atlantis-js\" controls preload=\"auto\" width='512' height='288' poster='%s' >", $brafton_id, $presplash );

    foreach($list as $list_item){
      $output = $this->video_client_outputs->Get($list_item->id);
      $path = $output->path;
      $resolution = $output->height;
      $source = $this->generate_source_tag( $path, $resolution );
      $embed_code .= $source;
    }
    $embed_code .= '</video>';

    $script = '<script type="text/javascript">';
    $script .=  'var atlantisVideo = AtlantisJS.Init({';
    $script .=  'videos: [{';
    $script .='id: "video-' . $brafton_id . '"';

    // CTA stuff here
    if($this->cta_option){
      $this->get_cta_info();
      $marpro = '';
      if($this->pause_asset_id != ''){
          $marpro = "assetGateway: { id: '$this->pause_asset_id' },";
      }
      $endingBackground = '';
      if($this->end_background_image_url != ''){
          $end_background_image_url_2 = file_create_url($this->end_background_image_url);
          $endingBackground = "background: '$end_background_image_url_2',";
      }
      if($this->end_asset_id != ''){
          $endingBackground .= "assetGateway: { id: '$this->end_asset_id' },";
      }
      $buttonImage = '';
      if($this->button_image_url != ''){
          $button_image_url_2 = file_create_url($this->button_image_url);
          $buttonImage = "image: '$button_image_url_2',";
      }

      $script .=',';
      $script .= <<<EOT
          pauseCallToAction: {
                        $marpro
                        link: "$this->pause_cta_link",
              text: "$this->pause_cta_text"
          },
          endOfVideoOptions: {
                        $endingBackground
              callToAction: {
                  title: "$this->end_cta_title",
                  subtitle: "$this->end_cta_subtitle",
                  button: {
                      link: "$this->end_cta_link",
                      text: "$this->end_cta_text",
                      $buttonImage
                  }
              }
          }
EOT;
    }
    // End CTA stuff
    $script .= '}]';
    $script .= '});';
    $script .=  '</script>';
    $embed_code .= $script;
    //Wraps a Div around the embed code
    $embed_code = "<div id='post-single-video'>" . $embed_code . "</div>";

    return $embed_code;
  }

  /**
   * Retrieves the date for the video article.
   *
   * @param object $this_article Contains all title and content fields, etc...
   *
   * @return string $date The publish date
   */
  public function get_video_date($this_article) {
    if ($this->video_date_setting == 'lastmodified') {
      $date = strtotime($this_article->fields['lastModifiedDate']);
    }
    else {
      $date = strtotime($this_article->fields['date']);
    }
    return $date;
  }

  /**
   * Loops through each video article and saves it as Drupal node.
   *
   * @return void
   */
  public function run_video_loop() {
    $counter = 0;
    $import_list = array();
    foreach($this->article_list->items as $article) {
      // $article is an object containing just the brafton id
      $brafton_id = $article->id;
      $existing_posts = $this->brafton_post_exists($brafton_id);

      if ( !empty($existing_posts) && $this->overwrite == 1 ) {
        $nid = reset($existing_posts);
        $new_node = \Drupal\node\Entity\Node::load($nid);
      }
      elseif (empty($existing_posts)) {
        $new_node = \Drupal\node\Entity\Node::create(array('type' => 'brafton_video'));
      }
      else {
        continue;
      }
      // $this_article contains all the actual info.
      $this_article = $this->articles->Get($brafton_id);

      $category_names = $this->get_video_tax_names($brafton_id);
      $category_ids = $this->load_tax_terms($category_names);
      $image = $this->get_video_image($brafton_id);
      $date = $this->get_video_date($this_article);
      $embed_code = $this->create_embed($brafton_id);

      $new_node->uid = $this->video_author_id;
      $new_node->title = $this_article->fields['title'];
      $new_node->field_brafton_body = array(
        'value' => $this_article->fields['content'],
        'summary' => $this_article->fields['extract'],
        'format' => 'full_html'
      );
      $new_node->field_brafton_video = array(
        'value' => $embed_code,
        'format' => 'full_html'
      );
      $new_node->status = $this->publish_status;
      $new_node->created = $date;
      $new_node->field_brafton_id = $brafton_id;
      $new_node->field_brafton_term = $category_ids;
      if ( $image) {
        $new_node->field_brafton_image = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );
        $new_node->field_brafton_image->alt = $image['alt'];
      }

      $new_node->save();
      $import_list['items'][] = array(
        'title' => $this_article->fields['title'],
        'url' => $new_node->url()
      );

      $counter = $counter + 1;
    }
    $import_list['counter'] = $counter;
    $this->display_import_message($import_list);
  }


  /**
   * Retrieves information for the video article image.
   *
   * @param int $brafton_id The unique Brafton ID
   *
   * @return array $image_info Array with image ulr, alt, caption
   */
  public function get_video_image($brafton_id) {
    $images = $this->photos->ListForArticle($brafton_id, 0, 100);
    if ($images->items) {
      $photo_id = $this->photos->Get($images->items[0]->id)->sourcePhotoId;
      $image_info = array(
        'url' => $this->photo_client->Photos()->GetLocationUrl($photo_id)->locationUri,
        'alt' => $this->photos->Get($images->items[0]->id)->fields['caption'],
        'title' => $this->photos->Get($images->items[0]->id)->fields['caption']
      );
    } else {
      $image_info = null;
    }
    return $image_info;
  }

  public function import_videos() {
    $this->get_video_feed();
    $this->run_video_loop();
  }

}
