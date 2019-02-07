<?php

use Timber\Timber;

/**
 * Class Collection_Widget
 */
class Collection_Widget extends WP_Widget {

	/**
	 * The available layout options.
	 */
	const LAYOUT_OPTIONS = [
		'hero'             => [
			'label' => 'Hero',
			'class' => 'listing--hero',
		],
		'single'           => [
			'label' => 'Single',
			'class' => 'listing--single',
		],
		'single-w-sidebar' => [
			'label' => 'Single inc sidebar',
			'class' => 's-container--inc-sidebar listing--single-inc-sidebar',
		],
		'grid'             => [
			'label' => 'Grid',
			'class' => 'listing--grid',
		],
		'grid-w-sidebar'   => [
			'label' => 'Grid inc sidebar',
			'class' => 's-container--inc-sidebar listing--grid-inc-sidebar',
		],
		'filmstrip-3'      => [
			'label' => 'Filmstrip 3',
			'class' => 'filmstrip-3 listing--filmstrip',
		],
		'filmstrip-4'      => [
			'label' => 'Filmstrip 4',
			'class' => 'filmstrip-4 listing--filmstrip',
		],
		'filmstrip-5'      => [
			'label' => 'Filmstrip 5',
			'class' => 'filmstrip-5 listing--filmstrip',
		],
		'sidebar'          => [
			'label' => 'Sidebar',
			'class' => 'listing--sidebar',
		],
	];

	/**
	 * The available container options.
	 */
	const CONTAINER_OPTIONS = [
		'default'   => [
			'label' => 'Default',
			'class' => 's-container',
		],
		'full'      => [
			'label' => 'Full',
			'class' => 's-container s-container--full sticky-anchor',
		],
		'fullbleed' => [
			'label' => 'Fullbleed',
			'class' => 's-container s-container--fullbleed sticky-anchor',
		],
		'sidebar'   => [
			'label' => 'Sidebar',
			'class' => 's-sidebar',
		],
	];

	/**
	 * The available style options.
	 */
	const STYLE_OPTIONS = [
		'style-a' => [
			'label' => 'Style A',
			'class' => 'a',
		],
		'style-b' => [
			'label' => 'Style B',
			'class' => 'b',
		],
		'style-c' => [
			'label' => 'Style C',
			'class' => 'c',
		],
	];

	/**
	 * The available image options.
	 */
	const IMAGE_OPTIONS = [
		'text-only' => [
			'label' => 'Text Only',
			'class' => '',
		],
		'landscape' => [
			'label' => 'Landscape',
			'class' => 'image-aspect-landscape',
		],
		'square'    => [
			'label' => 'Square',
			'class' => 'image-aspect-square',
		],
		'portrait'  => [
			'label' => 'Portrait',
			'class' => 'image-aspect-portrait',
		],
	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		WP_Widget::__construct( 'collection_widget', __( 'Collection Widget' ), [
			'description' => __( 'Collection Widget' ),
		] );
	}

	/**
	 * The widget template.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( empty( $instance['collection_items'] ) ) {
			return;
		}

		$extra_classes = [];
		$title         = ( isset( $instance['title'] ) ? $instance['title'] : '' );
		$title         = apply_filters( 'widget_title', $title );
		$hide_title    = ( isset( $instance['hide_title'] ) ? $instance['hide_title'] : false );
		$layout        = ( isset( $instance['layout'] ) ? $instance['layout'] : 'single' );
		$container     = ( isset( $instance['container'] ) ? $instance['container'] : 'default' );
		$image         = ( isset( $instance['image'] ) ? $instance['image'] : '' );
		$style         = ( isset( $instance['style'] ) ? $instance['style'] : 'style-a' );

		if ( $layout && ! empty( self::LAYOUT_OPTIONS[ $layout ]['class'] ) ) {
			$extra_classes[] = self::LAYOUT_OPTIONS[ $layout ]['class'];

			if ( $style && ! empty( self::STYLE_OPTIONS[ $style ]['class'] ) ) {
				$extra_classes[] = self::LAYOUT_OPTIONS[ $layout ]['class'] . '-' . self::STYLE_OPTIONS[ $style ]['class'];
			}
		}

		if ( $container ) {
			$extra_classes = array_merge( explode( ' ', $container ), $extra_classes );
		}

		if ( $image && ! empty( self::IMAGE_OPTIONS[ $image ]['class'] ) ) {
			$extra_classes[] = self::IMAGE_OPTIONS[ $image ]['class'];
		}

		echo $args['before_widget'] = str_replace( 'class="', 'class="' . implode( ' ', $extra_classes ) . ' ', $args['before_widget'] );

		if ( ! empty( $title ) && ! $hide_title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$items                  = explode( ',', $instance['collection_items'] );
		$context                = Timber::get_context();
		$context['posts']       = [];
		foreach ( $items as $item ) {
			if ( substr( $item, 0, 10 ) === 'post_type_' ) {
				$post = Timber::get_post( substr( $item, 10 ), KeystonePost::class );
				if ( ! $post instanceof \Timber\Post ) {
					continue;
				}

				$context['posts'][] = $post;
			}

			if ( substr( $item, 0, 9 ) === 'taxonomy_' ) {
				$term = Timber::get_term( substr( $item, 9 ), null, KeystoneTerm::class );
				if ( ! $term instanceof \Timber\Term ) {
					continue;
				}

				$context['posts'][] = $term;
			}
        }
		$context['image_style'] = $image;
		$context['instance']    = $instance;

		echo Timber::fetch( 'post-collections/post-collections.twig', $context );

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$id     = mt_rand( 1, 10000 );
		$title  = ( isset( $instance['title'] ) ? $instance['title'] : '' );
		$layout = ( isset( $instance['layout'] ) ? $instance['layout'] : 'single' );
		$image  = ( isset( $instance['image'] ) ? $instance['image'] : '' );
		$style  = ( isset( $instance['style'] ) ? $instance['style'] : 'style-a' );
		$ad_position = ( isset( $instance['ad-position'] ) ? $instance['ad-position'] : 0 );
		$ad_display = ( isset( $instance['ad-display'] ) ? $instance['ad-display'] : 'all' );
		$teaser_length = ( isset( $instance['teaser_length'] ) ? $instance['teaser_length'] : '' );
		$show_author = isset( $instance['show_author'] ) ? 'true' : 'false';
		$show_comment_count = isset( $instance['show_comment_count'] ) ? 'true' : 'false';
		$show_date = isset( $instance['show_date'] ) ? 'true' : 'false';
		$show_rating = isset( $instance['show_rating'] ) ? 'true' : 'false';
		$show_price = isset( $instance['show_price'] ) ? 'true' : 'false';
		$show_duration = isset( $instance['show_duration'] ) ? 'true' : 'false';
		$show_start_time = isset( $instance['show_start_time'] ) ? 'true' : 'false';
		$show_content_type = isset( $instance['show_content_type'] ) ? 'true' : 'false';
		$show_taxonomy_signpost = isset( $instance['show_taxonomy_signpost'] ) ? 'true' : 'false';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
			<input class="widefat"
				   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				   type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>

		<p>
			<label>Add an item to this collection:</label>
			<input type="hidden"
				   class="js-post-collection-items-wrapper js-post-collection-items-<?php echo esc_attr( $id ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'collection_items' ) ); ?>">
		</p>

		<?php if ( ! empty( self::LAYOUT_OPTIONS ) ) : ?>
			<p>
				<label for="layout">Layout</label>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
					<?php foreach ( self::LAYOUT_OPTIONS as $key => $layout_option ) : ?>
						<option
							<?php selected( $layout, $key ); ?>
								value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $layout_option['label'] ); ?>
						</option>
					<?php endforeach; ?>s
				</select>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( self::STYLE_OPTIONS ) ) : ?>
			<p>
				<label for="layout">Style</label>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>">
					<?php foreach ( self::STYLE_OPTIONS as $key => $layout_option ) : ?>
						<option
							<?php selected( $style, $key ); ?>
								value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $layout_option['label'] ); ?>
						</option>
					<?php endforeach; ?>s
				</select>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( self::IMAGE_OPTIONS ) ) : ?>
			<p>
				<label for="layout">Image</label>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>">
					<?php foreach ( self::IMAGE_OPTIONS as $key => $layout_option ) : ?>
						<option
							<?php selected( $image, $key ); ?>
								value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $layout_option['label'] ); ?>
						</option>
					<?php endforeach; ?>s
				</select>
			</p>
		<?php endif; ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad-position' ) ); ?>">Ad Position:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'ad-position' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'ad-position' ) ); ?>" class="widefat">
				<option value="0" <?php selected( $ad_position, 0 ); ?>>No ad</option>
				<option value="1" <?php selected( $ad_position, 1 ); ?>>1</option>
				<option value="2" <?php selected( $ad_position, 2 ); ?>>2</option>
				<option value="3" <?php selected( $ad_position, 3 ); ?>>3</option>
				<option value="4" <?php selected( $ad_position, 4 ); ?>>4</option>
				<option value="5" <?php selected( $ad_position, 5 ); ?>>5</option>
				<option value="6" <?php selected( $ad_position, 6 ); ?>>6</option>
				<option value="7" <?php selected( $ad_position, 7 ); ?>>7</option>
				<option value="8" <?php selected( $ad_position, 8 ); ?>>8</option>
				<option value="9" <?php selected( $ad_position, 9 ); ?>>9</option>
				<option value="10" <?php selected( $ad_position, 10 ); ?>>10</option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad-display' ) ); ?>">Display Ad On:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'ad-display' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'ad-display' ) ); ?>" class="widefat">
				<option value="all" <?php selected( $ad_display, 'all' ); ?>>All Screens</option>
				<option value="mobile" <?php selected( $ad_display, 'mobile' ); ?>>Mobile Screens</option>
				<option value="desktop" <?php selected( $ad_display, 'desktop' ); ?>>Desktop Screens</option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'teaser_length' ) ); ?>">Teaser Length (words):</label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'teaser_length' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'teaser_length' ) ); ?>"
				   value="<?php echo esc_attr( $teaser_length ); ?>" class="widefat block-input">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_author' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>"
					<?php checked( $show_author, 'true' ); ?> class="block-input">
				Show Author
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_comment_count' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_comment_count' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_comment_count' ) ); ?>"
					<?php checked( $show_comment_count, 'true' ); ?> class="block-input">
				Show Comment Count
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"
					<?php checked( $show_date, 'true' ); ?> class="block-input">
				Show Date
			</label>
		</p>

		<?php if ( post_type_exists( 'review' ) ) : ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_rating' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_rating' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_rating' ) ); ?>"
					<?php checked( $show_rating, 'true' ); ?> class="block-input">
				Show Rating
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_price' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>"
					<?php checked( $show_price, 'true' ); ?> class="block-input">
				Show Price
			</label>
		</p>
		<?php endif; ?>

		<?php if ( post_type_exists( 'video' ) ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_duration' ) ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_duration' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_duration' ) ); ?>"
						<?php checked( $show_duration, 'true' ); ?> class="block-input">
					Show Duration
				</label>
			</p>
		<?php endif; ?>

		<?php if ( post_type_exists( 'event' ) ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_start_time' ) ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_start_time' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_start_time' ) ); ?>"
						<?php checked( $show_start_time, 'true' ); ?> class="block-input">
					Show Start Time (Events)
				</label>
			</p>
		<?php endif; ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_content_type' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_content_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_content_type' ) ); ?>"
					<?php checked( $show_content_type, 'true' ); ?> class="block-input">
				Show Content Type
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_taxonomy_signpost' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_taxonomy_signpost' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_taxonomy_signpost' ) ); ?>"
					<?php checked( $show_taxonomy_signpost, 'true' ); ?> class="block-input">
				Show Taxonomy Signpost
			</label>
		</p>

		<style>
			.js-post-collection-items-wrapper,
			.js-post-collection-items-wrapper .select2-input {
				display: block;
				width: 100%;
			}
		</style>

		<script>
		  jQuery(function ($) {
			var selections = [
				<?php
				if ( ! empty( $instance['collection_items'] ) ) {
					$items = explode( ',', $instance['collection_items'] );
					foreach ( $items as $item ) {
						if ( substr( $item, 0, 10 ) === 'post_type_' ) {
							$post = get_post( substr( $item, 10 ) );
							if ( ! $post instanceof WP_Post ) {
								continue;
							}

							echo "{id:'post_type_" . esc_js( $post->ID ) . "',text:'" . esc_js( get_post_type_object( $post->post_type )->labels->singular_name )  . ': ' . esc_js( $post->post_title ) . "'},";
						}

						if ( substr( $item, 0, 9 ) === 'taxonomy_' ) {
							$term = get_term( substr( $item, 9 ) );
							if ( ! $term instanceof WP_Term ) {
								continue;
							}

							echo "{id:'taxonomy_" . esc_js( $term->term_id ) . "',text:'" . esc_js( get_taxonomy( $term->taxonomy )->labels->singular_name ) . ': ' . esc_js( $term->name ) . "'},";
						}
					}
				}
				?>
			];

			$('.js-post-collection-items-<?php echo esc_attr( $id ); ?>').select2({
			  multiple: true,
			  placeholder: "Search for an item",
			  minimumInputLength: 1,
			  ajax: {
				url: ajaxurl,
				dataType: 'json',
				quietMillis: 250,
				data: function (term, page) {
				  return {
					action: 'post_collection_search_with_terms',
					search: term,
					post_type: 'post',
				  };
				},
				results: function (data, page) {
				  let myResults = [];
				  if (data.posts) {
					$.each(data.posts, function (index, item) {
					  var prefix = item.type === 'post_type' ? item.post_type : item.taxonomy;
					  myResults.push({
						'id': item.type + '_' + item.ID,
						'text': prefix + ': ' + item.title
					  });
					});
				  }
				  return {
					results: myResults
				  };
				},
				cache: true
			  },
			  allowClear: true,
			  initSelection: function (element, callback) {
				callback(selections);
			  }
			}).select2('val', []);
		  });
		</script>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$new_instance['show_author'] = isset( $new_instance['show_author'] ) ? 'true' : null;
		$new_instance['show_comment_count'] = isset( $new_instance['show_comment_count'] ) ? 'true' : null;
		$new_instance['show_date'] = isset( $new_instance['show_date'] ) ? 'true' : null;
		$new_instance['show_rating'] = isset( $new_instance['show_rating'] ) ? 'true' : null;
		$new_instance['show_price'] = isset( $new_instance['show_price'] ) ? 'true' : null;
		$new_instance['show_duration'] = isset( $new_instance['show_duration'] ) ? 'true' : null;
		$new_instance['show_start_time'] = isset( $new_instance['show_start_time'] ) ? 'true' : null;
		$new_instance['show_content_type'] = isset( $new_instance['show_content_type'] ) ? 'true' : null;
		$new_instance['show_taxonomy_signpost'] = isset( $new_instance['show_taxonomy_signpost'] ) ? 'true' : null;

		return $new_instance;
	}
}

add_action( 'widgets_init', function () {
	if ( ! defined( 'KEYSTONE_PREMIUM' ) || ! KEYSTONE_PREMIUM ) {
		return;
	}

	register_widget( 'Collection_Widget' );
} );
