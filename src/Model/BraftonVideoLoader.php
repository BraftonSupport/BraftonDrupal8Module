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
    $this->photo_url = 'http://pictures.' . $this->domain . '/v2/';
  }

  public function get_taxonomy_terms_video($brafton_id) {
    if ( $this->brafton_config->get('brafton_importer.brafton_category_switch') == 'off' ) {
      return array();
    }

    //$categories = $this->categories->ListForFeed( $this->feed_list->items[ $this->feed_number ]->id,0,100 )->items;
    //$category_id = $this->categories->ListForArticle( $brafton_id,0,100 )->items[0]->id;

    $vocab = 'brafton_tax';
    $cat_array = array();

    $cat_list = $this->categories->ListForArticle( $brafton_id,0,100 )->items;
    //If the video article has categories...
    if(!empty($cat_list)) {
      foreach($cat_list as $cat) {
        //$category_id = $cat->id;

        $cat_obj = $this->categories->Get( $cat->id );
        $name = $cat_obj->name;
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

  public function get_video_image($brafton_id) {
    $photo_check_id = $this->photos->ListForArticle( $brafton_id,0,100 );

    if($photo_check_id->items[0]->id){
      $image = $this->get_image_attributes( NULL, 'video', $this->photo_client, $this->photos, $brafton_id );
    } else {
      $image = array('url' => '','alt' => '','title' => '',);
    }


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

  function generate_source_tag($src, $resolution) {
      $tag = '';
      $ext = pathinfo($src, PATHINFO_EXTENSION);
      return sprintf('<source src="%s" type="video/%s" data-resolution="%s" />', $src, $ext, $resolution );
  }

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
    $cta_option = $this->brafton_config->get('brafton_importer.brafton_cta_switch');
    $pause_cta_text = $this->brafton_config->get( 'brafton_importer.brafton_video_pause_cta_text' );
    $pause_cta_link = $this->brafton_config->get( 'brafton_importer.brafton_video_pause_cta_link' );
    $end_cta_title = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_title' );
    $end_cta_subtitle = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_subtitle' );
    $end_cta_link = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_link' );
    $end_cta_text = $this->brafton_config->get( 'brafton_importer.brafton_video_end_cta_text' );

    if($cta_option){
      $marpro = '';
      $pause_asset_id = $this->brafton_config->get('brafton_importer.brafton_video_pause_cta_asset_gateway_id');
      if($pause_asset_id != ''){
          $marpro = "assetGateway: { id: '$pause_asset_id' },";
      }
      $endingBackground = '';
      $end_background_image = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_background_url');
      if($end_background_image != ''){
          $end_background_image = file_create_url($end_background_image);
          $endingBackground = "background: '$end_background_image',";
      }
      $end_asset_id = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_asset_gateway_id');
      if($end_asset_id != ''){
          $endingBackground .= "assetGateway: { id: '$end_asset_id' },";
      }
      $buttonImage = '';
      $button_image_url = $this->brafton_config->get('brafton_importer.brafton_video_end_cta_button_image_url');
      if($button_image_url != ''){
          $button_image_url = file_create_url($button_image_url);
          $buttonImage = "image: '$button_image_url',";
      }

      $script .=',';
      $script .= <<<EOT
          pauseCallToAction: {
                        $marpro
                        link: "$pause_cta_link",
              text: "$pause_cta_text"
          },
          endOfVideoOptions: {
                        $endingBackground
              callToAction: {
                  title: "$end_cta_title",
                  subtitle: "$end_cta_subtitle",
                  button: {
                      link: "$end_cta_link",
                      text: "$end_cta_text",
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

        $categories = $this->get_taxonomy_terms_video($brafton_id);

        $image = $this->get_image_attributes( NULL, 'video', $this->photo_client, $this->photos, $brafton_id );

        $this_article = $this->articles->Get($brafton_id);

        $embed_code = $this->create_embed($brafton_id);

        $new_node->uid = $this->brafton_config->get('brafton_importer.brafton_author');
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
        $new_node->status = $this->brafton_config->get('brafton_importer.brafton_publish');
        $new_node->created = strtotime( $this_article->fields['lastModifiedDate'] );
        $new_node->field_brafton_id = $brafton_id;
  //      if (!empty($categories)) {
          $new_node->field_brafton_term = $categories;
  //      }
        if ( $image) {
          $new_node->field_brafton_image = system_retrieve_file( $image['url'], NULL, TRUE, FILE_EXISTS_REPLACE );
          $new_node->field_brafton_image->alt = $image['alt'];
        }





        $new_node->save();
//      }

    }

  }

  public function import_videos() {
    $this->get_video_feed();



    $this->run_loop();
  }

}
