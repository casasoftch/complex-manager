<?php
namespace casasoft\complexmanager;

class import extends Feature {

	public function addToLog($transcript){
    $dir = CASASYNC_CUR_UPLOAD_BASEDIR  . '/cxm/logs';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dir."/".get_date_from_gmt('', 'Y M').'.log', "\n".json_encode(array(get_date_from_gmt('', 'Y-m-d H:i') => $transcript)), FILE_APPEND);
  }

	public function property2Array($property_xml){
	 $propertydata['address'] = array(
			 'country'       => ($property_xml->address->country->__toString() ?:''),
			 'locality'      => ($property_xml->address->locality->__toString() ?:''),
			 'region'        => ($property_xml->address->region->__toString() ?:''),
			 'postal_code'   => ($property_xml->address->postalCode->__toString() ?:''),
			 'street'        => ($property_xml->address->street->__toString() ?:''),
			 'streetNumber' => ($property_xml->address->streetNumber->__toString() ?:''),
			 'streetAddition' => ($property_xml->address->streetAddition->__toString() ?:''),
			 'subunit'       => ($property_xml->address->subunit->__toString() ?:''),
			 'lng'           => ($property_xml->address->geo ? $property_xml->address->geo->longitude->__toString():''),
			 'lat'           => ($property_xml->address->geo ? $property_xml->address->geo->latitude->__toString():''),
	 );
	 $propertydata['creation'] = (isset($property_xml->softwareInformation->creation) ? new \DateTime($property_xml->softwareInformation->creation->__toString()) : '');
	 $propertydata['last_update'] = (isset($property_xml->softwareInformation->lastUpdate) ? new \DateTime($property_xml->softwareInformation->lastUpdate->__toString()) : '');
	 $propertydata['exportproperty_id'] = (isset($property_xml['id']) ? $property_xml['id']->__toString() : '');
	 $propertydata['referenceId'] = (isset($property_xml->referenceId) ? $property_xml->referenceId->__toString() : '');
	 $propertydata['visualReferenceId'] = (isset($property_xml->visualReferenceId) ? $property_xml->visualReferenceId->__toString() : '');
	 $propertydata['availability'] = ($property_xml->availability->__toString() ? $property_xml->availability->__toString() : 'available');
	 $propertydata['price_currency'] = $property_xml->priceCurrency->__toString();
	 $propertydata['price'] = $property_xml->price->__toString();
	 $propertydata['price_property_segment'] = (!$property_xml->price['propertysegment']?:str_replace('2', '', $property_xml->price['propertysegment']->__toString()));
	 $propertydata['net_price'] = $property_xml->netPrice->__toString();
	 $propertydata['net_price_time_segment'] = ($property_xml->netPrice['timesegment'] ? strtolower($property_xml->netPrice['timesegment']->__toString()) : '');
	 $propertydata['net_price_property_segment'] = (!$property_xml->netPrice['propertysegment']?: str_replace('2', '', $property_xml->netPrice['propertysegment']->__toString()));
	 $propertydata['gross_price'] = $property_xml->grossPrice->__toString();
	 $propertydata['gross_price_time_segment'] = ($property_xml->grossPrice['timesegment'] ? strtolower($property_xml->grossPrice['timesegment']->__toString()) : '');
	 $propertydata['gross_price_property_segment'] = (!$property_xml->grossPrice['propertysegment']?:str_replace('2', '', $property_xml->grossPrice['propertysegment']->__toString()));

	 if ($property_xml->integratedOffers) {
			 $propertydata['integratedoffers'] = array();
			 foreach ($property_xml->integratedOffers->integratedOffer as $xml_integratedoffer) {
					 $cost = $xml_integratedoffer->__toString();
					 $propertydata['integratedoffers'][] = array(
							 'type'             => ($xml_integratedoffer['type'] ? $xml_integratedoffer['type']->__toString() : ''),
							 'cost'             => $cost,
							 'frequency'        => ($xml_integratedoffer['frequency'] ? $xml_integratedoffer['frequency']->__toString() : ''),
							 'time_segment'     => ($xml_integratedoffer['timesegment'] ? $xml_integratedoffer['timesegment']->__toString() : ''),
							 'property_segment' => ($xml_integratedoffer['propertysegment'] ? $xml_integratedoffer['propertysegment']->__toString() : ''),
							 'inclusive'        => ($xml_integratedoffer['inclusive'] ? $xml_integratedoffer['inclusive']->__toString() : 0)
					 );
			 }
	 }

	 if ($property_xml->extraCosts) {
			 $propertydata['extracosts'] = array();
			 foreach ($property_xml->extraCosts->extraCost as $xml_extra_cost) {
					 $cost = $xml_extra_cost->__toString();
					 $propertydata['extracosts'][] = array(
							 'type'             => ($xml_extra_cost['type'] ? $xml_extra_cost['type']->__toString() : ''),
							 'cost'             => $cost,
							 'frequency'        => ($xml_extra_cost['frequency'] ? $xml_extra_cost['frequency']->__toString() : ''),
							 'property_segment' => ($xml_extra_cost['propertysegment'] ? $xml_extra_cost['propertysegment']->__toString() : ''),
							 'time_segment'     => ($xml_extra_cost['timesegment'] ? $xml_extra_cost['timesegment']->__toString() : ''),
					 );
			 }
	 }

	 $propertydata['status'] = 'active';
	 $propertydata['type'] =  $property_xml->type->__toString();
	 $propertydata['zoneTypes'] = ($property_xml->zoneTypes ? $property_xml->zoneTypes->__toString() : '');
	 $propertydata['parcelNumbers'] = ($property_xml->parcelNumbers ? $property_xml->parcelNumbers->__toString() : '');

	 $propertydata['property_categories'] = array();
	 if ($property_xml->categories) {
			 foreach ($property_xml->categories->category as $xml_category) {
					 $propertydata['property_categories'][] = $xml_category->__toString();
			 }
	 }

	 $propertydata['property_utilities'] = array();
	 if ($property_xml->utilities) {
			 foreach ($property_xml->utilities->utility as $xml_utility) {
					 $propertydata['property_utilities'][] = $xml_utility->__toString();
			 }
	 }

	 $propertydata['numeric_values'] = array();
	 if ($property_xml->numericValues) {
			 foreach ($property_xml->numericValues->value as $xml_numval) {
					 $key = (isset($xml_numval['key']) ? $xml_numval['key']->__toString() : false);
					 if ($key) {
							 $value = $xml_numval->__toString();
							 $propertydata['numeric_values'][] = array(
									 'key' => $key,
									 'value' => $value
							 );
					 }
			 }
	 }

	 $propertydata['features'] = array();
	 if ($property_xml->features) {
			 foreach ($property_xml->features->feature as $xml_feature) {
					 $propertydata['features'][] = $xml_feature->__toString();
			 }
	 }

	 //seller ****************************************************************
	 if ($property_xml->seller) {

			 $propertydata['organization'] = array();

			 //organization
			 if ($property_xml->seller->organization) {
					 if ($property_xml->seller->organization['id']) {
						 $propertydata['organization']['id']    = $property_xml->seller->organization['id']->__toString();
					 } else {
						 $propertydata['organization']['id'] = false;
					 }
					 $propertydata['organization']['displayName']    = $property_xml->seller->organization->legalName->__toString();
					 $propertydata['organization']['addition']         = $property_xml->seller->organization->brand->__toString();
					 $propertydata['organization']['email']         = $property_xml->seller->organization->email->__toString();
					 $propertydata['organization']['email_rem']     = $property_xml->seller->organization->emailRem->__toString();
					 $propertydata['organization']['fax']           = $property_xml->seller->organization->fax->__toString();
					 $propertydata['organization']['phone']         = $property_xml->seller->organization->phone->__toString();
					 $propertydata['organization']['website_url']   = ($property_xml->seller->organization ? $property_xml->seller->organization->website->__toString() : '');
					 $propertydata['organization']['website_title'] = ($property_xml->seller->organization && $property_xml->seller->organization->website ? $property_xml->seller->organization->website['title']->__toString() : '');
					 $propertydata['organization']['website_label'] = ($property_xml->seller->organization && $property_xml->seller->organization->website ? $property_xml->seller->organization->website['label']->__toString() : '');

					 //organization address
					 if ($property_xml->seller->organization->address) {
							 $propertydata['organization']['postalAddress'] = array();
							 $propertydata['organization']['postalAddress']['country'] = $property_xml->seller->organization->address->country->__toString();
							 $propertydata['organization']['postalAddress']['locality'] = $property_xml->seller->organization->address->locality->__toString();
							 $propertydata['organization']['postalAddress']['region'] = $property_xml->seller->organization->address->region->__toString();
							 $propertydata['organization']['postalAddress']['postal_code'] = $property_xml->seller->organization->address->postalCode->__toString();
							 $propertydata['organization']['postalAddress']['street'] = $property_xml->seller->organization->address->street->__toString();
							 $propertydata['organization']['postalAddress']['street_number'] = $property_xml->seller->organization->address->streetNumber->__toString();
							 $propertydata['organization']['postalAddress']['street_addition'] = $property_xml->seller->organization->address->streetAddition->__toString();
							 $propertydata['organization']['postalAddress']['post_office_box_number'] = $property_xml->seller->organization->address->postOfficeBoxNumber->__toString();
					 }
			 }

			 //viewPerson
			 $propertydata['viewPerson'] = array();
			 if ($property_xml->seller->viewPerson) {
					 $person                                  = $property_xml->seller->viewPerson;
					 $propertydata['viewPerson']['function']  = $person->function->__toString();
					 $propertydata['viewPerson']['firstName'] = $person->givenName->__toString();
					 $propertydata['viewPerson']['lastName']  = $person->familyName->__toString();
					 $propertydata['viewPerson']['email']     = $person->email->__toString();
					 $propertydata['viewPerson']['fax']       = $person->fax->__toString();
					 $propertydata['viewPerson']['phone']     = $person->phone->__toString();
					 $propertydata['viewPerson']['mobile']    = $person->mobile->__toString();
					 $propertydata['viewPerson']['gender']    = $person->gender->__toString();
					 $propertydata['viewPerson']['note']      = $person->note->__toString();
			 }

			 //visitPerson
			 $propertydata['visitPerson'] = array();
			 if ($property_xml->seller->visitPerson) {
					 $person                                   = $property_xml->seller->visitPerson;
					 $propertydata['visitPerson']['function']  = $person->function->__toString();
					 $propertydata['visitPerson']['firstName'] = $person->givenName->__toString();
					 $propertydata['visitPerson']['lastName']  = $person->familyName->__toString();
					 $propertydata['visitPerson']['email']     = $person->email->__toString();
					 $propertydata['visitPerson']['fax']       = $person->fax->__toString();
					 $propertydata['visitPerson']['phone']     = $person->phone->__toString();
					 $propertydata['visitPerson']['mobile']    = $person->mobile->__toString();
					 $propertydata['visitPerson']['gender']    = $person->gender->__toString();
					 $propertydata['visitPerson']['note']      = $person->note->__toString();
			 }

			 //inquiryPerson
			 $propertydata['inquiryPerson'] = array();
			 if ($property_xml->seller->inquiryPerson) {
					 $person                                     = $property_xml->seller->inquiryPerson;
					 $propertydata['inquiryPerson']['function']  = $person->function->__toString();
					 $propertydata['inquiryPerson']['firstName'] = $person->givenName->__toString();
					 $propertydata['inquiryPerson']['lastName']  = $person->familyName->__toString();
					 $propertydata['inquiryPerson']['email']     = $person->email->__toString();
					 $propertydata['inquiryPerson']['fax']       = $person->fax->__toString();
					 $propertydata['inquiryPerson']['phone']     = $person->phone->__toString();
					 $propertydata['inquiryPerson']['mobile']    = $person->mobile->__toString();
					 $propertydata['inquiryPerson']['gender']    = $person->gender->__toString();
					 $propertydata['inquiryPerson']['note']      = $person->note->__toString();
			 }

	 }
	 //END sellers ****************************************************************



	 //offers
	 $offerDatas = array();
	 if ($property_xml->offers) {
			 foreach ($property_xml->offers->offer as $offer_xml) {
					 $offerData['lang'] =  strtolower($offer_xml['lang']->__toString());
					 $offerData['type'] =  $property_xml->type->__toString();
					 if ($property_xml->start) {
						 $offerData['start'] =  new \DateTime($property_xml->start->__toString());
					 } else {
						 $offerData['start'] = null;
					 }
					 $offerData['status'] = 'active';
					 $offerData['name'] = $offer_xml->name->__toString();
					 $offerData['excerpt'] = $offer_xml->excerpt->__toString();

					 //publisher settings
					 $publishingDatas = array();
					 if ($offer_xml->publishers) {
							 foreach ($offer_xml->publishers->publisher as $publisher_xml) {
									 $options = array();
									 if ($publisher_xml->options) {
											 foreach ($publisher_xml->options->option as $option_xml) {
													 $options[$option_xml['key']->__toString()][] = $option_xml->__toString();
											 }
									 }
									 $publishingDatas[$publisher_xml['id']->__toString()] = array(
											 'options' => $options
									 );
							 }
					 }

					 $offerData['publish'] = $publishingDatas;

					 //urls
					 $urlDatas = array();
					 if ($offer_xml->urls) {
							 foreach ($offer_xml->urls->url as $xml_url) {
									 $title = (isset($xml_url['title']) ? $xml_url['title']->__toString() : false);
									 $type = (isset($xml_url['type']) ? $xml_url['type']->__toString() : false);
									 $label = (isset($xml_url['label']) ? $xml_url['label']->__toString() : false);
									 $url = $xml_url->__toString();

									 $urlDatas[] = array(
											 'title' => $title,
											 'type' => $type,
											 'label' => $label,
											 'url' => $url,

									 );
							 }
					 }
					 $offerData['urls'] = $urlDatas;

					 //descriptions
					 $descriptionDatas = array();
					 if ($offer_xml->descriptions) {
							 foreach ($offer_xml->descriptions->description as $xml_description) {
									 $title = (isset($xml_description['title']) ? $xml_description['title']->__toString() : false);
									 $text = $xml_description->__toString();

									 $descriptionDatas[] = array(
											 'title' => $title,
											 'text' => $text,
									 );
							 }
					 }
					 $offerData['descriptions'] = $descriptionDatas;

					 //attachments
					 $offerData['offer_medias'] = array();
					 if ($offer_xml->attachments) {
							 foreach ($offer_xml->attachments->media as $xml_media) {
									 if ($xml_media->file) {
											 $source = dirname($this->file) . $xml_media->file->__toString();
									 } elseif ($xml_media->url) {
											 $source = $xml_media->url->__toString();
											 $source = implode('/', array_map('rawurlencode', explode('/', $source)));
											 $source = str_replace('http%3A//', 'http://', $source);
											 $source = str_replace('https%3A//', 'https://', $source);
									 } else {
											 $this->addToTranscript("file or url missing from attachment media!");
											 continue;
									 }
									 $offerData['offer_medias'][] = array(
											 'alt' => $xml_media->alt->__toString(),
											 'title' => $xml_media->title->__toString(),
											 'caption' => $xml_media->caption->__toString(),
											 'description' => $xml_media->description->__toString(),
											 'type' => (isset($xml_media['type']) ? $xml_media['type']->__toString() : 'image'),
											 'media' => array(
													 'original_file' => $source,
											 )
									 );
							 }
					 }

					 $offerDatas[] = $offerData;

			 }
	 }

	 $propertydata['offers'] = $offerDatas;

	 return $propertydata;

 	}

	public function project2Array($project_xml){
    $data['ref'] = (isset($project_xml['id']) ? $project_xml['id']->__toString() : '');

    $di = 0;
    if ($project_xml->details) {
      foreach ($project_xml->details->detail as $xml_detail) {
        $di++;
        $data['details'][$di]['lang'] = (isset($xml_detail['lang']) ? $xml_detail['lang']->__toString() : '');
        $data['details'][$di]['name'] = (isset($xml_detail->name) ? $xml_detail->name->__toString() : '');

        $dd = 0;
        if ($xml_detail->descriptions) {
          foreach ($xml_detail->descriptions->description as $xml_description) {
            $dd++;
            $data['details'][$di]['descriptions'][$dd]['title'] = (isset($xml_description['title']) ? $xml_description['title']->__toString() : '');
            $data['details'][$di]['descriptions'][$dd]['text'] = $xml_description->__toString();
          }
        }

      }
    }

    $ui = 0;
    if ($project_xml->units) {
        $data['units'] = array();
        foreach ($project_xml->units->unit as $xml_unit) {
          $ui++;
          $data['units'][$ui]['ref'] = (isset($xml_unit['id']) ? $xml_unit['id']->__toString() : '');
          $data['units'][$ui]['name'] = (isset($xml_unit->name) ? $xml_unit->name->__toString() : '');
          if ($xml_unit->details) {
            foreach ($xml_unit->details->detail as $xml_detail) {
              $di++;
              $data['units'][$ui]['details'][$di]['lang'] = (isset($xml_detail['lang']) ? $xml_detail['lang']->__toString() : '');
              $data['units'][$ui]['details'][$di]['name'] = (isset($xml_detail->name) ? $xml_detail->name->__toString() : '');

              $dd = 0;
              if ($xml_detail->descriptions) {
                foreach ($xml_detail->descriptions->description as $xml_description) {
                  $dd++;
                  $data['units'][$ui]['details'][$di]['descriptions'][$dd]['title'] = (isset($xml_description['title']) ? $xml_description['title']->__toString() : '');
                  $data['units'][$ui]['details'][$di]['descriptions'][$dd]['text'] = $xml_description->__toString();
                }
              }

            }
          }

          $data['units'][$ui]['property_links'] = array();
          $pri = 0;
          foreach ($xml_unit->properties->propertyRef as $propertyRef) {
              $pri++;
              $data['units'][$ui]['property_links'][$pri]['ref'] = $propertyRef->__toString();
          }
        }
    }

    return $data;

  }

	public function updateImportFileThroughCasaGateway(){
    $this->addToLog('gateway file retriaval start: ' . time());

    $apikey = get_option('cxm_api_key');
    $privatekey = get_option('cxm_private_key');
    $apiurl = 'http://casagateway.ch/rest/publisher-properties';
    $options = array(
      'format' => 'casa-xml',
      'debug' => 1
    );
    if ($apikey && $privatekey) {

      //specify the current UnixTimeStamp
      $timestamp = time();

      //sort the options alphabeticaly and combine it into the checkstring
      ksort($options);
      $checkstring = '';
      foreach ($options as $key => $value) {
          $checkstring .= $key . $value;
      }

      //add private key at end of the checkstring
      $checkstring .= $privatekey;

      //add the timestamp at the end of the checkstring
      $checkstring .= $timestamp;

      //hash it to specify the hmac
      $hmac = hash('sha256', $checkstring, false);

      //combine the query (DONT INCLUDE THE PRIVATE KEY!!!)
      $query = array(
          'hmac' => $hmac,
          'apikey' => $apikey,
          'timestamp' => $timestamp
      ) + $options;

      //build url
      $url = $apiurl . '?' . http_build_query($query, '', '&');

      $response = false;
      try {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
          $response = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($httpCode == 404) {
              $response = $httpCode;
          }
          curl_close($ch);
      } catch (Exception $e) {
          $response =  $e->getMessage() ;
      }

      if ($response) {
        if (!is_dir(CASASYNC_CUR_UPLOAD_BASEDIR . '/cxm/import')) {
          mkdir(CASASYNC_CUR_UPLOAD_BASEDIR . '/cxm/import');
        }
        $file = CASASYNC_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml';

        file_put_contents($file, $response);
      }



      //echo '<div id="message" class="updated">XML wurde aktualisiert</div>';
    } else {
      echo '<div id="message" class="updated"> API Keys missing</div>';
    }
  }

	public function importFromCasaXML(){
		set_time_limit(600);
		$this->addToLog('import start');
			$file = CASASYNC_CUR_UPLOAD_BASEDIR  . '/cxm/import/data.xml';
			$processingFile = CASASYNC_CUR_UPLOAD_BASEDIR  . '/cxm/import/data-done.xml';
			rename($file, $processingFile);


	    global $wpdb;
	    $xml = simplexml_load_file($processingFile, 'SimpleXMLElement', LIBXML_NOCDATA);

			$found_posts = array();
	    foreach ($xml->properties->property as $property) {
	      $propertyData = $this->property2Array($property);
	      //make main language first and "single out" if not multilingual
	      $theoffers = array();
	      $i = 0;
	      foreach ($propertyData['offers'] as $offer) {
	        $i++;
	        if ($offer['lang'] == $this->getMainLang()) {
	          $theoffers[0] = $offer;
	        } else {
	          if ($this->hasWPML()) {
	            $theoffers[$i] = $offer;
	          }
	        }
	      }

	      //complete missing translations if multilingual
	      if ($this->hasWPML()) {
	        $theoffers = $this->fillMissingTranslations($theoffers);
	      }

	      $offer_pos = 0;
	      foreach ($theoffers as $offerData) {
	        $offer_pos++;

	        //is it already in db
	        $casawp_id = $propertyData['exportproperty_id'] . $offerData['lang'];

	        $the_query = new \WP_Query( 'post_type=casawp_property&suppress_filters=true&meta_key=casawp_id&meta_value=' . $casawp_id );
	        $wp_post = false;
	        while ( $the_query->have_posts() ) :
	          $the_query->the_post();
	          global $post;
	          $wp_post = $post;
	        endwhile;
	        wp_reset_postdata();

	        //if not create a basic property
	        if (!$wp_post) {
	          $this->transcript[$casawp_id]['action'] = 'new';
	          $the_post['post_title'] = $offerData['name'];
	          $the_post['post_content'] = 'unsaved property';
	          $the_post['post_status'] = 'publish';
	          $the_post['post_type'] = 'casawp_property';
	          $the_post['post_name'] = sanitize_title_with_dashes($casawp_id . '-' . $offerData['name'],'','save');
	          $_POST['icl_post_language'] = $offerData['lang'];
	          $insert_id = wp_insert_post($the_post);
	          update_post_meta($insert_id, 'casawp_id', $casawp_id);
	          $wp_post = get_post($insert_id, OBJECT, 'raw');
	          $this->addToLog('new property: '. $casawp_id);
	        }
	        $found_posts[] = $wp_post->ID;

	        $this->updateOffer($casawp_id, $offer_pos, $propertyData, $offerData, $wp_post);
	        $this->updateInsertWPMLconnection($wp_post, $offerData['lang'], $propertyData['exportproperty_id']);

	      }
	    }

	    //3. remove all the unused properties
	    $properties_to_remove = get_posts(  array(
	      'suppress_filters'=>true,
	      'language'=>'ALL',
	      'numberposts' =>  100,
	      'exclude'     =>  $found_posts,
	      'post_type'   =>  'casawp_property',
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
	      $this->transcript['properties_removed'] = count($properties_to_remove);
	    }



		$this->addToLog('import end');

	}

	public function __construct() {

	}

}



// Subscribe to the drop-in to the initialization event
add_action( 'complexmanager_init', array( 'casasoft\complexmanager\import', 'init' ) );
