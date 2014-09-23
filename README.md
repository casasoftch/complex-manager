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

cols="name, number_of_rooms, story, purchase_price, rent_net


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
