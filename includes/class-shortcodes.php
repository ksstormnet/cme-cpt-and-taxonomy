<?php
/**
 * Shortcodes functionality.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * Shortcodes class.
 *
 * This class handles all shortcode functionality.
 *
 * @since      1.0.3
 * @package    CME_CPT_Taxonomy
 */
class Shortcodes {

	/**
	 * Register shortcodes.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register(): void {
		add_shortcode( 'cme-persona-rotator', array( $this, 'persona_rotator_shortcode' ) );
	}

	/**
	 * Persona rotator shortcode callback.
	 *
	 * @since    1.0.0
	 * @param    array $atts    Shortcode attributes.
	 * @return   string          Shortcode output.
	 */
	public function persona_rotator_shortcode( $atts ): string {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'limit' => 3,    // Number of personas to show
				'speed' => 5000, // Rotation speed in milliseconds
			),
			$atts
		);

		// Get all personas
		$personas = get_posts(
			array(
				'post_type'      => 'persona',
				'posts_per_page' => intval( $atts['limit'] ),
				'orderby'        => 'rand',
			)
		);

		if ( empty( $personas ) ) {
			return '<p>' . __( 'No personas found.', 'cme-cpt-and-taxonomy' ) . '</p>';
		}

		// Start output buffer
		ob_start();

		// Enqueue necessary styles and scripts
		$this->enqueue_rotator_assets();

		// Generate a unique ID for this rotator instance
		$rotator_id = 'cme-persona-rotator-' . wp_rand();

		// Open rotator container
		echo '<div id="' . esc_attr( $rotator_id ) . '" class="cme-persona-rotator">';

		foreach ( $personas as $persona ) {
			// Get persona data
			$title   = get_the_title( $persona );
			$excerpt = get_the_excerpt( $persona );

			// Get gender-specific image
			$genders       = array( 'male', 'female', 'indeterminate' );
			$random_gender = $genders[ array_rand( $genders ) ];

			// Get image ID for this gender
			$image_id = get_post_meta( $persona->ID, 'persona_image_' . $random_gender, true );

			if ( ! $image_id ) {
				// Fallback to featured image if gender-specific image not found
				$image_id = get_post_thumbnail_id( $persona );
			}

			$image_url = wp_get_attachment_image_url( $image_id, 'large' );
			if ( ! $image_url ) {
				$image_url = plugin_dir_url( CME_PLUGIN_FILE ) . 'assets/images/placeholder.png';
			}

			// Output persona slide
			echo '<div class="cme-persona-slide">';
			echo '<div class="cme-persona-image-container">';
			echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" />';
			echo '<div class="cme-persona-overlay">';
			echo '<h3>' . esc_html( $title ) . '</h3>';
			echo '<div class="cme-persona-excerpt">' . wp_kses_post( $excerpt ) . '</div>';
			echo '</div>'; // overlay
			echo '</div>'; // image container
			echo '</div>'; // slide
		}

		echo '</div>'; // rotator container

		// Add inline script for rotating
		?>
		<script>
		(function() {
			document.addEventListener('DOMContentLoaded', function() {
				const rotator = document.getElementById('<?php echo esc_js( $rotator_id ); ?>');
				const slides = rotator.querySelectorAll('.cme-persona-slide');
				let currentSlideIndex = 0;

				// Hide all slides except the first one
				for (let i = 1; i < slides.length; i++) {
					slides[i].style.display = 'none';
				}

				// Set up rotation if more than one slide
				if (slides.length > 1) {
					setInterval(function() {
						slides[currentSlideIndex].style.display = 'none';
						currentSlideIndex = (currentSlideIndex + 1) % slides.length;
						slides[currentSlideIndex].style.display = 'block';
					}, <?php echo intval( $atts['speed'] ); ?>);
				}
			});
		})();
		</script>
		<?php

		// Return output buffer content
		return ob_get_clean();
	}

	/**
	 * Enqueue assets for the rotator.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	private function enqueue_rotator_assets(): void {
		// Add inline styles for the rotator
		wp_add_inline_style(
			'wp-block-library',
			'
            .cme-persona-rotator {
                position: relative;
                overflow: hidden;
                margin: 0 auto;
                max-width: 800px;
            }
            .cme-persona-slide {
                width: 100%;
            }
            .cme-persona-image-container {
                position: relative;
            }
            .cme-persona-image-container img {
                width: 100%;
                height: auto;
                display: block;
            }
            .cme-persona-overlay {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 15px;
            }
            .cme-persona-overlay h3 {
                margin: 0 0 10px;
                color: white;
            }
            .cme-persona-excerpt {
                font-size: 14px;
            }
        '
		);
	}
}
