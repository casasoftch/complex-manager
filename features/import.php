<?php

namespace casasoft\complexmanager;

require_once('silence.php');

class eMonitorImport extends Feature
{

  public $importFile = false;
  public $main_lang = false;
  public $WPML = null;
  public $transcript = array();
  public $curtrid = false;
  public $trid_store = array();

  public function __construct($autoimport = false, $emonitorupdate = false)
  {
    if ($autoimport) {
      $this->addToLog('autoimport ' . time());
      $this->updateImportDataThroughEmonitor();
    } elseif ($emonitorupdate) {
      $this->addToLog('updateImportDataThroughEmonitor ' . time());
      add_action('init', array($this, 'updateImportDataThroughEmonitor'), 20);
    }
  }

  public function r($data)
  {
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
  }

  public function getEmonitorImportFile()
  {
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
      $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.json';
      if (file_exists($file)) {
        $good_to_go = true;
        $this->addToLog('file found lets go: ' . time());
      } else {
        $this->addToLog('file was missing ' . time());
        if (isset($_GET['force_last_import'])) {
          $this->addToLog('importing last file based on force_last_import: ' . time());
          $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.json';
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

  public function renameImportFileTo($to)
  {
    if ($this->importFile != $to) {
      rename($this->importFile, $to);
      $this->importFile = $to;
    }
  }

  public function backupImportFile()
  {
    copy($this->getEmonitorImportFile(), CXM_CUR_UPLOAD_BASEDIR  . '/cxm/done/' . get_date_from_gmt('', 'Y_m_d_H_i_s') . '_completed.json');
    return true;
  }

  public function cxmUploadAttachmentFromGateway($casawp_id, $fileurl)
  {

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
      $pathWithoutExtension = str_replace('.' . $file_parts['extension'], '', $path);

      $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
      $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

      $converted = $scheme . $user . $pass . $host . $port . $pathWithoutExtension . str_replace(['?', '&', '#', '='], '-', $query . $fragment) . '.' . $extension;

      $filename = '/cxm/import/attachment/externalsync/' . $casawp_id . '/' . basename($converted);
    } else {
      $filename = '/cxm/import/attachment/externalsync/' . $casawp_id . '/' . basename($fileurl);
    }

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
      if (!is_file(CXM_CUR_UPLOAD_BASEDIR . $filename)) {
        if (!isset($this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"])) {
          $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"] = array();
        }
        $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"][] = $filename;

        $currentDomain = $_SERVER['HTTP_HOST'];
        $isLocalDomain = strpos($currentDomain, '.local') !== false;

        $contextOptions = array(
          "ssl" => array(
            "verify_peer" => !$isLocalDomain,
            "verify_peer_name" => !$isLocalDomain,
          ),
        );

        $ch = curl_init($fileurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($isLocalDomain) {
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        } else {
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        $data = curl_exec($ch);

        if ($data === false) {
          $this->transcript['attachments'][$casawp_id]["uploaded_from_gateway"][] = 'cURL error: ' . curl_error($ch);
        } else {
          file_put_contents(CXM_CUR_UPLOAD_BASEDIR . $filename, $data);
        }

        curl_close($ch);
      }
    }
    return $filename;
  }

  public function cxmUploadAttachment($the_mediaitem, $post_id, $casawp_id)
  {

    if ($the_mediaitem) {
      $filename = $this->cxmUploadAttachmentFromGateway($casawp_id, $the_mediaitem['url']);
    } else {
      $filename = false;
    }

    if ($filename && (is_file(CXM_CUR_UPLOAD_BASEDIR . $filename))) {

      $wp_filetype = wp_check_filetype(basename($filename), null);

      $this->addToLog('new file attachment upload it and attach it fully');

      $this->addToLog($wp_filetype);


      $guid = CXM_CUR_UPLOAD_BASEDIR . $filename;
      if ($the_mediaitem['type'] === 'image') {
        $guid = $filename;
      }
      $attachment = array(
        'guid'           => $guid,
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => ($the_mediaitem['title'] ? $the_mediaitem['title'] : basename($filename)),
        'post_name'      => sanitize_title_with_dashes($guid, '', 'save'),
        'post_content'   => '',
        'post_excerpt'   => $the_mediaitem['caption'],
        'post_status'    => 'inherit',
        'menu_order'     => $the_mediaitem['order']
      );

      $attach_id = wp_insert_attachment($attachment, CXM_CUR_UPLOAD_BASEDIR . $filename, $post_id);

      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata($attach_id, CXM_CUR_UPLOAD_BASEDIR . $filename);
      wp_update_attachment_metadata($attach_id, $attach_data);

      $term = get_term_by('slug', $the_mediaitem['type'], 'cxm_attachment_type');
      $term_id = $term->term_id;
      wp_set_post_terms($attach_id,  array($term_id), 'cxm_attachment_type');

      update_post_meta($attach_id, '_wp_attachment_image_alt', $the_mediaitem['alt']);

      update_post_meta($attach_id, '_origin', ($the_mediaitem['file'] ? $the_mediaitem['file'] : $the_mediaitem['url']));

      return $attach_id;
    } else {
      return $filename . " could not be found!";
    }
  }

  public function setPropertyBuilding($wp_post, $building, $casawp_id, $buildingcontent, $propertytype)
  {
    $new_term = null;
    $old_term = null;

    $separateBuildingPropertyType = PluginOptions::get_option('separate_building_property_type', false); //works false or 1

    if ($separateBuildingPropertyType == 1) {
      $building = $building . ' ' . $propertytype;
    }


    if ($building) {
      $new_term = get_term_by('slug', $building, 'building', OBJECT, 'raw');
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
        $this->addToLog('wp_insert_term');
        $new_term = get_term($id, 'building', OBJECT, 'raw');
      }
    }

    $wp_post_terms = wp_get_object_terms($wp_post->ID, 'building');

    if ($old_term != $new_term) {
      $this->transcript[$casawp_id]['building']['from'] = ($old_term ? $old_term->name : 'none');
      $this->transcript[$casawp_id]['building']['to'] =   ($new_term ? $new_term->name : 'none');
      wp_set_object_terms($wp_post->ID, ($new_term ? $new_term->term_id : NULL), 'building');
    }
  }

  public function addToLog($transcript)
  {
    $dir = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/logs';
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }
    file_put_contents($dir . "/" . get_date_from_gmt('', 'Ym') . '.log', "\n" . json_encode(array(get_date_from_gmt('', 'Y-m-d H:i') => $transcript)), FILE_APPEND);
  }

  public function updateImportDataThroughEmonitor()
  {

    $this->addToLog('emonitor data retriaval start: ' . time());

    if (PluginOptions::get_option('cxm_emonitor_api', false)) {
      $apikey = PluginOptions::get_option('cxm_emonitor_api', false);
    }

    if (str_contains($apikey, '/v2/')) {
      $version = 'v2';
    } else {
      $version = 'v1';
    }

    if ($apikey && $version) {

      $url = $apikey;

      $response = false;

      if (!function_exists('curl_version')) {
        $this->addToLog('gateway ERR (CURL MISSING!!!): ' . time());
        echo '<div id="message" class="updated"> CURL MISSING!!!</div>';
      }

      try {
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
          $error_message = $response->get_error_message();
          echo "Something went wrong: $error_message";
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
        }


       /*  echo '<pre>';
        print_r($data);
        echo '</pre>';
        die(); */

      } catch (Exception $e) {

        $response =  $e->getMessage();
        $this->addToLog('gateway ERR (' . $response . '): ' . time());

      }

      if ($response) {

        if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
          mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import');
        }

        $file = CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.json';

        file_put_contents($file, $body);

        $this->addToLog('gateway start update: ' . time());

        if ($this->getEmonitorImportFile()) {

          $this->addToLog('import start');
          $this->updateUnits($data, $version);
          $this->addToLog('import end');

        }

      } else {

        $this->addToLog('ERR no response from gateway: ' . time());

        $emailreal = "alert@casasoft.com";
        $subject = get_bloginfo('name');
        $message = "Die Emonitor Schnittstelle scheint nicht mehr zu funktionieren. Kann das sein? Bitte prüfen.";
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $sent = wp_mail($emailreal, $subject, $message, $headers);

        if ($sent) {
          $this->addToLog('Email Notification Sent! : ' . $emailreal);
        } else {
          $this->addToLog('Email Notification NOT Sent! : ' . $emailreal);
        }
      }
    } else {
      $this->addToLog('API Keys missing emonitor: ' . time());
      echo '<div id="message" class="updated"> API Keys missing emonitor</div>';
    }
  }

  public function addToTranscript($msg)
  {
    $this->transcript[] = $msg;
  }

  public function unit2Array($property)
  {

      $propertydata = array(

        'building_adress'       =>  $property['building']['adress'] ?? '',
        'building_publicated_adress'       =>  $property['building']['publicated_adress'] ?? '',
        'building_house_number_supplement'       =>  $property['building']['house_number_supplement'] ?? '',
        'building_house_number'       =>  $property['building']['house_number'] ?? '',
        'building_year_of_construction'       =>  $property['building']['year_of_construction'] ?? '',
        'building_plz'       =>  $property['building']['plz'] ?? '',
        'building_city'       =>  $property['building']['city'] ?? '',
        'building_state'       =>  $property['building']['state'] ?? '',
        'building_lift'       =>  $property['building']['lift'] ?? '',
        'building_title'       =>  $property['building']['title'] ?? '',
        'building_mark'       =>  $property['building']['mark'] ?? '',
        'building_mark_property'       =>  $property['building']['mark_property'] ?? '',
        'building_colony'       =>  $property['building']['colony'] ?? '',
        'building_metropolitan'       =>  $property['building']['metropolitan'] ?? '',
        'building_district'       =>  $property['building']['district'] ?? '',


        'images'       =>  $property['images'] ?? '',
        'factsheet'       =>  $property['factsheet'] ?? '',
        'pdf_file_link'       =>  $property['pdf_file_link'] ?? '',
        'pdf_file'       =>  $property['pdf_file'] ?? '',
        'image' => $property['image'] ?? '',
        'isometry'       =>  $property['isometry'] ?? '',
        'situation_plan'       =>  $property['situation_plan'] ?? '',
        'factsheets'       =>  $property['factsheets'] ?? '',
        'application_pdf'       =>  $property['application_pdf'] ?? '',
        'ref'       =>  $property['ref'] ?? '',
        'layout_plan'       =>  $property['layout_plan'] ?? '',


        'incidental_costs'       =>  $property['incidental_costs'] ?? '',
        'incidental_costs_squaremeter'       =>  $property['incidental_costs_squaremeter'] ?? '',
        'rentalprice_squaremeter'       =>  $property['rentalprice_squaremeter'] ?? '',
        'rentalprice_squaremeter_net'       =>  $property['rentalprice_squaremeter_net'] ?? '',
        'state_simplyfied'       =>  $property['state_simplyfied'] ?? '',
        'url'       =>  $property['url'] ?? '',
        'end_date'       =>  $property['end_date'] ?? '',
        'creation'       =>  $property['start_date'] ?? '',
        'move_in_date'       =>  $property['move_in_date'] ?? '',
        'property_type'       =>  $property['property_type'] ?? '',
        'rentalgross'       =>  $property['rentalgross'] ?? '',
        'rentalgross_net'       =>  $property['rentalgross_net'] ?? '',
        'price_unit'       =>  $property['price_unit'] ?? '',
        'state'       =>  $property['state'] ?? '',
        'mark'       =>  $property['mark'] ?? '',
        'title'       =>  $property['title'] ?? '',
        'area'       =>  $property['area'] ?? '',
        'rooms'       =>  $property['rooms'] ?? '',
        'area_brutto'       =>  $property['area_brutto'] ?? '',
        'area_property'       =>  $property['area_property'] ?? '',
        'reference_date'       =>  $property['reference_date'] ?? '',
        'object'       =>  $property['object'] ?? '',
        'orientation'       =>  $property['orientation'] ?? '',
        'add_costs_per_month_m2'       =>  $property['add_costs_per_month_m2'] ?? '',
        'virtual_tour_link'       =>  $property['virtual_tour_link'] ?? '',
        'website_link'       =>  $property['website_link'] ?? '',
        'balcony'       =>  $property['balcony'] ?? '',
        'balcony_yes_no'       =>  $property['balcony_yes_no'] ?? '',
        'loggia'       =>  $property['loggia'] ?? '',
        'loggia_area'       =>  $property['loggia_area'] ?? '',
        'loggia_number'       =>  $property['loggia_number'] ?? '',
        'garden_text'       =>  $property['garden_text'] ?? '',
        'garden_sitting_place'       =>  $property['garden_sitting_place'] ?? '',
        'garden_sitting_place_area'       =>  $property['garden_sitting_place_area'] ?? '',
        'shared_garden'       =>  $property['shared_garden'] ?? '',
        'winter_garden'       =>  $property['winter_garden'] ?? '',
        'winter_garden_number'       =>  $property['winter_garden_number'] ?? '',
        'terrace'       =>  $property['terrace'] ?? '',
        'terrace_area'       =>  $property['terrace_area'] ?? '',
        'terrace_garden_number'       =>  $property['terrace_garden_number'] ?? '',
        'floor'       =>  $property['floor'] ?? '',
        'location_on_floor'       =>  $property['location_on_floor'] ?? '',
        'bath'       =>  $property['bath'] ?? '',
        'bath_number'       =>  $property['bath_number'] ?? '',
        'additional_costs_1'       =>  $property['additional_costs_1'] ?? '',
        'additional_costs_2'       =>  $property['additional_costs_2'] ?? '',
        'min_adult'       =>  $property['min_adult'] ?? '',
        'max_adult'       =>  $property['max_adult'] ?? '',
        'rental_conditions'       =>  $property['rental_conditions'] ?? '',

      );

    return $propertydata;
  }

  public function updateUnits($response, $version)
  {


    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm');
    }
    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import');
    }
    if (!is_dir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment')) {
      mkdir(CXM_CUR_UPLOAD_BASEDIR . '/cxm/import/attachment');
    }


    set_time_limit(600);
    global $wpdb;

    $json_string = file_get_contents($this->getEmonitorImportFile());

    if ($json_string === false || empty($json_string)) {
      return;
    }

    $data = json_decode($json_string, true);

    if ($data === null) {
        return;
    }

    if (!is_array($data)) {
      #error_log("Error: Decoded data is not an array.");
      return;
    }

    $found_posts = array();
    $ranksort = array();
    $curRank = 0;

    $startfullselectiontime = microtime(true);
    $posts_pool = [];
    $the_query = new \WP_Query('post_status=publish,pending,draft,future,trash&post_type=complex_unit&suppress_filters=true&posts_per_page=100000');
    $wp_post = false;
    while ($the_query->have_posts()) :
      $the_query->the_post();
      global $post;
      $existing_casawp_import_id = get_post_meta($post->ID, 'casawp_id', true);
      if ($existing_casawp_import_id) {
        $posts_pool[$existing_casawp_import_id] = $post;
      }
    endwhile;
    wp_reset_postdata();

    $sortArray = array();
    foreach ($data as $data_unit) {
      foreach ($data_unit as $key => $value) {
        if (!isset($sortArray[$key])) {
          $sortArray[$key] = array();
        }
        $sortArray[$key][] = $value;
      }
    }

    $orderby = "title";

    if (isset($sortArray[$orderby]) && is_array($sortArray[$orderby]) && !empty($sortArray[$orderby])) {
      array_multisort($sortArray[$orderby], SORT_ASC, $data);
    } else {
      #error_log("Error: Cannot sort data because 'title' field is missing or invalid.");
      return;
    }

    array_multisort($sortArray[$orderby], SORT_ASC, $data);

    if ($version == 'v2') {
      foreach ($data as $propertyData) {
        $curRank++;

        $theoffers = array();
        $i = 0;
        foreach ($propertyData as $offer) {
          $i++;
          $theoffers[$i] = $offer;
        }

        $building_title = $propertyData['building']['building_title'] ?? '';

        $offer_pos = 0;
        $offer_pos++;
        $casawp_id = $propertyData['title'] . '-' . $building_title;

        $this->addToLog($casawp_id);

        $wp_post = false;
        if (array_key_exists($casawp_id, $posts_pool)) {
          $wp_post = $posts_pool[$casawp_id];
        }

        if (!$wp_post) {
          $this->transcript[$casawp_id]['action'] = 'new';
          $the_post['post_title'] = $propertyData['title'];
          $the_post['post_content'] = '';
          $the_post['post_status'] = 'publish';
          $the_post['post_type'] = 'complex_unit';
          $the_post['menu_order'] = $curRank;
          $the_post['post_name'] = sanitize_title_with_dashes($casawp_id, '', 'save');

          $the_post['post_date'] = ($propertyData['start_date'] ?: '');

          $insert_id = wp_insert_post($the_post);
          update_post_meta($insert_id, 'casawp_id', $casawp_id);
          $wp_post = get_post($insert_id, OBJECT, 'raw');
          $this->addToLog('new property: ' . $casawp_id);
        }

        $ranksort[$wp_post->ID] = $curRank;

        $found_posts[] = $wp_post->ID;

        $this->updateUnit($casawp_id, $offer_pos, $propertyData, $wp_post, $version);

      }
    } else {
      foreach ($data as $property) {
        $curRank++;
        $timeStart = microtime(true);
        $propertyData = $this->unit2Array($property);

        $theoffers = array();
        $i = 0;
        foreach ($propertyData as $offer) {
          $i++;
          $theoffers[$i] = $offer;
        }

        $building_title = $propertyData['building_title'] ?? '';

        $offer_pos = 0;
        $offer_pos++;
        $casawp_id = $propertyData['title'] . '-' . $building_title;

        $this->addToLog($casawp_id);

        $wp_post = false;
        if (array_key_exists($casawp_id, $posts_pool)) {
          $wp_post = $posts_pool[$casawp_id];
        }

        if (!$wp_post) {
          $this->transcript[$casawp_id]['action'] = 'new';
          $the_post['post_title'] = $propertyData['title'];
          $the_post['post_content'] = '';
          $the_post['post_status'] = 'publish';
          $the_post['post_type'] = 'complex_unit';
          $the_post['menu_order'] = $curRank;
          $the_post['post_name'] = sanitize_title_with_dashes($casawp_id, '', 'save');

          $the_post['post_date'] = ($propertyData['creation'] ?: '');

          $insert_id = wp_insert_post($the_post);
          update_post_meta($insert_id, 'casawp_id', $casawp_id);
          $wp_post = get_post($insert_id, OBJECT, 'raw');
          $this->addToLog('new property: ' . $casawp_id);
        }

        $ranksort[$wp_post->ID] = $curRank;

        $found_posts[] = $wp_post->ID;

        $this->updateUnit($casawp_id, $offer_pos, $propertyData, $wp_post, $version);
      }
    }

    

    if (!$found_posts) {
      $this->transcript['error'] = 'NO PROPERTIES FOUND IN JSON!!!';
      $this->transcript['error_infos'] = [
        'filesize' => filesize(CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.json') . ' !'
      ];

      copy(CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.json', CXM_CUR_UPLOAD_BASEDIR  . '/cxm/import/data-error.json');
    }

    if (PluginOptions::get_option('cxm_exclude_buildings', false)) {
      $buildings = PluginOptions::get_option('cxm_exclude_buildings', false);
      $excluded_building_ids = explode(',', $buildings);

      $excluded_posts = get_posts(array(
          'posts_per_page' => -1,
          'post_type'      => 'complex_unit',
          'fields'         => 'ids',
          'tax_query'      => array(
              array(
                  'taxonomy' => 'building',
                  'field'    => 'term_id',
                  'terms'    => $excluded_building_ids,
                  'operator' => 'IN',
              ),
          ),
      ));
      $found_posts = array_unique(array_merge($found_posts, $excluded_posts));
    }

    $properties_to_remove = get_posts(
      array(
        'suppress_filters' => true,
        'language' => 'ALL',
        'numberposts' =>  100,
        'exclude'     =>  $found_posts,
        'post_type'   =>  'complex_unit',
        'post_status' =>  'publish'
      )
    );

    foreach ($properties_to_remove as $prop_to_rm) {
      $attachments = get_posts(array(
        'suppress_filters' => true,
        'language' => 'ALL',
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_parent'    => $prop_to_rm->ID,
        'exclude'        => get_post_thumbnail_id()
      ));
      if ($attachments) {
        foreach ($attachments as $attachment) {
          $attachment_id = $attachment->ID;
        }
      }
      wp_trash_post($prop_to_rm->ID);
    }

    $properties_to_sort = get_posts(
      array(
        'suppress_filters' => true,
        'language' => 'ALL',
        'numberposts' =>  100,
        'include'     =>  $found_posts,
        'post_type'   =>  'complex_unit',
        'post_status' =>  'publish'
      )
    );
    $sortsUpdated = 0;

    foreach ($properties_to_sort as $prop_to_sort) {
      if (array_key_exists($prop_to_sort->ID, $ranksort)) {
        if ($prop_to_sort->menu_order != $ranksort[$prop_to_sort->ID]) {
          $sortsUpdated++;
          try {
            $newPostID = wp_update_post(array(
              'ID' => $prop_to_sort->ID,
              'menu_order' => $ranksort[$prop_to_sort->ID]
            ));
          } catch (\Throwable $th) {
            if (isset($this->transcript['wp_update_post_error'])) {
              $this->transcript['wp_update_post_error'][] = $th->getMessage();
            } else {
              $this->transcript['wp_update_post_error'] = [$th->getMessage()];
            }
          }
        }
      }
    }

    $this->transcript['sorts_updated'] = $sortsUpdated;
    $this->transcript['properties_found_in_xml'] = count($found_posts);
    $this->transcript['properties_removed'] = count($properties_to_remove);

    flush_rewrite_rules();

    global $wpe_common;
    if (isset($wpe_common)) {
      $this->transcript['wpengine'] = 'cache-cleared';
      foreach (array('clean_post_cache', 'trashed_posts', 'deleted_posts') as $hook) {
        add_action($hook, array($wpe_common, 'purge_varnish_cache'));
      }
    }
  }

  public function simpleXMLget($node, $fallback = false)
  {
    if ($node) {
      $result = $node->__toString();
      if ($result) {
        return $result;
      }
    }
    return $fallback;
  }

  public function setUnitAttachments($offer_medias, $wp_post, $casawp_id, $property)
  {


    $this->addToLog($offer_medias);
    $this->addToLog($casawp_id);


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
    }

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
          'terms'    => array('image', 'plan', 'document', 'offer-logo', 'sales-brochure')
        )
      )
    );



    $attachments = get_posts($args);

    if ($attachments) {
      foreach ($attachments as $attachment) {
        $wp_casawp_attachments[] = $attachment;
      }
    }

    if (isset($the_casawp_attachments)) {

      $wp_casawp_attachments_to_remove = $wp_casawp_attachments;
      $dup_checker_arr = [];
      foreach ($the_casawp_attachments as $the_mediaitem) {
        $existing = false;
        $existing_attachment = array();
        foreach ($wp_casawp_attachments as $key => $wp_mediaitem) {
          $attachment_customfields = get_post_custom($wp_mediaitem->ID);
          $original_filename = (array_key_exists('_origin', $attachment_customfields) ? $attachment_customfields['_origin'][0] : '');

          if (in_array($original_filename, $dup_checker_arr)) {
            $this->addToLog('found duplicate for id: ' . $wp_mediaitem->ID . ' orig: ' . $original_filename);
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

            unset($wp_casawp_attachments_to_remove[$key]);

            $types = wp_get_post_terms($wp_mediaitem->ID, 'cxm_attachment_type');
            if (array_key_exists(0, $types)) {
              $typeslug = $types[0]->slug;
              $alt = get_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', true);
              $existing_attachment = array(
                'type'    => $typeslug,
                'alt'     => $alt,
                'title'   => $wp_mediaitem->post_title,
                'file'    => $the_mediaitem['file'],
                'url'     => $the_mediaitem['url'],
                'caption' => $wp_mediaitem->post_excerpt,
                'order'   => $wp_mediaitem->menu_order
              );
            }

            if ($existing_attachment != $the_mediaitem) {
              $changed = true;
              $this->addToLog('changed');
              $this->transcript[$casawp_id]['attachments']["updated"] = 1;
              if (
                $existing_attachment['caption'] != $the_mediaitem['caption']
                || $existing_attachment['title'] != $the_mediaitem['title']
                || $existing_attachment['order'] != $the_mediaitem['order']
              ) {
                $att['post_excerpt'] = $the_mediaitem['caption'];
                $att['post_title']   = ($the_mediaitem['title'] ? $the_mediaitem['title'] : basename($filename));
                $att['ID']           = $wp_mediaitem->ID;
                $att['menu_order']   = $the_mediaitem['order'];
                $insert_id           = wp_update_post($att);
              }
              if ($existing_attachment['type'] != $the_mediaitem['type']) {
                $term = get_term_by('slug', $the_mediaitem['type'], 'cxm_attachment_type');
                $term_id = $term->term_id;
                wp_set_post_terms($wp_mediaitem->ID,  array($term_id), 'cxm_attachment_type');
              }
              if ($alt != $the_mediaitem['alt']) {
                update_post_meta($wp_mediaitem->ID, '_wp_attachment_image_alt', $the_mediaitem['alt']);
              }
            }
          }
        }

        if (!$existing) {
          $new_id = $this->cxmUploadAttachment($the_mediaitem, $wp_post->ID, $casawp_id);
          if (is_int($new_id)) {
            $this->transcript[$casawp_id]['attachments']["created"] = $the_mediaitem['file'];
          } else {
            $this->transcript[$casawp_id]['attachments']["failed_to_create"] = $new_id;
          }
        }

        if (!get_option('casawp_use_casagateway_cdn', false) && isset($the_mediaitem['url'])) {
          $this->cxmUploadAttachmentFromGateway($casawp_id, $the_mediaitem['url']);
        }
      }

      if ($wp_casawp_attachments_to_remove) {
        $this->addToLog('removing ' . count($wp_casawp_attachments_to_remove) . ' attachments');
      }
      foreach ($wp_casawp_attachments_to_remove as $attachment) {
        $this->addToLog('removing ' . $attachment->ID);
        $this->transcript[$casawp_id]['attachments']["removed"] = $attachment;

        wp_delete_attachment($attachment->ID);
      }

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
            'terms'    => array('image', 'plan', 'document', 'offer-logo', 'sales-brochure')
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
              $cur_thumbnail_id = get_post_thumbnail_id($wp_post->ID);
              if ($cur_thumbnail_id != $wp_mediaitem->ID) {
                set_post_thumbnail($wp_post->ID, $wp_mediaitem->ID);
                $this->transcript[$casawp_id]['attachments']["featured_image_set"] = 1;
                break;
              }
            }
          }
        }
      }
    }
  }


  public function updateUnit($casawp_id, $offer_pos, $property, $wp_post, $version)
  {

    $new_meta_data = array();

    $old_meta_data = array();
    $meta_values = get_post_meta($wp_post->ID, null, true);
    foreach ($meta_values as $key => $meta_value) {
      $old_meta_data[$key] = $meta_value[0];
    }
    ksort($old_meta_data);

    $cleanPropertyData = $property;
    unset($cleanPropertyData['last_update']);
    unset($cleanPropertyData['last_import_hash']);
    if (isset($cleanPropertyData['modified'])) {
      unset($cleanPropertyData['modified']);
    }
    $curImportHash = md5(serialize($cleanPropertyData));

    if (!isset($old_meta_data['last_import_hash'])) {
      $old_meta_data['last_import_hash'] = 'no_hash';
    }

    if ($wp_post->post_status == 'publish' && isset($old_meta_data['last_import_hash']) && !isset($_GET['force_all_properties'])) {
      if ($curImportHash == $old_meta_data['last_import_hash']) {
        $this->addToLog('skipped property: ' . $casawp_id);
        return 'skipped';
      }
    }

    $this->addToLog('beginn property update: [' . $casawp_id . ']' . time());
    $this->addToLog(array($old_meta_data['last_import_hash'], $curImportHash));

    $new_meta_data['last_import_hash'] = $curImportHash;

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

    if ($version == 'v2') {

      $building_address = $property['building']['address'] ?? '';
      $building_house_number = $property['building']['house_number'] ?? '';
      $building_plz = $property['building']['postal_code'] ?? '';
      $building_city = $property['building']['city'] ?? '';
      $buildingcontent = $building_address . ' ' . $building_house_number . ', ' . $building_plz . ' ' . $building_city;
      if (is_array($name)) {
        $name = $name[0];
      }

      if ($property['object_state'] == 'free') {
        $property['state_simplyfied'] = 'available';
      } elseif($property['object_state'] == 'reserved') {
        $property['state_simplyfied'] = 'reserved';
      } elseif($property['object_state'] == 'rented') {
        $property['state_simplyfied'] = 'rented';
      } else {
        $property['state_simplyfied'] = 'available';
      }

      $new_meta_data['_complexmanager_unit_extra_costs'] = $property['incidentals'] ?? '';

      if (isset($property['rentalgross_net']) && $property['rentalgross_net'] == '' && isset($property['rentalprice_squaremeter_net']) && $property['rentalprice_squaremeter_net']) {
        $new_meta_data['_complexmanager_unit_rent_net'] = $property['rentalprice_squaremeter_net'];
        $new_meta_data['_complexmanager_unit_rent_propertysegment'] = 'M2';
      } elseif(isset($property['rentalgross_net']) && $property['rentalgross_net'])  {
        $new_meta_data['_complexmanager_unit_rent_net'] = $property['rentalgross_net'];
      }

      if (isset($property['incidentals']) && $property['incidentals'] == '' && isset($property['incidental_costs_squaremeter']) && $property['incidental_costs_squaremeter']) {
        $new_meta_data['_complexmanager_unit_extra_costs'] = $property['incidental_costs_squaremeter'];
      } elseif(isset($property['incidentals']) && $property['incidentals']) {
        $new_meta_data['_complexmanager_unit_extra_costs'] = $property['incidentals'];
      }
      
      $new_meta_data['_complexmanager_unit_name']                 = $property['title'];
      $new_meta_data['_complexmanager_unit_number_of_rooms']      = (is_numeric($property['rooms']) ? $property['rooms'] : '');
      $new_meta_data['_complexmanager_unit_min_persons']          = (is_numeric($property['min_adult']) ? $property['min_adult'] : '');
      $new_meta_data['_complexmanager_unit_max_persons']          = (is_numeric($property['max_adult']) ? $property['max_adult'] : '');
      $new_meta_data['_complexmanager_unit_story']                = $property['floor'] ?? '';
      $new_meta_data['_complexmanager_unit_status']               = $property['state_simplyfied'];
      $new_meta_data['_complexmanager_unit_living_space']         = $property['area'] ?? '';
      $new_meta_data['_complexmanager_unit_terrace_space']        = $property['terrace_area'] ?? '';
      $new_meta_data['_complexmanager_unit_balcony_space']        = $property['balcony_area'] ?? '';

      $new_meta_data['_complexmanager_unit_custom_overlay']       = $property['isometry'] ?? '';

      if (isset($property['factsheets']) && $property['factsheets']) {
        $new_meta_data['_complexmanager_unit_download_file']        = $property['factsheets'];
        $new_meta_data['_complexmanager_unit_download_label']       = 'Grundriss';
      } /* elseif(isset($property['layout_plan']) && $property['layout_plan']) {
        $new_meta_data['_complexmanager_unit_download_file']        = $property['layout_plan'];
        $new_meta_data['_complexmanager_unit_download_label']       = 'Grundriss';
      } elseif(isset($property['situation_plan']) && $property['situation_plan']) {
        $new_meta_data['_complexmanager_unit_download_file']        = $property['situation_plan'];
        $new_meta_data['_complexmanager_unit_download_label']       = 'Grundriss';
      } elseif(isset($property['pdf_file']) && $property['pdf_file']) {
        $new_meta_data['_complexmanager_unit_download_file']        = $property['pdf_file'];
        $new_meta_data['_complexmanager_unit_download_label']       = 'Grundriss';
      } */

      if (isset($property['virtual_tour_link']) && $property['virtual_tour_link']) {
        $new_meta_data['_complexmanager_unit_tour_url']       = $property['virtual_tour_link'];
        $new_meta_data['_complexmanager_unit_tour_label']       = 'Virtuelle Tour';
        $new_meta_data['_complexmanager_unit_tour_target']       = '_blank';
      }

      if (isset($property['website_link']) && $property['website_link']) {
        $new_meta_data['_complexmanager_unit_link_url']       = $property['website_link'];
        $new_meta_data['_complexmanager_unit_link_label']       = 'Jetzt online bewerben';
        $new_meta_data['_complexmanager_unit_link_target']       = '_blank';
      }

      $cxm_emonitor_custom1_matching = PluginOptions::get_option('cxm_emonitor_custom1_matching', false);
      $cxm_emonitor_custom2_matching = PluginOptions::get_option('cxm_emonitor_custom2_matching', false);
      $cxm_emonitor_custom3_matching = PluginOptions::get_option('cxm_emonitor_custom3_matching', false);

      if (isset($cxm_emonitor_custom1_matching) && $cxm_emonitor_custom1_matching) {
        $new_meta_data['_complexmanager_unit_custom_1'] = $property[$cxm_emonitor_custom1_matching] ?? '';
      }

      if (isset($cxm_emonitor_custom2_matching) && $cxm_emonitor_custom2_matching) {
        $new_meta_data['_complexmanager_unit_custom_2'] = $property[$cxm_emonitor_custom2_matching] ?? '';
      }

      if (isset($cxm_emonitor_custom3_matching) && $cxm_emonitor_custom3_matching) {
        $new_meta_data['_complexmanager_unit_custom_3'] = $property[$cxm_emonitor_custom3_matching] ?? '';
      }

    } else {

      $buildingcontent = $property['building_adress'] . ' ' . $property['building_house_number'] . ', ' . $property['building_plz'] . ' ' . $property['building_city'];
      if (is_array($name)) {
        $name = $name[0];
      }

      if ($property['state_simplyfied'] == 'reserviert' || $property['state_simplyfied'] == 'Reserviert' || $property['state_simplyfied'] == 'réservé') {
        $property['state_simplyfied'] = 'reserved';
      } elseif ($property['state_simplyfied'] == 'vermietet' || $property['state_simplyfied'] == 'Vermietet' || $property['state_simplyfied'] == 'loué') {
        $property['state_simplyfied'] = 'rented';
      } else {
        $property['state_simplyfied'] = 'available';
      }

      if (isset($property['incidental_costs'])) {
        $property['additional_costs_1'] = $property['incidental_costs'];
      } elseif (isset($property['incidental_costs_squaremeter'])) {
        $property['additional_costs_1'] = $property['incidental_costs_squaremeter'];
      }

      if (!isset($property['rentalgross_net']) && isset($property['rentalprice_squaremeter_net'])) {
        $property['rentalgross_net'] = $property['rentalprice_squaremeter_net'];
      }

      $new_meta_data['_complexmanager_unit_name']                 = $property['title'];
      $new_meta_data['_complexmanager_unit_rent_net']             = (is_numeric($property['rentalgross_net']) ? $property['rentalgross_net'] : '');
      $new_meta_data['_complexmanager_unit_rent_gross']           = (is_numeric($property['rentalgross']) ? $property['rentalgross'] : '');
      $new_meta_data['_complexmanager_unit_number_of_rooms']      = (is_numeric($property['rooms']) ? $property['rooms'] : '');
      $new_meta_data['_complexmanager_unit_min_persons']          = (is_numeric($property['min_adult']) ? $property['min_adult'] : '');
      $new_meta_data['_complexmanager_unit_max_persons']          = (is_numeric($property['max_adult']) ? $property['max_adult'] : '');
      $new_meta_data['_complexmanager_unit_story']                = $property['floor'];
      $new_meta_data['_complexmanager_unit_status']               = $property['state_simplyfied'];
      $new_meta_data['_complexmanager_unit_living_space']         = $property['area'];
      $new_meta_data['_complexmanager_unit_terrace_space']        = $property['terrace_area'];
      $new_meta_data['_complexmanager_unit_extra_costs']          = $property['additional_costs_1'];
      $new_meta_data['_complexmanager_unit_usable_space']         = $property['area_property'];
      $new_meta_data['_complexmanager_unit_balcony_space']        = $property['balcony'];

      $squaremeterprices = PluginOptions::get_option('squaremeterprices', false);

      if ($property['property_type'] == "Gewerbe" && $squaremeterprices == 1) {
        $new_meta_data['_complexmanager_unit_custom_2']             = "CHF " . $property['rentalprice_squaremeter'] . "/m²/Jahr";
        $new_meta_data['_complexmanager_unit_custom_3']             = "CHF " . $property['rentalprice_squaremeter_net'] . "/m²/Jahr";
      }

      $propertytype = PluginOptions::get_option('propertytype', false);
      if ($propertytype == 1) {
        $new_meta_data['_complexmanager_unit_custom_1']             = $property['rental_conditions'];
      }

      $virtualtour = PluginOptions::get_option('virtualtour', false);
      if ($virtualtour == 1) {
        $new_meta_data['_complexmanager_unit_custom_2']             = $property['virtual_tour_link'];
      }

      $new_meta_data['_complexmanager_unit_download_file']        = $property['factsheet'];
      $new_meta_data['_complexmanager_unit_download_label']       = (isset($property['factsheet']) ? 'Grundriss' : '');

      $new_meta_data['_complexmanager_unit_link_target']          = (isset($property['url']) ? '_blank' : '');
      $new_meta_data['_complexmanager_unit_link_url']             = $property['url'];
      $new_meta_data['_complexmanager_unit_link_label']           = (isset($property['url']) ? 'Jetzt online bewerben' : '');

      $new_meta_data['_complexmanager_unit_custom_overlay']       = $property['isometry'];

    }

    $excerpt = (isset($publisher_options['override_excerpt']) && $publisher_options['override_excerpt'] ? $publisher_options['override_excerpt'] : '');
    if (is_array($excerpt)) {
      $excerpt = $excerpt[0];
    }

    $new_main_data = array(
      'ID'            => $wp_post->ID,
      'post_title'    => ($name ? $name : 'Objekt'),
      'post_content'  => '',
      'post_status'   => 'publish',
      'post_type'     => 'complex_unit',
      'post_excerpt'  => $excerpt,
      'post_date' => $wp_post->post_date,
    );

    $old_main_data = array(
      'ID'            => $wp_post->ID,
      'post_title'    => $wp_post->post_title,
      'post_content'  => $wp_post->post_content,
      'post_status'   => $wp_post->post_status,
      'post_type'     => $wp_post->post_type,
      'post_excerpt'  => $wp_post->post_excerpt,
      'post_date' => $wp_post->post_date
    );
   

    if ($new_main_data != $old_main_data) {
      foreach ($old_main_data as $key => $value) {
        if ($new_main_data[$key] != $old_main_data[$key]) {
          $this->transcript[$casawp_id]['main_data'][$key]['from'] = $old_main_data[$key];
          $this->transcript[$casawp_id]['main_data'][$key]['to'] = $new_main_data[$key];
          $this->addToLog('updating main data (' . $key . '): ' . $old_main_data[$key] . ' -> ' . $new_main_data[$key]);
        }
      }

      if (!$wp_post->post_name) {
        $new_main_data['post_name'] = sanitize_title_with_dashes($casawp_id . '-' . $property['title'], '', 'save');
      } else {
        $new_main_data['post_name'] = $wp_post->post_name;
      }

      $newPostID = wp_insert_post($new_main_data);
    }

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
          $newval = "EG";
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

      $this->addToLog('removing supurflous metadata');
      foreach ($old_meta_data as $key => $value) {
        if (
          !isset($new_meta_data[$key])
          && !in_array($key, array('casawp_id', 'projectunit_id', 'projectunit_sort'))
          && strpos($key, '_') !== 0
        ) {
          delete_post_meta($wp_post->ID, $key, $value);
          $this->transcript[$casawp_id]['meta_data']['removed'][$key] = $value;
        }
      }
    }


    $this->addToLog('updating buildings');

    if ($version == 'v2') {
      if (isset($property['building']['building_title']) && $property['building']['building_title']) {
        $property_type = $property['object_type']['displayName'] ?: '';
        $this->setPropertyBuilding($wp_post, $property['building']['building_title'], $casawp_id, $buildingcontent, $property_type);
      }
      $this->addToLog('updating attachments');
      if (isset($property['layout_plan']) && $property['layout_plan']) {
        $plan = $property['layout_plan'];
        $this->setUnitAttachments($plan, $wp_post, $casawp_id, $property);
      } elseif(isset($property['situation_plan']) && $property['situation_plan']) {
        $plan = $property['situation_plan'];
        $this->setUnitAttachments($plan, $wp_post, $casawp_id, $property);
      } elseif(isset($property['pdf_file']) && $property['pdf_file']) {
        $plan = $property['pdf_file'];
        $this->setUnitAttachments($plan, $wp_post, $casawp_id, $property);
      }
    } else {
      $this->setPropertyBuilding($wp_post, $property['building_title'], $casawp_id, $buildingcontent, $property['property_type']);
      $this->addToLog('updating attachments');
      $this->setUnitAttachments($property['layout_plan'], $wp_post, $casawp_id, $property);
    }

    $this->addToLog('finish property update: [' . $casawp_id . ']' . time());

    $removed = 0;
    $dir = wp_upload_dir(null, true, false);
    if (is_dir($dir['basedir'] . '/cmx_cache')) {
      $files = glob($dir['basedir'] . '/cmx_cache/*');
      foreach ($files as $file) {
        if (is_file($file))
          unlink($file);
        $removed++;
      }
    }
  }
}
