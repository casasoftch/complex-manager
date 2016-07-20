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

### [CXM-graphic] ###

Renders a interactive jumpotron graphic. Sadly IE8> will only render the image without the interactivity.


### [CXM-list] ###

**Args**

* ``integrate_form``: Renders form inside of list (default:1)
* ``collapsible``: (default:1)
* ``building_id``: only render specific table (default:false)


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

## Actions and Hooks


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


