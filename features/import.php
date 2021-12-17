<?php
namespace casasoft\complexmanager;
require_once( 'silence.php' );

class eMonitorImport extends Feature{
  public $importFile = false;
  public $main_lang = false;
  public $WPML = null;
  public $transcript = array();
  public $curtrid = false;
  public $trid_store = array();


  public function __construct($autoimport = false, $emonitorupdate = false){
    if ($autoimport) {
      $this->addToLog('autoimport ' . time());
      $this->updateImportDataThroughEmonitor();
      //add_action( 'init', array($this, 'casawpImport') );
    }
    elseif ($emonitorupdate) {
      $this->addToLog('updateImportDataThroughEmonitor ' . time());
      //add_action( 'init', array($this, 'updateImportFileThroughCasaGateway') );
      add_action( 'init', array($this, 'updateImportDataThroughEmonitor'), 20);
    }
  } 

  


  public function r( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
  }

  public function getEmonitorImportFile(){
    if (!$this->importFile) {
      $good_to_go = false;
      if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm')) {
        mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm');
        $this->addToLog('directory cxm was missing: ' . time());
      }
      if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
        mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import');
        $this->addToLog('directory cxm/import was missing: ' . time());
      }
      $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml';
      if (file_exists($file)) {
        $good_to_go = true;
        $this->addToLog('file found lets go: ' . time());
      } else {
        //if force last check for last
        $this->addToLog('file was missing ' . time());
        if (isset($_GET['force_last_import'])) {
          $this->addToLog('importing last file based on force_last_import: ' . time());
          $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml';
          if (file_exists($file)) {
            $good_to_go = true;
          }
        }
      }
      if ($good_to_go) {
        $this->importFile = $file;
      }
    } else {
        $this->addToLog('importfile already set: ' . time());
    }

    return $this->importFile;
  }

  public function renameImportFileTo($to){
    if ($this->importFile != $to) {
      rename($this->importFile, $to);
      $this->importFile = $to;
    }
  }

  public function backupImportFile(){
    copy ( $this->getEmonitorImportFile() , CXM_CUR_UPLOAD_BASEDIR  . '/cxm/done/' . get_date_from_gmt('', 'Y_m_d_H_i_s') . '_completed.xml');
    return true;
  }



  public function cxmUploadAttachmentFromGateway($casawp_id, $fileurl){

    

    if (strpos($fileurl, '://')) {
      $parsed_url = parse_url(urldecode($fileurl));
    } else {
      $parsed_url = [];
    }
    if (isset($parsed_url['query']) && $parsed_url['query']) {
      $file_parts = pathinfo($parsed_url['path']);

      $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
      $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
      $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
      $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
      $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
      $pass     = ($user || $pass) ? "$pass@" : '';
      $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';

      $extension = $file_parts['extension'];
      $pathWithoutExtension = str_replace('.'.$file_parts['extension'], '', $path);

      $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
      $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

      $converted = $scheme.$user.$pass.$host.$port.$pathWithoutExtension . str_replace(['?', '&', '#', '='], '-', $query.$fragment) . '.'.$extension;

      $filename = '/cxm/import/attachment/externalsync/' . $casawp_id . '/' . basename($converted);

    } else {
      $filename = '/cxm/import/attachment/externalsync/' . $casawp_id . '/' . basename($fileurl);
    }

    // $this->addToLog('fileurl');
    // $this->addToLog($fileurl);

    // $this->addToLog('filename');
    // $this->addToLog($filename);

    // $this->addToLog('basename fileurl');
    // $this->addToLog(basename($fileurl));


    //extention is required
    $file_parts = pathinfo($filename);
    if (!isset($file_parts['extension'])) {
        $filename = $filename . '.jpg';
    }
    if (!is_file(CXM_CUR_UPLOAD_BASEDIR . $filename)) {
      if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync')) {
        mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync');
      }
      if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync/' . $casawp_id)) {
        mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync/' . $casawp_id);
      }
      if (!is_file(CXM_CUR_UPLOAD_BASEDIR . $filename )) {
        if (!isset($this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"])) {
          $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"] = array();
        }
        $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"][] = $filename;

        $contextOptions = array(
          "ssl" => array(
            "verify_peer"      => false,
            "verify_peer_name" => false,
          ),
        );

        if (strpos($fileurl, '://') && !strpos($fileurl, 'missing_file_icon')) {
          $could_copy = copy(urldecode($fileurl), CXM_CUR_UPLOAD_BASEDIR . $filename, stream_context_create( $contextOptions ));
        } else {
          //$could_copy = copy($fileurl, CXM_CUR_UPLOAD_BASEDIR . $filename );
        }
        if (!$could_copy) {
          $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"][] = 'FAILED: ' .$filename;
          $filename = false;
        }

      }
    }
    return $filename;
  }

  public function cxmUploadAttachment($the_mediaitem, $post_id, $casawp_id) {

    // $this->addToLog('cxmUploadAttachment');

    //$this->addToLog($the_mediaitem);

    // $this->addToLog($post_id);

    // $this->addToLog($casawp_id);


    if ($the_mediaitem) {
    //   $filename = $the_mediaitem['url']; //'/cxm/import/attachment/'. basename($the_mediaitem['url']);
    // } elseif ($the_mediaitem['url']) { //external
    //   if ($the_mediaitem['type'] === 'image'){
    //     // simply don't copy the original file (the orig meta is used for rendering instead)
    //     $filename = $the_mediaitem['url'];
    //   } else {
        $filename = $this->cxmUploadAttachmentFromGateway($casawp_id, $the_mediaitem['url']);
      //}
    } else { //missing
      $filename = false;
    }

    if ($filename && (is_file(CXM_CUR_UPLOAD_BASEDIR . $filename)) ) {
      //new file attachment upload it and attach it fully
      $wp_filetype = wp_check_filetype(basename($filename), null );

      $this->addToLog('new file attachment upload it and attach it fully');

      $this->addToLog($wp_filetype);


      $guid = CXM_CUR_UPLOAD_BASEDIR . $filename;
      if ($the_mediaitem['type'] === 'image') {
        $guid = $filename;
      }
      $attachment = array(
        'guid'           => $guid,
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => ( $the_mediaitem['title'] ? $the_mediaitem['title'] : basename($filename)),
        'post_name'      => sanitize_title_with_dashes($guid,'', 'save'),
        'post_content'   => '',
        'post_excerpt'   => $the_mediaitem['caption'],
        'post_status'    => 'inherit',
        'menu_order'     => $the_mediaitem['order']
      );

      $attach_id = wp_insert_attachment( $attachment, CXM_CUR_UPLOAD_BASEDIR . $filename, $post_id );
      // you must first include the image.php file
      // for the function wp_generate_attachment_metadata() to work
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata( $attach_id, CXM_CUR_UPLOAD_BASEDIR . $filename );
      wp_update_attachment_metadata( $attach_id, $attach_data );

      //category
      $term = get_term_by('slug', $the_mediaitem['type'], 'cxm_attachment_type');
      $term_id = $term->term_id;
      wp_set_post_terms( $attach_id,  array($term_id), 'cxm_attachment_type' );

      //alt
      update_post_meta($attach_id, '_wp_attachment_image_alt', $the_mediaitem['alt']);

      //orig
      update_post_meta($attach_id, '_origin', ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url']));

      return $attach_id;
    } else {
      return $filename . " could not be found!";
    }
  }



  // public function setOfferAttachments($offer_medias, $wp_post, $casawp_id, $casawp_id, $property){
  //   ### future task: for better performace compare new and old data ###


  //   //get xml media files
  //   $the_casawp_attachments = array();
  //   if ($offer_medias) {
  //     $o = 0;
  //     foreach ($offer_medias as $offer_media) {
  //       $o++;
  //       $media = $offer_media['media'];
  //       if (in_array($offer_media['type'], array('image', 'document', 'plan', 'offer-logo', 'sales-brochure'))) {
  //         $the_casawp_attachments[] = array(
  //           'type'    => $offer_media['type'],
  //           'alt'     => $offer_media['alt'],
  //           'title'   => ( $offer_media['title'] ? $offer_media['title'] : basename($media['original_file'])),
  //           'file'    => '',
  //           'url'     => $media['original_file'],
  //           'caption' => $offer_media['caption'],
  //           'order'   => $o
  //         );
  //       }
  //     }
  //   }

  //   if (get_option('casawp_limit_reference_images') && $property['availability'] == 'reference') {
  //     $title_image = false;
  //     foreach ($the_casawp_attachments as $key => $attachment) {
  //       if ($attachment['type'] == 'image') {
  //         $title_image = $attachment;
  //         break;
  //       }
  //     }
  //     if ($title_image) {
  //       $the_casawp_attachments = array(0 => $title_image);
  //     }
  //   }

  //   //get post attachments already attached
  //   $wp_casawp_attachments = array();
  //   $args = array(
  //     'post_type'   => 'attachment',
  //     'numberposts' => -1,
  //     'post_status' => null,
  //     'post_parent' => $wp_post->ID,
  //     'tax_query'   => array(
  //       'relation'  => 'AND',
  //       array(
  //         'taxonomy' => 'cxm_attachment_type',
  //         'field'    => 'slug',
  //         'terms'    => array( 'image', 'plan', 'document', 'offer-logo', 'sales-brochure' )
  //       )
  //     )
  //   );
  //   $attachments = get_posts($args);
  //   if ($attachments) {
  //     foreach ($attachments as $attachment) {
  //       $wp_casawp_attachments[] = $attachment;
  //     }
  //   }

  //   //upload necesary images to wordpress
  //   if (isset($the_casawp_attachments)) { // go through each attachment specified in xml
  //     $wp_casawp_attachments_to_remove = $wp_casawp_attachments;
  //     $dup_checker_arr = [];
  //     foreach ($the_casawp_attachments as $the_mediaitem) { // go through each available attachment already in db
  //       //look up wp and see if file is already attached
  //       $existing = false;
  //       $existing_attachment = array();
  //       foreach ($wp_casawp_attachments as $key => $wp_mediaitem) {
  //         $attachment_customfields = get_post_custom($wp_mediaitem->ID);
  //         $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');

  //         // this checks for duplicates and ignores them if they exist. This can fix duplicates existing in the DB if they where, for instance, created durring run-in imports.
  //         if (in_array($original_filename, $dup_checker_arr)) {
  //           $this->addToLog('found duplicate for id: ' . $wp_mediaitem->ID . ' orig: ' . $original_filename);
  //           // this file appears to be a duplicate, skip it (that way it will be deleted later) aka. it will remain in $wp_casawp_attachments_to_remove.
  //           // because it encountered this file before it must be made existing in the past loop right?
  //           // DISABLE FOR NOW
  //           // $existing = true;
  //           // continue;
  //         }
  //         $dup_checker_arr[] = $original_filename;

  //         $alt = '';
  //         if (
  //           $original_filename == ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url'])
  //           ||
  //           str_replace('%3D', '=', str_replace('%3F', '?', $original_filename)) == ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url'])
  //         ) {
  //           $existing = true;
  //           $this->addToLog('updating attachment ' . $wp_mediaitem->ID);

  //           //it's here to stay
  //           unset($wp_casawp_attachments_to_remove[$key]);

  //           $types = wp_get_post_terms( $wp_mediaitem->ID, 'cxm_attachment_type');
  //           if (array_key_exists(0, $types)) {
  //             $typeslug = $types[0]->slug;
  //             $alt = get_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', true);
  //             //build a proper array out of it
  //             $existing_attachment = array(
  //               'type'    => $typeslug,
  //               'alt'     => $alt,
  //               'title'   => $wp_mediaitem->post_title,
  //               'file'    => $the_mediaitem['file'],
  //               //'file'    => maibe? -> (is_file($the_mediaitem['file']) ? $the_mediaitem['file'] : '')
  //               'url'     => $the_mediaitem['url'],
  //               'caption' => $wp_mediaitem->post_excerpt,
  //               'order'   => $wp_mediaitem->menu_order
  //             );
  //           }

  //           //have its values changed?
  //           if($existing_attachment != $the_mediaitem ){
  //             $changed = true;
  //             $this->transcript[$casawp_id]['attachments']["updated"] = 1;
  //             //update attachment data
  //             if ($existing_attachment['caption'] != $the_mediaitem['caption']
  //               || $existing_attachment['title'] != $the_mediaitem['title']
  //               || $existing_attachment['order'] != $the_mediaitem['order']
  //               ) {
  //               $att['post_excerpt'] = $the_mediaitem['caption'];
  //               $att['post_title']   = ( $the_mediaitem['title'] ? $the_mediaitem['title'] : basename($filename));
  //               $att['ID']           = $wp_mediaitem->ID;
  //               $att['menu_order']   = $the_mediaitem['order'];
  //               $insert_id           = wp_update_post( $att);
  //             }
  //             //update attachment category
  //             if ($existing_attachment['type'] != $the_mediaitem['type']) {
  //               $term = get_term_by('slug', $the_mediaitem['type'], 'cxm_attachment_type');
  //               $term_id = $term->term_id;
  //               wp_set_post_terms( $wp_mediaitem->ID,  array($term_id), 'cxm_attachment_type' );
  //             }
  //             //update attachment alt
  //             if ($alt != $the_mediaitem['alt']) {
  //               update_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', $the_mediaitem['alt']);
  //             }
  //           }
  //         }


  //       }

  //       if (!$existing) {
  //         $this->addToLog('creating new attachment ' . $wp_mediaitem->ID);
  //         //insert the new image
  //         $new_id = $this->cxmUploadAttachment($the_mediaitem, $wp_post->ID, $casawp_id);
  //         if (is_int($new_id)) {
  //           $this->transcript[$casawp_id]['attachments']["created"] = $the_mediaitem['file'];
  //         } else {
  //           $this->transcript[$casawp_id]['attachments']["failed_to_create"] = $new_id;
  //         }
  //       }

  //       //tries to fix missing files
  //       if (! get_option('casawp_use_casagateway_cdn', false) && isset($the_mediaitem['url'])) {
  //         $this->cxmUploadAttachmentFromGateway($casawp_id, $the_mediaitem['url']);
  //       }


  //     } //foreach ($the_casawp_attachments as $the_mediaitem) {

  //     //images to remove
  //     if ($wp_casawp_attachments_to_remove){
  //       $this->addToLog('removing ' . count($wp_casawp_attachments_to_remove) . ' attachments');
  //     }
  //     foreach ($wp_casawp_attachments_to_remove as $attachment) {
  //       $this->addToLog('removing ' . $attachment->ID);
  //       $this->transcript[$casawp_id]['attachments']["removed"] = $attachment;

  //       // $attachment_customfields = get_post_custom($attachment->ID);
  //       // $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');
  //       wp_delete_attachment( $attachment->ID );
  //     }

  //     //featured image (refetch to avoid setting just removed items or not having new items)
  //     $args = array(
  //       'post_type'   => 'attachment',
  //       'numberposts' => -1,
  //       'post_status' => null,
  //       'post_parent' => $wp_post->ID,
  //       'tax_query'   => array(
  //         'relation'  => 'AND',
  //         array(
  //           'taxonomy' => 'cxm_attachment_type',
  //           'field'    => 'slug',
  //           'terms'    => array( 'image', 'plan', 'document', 'offer-logo', 'sales-brochure' )
  //         )
  //       )
  //     );
  //     $attachments = get_posts($args);
  //     if ($attachments) {
  //       unset($wp_casawp_attachments);
  //       foreach ($attachments as $attachment) {
  //         $wp_casawp_attachments[] = $attachment;
  //       }
  //     }

  //     $attachment_image_order = array();
  //     foreach ($the_casawp_attachments as $the_mediaitem) {
  //       if ($the_mediaitem['type'] == 'image') {
  //         $attachment_image_order[$the_mediaitem['order']] = $the_mediaitem;
  //       }
  //     }
  //     if (isset($attachment_image_order) && !empty($attachment_image_order)) {
  //       ksort($attachment_image_order);
  //       $attachment_image_order = reset($attachment_image_order);
  //       if (!empty($attachment_image_order)) {
  //         foreach ($wp_casawp_attachments as $wp_mediaitem) {
  //           $attachment_customfields = get_post_custom($wp_mediaitem->ID);
  //           $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');
  //           if (
  //             $original_filename == ($attachment_image_order['file'] ? $attachment_image_order['file'] : $attachment_image_order['url'])
  //             ||
  //             str_replace('%3D', '=', str_replace('%3F', '?', $original_filename)) == ($attachment_image_order['file'] ? $attachment_image_order['file'] : $attachment_image_order['url'])
  //           ) {
  //             $cur_thumbnail_id = get_post_thumbnail_id( $wp_post->ID );
  //             if ($cur_thumbnail_id != $wp_mediaitem->ID) {
  //               set_post_thumbnail( $wp_post->ID, $wp_mediaitem->ID );
  //               $this->transcript[$casawp_id]['attachments']["featured_image_set"] = 1;
  //               break;
  //             }
  //           }
  //         }
  //       }
  //     }




  //   } //(isset($the_casawp_attachments)


  // }


  public function setPropertyBuilding($wp_post, $building, $casawp_id, $buildingcontent, $propertytype){
    $new_term = null;
    $old_term = null;

    //$this->addToLog($building);
    //$this->addToLog($propertytype);

    $separateBuildingPropertyType = PluginOptions::get_option( 'separate_building_property_type', false ); //works false or 1

    if ($separateBuildingPropertyType == 1){
      $building = $building . ' ' . $propertytype;
      //$building = $propertytype;
    }
    //$this->addToLog($building);


    if ($building) {
      $new_term = get_term_by('slug', $building, 'building', OBJECT, 'raw' );



      if (!$new_term) {
        $options = array(
          'description' => $buildingcontent,
          'slug' => $building
        );
        $id = wp_insert_term(
          $building,
          'building',
          $options
        );
        //$this->addToLog($id);
        $this->addToLog('wp_insert_term');
        $new_term = get_term($id, 'building', OBJECT, 'raw');

      }
    }

    $wp_post_terms = wp_get_object_terms($wp_post->ID, 'building');

    if ($wp_post_terms) {
      //$old_term = $wp_post_terms[0];
    }

    if ($old_term != $new_term) {
      $this->transcript[$casawp_id]['building']['from'] = ($old_term ? $old_term->name : 'none');
      $this->transcript[$casawp_id]['building']['to'] =   ($new_term ? $new_term->name : 'none');
      wp_set_object_terms( $wp_post->ID, ($new_term ? $new_term->term_id : NULL), 'building' );
    }

  }


  public function addToLog($transcript){
    $dir = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/logs';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dir."/".get_date_from_gmt('', 'Ym').'.log', "\n".json_encode(array(get_date_from_gmt('', 'Y-m-d H:i') => $transcript)), FILE_APPEND);
  }

  // public function casawpImport(){
  //   if ($this->getImportFile()) {
  //     if (is_admin()) {
  //       $this->updateUnits();
  //       echo '<div id="message" class="updated"><p>casawp <strong>updated</strong>.</p><pre>' . print_r($this->transcript, true) . '</pre></div>';
  //     } else {
  //       $this->updateUnits();
  //       $this->transcript;
  //       //echo '<div id="message" class="updated"><p>casawp <strong>updated</strong>.</p><pre>' . print_r($this->transcript, true) . '</pre></div>';
  //       //do task in the background
  //       //add_action('asynchronous_import', array($this,'updateOffers'));
  //       //wp_schedule_single_event(time(), 'asynchronous_import');
  //     }
  //   }
  // }  

  // public function gatewaypoke(){
  //   add_action('asynchronous_gatewayupdate', array($this,'gatewaypokeanswer'));
  //   $this->addToLog('Scheduled an Update on: ' . time());
  //   wp_schedule_single_event(time(), 'asynchronous_gatewayupdate');
  // }

  // public function gatewaypokeanswer(){
  //   $this->addToLog('gateway call file: ' . time());
  //   $this->updateImportDataThroughEmonitor();
  //   $this->addToLog('gateway import answer: ' . time());
  //   $this->updateUnits();
  // }


  public function updateImportDataThroughEmonitor(){
    $this->addToLog('emonitor data retriaval start: ' . time());

    $apikey = PluginOptions::get_option( 'cxm_emonitor_api', false );
    //$this->addToLog($apikey);


    if ($apikey) {
      
      //build url
      $url = $apikey;
      //$this->addToLog($url);

      $response = false;

      if (!function_exists('curl_version')) {
        $this->addToLog('gateway ERR (CURL MISSING!!!): ' . time());
        echo '<div id="message" class="updated"> CURL MISSING!!!</div>';
      }

      $ch = curl_init();
      try {
          //$url = 'http://casacloud.cloudcontrolapp.com' . '/rest/provider-properties?' . http_build_query($query);
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
          $response = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


          $this->addToLog($httpCode);

          if($httpCode == 404 || $httpCode == 0 || $httpCode == 301) {
            $response = false;
          }

      } catch (Exception $e) {
          $response =  $e->getMessage() ;
          $this->addToLog('gateway ERR (' . $response . '): ' . time());
      }

      //$this->addToLog($response); //Object Array

      if ($response) {
        if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
          mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import');
        }
        $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml';

        file_put_contents($file, $response);

        $this->addToLog('gateway start update: ' . time());
        //UPDATE OFFERS NOW!!!!
        if ($this->getEmonitorImportFile()) {
          $this->addToLog('import start');
          $this->updateUnits($response);
          $this->addToLog('import end');
        }


      } else {
        $this->addToLog('ERR no response from gateway: ' . time());

        $emailreal = "alert@casasoft.com";
        $subject = get_bloginfo('name');
        $message = "Die Emonitor Schnittstelle scheint nicht mehr zu funktionieren. Kann das sein? Bitte prüfen.";
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        //$headers[] = 'From: no-reply@central-malley.ch <no-reply@central-malley.ch>';

        $sent = wp_mail( $emailreal, $subject, $message, $headers);

        if( $sent ){
            $this->addToLog('Email Notification Sent! : ' . $emailreal);
        } else {
            $this->addToLog('Email Notification NOT Sent! : ' . $emailreal);
        }

      }
      curl_close($ch);

      //echo '<div id="message" class="updated">XML wurde aktualisiert</div>';
    } else {
      $this->addToLog('API Keys missing emonitor: ' . time());
      echo '<div id="message" class="updated"> API Keys missing emonitor</div>';
    }
  }

  public function addToTranscript($msg){
    $this->transcript[] = $msg;
  }

  public function unit2Array($property_xml){

    //$this->addToLog(isset($property_xml->building->country));

    $propertydata = array(

        //building data
        'building_adress'       =>  ($property_xml->building->adress ?:''),
        'building_publicated_adress'       =>  ($property_xml->building->publicated_adress ?:''),
        'building_house_number_supplement'       =>  ($property_xml->building->house_number_supplement ?:''),
        'building_house_number'       =>  ($property_xml->building->house_number ?:''),
        'building_year_of_construction'       =>  ($property_xml->building->year_of_construction ?:''),
        'building_plz'       =>  ($property_xml->building->plz ?:''),
        'building_city'       =>  ($property_xml->building->city ?:''),
        'building_state'       =>  ($property_xml->building->state ?:''),
        'building_lift'       =>  ($property_xml->building->lift ?:''),
        'building_title'       =>  ($property_xml->building->title ?:''),
        'building_mark'       =>  ($property_xml->building->mark ?:''),
        'building_mark_property'       =>  ($property_xml->building->mark_property ?:''),
        'building_colony'       =>  ($property_xml->building->colony ?:''),
        'building_metropolitan'       =>  ($property_xml->building->metropolitan ?:''),
        'building_district'       =>  ($property_xml->building->district ?:''),
        
        
        //property data media
        'images'       =>  ($property_xml->images ?:''),
        'factsheet'       =>  ($property_xml->factsheet ?:''),
        'pdf_file_link'       =>  ($property_xml->pdf_file_link ?:''),
        'pdf_file'       =>  ($property_xml->pdf_file ?:''),
        'image'       =>  ($property_xml->image ?:''),
        'isometry'       =>  ($property_xml->isometry ?:''),
        'situation_plan'       =>  ($property_xml->situation_plan ?:''),
        'factsheets'       =>  ($property_xml->factsheets ?:''),
        'application_pdf'       =>  ($property_xml->application_pdf ?:''),
        'ref'       =>  ($property_xml->ref ?:''),
        'layout_plan'       =>  ($property_xml->layout_plan ?:''),


        //property data
        'incidental_costs'       =>  ($property_xml->incidental_costs ?:''),
        'incidental_costs_squaremeter'       =>  ($property_xml->incidental_costs_squaremeter ?:''),
        'rentalprice_squaremeter'       =>  ($property_xml->rentalprice_squaremeter ?:''),
        'rentalprice_squaremeter_net'       =>  ($property_xml->rentalprice_squaremeter_net ?:''),
        'state_simplyfied'       =>  ($property_xml->state_simplyfied ?:''),
        'url'       =>  ($property_xml->url ?:''),
        'end_date'       =>  ($property_xml->end_date ?:''),
        'creation'       =>  ($property_xml->start_date ?:''),
        'move_in_date'       =>  ($property_xml->move_in_date ?:''),
        'property_type'       =>  ($property_xml->property_type ?:''),
        'rentalgross'       =>  ($property_xml->rentalgross ?:''),
        'rentalgross_net'       =>  ($property_xml->rentalgross_net ?:''),
        'price_unit'       =>  ($property_xml->price_unit ?:''),
        'state'       =>  ($property_xml->state ?:''),
        'mark'       =>  ($property_xml->mark ?:''),
        'title'       =>  ($property_xml->title ?:''),
        'area'       =>  ($property_xml->area ?:''),
        'rooms'       =>  ($property_xml->rooms ?:''),
        'area_brutto'       =>  ($property_xml->area_brutto ?:''),
        'area_property'       =>  ($property_xml->area_property ?:''),
        'reference_date'       =>  ($property_xml->reference_date ?:''),
        'object'       =>  ($property_xml->object ?:''),
        'orientation'       =>  ($property_xml->orientation ?:''),
        'add_costs_per_month_m2'       =>  ($property_xml->add_costs_per_month_m2 ?:''),
        'virtual_tour_link'       =>  ($property_xml->virtual_tour_link ?:''),
        'website_link'       =>  ($property_xml->website_link ?:''),
        'balcony'       =>  ($property_xml->balcony ?:''),
        'balcony_yes_no'       =>  ($property_xml->balcony_yes_no ?:''),
        'loggia'       =>  ($property_xml->loggia ?:''),
        'loggia_area'       =>  ($property_xml->loggia_area ?:''),
        'loggia_number'       =>  ($property_xml->loggia_number ?:''),
        'garden_text'       =>  ($property_xml->garden_text ?:''),
        'garden_sitting_place'       =>  ($property_xml->garden_sitting_place ?:''),
        'garden_sitting_place_area'       =>  ($property_xml->garden_sitting_place_area ?:''),
        'shared_garden'       =>  ($property_xml->shared_garden ?:''),
        'winter_garden'       =>  ($property_xml->winter_garden ?:''),
        'winter_garden_number'       =>  ($property_xml->winter_garden_number ?:''),
        'terrace'       =>  ($property_xml->terrace ?:''),
        'terrace_area'       =>  ($property_xml->terrace_area ?:''),
        'terrace_garden_number'       =>  ($property_xml->terrace_garden_number ?:''),
        'floor'       =>  ($property_xml->floor ?:''),
        'location_on_floor'       =>  ($property_xml->location_on_floor ?:''),
        'bath'       =>  ($property_xml->bath ?:''),
        'bath_number'       =>  ($property_xml->bath_number ?:''),
        'additional_costs_1'       =>  ($property_xml->additional_costs_1 ?:''),
        'additional_costs_2'       =>  ($property_xml->additional_costs_2 ?:''),
        'min_adult'       =>  ($property_xml->min_adult ?:''),
        'max_adult'       =>  ($property_xml->max_adult ?:''),
        'rental_conditions'       =>  ($property_xml->rental_conditions ?:''),

    );

  

  //attachments
  // $propertydata['layout_plan'] = array();
  // if ($property_xml->layout_plan) {
  //   $this->addToLog('TEST1');

  //     foreach ($property_xml->layout_plan as $xml_media) {
  //       $this->addToLog('TEST2');
  //         if ($xml_media->file) {
  //             $source = dirname($this->file) . $xml_media->file->__toString();
  //             $this->addToLog('TEST3');
  //         } elseif ($xml_media->url) {
  //             $source = $xml_media->url->__toString();
  //             $source = implode('/', array_map('rawurlencode', explode('/', $source)));
  //             $source = str_replace('http%3A//', 'http://', $source);
  //             $source = str_replace('https%3A//', 'https://', $source);
  //             $this->addToLog('TEST4');
  //         } else {
  //           $this->addToLog('TEST5');
  //             $this->addToTranscript("file or url missing from attachment media!");
  //             continue;
  //         }
  //         $offerData['offer_medias'][] = array(
  //             'alt' => $xml_media->alt->__toString(),
  //             'title' => $xml_media->title->__toString(),
  //             'caption' => $xml_media->caption->__toString(),
  //             'description' => $xml_media->description->__toString(),
  //             'type' => (isset($xml_media['type']) ? $xml_media['type']->__toString() : 'image'),
  //             'media' => array(
  //                 'original_file' => $source,
  //             )
  //         );
  //     }
  // }



    // emonitor fields not included for now

    //"sightseeing_schedule": null,
    //"max_applications": null,
    //"side_area": null,
    //"min_occupancy": 0.0,
    //"tender_notice": "",
    //"tender_teaser": "",
    //"tender_title": null,
    // "pavement": false,
    // "pavement_area": 0.0,
    // "shared_rooftop": false,
    // "basement": false,
    // "basement_area": null,
    // "kitchen": null,
    // "granite": false,
    // "ceramic": false,
    // "dishwasher": false,
    // "seperate_kitchen": false,
    // "heating": null,
    // "stove_heating": false,
    // "bath_with_toilet": false,
    // "bath_with_toilet_number": null,
    // "sep_toilet": false,
    // "shower": false,
    // "shower_number": null,
    // "shower_with_toilet": false,
    // "shower_with_toilet_number": null,
    // "toilet_add": false,
    // "toilet_number": null,
    // "incidentals": null,
    // "valued_net_rental_amount_mm": null,
    // "operating_costs": null,
    // "heating_costs": null,
    // "evaluated_rental_amount_total_mm": null,
    // "electricity_cost": null,
    // "car_free": false,
    // "car_poor": false,
    // "loud_apartment": false,
    // "buggy_parking": false,
    // "wheelchair_accessible": false,
    // "unobstructed": false,
    // "lift": false,
    // "washing_tower": false,
    // "fireplace": false,
    // "parking_add": false,
    // "parking_space_cost": null,
    // "parking_space_type": null,
    // "free_from": null,
    // "compound_site": null,
    // "captured_room": false,
    // "storage_room_text": null,
    // "storage_area": null,
    // "capital_share": null,
    // "usage": "",
    // "deposit": null,
    // "corridor": false,
    // "corridor_number": null,
    // "hallway": false,
    // "hallway_number": null,
    // "min_max_income": null,
    // "min_max_fortune": null,
    // "household_type": null,
    // "min_max_age_structure": null,
    // "children_stay": 0,
    // "musician": false,
    // "pets": false,
    // "dogs_allowed": false,
    // "cats_allowed": false,
    // "min_child": 0,
    // "max_child": 0,
    // "target_group": null,
    // "accounting_date": null,
    // "accounting_cleared": false,
    // "saved_data": null,
    // "features_boolean": "",
    // "features_numeric": null,
    // "reference_number": null,
    // "honorary_percent": 0.0,
    // "form_id": 1,
    // "second_form_id": null,
    // "proprietor": null,
    // "collectable": null

    return $propertydata;

  }

  public function updateUnits($response){

    //make sure dires exist

    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm');
    }
    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import');
    }
    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment');
    }
    // if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync')) {
    //   mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment/externalsync');
    // }

    //$this->addToLog('Response: '. $response);

    //$this->renameImportFileTo(CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data-done.xml');
    set_time_limit(600);
    global $wpdb;
    //libxml_use_internal_errors();


    $xml = file_get_contents($this->getEmonitorImportFile(), true);
    $xml = json_decode($xml);
    
    // $this->addToLog(is_object($xml));
    // $this->addToLog(is_array($xml));

    // $errors = libxml_get_errors();
    // if (!$xml) {
    //   die('could not read XML!!!');
    // }
    // if ($errors) {
    //   $this->transcript['error'] = 'XML read error' . print_r($errors, true);
    //   die('XML read error');
    // }
    $found_posts = array();
    //key is id value is rank!!!!
    $ranksort = array();
    $curRank = 0;


    // echo '<pre>';
    // $totalTime = microtime(true);

    // select all properties from db at once
    $startfullselectiontime = microtime(true);
    $posts_pool = [];
    $the_query = new \WP_Query( 'post_status=publish,pending,draft,future,trash&post_type=complex_unit&suppress_filters=true&posts_per_page=100000' );
    $wp_post = false;
    while ( $the_query->have_posts() ) :
      $the_query->the_post();
      global $post;
      $existing_casawp_import_id = get_post_meta($post->ID, 'casawp_id', true);
      if ($existing_casawp_import_id) {
        $posts_pool[$existing_casawp_import_id] = $post;
      }
    endwhile;
    wp_reset_postdata();
    // echo count($posts_pool);
    // echo'<br />select all time';
    // echo number_format((microtime(true) - $startfullselectiontime), 10);
    // echo '<br />';


    // function convert($size)
    // {
    //     $unit=array('b','kb','mb','gb','tb','pb');
    //     return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    // }

    // echo convert(memory_get_usage(true)); // 123 kb

    // die();

      //$this->addToLog(json_decode($xml));

      //$xml = json_decode(json_encode($xml), true);

      

    // Sort the XML by unit title
    $sortArray = array();
    foreach($xml as $xml_unit){
        foreach($xml_unit as $key=>$value){
            if(!isset($sortArray[$key])){
                $sortArray[$key] = array();
            }
            $sortArray[$key][] = $value;
        }
    }

    $orderby = "title"; //change this to whatever key you want from the array

    array_multisort($sortArray[$orderby],SORT_ASC,$xml);

    //var_dump($xml);
    //$this->addToLog($xml);

    

    foreach ($xml as $property) {
      $curRank++;

      $timeStart = microtime(true);

      $propertyData = $this->unit2Array($property);
      //make main language first and "single out" if not multilingual

      //$this->addToLog($propertyData);

      $theoffers = array();
      $i = 0;

      //$this->r($propertyData['offers']);
      foreach ($propertyData as $offer) {
        $i++;
        //if ($offer['lang'] == $this->getMainLang()) {
          $theoffers[$i] = $offer;
        // } else {
        //   if ($this->hasWPML()) {
        //     $theoffers[] = $offer;
        //   }
        // }
      }

      

      //complete missing translations if multilingual
      // if ($this->hasWPML()) {
      //   $theoffers = $this->fillMissingTranslations($theoffers);
      // }

      //$this->addToLog($propertyData);

      //$this->addToLog($theoffers);

      $offer_pos = 0;
      //$this->addToLog($theoffers);
      //foreach ($theoffers as $offerData) {
        $offer_pos++;

        //is it already in db
        //$casawp_id = $propertyData['exportcasawp_id'] . $offerData['lang'];
        $casawp_id = $propertyData['title'];
        $this->addToLog($casawp_id);


        // select one at a time
        // $the_query = new \WP_Query( 'post_status=publish,pending,draft,future,trash&post_type=complex_unit&suppress_filters=true&meta_key=casawp_id&meta_value=' . $casawp_id );
        // $wp_post = false;
        // while ( $the_query->have_posts() ) :
        //   $the_query->the_post();
        //   global $post;
        //   $wp_post = $post;
        // endwhile;
        // wp_reset_postdata();

        // select from pool
        $wp_post = false;
        if (array_key_exists($casawp_id, $posts_pool)) {
          $wp_post = $posts_pool[$casawp_id];
        }

        //$this->addToLog($posts_pool[$casawp_id]);

        //if not create a basic property
        //$this->addToLog('WP post???');
        if (!$wp_post) {
          $this->transcript[$casawp_id]['action'] = 'new';
          $the_post['post_title'] = $propertyData['title'];
          //$this->addToLog($the_post['post_title']);
          $the_post['post_content'] = '';
          $the_post['post_status'] = 'publish';
          $the_post['post_type'] = 'complex_unit';
          $the_post['menu_order'] = $curRank;
          $the_post['post_name'] = sanitize_title_with_dashes($casawp_id,'','save');

          //use the casagateway creation date if its new
          $the_post['post_date'] = ($propertyData['creation'] ?: '');
          //die($the_post['post_date']);

          $_POST['icl_post_language'] = $offerData['lang'];
          $insert_id = wp_insert_post($the_post);
          update_post_meta($insert_id, 'casawp_id', $casawp_id);
          $wp_post = get_post($insert_id, OBJECT, 'raw');
          $this->addToLog('new property: '. $casawp_id);
        }

        $ranksort[$wp_post->ID] = $curRank;

        $found_posts[] = $wp_post->ID;


        //$this->addToLog($found_posts);

        $this->updateUnit($casawp_id, $offer_pos, $propertyData, $offerData, $wp_post);

        //$this->updateInsertWPMLconnection($wp_post, $offerData['lang'], $propertyData['exportcasawp_id']);

      //}

      // echo $curRank . '<br />';
      // echo number_format((microtime(true) - $timeStart), 10);
      // echo '<br />';
      // if ($curRank > 500) {
      //   break;
      // }
      // echo '</pre>';
    }

    //$this->addToLog('End of Loop');

    // echo'<br />Total';
    // echo number_format((microtime(true) - $totalTime), 10);
    // echo '<br />';
    // die();

    if (!$found_posts) {
      $this->transcript['error'] = 'NO PROPERTIES FOUND IN XML!!!';
      $this->transcript['error_infos'] = [
        'filesize' => filesize(CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml') . ' !'
      ];

      copy(CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml', CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data-error.xml');

      //wp_mail('alert@casasoft.com', get_bloginfo('name'), 'Dieser Kunde hat alle Objekte von der Webseite gelöscht. Kann das sein? Bitte prüfen.');
      //die('custom block');
    }

      //3. remove all the unused properties
      $properties_to_remove = get_posts(  array(
        'suppress_filters'=>true,
        'language'=>'ALL',
        'numberposts' =>  100,
        'exclude'     =>  $found_posts,
        'post_type'   =>  'complex_unit',
        'post_status' =>  'publish'
        )
      );
      foreach ($properties_to_remove as $prop_to_rm) {
        //remove the attachments
        $attachments = get_posts( array(
          'suppress_filters'=>true,
          'language'=>'ALL',
          'post_type'      => 'attachment',
          'posts_per_page' => -1,
          'post_parent'    => $prop_to_rm->ID,
          'exclude'        => get_post_thumbnail_id()
        ) );
        if ( $attachments ) {
          foreach ( $attachments as $attachment ) {
            $attachment_id = $attachment->ID;
          }
        }
        wp_trash_post($prop_to_rm->ID);

      }

      //4. set property menu_order
      $properties_to_sort = get_posts(  array(
        'suppress_filters'=>true,
        'language'=>'ALL',
        'numberposts' =>  100,
        'include'     =>  $found_posts,
        'post_type'   =>  'complex_unit',
        'post_status' =>  'publish'
        )
      );
      $sortsUpdated = 0;
      // echo '<pre>';
      // echo "properties_to_sort\n";
      // print_r($properties_to_sort);

      // echo "ranksort\n";
      // print_r($ranksort);
      // TODO: when one changes an id of a property in the xml with wpml:  Error: Maximum function nesting level of '256'  happens:   WPML_Post_Synchronization->sync_with_translations( ) happens indefinetly
      foreach ($properties_to_sort as $prop_to_sort) {
        if (array_key_exists($prop_to_sort->ID, $ranksort)) {
          if ($prop_to_sort->menu_order != $ranksort[$prop_to_sort->ID]) {
            // echo "wp_post_update\n";
            // print_r('ID' . $prop_to_sort->ID . ':' . $prop_to_sort->menu_order . 'to' . $ranksort[$prop_to_sort->ID]);
            $sortsUpdated++;
            try {
              $newPostID = wp_update_post(array(
                'ID' => $prop_to_sort->ID,
                'menu_order' => $ranksort[$prop_to_sort->ID]
              ));
            } catch (\Throwable $th) {
              //throw $th;
              if (isset($this->transcript['wp_update_post_error'])) {
                $this->transcript['wp_update_post_error'][] = $th->getMessage();
              } else {
                $this->transcript['wp_update_post_error'] = [$th->getMessage()];
              }
            }
          }

        }

      }
      // echo '</pre>';

      $this->transcript['sorts_updated'] = $sortsUpdated;
      $this->transcript['properties_found_in_xml'] = count($found_posts);
      $this->transcript['properties_removed'] = count($properties_to_remove);

      // //5a. fetch max and min options and set them anew
      // global $wpdb;
      // $query = $wpdb->prepare("SELECT max( cast( meta_value as UNSIGNED ) ) FROM {$wpdb->postmeta} WHERE meta_key='areaForOrder'", 'foo', 'bar');
      // $max_area = $wpdb->get_var( $query );
      // $query = $wpdb->prepare("SELECT min( cast( meta_value as UNSIGNED ) ) FROM {$wpdb->postmeta} WHERE meta_key='areaForOrder'", 'foo', 'bar');
      // $min_area = $wpdb->get_var( $query );

      // //5b. fetch max and min options and set them anew
      // $query = $wpdb->prepare("SELECT max( cast(meta_value as DECIMAL(10, 1) ) ) FROM {$wpdb->postmeta} WHERE meta_key='number_of_rooms'", 'foo', 'bar');
      // $max_rooms = $wpdb->get_var( $query );
      // $query = $wpdb->prepare("SELECT min( cast( meta_value as DECIMAL(10, 1) ) ) FROM {$wpdb->postmeta} WHERE meta_key='number_of_rooms'", 'foo', 'bar');
      // $min_rooms = $wpdb->get_var( $query );

      // update_option('casawp_archive_area_min', $min_area);
      // update_option('casawp_archive_area_max', $max_area);
      // update_option('casawp_archive_rooms_min', $min_rooms);
      // update_option('casawp_archive_rooms_max', $max_rooms);


    //projects
    // if ($xml->projects) {

    //   $found_posts = array();
    //   $sorti = 0;
    //   foreach ($xml->projects->project as $project) {
    //     $sorti++;

    //     $projectData = $this->project2Array($project);
    //     $projectDataLangified = $this->langifyProject($projectData);

    //     foreach ($projectDataLangified as $projectData) {
    //       $lang = $projectData['lang'];
    //       //is project already in db
    //       $casawp_id = $projectData['ref'] . $projectData['lang'];

    //       $the_query = new \WP_Query( 'post_type=complex_unit&suppress_filters=true&meta_key=casawp_id&meta_value=' . $casawp_id );
    //       $wp_post = false;
    //       while ( $the_query->have_posts() ) :
    //         $the_query->the_post();
    //         global $post;
    //         $wp_post = $post;
    //       endwhile;
    //       wp_reset_postdata();

    //       //if not create a basic project
    //       if (!$wp_post) {
    //         $this->transcript[$casawp_id]['action'] = 'new';
    //         $the_post['post_title'] = $projectData['detail']['name'];
    //         $the_post['post_content'] = 'unsaved project';
    //         $the_post['post_status'] = 'publish';
    //         $the_post['post_type'] = 'casawp_project';
    //         $the_post['post_name'] = sanitize_title_with_dashes($casawp_id . '-' . $projectData['detail']['name'],'','save');
    //         $_POST['icl_post_language'] = $lang;
    //         $insert_id = wp_insert_post($the_post);

    //         update_post_meta($insert_id, 'casawp_id', $casawp_id);
    //         $wp_post = get_post($insert_id, OBJECT, 'raw');

    //       }
    //       $found_posts[] = $wp_post->ID;


    //       $found_posts = $this->updateProject($sorti, $casawp_id, $projectData, $wp_post, false, $found_posts);
    //       $this->updateInsertWPMLconnection($wp_post, $lang, 'project_'.$projectData['ref']);


    //     }
    //   }


      // //3. remove all the unused projects
      // $projects_to_remove = get_posts(  array(
      //   'suppress_filters' => true,
      //   'language' => 'ALL',
      //   'numberposts' =>  100,
      //   'exclude'     =>  $found_posts,
      //   'post_type'   =>  'casawp_project',
      //   'post_status' =>  'publish'
      //   )
      // );
      // foreach ($projects_to_remove as $prop_to_rm) {
      //   //remove the attachments
      //   $attachments = get_posts( array(
      //     'suppress_filters'=>true,
      //     'language'=>'ALL',
      //     'post_type'      => 'attachment',
      //     'posts_per_page' => -1,
      //     'post_parent'    => $prop_to_rm->ID,
      //     'exclude'        => get_post_thumbnail_id()
      //   ) );
      //   if ( $attachments ) {
      //     foreach ( $attachments as $attachment ) {
      //       $attachment_id = $attachment->ID;
      //     }
      //   }
      //   wp_trash_post($prop_to_rm->ID);
      //   $this->transcript['projects_removed'] = count($projects_to_remove);
      // }
    //}

    flush_rewrite_rules();

    //WPEngine clear cache hook
    global $wpe_common;
    if (isset($wpe_common)) {
      $this->transcript['wpengine'] = 'cache-cleared';
      foreach (array('clean_post_cache','trashed_posts','deleted_posts') as $hook){
        add_action( $hook, array( $wpe_common, 'purge_varnish_cache'));
      }
    }


    //$this->addToLog($this->transcript);
  }

  public function simpleXMLget($node, $fallback = false){
    if ($node) {
      $result = $node->__toString();
      if ($result) {
        return $result;
      }
    }
    return $fallback;
  }

  public function setUnitAttachments($offer_medias, $wp_post, $casawp_id, $property){
    ### future task: for better performace compare new and old data ###

    $this->addToLog($offer_medias);
    //$this->addToLog($wp_post);
    $this->addToLog($casawp_id);
    //$this->addToLog($property);


    //get xml media files
    $the_casawp_attachments = array();
    if ($offer_medias) {
      $o = 0;
      $media = $offer_medias;
      $the_casawp_attachments[] = array(
        'type'    => 'image',
        'alt'     => $offer_medias,
        'title'   => $offer_medias,
        'file'    => '',
        'url'     => $media,
        'caption' => $offer_medias,
        'order'   => $o
      );

      // foreach ($offer_medias as $offer_media) {
      //   $o++;
      //   $media = $offer_media;
      //   //if (in_array($offer_media['type'], array('image', 'document', 'plan', 'offer-logo', 'sales-brochure'))) {
      //     $the_casawp_attachments[] = array(
      //       'type'    => 'image',
      //       'alt'     => $offer_media['alt'],
      //       'title'   => ( $offer_media['title'] ? $offer_media['title'] : basename($media['original_file'])),
      //       'file'    => '',
      //       'url'     => $media['original_file'],
      //       'caption' => $offer_media['caption'],
      //       'order'   => $o
      //     );
      //   //}
      // }
    }

    // if (get_option('casawp_limit_reference_images') && $property['availability'] == 'reference') {
    //   $title_image = false;
    //   foreach ($the_casawp_attachments as $key => $attachment) {
    //     if ($attachment['type'] == 'image') {
    //       $title_image = $attachment;
    //       break;
    //     }
    //   }
    //   if ($title_image) {
    //     $the_casawp_attachments = array(0 => $title_image);
    //   }
    // }

    //get post attachments already attached
    $wp_casawp_attachments = array();
    $args = array(
      'post_type'   => 'attachment',
      'numberposts' => -1,
      'post_status' => null,
      'post_parent' => $wp_post->ID,
      'tax_query'   => array(
        'relation'  => 'AND',
        array(
          'taxonomy' => 'cxm_attachment_type',
          'field'    => 'slug',
          'terms'    => array( 'image', 'plan', 'document', 'offer-logo', 'sales-brochure' )
        )
      )
    );



    $attachments = get_posts($args);

    if ($attachments) {
       // $this->addToLog('SOFAR');
       // $this->addToLog($attachments);
      foreach ($attachments as $attachment) {
        $wp_casawp_attachments[] = $attachment;
      }
    }

    //upload necesary images to wordpress
    if (isset($the_casawp_attachments)) { // go through each attachment specified in xml
       
      $wp_casawp_attachments_to_remove = $wp_casawp_attachments;
      $dup_checker_arr = [];
      foreach ($the_casawp_attachments as $the_mediaitem) { // go through each available attachment already in db
        //$this->addToLog('SOFAR');
        //look up wp and see if file is already attached
        $existing = false;
        $existing_attachment = array();
        foreach ($wp_casawp_attachments as $key => $wp_mediaitem) {
          $attachment_customfields = get_post_custom($wp_mediaitem->ID);
          $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');

          // this checks for duplicates and ignores them if they exist. This can fix duplicates existing in the DB if they where, for instance, created durring run-in imports.
          if (in_array($original_filename, $dup_checker_arr)) {
            $this->addToLog('found duplicate for id: ' . $wp_mediaitem->ID . ' orig: ' . $original_filename);
            // this file appears to be a duplicate, skip it (that way it will be deleted later) aka. it will remain in $wp_casawp_attachments_to_remove.
            // because it encountered this file before it must be made existing in the past loop right?
            // DISABLE FOR NOW
            // $existing = true;
            // continue;
          }
          $dup_checker_arr[] = $original_filename;

          $alt = '';
          if (
            $original_filename == ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url'])
            ||
            str_replace('%3D', '=', str_replace('%3F', '?', $original_filename)) == ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url'])
          ) {
            $existing = true;
            $this->addToLog('updating attachment ' . $wp_mediaitem->ID);

            //it's here to stay
            unset($wp_casawp_attachments_to_remove[$key]);

            $types = wp_get_post_terms( $wp_mediaitem->ID, 'cxm_attachment_type');
            if (array_key_exists(0, $types)) {
              $typeslug = $types[0]->slug;
              $alt = get_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', true);
              //build a proper array out of it
              $existing_attachment = array(
                'type'    => $typeslug,
                'alt'     => $alt,
                'title'   => $wp_mediaitem->post_title,
                'file'    => $the_mediaitem['file'],
                //'file'    => maibe? -> (is_file($the_mediaitem['file']) ? $the_mediaitem['file'] : '')
                'url'     => $the_mediaitem['url'],
                'caption' => $wp_mediaitem->post_excerpt,
                'order'   => $wp_mediaitem->menu_order
              );
            }

            //have its values changed?
            if($existing_attachment != $the_mediaitem ){
              $changed = true;
              $this->addToLog('changed');
              $this->transcript[$casawp_id]['attachments']["updated"] = 1;
              //update attachment data
              if ($existing_attachment['caption'] != $the_mediaitem['caption']
                || $existing_attachment['title'] != $the_mediaitem['title']
                || $existing_attachment['order'] != $the_mediaitem['order']
                ) {
                $att['post_excerpt'] = $the_mediaitem['caption'];
                $att['post_title']   = ( $the_mediaitem['title'] ? $the_mediaitem['title'] : basename($filename));
                $att['ID']           = $wp_mediaitem->ID;
                $att['menu_order']   = $the_mediaitem['order'];
                $insert_id           = wp_update_post( $att);
              }
              //update attachment category
              if ($existing_attachment['type'] != $the_mediaitem['type']) {
                $term = get_term_by('slug', $the_mediaitem['type'], 'cxm_attachment_type');
                $term_id = $term->term_id;
                wp_set_post_terms( $wp_mediaitem->ID,  array($term_id), 'cxm_attachment_type' );
              }
              //update attachment alt
              if ($alt != $the_mediaitem['alt']) {
                update_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', $the_mediaitem['alt']);
              }
            }
          }


        }

        if (!$existing) {
          $this->addToLog('creating new attachment ' . $wp_mediaitem->ID);
          //insert the new image
          $new_id = $this->cxmUploadAttachment($the_mediaitem, $wp_post->ID, $casawp_id);
          if (is_int($new_id)) {
            $this->transcript[$casawp_id]['attachments']["created"] = $the_mediaitem['file'];
          } else {
            $this->transcript[$casawp_id]['attachments']["failed_to_create"] = $new_id;
          }
        }

        //tries to fix missing files
        if (! get_option('casawp_use_casagateway_cdn', false) && isset($the_mediaitem['url'])) {
          $this->cxmUploadAttachmentFromGateway($casawp_id, $the_mediaitem['url']);
        }


      } //foreach ($the_casawp_attachments as $the_mediaitem) {

      //images to remove
      if ($wp_casawp_attachments_to_remove){
        $this->addToLog('removing ' . count($wp_casawp_attachments_to_remove) . ' attachments');
      }
      foreach ($wp_casawp_attachments_to_remove as $attachment) {
        $this->addToLog('removing ' . $attachment->ID);
        $this->transcript[$casawp_id]['attachments']["removed"] = $attachment;

        // $attachment_customfields = get_post_custom($attachment->ID);
        // $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');
        wp_delete_attachment( $attachment->ID );
      }

      //featured image (refetch to avoid setting just removed items or not having new items)
      $args = array(
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $wp_post->ID,
        'tax_query'   => array(
          'relation'  => 'AND',
          array(
            'taxonomy' => 'cxm_attachment_type',
            'field'    => 'slug',
            'terms'    => array( 'image', 'plan', 'document', 'offer-logo', 'sales-brochure' )
          )
        )
      );
      $attachments = get_posts($args);
      if ($attachments) {
        unset($wp_casawp_attachments);
        foreach ($attachments as $attachment) {
          $wp_casawp_attachments[] = $attachment;
        }
      }

      $attachment_image_order = array();
      foreach ($the_casawp_attachments as $the_mediaitem) {
        if ($the_mediaitem['type'] == 'image') {
          $attachment_image_order[$the_mediaitem['order']] = $the_mediaitem;
        }
      }
      if (isset($attachment_image_order) && !empty($attachment_image_order)) {
        ksort($attachment_image_order);
        $attachment_image_order = reset($attachment_image_order);
        if (!empty($attachment_image_order)) {
          foreach ($wp_casawp_attachments as $wp_mediaitem) {
            $attachment_customfields = get_post_custom($wp_mediaitem->ID);
            $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');
            if (
              $original_filename == ($attachment_image_order['file'] ? $attachment_image_order['file'] : $attachment_image_order['url'])
              ||
              str_replace('%3D', '=', str_replace('%3F', '?', $original_filename)) == ($attachment_image_order['file'] ? $attachment_image_order['file'] : $attachment_image_order['url'])
            ) {
              $cur_thumbnail_id = get_post_thumbnail_id( $wp_post->ID );
              if ($cur_thumbnail_id != $wp_mediaitem->ID) {
                set_post_thumbnail( $wp_post->ID, $wp_mediaitem->ID );
                $this->transcript[$casawp_id]['attachments']["featured_image_set"] = 1;
                break;
              }
            }
          }
        }
      }




    } //(isset($the_casawp_attachments)


  }


  public function updateUnit($casawp_id, $offer_pos, $property, $offer, $wp_post){

    $new_meta_data = array();

    //load meta data
    $old_meta_data = array();
    $meta_values = get_post_meta($wp_post->ID, null, true);
    foreach ($meta_values as $key => $meta_value) {
      $old_meta_data[$key] = $meta_value[0];
    }
    ksort($old_meta_data);

    //generate import hash
    $cleanPropertyData = $property;
    //We dont trust this date – it tends to interfere with serialization because large exporters sometimes refresh this date without reason
    unset($cleanPropertyData['last_update']);
    unset($cleanPropertyData['last_import_hash']);
    if (isset($cleanPropertyData['modified'])) {
        unset($cleanPropertyData['modified']);
    }
    $curImportHash = md5(serialize($cleanPropertyData));

    if (!isset($old_meta_data['last_import_hash'])) {
      $old_meta_data['last_import_hash'] = 'no_hash';
    }

    //skip if is the same as before (accept if was trashed (reactivation))
    if ($wp_post->post_status == 'publish' && isset($old_meta_data['last_import_hash']) && !isset($_GET['force_all_properties'])) { 
      if ($curImportHash == $old_meta_data['last_import_hash']) {
        $this->addToLog('skipped property: '. $casawp_id);
        return 'skipped';
      }
    }

    $this->addToLog('beginn property update: [' . $casawp_id . ']' . time());
    $this->addToLog(array($old_meta_data['last_import_hash'], $curImportHash));

    //set new hash;
    $new_meta_data['last_import_hash'] = $curImportHash;


    //$publisher_options = $offer->publish;
    $publisher_options = array();
    if (isset($offer['publish'])) {
      foreach ($offer['publish'] as $slug => $content) {
        if (isset($content['options'])) {
          foreach ($content['options'] as $key => $value) {
            $publisher_options[$key] = $value;
          }
        }
      }
    }

    $name = $property['title'];
    $buildingcontent = $property['building_adress'] . ' ' . $property['building_house_number'] . ' ' . $property['building_plz'] . ' ' . $property['building_city']; 
    if (is_array($name)) {
      $name = $name[0];
    }

    $excerpt = (isset($publisher_options['override_excerpt']) && $publisher_options['override_excerpt'] ? $publisher_options['override_excerpt'] : $offer['excerpt']);
    if (is_array($excerpt)) {
      $excerpt = $excerpt[0];
    }

    /* main post data */
    $new_main_data = array(
      'ID'            => $wp_post->ID,
      'post_title'    => ($name ? $name : 'Objekt'),
      'post_content'  => '',
      'post_status'   => 'publish',
      'post_type'     => 'complex_unit',
      'post_excerpt'  => $excerpt,
      'post_date' => $wp_post->post_date,
      //'post_date'     => ($property['creation'] ? $property['creation']->format('Y-m-d H:i:s') : $property['last_update']->format('Y-m-d H:i:s')),
      /*'post_modified' => $property['last_update']->format('Y-m-d H:i:s'),*/
    );

    $old_main_data = array(
      'ID'            => $wp_post->ID,
      'post_title'    => $wp_post->post_title   ,
      'post_content'  => $wp_post->post_content ,
      'post_status'   => $wp_post->post_status  ,
      'post_type'     => $wp_post->post_type    ,
      'post_excerpt'  => $wp_post->post_excerpt ,
      'post_date' => $wp_post->post_date
      //'post_date'     => $wp_post->post_date    ,
      /*'post_modified' => $wp_post->post_modified,*/
    );
    if ($new_main_data != $old_main_data) {
      foreach ($old_main_data as $key => $value) {
        if ($new_main_data[$key] != $old_main_data[$key]) {
          $this->transcript[$casawp_id]['main_data'][$key]['from'] = $old_main_data[$key];
          $this->transcript[$casawp_id]['main_data'][$key]['to'] = $new_main_data[$key];
          $this->addToLog('updating main data (' . $key . '): ' . $old_main_data[$key] . ' -> ' . $new_main_data[$key]);
        }
      }


      //manage post_name and post_date (if new)
      if (!$wp_post->post_name) {
        $new_main_data['post_name'] = sanitize_title_with_dashes($casawp_id . '-' . $offer['name'],'','save');
        //$new_main_date['post_date'] = ($property['creation'] ? $property['creation']->format('Y-m-d H:i:s') : $property['last_update']->format('Y-m-d H:i:s'));
      } else {
        $new_main_data['post_name'] = $wp_post->post_name;
        //$new_main_date['post_date'] = ($property['creation'] ? $property['creation']->format('Y-m-d H:i:s') : $property['last_update']->format('Y-m-d H:i:s'));
      }

      //persist change
      $newPostID = wp_insert_post($new_main_data);

    }

    //$this->addToLog('propertystatus ' . $property['state_simplyfied']);

    if ($property['state_simplyfied'] == 'reserviert' || $property['state_simplyfied'] == 'Reserviert') {
      $property['state_simplyfied'] = 'reserved';
    }  elseif($property['state_simplyfied'] == 'vermietet' || $property['state_simplyfied'] == 'Vermietet' ){
      $property['state_simplyfied'] = 'rented';
    }  else{
      $property['state_simplyfied'] = 'available';
    }

    if(isset($property['incidental_costs'])){
      $property['additional_costs_1'] = $property['incidental_costs'];
    } elseif (isset($property['incidental_costs'])) {
      $property['additional_costs_1'] = $property['incidental_costs_squaremeter'];
    }

    if (!isset($property['rentalgross_net']) && isset($property['rentalprice_squaremeter_net'])) {
      $property['rentalgross_net'] = $property['rentalprice_squaremeter_net'];
    }



    

    /* echo '<pre>';
    print_r($property);
    echo '</pre>'; */
    //$this->addToLog('propertystatus ' . $property['state_simplyfied']);

    $new_meta_data['_complexmanager_unit_name']                 = $property['title'];
    //$new_meta_data['_complexmanager_unit_purchase_price']       = $property['']; kein passendes emonitor feld
    $new_meta_data['_complexmanager_unit_rent_net']             = (is_numeric($property['rentalgross_net']) ? $property['rentalgross_net'] : '');
    $new_meta_data['_complexmanager_unit_rent_gross']           = (is_numeric($property['rentalgross']) ? $property['rentalgross'] : '');
    $new_meta_data['_complexmanager_unit_number_of_rooms']      = (is_numeric($property['rooms']) ? $property['rooms'] : '');
    //$new_meta_data['_complexmanager_unit_min_income']           = $property['']; kein passendes emonitor feld
    //$new_meta_data['_complexmanager_unit_max_income']           = $property['']; kein passendes emonitor feld
    $new_meta_data['_complexmanager_unit_min_sorted_xmls']          = (is_numeric($property['min_adult']) ? $property['min_adult'] : '');
    $new_meta_data['_complexmanager_unit_max_sorted_xmls']          = (is_numeric($property['max_adult']) ? $property['max_adult'] : '');
    $new_meta_data['_complexmanager_unit_story']                = $property['floor'];
    $new_meta_data['_complexmanager_unit_status']               = $property['state_simplyfied'];
    //$new_meta_data['_complexmanager_unit_currency']             = $property['floor']; kein passendes emonitor feld
    $new_meta_data['_complexmanager_unit_living_space']         = $property['area'];
    $new_meta_data['_complexmanager_unit_usable_space']         = $property['area_property'];
    $new_meta_data['_complexmanager_unit_terrace_space']        = $property['terrace_area'];
    $new_meta_data['_complexmanager_unit_balcony_space']        = $property['balcony'];
    //$new_meta_data['_complexmanager_unit_idx_ref_house']        = $property['floor']; kein passendes emonitor feld
    //$new_meta_data['_complexmanager_unit_idx_ref_object']       = $property['floor']; kein passendes emonitor feld
    $new_meta_data['_complexmanager_unit_extra_costs']          = $property['additional_costs_1'];

    $squaremeterprices = PluginOptions::get_option( 'squaremeterprices', false ); //works false or 1

    if ($property['property_type'] == "Gewerbe" && $squaremeterprices == 1){
      // $new_meta_data['_complexmanager_unit_custom_1']             = $property['floor']; kein passendes emonitor feld
      $new_meta_data['_complexmanager_unit_custom_2']             = "CHF " . $property['rentalprice_squaremeter'] . "/m²/Jahr";
      $new_meta_data['_complexmanager_unit_custom_3']             = "CHF " . $property['rentalprice_squaremeter_net'] . "/m²/Jahr";
    }

    $propertytype = PluginOptions::get_option( 'propertytype', false ); //works false or 1
    if ($propertytype == 1){
      $new_meta_data['_complexmanager_unit_custom_1']             = $property['rental_conditions'];
    }

    $virtualtour = PluginOptions::get_option( 'virtualtour', false ); //works false or 1
    if ($virtualtour == 1){
      $new_meta_data['_complexmanager_unit_custom_2']             = $property['virtual_tour_link'];
    }

    // $new_meta_data['_complexmanager_unit_custom_1']             = $property['floor']; kein passendes emonitor feld
    // $new_meta_data['_complexmanager_unit_custom_2']             = $property['floor']; kein passendes emonitor feld
    // $new_meta_data['_complexmanager_unit_custom_3']             = $property['floor']; kein passendes emonitor feld

    $new_meta_data['_complexmanager_unit_download_file']        = $property['factsheet'];
    $new_meta_data['_complexmanager_unit_download_label']       = (isset($property['factsheet']) ? 'Grundriss' : '');
    
    //$new_meta_data['_complexmanager_unit_link']                 = $property['floor']; kein passendes emonitor feld
    $new_meta_data['_complexmanager_unit_link_target']          = (isset($property['url']) ? '_blank' : '');
    $new_meta_data['_complexmanager_unit_link_url']             = $property['url'];
    $new_meta_data['_complexmanager_unit_link_label']           = (isset($property['url']) ? 'Jetzt online Bewerben' : '');

    $new_meta_data['_complexmanager_unit_custom_overlay']       = $property['isometry'];


    //$this->addToLog($new_meta_data['']);
    
    ksort($new_meta_data);

    if ($new_meta_data != $old_meta_data) {
      $this->addToLog('updating metadata');
      foreach ($new_meta_data as $key => $value) {
        $newval = $value;

        if ($newval === true) {
          $newval = "1";
        }
        if (is_numeric($value)) {
          $newval = (string) $value;
        }
        if ($key == "floor" && $newval == 0) {
          $newval = "EG"; // TODO Translate
        }
        

        $oldval = (isset($old_meta_data[$key]) ? maybe_unserialize($old_meta_data[$key]) : '');
        if (function_exists("casawp_unicode_dirty_replace")) {
          $oldval = casawp_unicode_dirty_replace($oldval); 
        }
        
        if (($oldval || $newval || $newval === 0) && $oldval !== $newval) {
          update_post_meta($wp_post->ID, $key, $newval);
          $this->transcript[$casawp_id]['meta_data'][$key]['from'] = $oldval;
          $this->transcript[$casawp_id]['meta_data'][$key]['to'] = $newval;
        }
      }

      //remove supurflous meta_data
      $this->addToLog('removing supurflous metadata');
      foreach ($old_meta_data as $key => $value) {
        if (
          !isset($new_meta_data[$key])
          && !in_array($key, array('casawp_id', 'projectunit_id', 'projectunit_sort'))
          && strpos($key, '_') !== 0
        ) {
          //remove
          delete_post_meta($wp_post->ID, $key, $value);
          $this->transcript[$casawp_id]['meta_data']['removed'][$key] = $value;
        }
      }
    }


    $this->addToLog('updating buildings');
    $this->setPropertyBuilding($wp_post, $property['building_title'], $casawp_id, $buildingcontent, $property['property_type']);

    $this->addToLog('updating attachments');

    $this->setUnitAttachments($property['layout_plan'], $wp_post, $casawp_id, $property);


    $this->addToLog('finish property update: [' . $casawp_id . ']' . time());

    $removed = 0;
    $dir = wp_upload_dir(null, true, false);
    if (is_dir($dir['basedir'] . '/cmx_cache')) {
        $files = glob($dir['basedir'] . '/cmx_cache/*');
        foreach($files as $file){ // iterate files
          if(is_file($file))
            unlink($file); // delete file
            $removed++;
        }
    }

  }
}

