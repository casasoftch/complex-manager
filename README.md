# Complex-Manager #

Contributors: casasoft
Tags: bootstrap, real-estate, complex, inquiries, sell, rent, buy
Requires at least: 4
Tested up to: 4

## WordPress Plugin Foundation ##

**Complex Manager** is a plugin that will make selling and renting requests for real-estate building projects simple and hasle free.


## Getting Started with Complex-Managert ##

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

## Shortcodes ##

To make things easy to fill your page with custom content, the plugin utilizes shortcodes to render the offers.



### [CXM-filter] ###

All possible filters:

[CXM-filter filters="number_of_rooms, livingspace, usablespace, purchaseprice, rentnet, rentgross, story, types, custom_1, custom_2, custom_3, income, persons, status"]

### [CXM-graphic] ###

Renders a interactive jumpotron graphic. Sadly IE8> will only render the image without the interactivity.


### [CXM-list] ###

**Args**

* ``integrate_form``: Renders form inside of list (default:1)
* ``collapsible``: (default:1)
* ``building_id``: only render specific table (default:false)
* ``class``: append custom css class
* ``orderby``: define a key to order properties (possible values: menu_order, title, status, storey, living_space, usable_space, purchase_price, rent_net, rent_gross, number_of_rooms, custom_1, custom_2, custom_3)
* ``order``: define order direction (possible values: ASC, DESC)


### [CXM-form] ###

Used to be ``[contactform-complex]``. This will still work.


**Args**

* ``unit_id``: Preselected unit. Also hides the unit selector.


## functions ##

with `get_cxm($post_id, $field);` and `get_cxm_label($post_id, $field);`

You can retrieve data from the registered post types "complex_unit" and "complex_inquiry". This allows you to create custom views/templates of the archive and single pages


### example single-complex_unit.php ###

```php
<?php get_header(); ?>
	<section role="main">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<header class="section-heading">
					<h1 class="section-title"><?php the_title(); ?></h1>
				</header>
				<div class="section-content">
					<?php get_cxm(get_the_ID(), "name") ?><br>
					<?php get_cxm(get_the_ID(), "purchase_price") ?><br> //returns only the intiger value
					<?php get_cxm(get_the_ID(), "full_rent_net") ?><br> //full_* renders with currency and formating
					<?php get_cxm(get_the_ID(), "number_of_rooms") ?><br>
				</div>
			<?php endwhile; ?>
		<?php else : ?>
			<?php _e('no entries found', 'theme'); ?>
		<?php endif; ?>
	</section>
<?php get_footer(); ?>
```

### example archive-complex_unit.php ###

```php
<?php get_header(); ?>
	<section role="main">
		<?php if ( have_posts() ) : ?>
			<header class="section-heading">
				<h1 class="section-title">Index</h1>
			</header>
			<div class="section-content">
				<?php while ( have_posts() ) : the_post(); ?>
					<article>
						<header class="article-heading">
							<h1><?php the_title(); ?></h1>
						</header>
						<div class="article-content">
							<?php get_cxm(get_the_ID(), "name") ?> // << HERE
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<?php _e('no entries found', 'theme'); ?>
		<?php endif; ?>
	</section>
<?php get_footer(); ?>
```

## CSS ##

This plugin was developed for the following css environments

+   The template utilizes the twitter bootstrap v3 framework
+   You have control of the template and are willing to add the necesarry css to the template to make it look good :-)


## JavaScript ##
Although the plugin will run without javascript, some of the features, such as the "Contact" button work differently with javascript turned off. Ensure that you have implemented the wp_head and wp_foot functions within your theme where appropriate.
A single javascript will be included within the front-end.

## Translations ##
The following translations are pre-translated
+	**EN** English
+	**DE** German

## Future ##

+   Custom Templates
+   IE 8 interaction support

## Actions and Filters


### Send extra email(s) after inquiry submission.

`cxm_after_inquirysend`

```
function after_inquirysend($formData) {
    if (isset($formData['email'])) {
        $subject = "Neubauprojekt Berghalde in Pfäffikon ZH";
        $message = "Sehr geehrte Interessentin\nSehr geehrter Interessent\n\nHerzlichen Dank für Ihr Interesse an den Eigentumswohnungen «Berghalde» in Pfäffikon ZH.  \n\nDie Projektunterlagen befinden sich derzeit in der Aufbereitung und werden voraussichtlich im Juni 2016 vorliegen. Gerne nehmen wir Ihre Kontaktdaten auf und werden Ihnen die Dokumentation nach deren Fertigstellung umgehend zukommen lassen.\n\nWenn Sie vorher schon weitere Informationen wünschen, laden wir Sie gerne zu einem unverbindlichen, ersten Beratungsgespräch ein, bei dem wir Ihnen das Projekt näher vorstellen. Kontaktieren Sie uns für eine Terminvereinbarung unter +41 44 905 40 90 oder per E-Mail an uster@walde.ch.\n\nAuch für Rückfragen stehen wir Ihnen selbstverständlich zur Verfügung. Wir freuen uns auf Sie.\n\n\nFreundliche Grüsse\n\nNicole Stöckli\nImmobilienberaterin\n\nWalde & Partner Immobilien AG\nZentralstrasse 25\n8610 Uster\nwww.walde.ch\n\nDirect    +41 44 905 40 94\nPhone    +41 44 905 40 90\nFax        +41 44 905 40 99\n";
        wp_mail( $formData['email'], $subject, $message);
    }
}
add_action( 'cxm_after_inquirysend', 'after_inquirysend' );
```

Here an example how you could send a ga event to google after a form submission occured.

```
function after_inquirysend_gaevent($formData) {
   wp_add_inline_script( 'ga-count-inquiry-send', "ga('send', 'event', 'inquiry', 'sent', $formData['email']);", 'after' );
}
add_action( 'cxm_after_inquirysend', 'after_inquirysend_gaevent' );
```

### send custom google analytics events (performed serverside through PHP)
```
You will need to add the GA code to the backend for it to work.
cxm_send_ga_event($action = 'somthing-happend', $label = 'This happended just now', $value = 1)
```

### A filter to manipulate data sent to casamail

```
function cxm_filter_casamail_data($data, $postadata = array()){
	$recipients = array();
	if (isset($postdata['extra_data']['Tower/Arbeiten']) || isset($postdata['extra_data']['Tower/Gewerbeflächen']) || isset($postdata['extra_data']['Cube/Arbeiten']) || isset($postdata['extra_data']['Cube/Gewerbeflächen']) ) {
		$recipients[] = 'recipient_1@example.com';
	}
	if (isset($postdata['extra_data']['Tower/Wohnen'])) {
		$recipients[] = 'recipient_2@example.com';
	}
	if (isset($postdata['extra_data']['Plaza/Geschäfte']) || isset($postdata['extra_data']['Plaza/Anderes'])) {
		$recipients[] = 'recipient_3@example.com';
	}
	if (empty($recipients)) {
		$recipients[] = 'fallback@example.com';
	}
	if (isset($data['direct_recipient_email']) && $data['direct_recipient_email']) {
		$recipients[] = $data['direct_recipient_email'];
	}
	$data['direct_recipient_email'] = implode(',', $recipients);
	return $data;
}
add_filter('cxm_filter_casamail_data', 'cxm_filter_casamail_data');
```


#### Manipulate Object Reference:

```
function cxm_filter_casamail_data($data, $postdata = array()){
	if ($data['extra_data']) {
		$extra_data = json_decode($data['extra_data'], true);
		if (isset($extra_data['Wohnungen']) && $extra_data['Wohnungen'] == "Oui") {
			$data['property_reference'] = '..9258';
		} elseif(isset($extra_data['Büroräume']) && $extra_data['Büroräume'] == "Oui") {
			$data['property_reference'] = '..9802';
		}
	}
	return $data;
}
add_filter('cxm_filter_casamail_data', 'cxm_filter_casamail_data');
```

### Adjust validation messages on form posts (messages are always blocking)

```
function cxm_filter_form_messages($args = array()){
	$messages = $args["messages"];
	$data = $args["formData"];
	if ($data['extra_data']['AUX BUREAUX'] == 'Non' && $data['extra_data']['AUX COMMERCES'] == 'Non') {
		$messages['AUX BUREAUX'] = __('Champs obligatoires', 'theme');
	}
	return $messages;
}
add_filter('cxm_filter_form_messages', 'cxm_filter_form_messages');
```

### Adjust default required fields

#### Add/Remove required fields to an array that generates messages if not set

Default fields are:

```
[
	'first_name',
	'last_name',
	'phone',
	'street',
	'postal_code',
	'locality',
	'unit_id'
]
```

Add this to functions.php

```
function cxm_filter_form_required(Array $args){
	$fields = $args["fields"];

	// add field
	$fields[] => "somefield";

	// remove field
	$key = array_search("phone", $fields);
	if($key){
		unset($fields[$key]);	
	}

	$args['fields'] = $fields;
	
	return $args;
}
add_filter('cxm_filter_form_required', 'cxm_filter_form_required');
```

#### Manipulate the messages generated by these required fields

Default messages are:

```
[
	'first_name' => __('First name is required', 'complexmanager'),
	'last_name' => __('Last name is required', 'complexmanager'),
	'legal_name' => __('The company is required', 'complexmanager'),
	'email' => __('Email is not valid', 'complexmanager'),
	'phone' => __('A phone number is required', 'complexmanager'),
	'street' => __('A street address is required', 'complexmanager'),
	'postal_code' => __('ZIP is required', 'complexmanager'),
	'locality' =>  __('City is required', 'complexmanager'),
	'message' => __('Message is required', 'complexmanager'),
	'post' => __('Ivalid post', 'complexmanager'),
	'gender' => 'That should not be possible',
	'unit_id' => __('Please choose a unit', 'complexmanager'),//'Bitte wählen Sie eine Wohnung'
]
```

Add this to functions.php

```
function cxm_filter_form_required_messages(Array $args){
	$messages = $args['messages'];

	// add message
	$messages["somefield"] => "somefield is required";

	// remove message
	unset($messages["phone"]);

	$args['messages'] = $messages;
	
	return $args;
}
add_filter('cxm_filter_form_required_messages', 'cxm_filter_form_required_messages');
```


#### Theme Schnipsel für Cron automatische Emonitor Importe

function updateThemeUnits() {
    $force_update = PluginOptions::get_option('cxm_force_property_update', false);

    // Wenn Force-Option aktiv, GET-Parameter simulieren
    if ($force_update) {
        $_GET['force_all_properties'] = 'true';
        $_GET['force_last_import'] = 'true';
    }

    // Autoimport starten
    $import = new casasoft\complexmanager\eMonitorImport(true, true);
    $import->addToLog('Automatic Update from Emonitor caused import' . ($force_update ? ' (forced updates)' : ''));
}

add_action('update_theme_units_event', 'updateThemeUnits');


