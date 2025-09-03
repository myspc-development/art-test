<?php

namespace ArtPulse\Frontend;

class EventSubmissionShortcode {
	/**
	 * Transient key used when WooCommerce is unavailable.
	 */
	private const NOTICE_KEY = 'ap_event_notices';

	/**
	 * Store a notice for later display.
	 */
	protected static function add_notice( string $message, string $type = 'error' ): void {
		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $message, $type );
			return;
		}
		$notices   = get_transient( self::NOTICE_KEY ) ?: array();
		$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
		set_transient( self::NOTICE_KEY, $notices, defined( 'MINUTE_IN_SECONDS' ) ? MINUTE_IN_SECONDS : 60 );
	}

	/**
	 * Output any stored notices.
	 */
	protected static function print_notices(): void {
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
			return;
		}
		$notices = get_transient( self::NOTICE_KEY );
		if ( $notices ) {
			foreach ( $notices as $notice ) {
				$type    = esc_attr( $notice['type'] );
				$message = esc_html( $notice['message'] );
				echo "<div class='notice {$type}'>{$message}</div>";
			}
			delete_transient( self::NOTICE_KEY );
		}
	}

        /**
         * Redirect back to the form when possible.
         *
         * @param callable|null $redirect Optional redirect function.
         */
protected static function maybe_redirect( ?callable $redirect = null ): void {
    if ( function_exists( __NAMESPACE__ . '\wp_get_referer' ) ) {
        $target = wp_get_referer();
    } elseif ( function_exists( 'wp_get_referer' ) ) {
        $target = \wp_get_referer();
    } else {
        $target = false;
    }

    if ( ! $target ) {
        $target = \ArtPulse\Core\Plugin::get_event_submission_url();
    }

    if ( ! is_callable( $redirect ) ) {
        $redirect = __NAMESPACE__ . '\wp_safe_redirect';

        if ( ! function_exists( $redirect ) ) {
            if ( function_exists( 'wp_safe_redirect' ) ) {
                $redirect = '\wp_safe_redirect';
            } else {
                $redirect = null;
            }
        }
    }

    // Expose the intended target to tests when possible.
    if ( class_exists( __NAMESPACE__ . '\StubState' ) ) {
        StubState::$page = $target;
    }

    if ( is_callable( $redirect ) ) {
        $redirect( $target );

        // Only halt execution when using the global WordPress redirect.
        if ( '\wp_safe_redirect' === $redirect ) {
            exit;
        }
    } else {
        // No redirect handler available; throw so tests can detect the attempt.
        throw new \RuntimeException( 'redirect' );
    }
}

	public static function register() {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_submit_event', 'Submit Event', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) ); // Enqueue scripts and styles
		// Use a later priority so the handler runs during the same request
		// even though this callback is added while the `init` action is firing.
		add_action( 'init', array( self::class, 'maybe_handle_form' ), 20 ); // Handle form submission
	}

	public static function enqueue_scripts() {
		// Ensure the core UI styles are loaded
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You must be logged in to submit an event.', 'artpulse' ) . '</p>';
		}

		$user_id = get_current_user_id();

		$orgs = get_posts(
			array(
				'post_type'   => 'artpulse_org',
				'author'      => $user_id,
				'numberposts' => -1,
			)
		);

		$artists = get_posts(
			array(
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		$can_publish = current_user_can( 'publish_events' );

		ob_start();
		?>
		<div class="ap-form-messages" role="status" aria-live="polite">
			<?php self::print_notices(); ?>
		</div>

		<form method="post" enctype="multipart/form-data" class="ap-form-container" data-no-ajax="true">
			<?php wp_nonce_field( 'ap_submit_event', 'ap_event_nonce' ); ?>
			<input type="hidden" name="ap_submit_event" value="1" />

			<p>
				<label class="ap-form-label" for="ap_event_title">Event Title</label>
				<input class="ap-input" id="ap_event_title" type="text" name="event_title" required />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_description">Description</label>
				<textarea class="ap-input" id="ap_event_description" name="event_description" rows="5" required></textarea>
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_date">Date</label>
				<input class="ap-input" id="ap_event_date" type="date" name="event_date" required />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_start_date">Start Date</label>
				<input class="ap-input" id="ap_event_start_date" type="date" name="event_start_date" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_end_date">End Date</label>
				<input class="ap-input" id="ap_event_end_date" type="date" name="event_end_date" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_start_time">Start Time</label>
				<input class="ap-input" id="ap_event_start_time" type="time" name="event_start_time" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_end_time">End Time</label>
				<input class="ap-input" id="ap_event_end_time" type="time" name="event_end_time" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_recurrence">Recurrence Rule (iCal)</label>
				<input class="ap-input" id="ap_event_recurrence" type="text" name="event_recurrence_rule" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_location">Location</label>
				<input class="ap-input ap-google-autocomplete" id="ap_event_location" type="text" name="event_location" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_venue_name">Venue Name</label>
				<input class="ap-input" id="ap_venue_name" type="text" name="venue_name" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_street_address">Street Address</label>
				<input class="ap-input" id="ap_event_street_address" type="text" name="event_street_address" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_country">Country</label>
				<input class="ap-input" id="ap_event_country" type="text" name="event_country" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_state">State/Province</label>
				<input class="ap-input" id="ap_event_state" type="text" name="event_state" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_city">City</label>
				<input class="ap-input" id="ap_event_city" type="text" name="event_city" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_postcode">Postcode</label>
				<input class="ap-input" id="ap_event_postcode" type="text" name="event_postcode" />
			</p>

			<input type="hidden" name="address_components" id="ap_address_components" />

			<p>
				<label class="ap-form-label" for="ap_event_address">Address</label>
				<input class="ap-input" id="ap_event_address" type="text" name="event_address" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_contact">Contact Info</label>
				<input class="ap-input" id="ap_event_contact" type="text" name="event_contact" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_rsvp">RSVP URL</label>
				<input class="ap-input" id="ap_event_rsvp" type="url" name="event_rsvp_url" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_organizer_name">Organizer Name</label>
				<input class="ap-input" id="ap_event_organizer_name" type="text" name="event_organizer_name" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_organizer_email">Organizer Email</label>
				<input class="ap-input" id="ap_event_organizer_email" type="email" name="event_organizer_email" />
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_type">Event Type</label>
				<select class="ap-input" id="ap_event_type" name="event_type">
					<option value="">Select Type</option>
					<?php
					$terms = get_terms(
						array(
							'taxonomy'   => 'event_type',
							'hide_empty' => false,
						)
					);
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
					}
					?>
				</select>
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_org">Organization</label>
				<select class="ap-input" id="ap_event_org" name="event_org" required>
					<option value="">Select Organization</option>
					<?php foreach ( $orgs as $org ) : ?>
						<option value="<?php echo esc_attr( $org->ID ); ?>"><?php echo esc_html( $org->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_artists">Co-Host Artists</label>
				<select class="ap-input" id="ap_event_artists" name="event_artists[]" multiple>
					<?php foreach ( $artists as $artist ) : ?>
						<option value="<?php echo esc_attr( $artist->ID ); ?>"><?php echo esc_html( $artist->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label class="ap-form-label" for="ap_event_banner">Event Banner</label>
				<input class="ap-input" id="ap_event_banner" type="file" name="event_banner" />
			</p>

			<p>
				<label class="ap-form-label">Additional Images (max 5)</label>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<input class="ap-input" id="ap_event_image_<?php echo $i; ?>" type="file" name="image_<?php echo $i; ?>" />
				<?php endfor; ?>
			</p>

			<p>
				<label class="ap-form-label">
					<input class="ap-input" type="checkbox" name="event_featured" value="1" /> Request Featured
				</label>
			</p>

			<?php if ( $can_publish ) : ?>
			<p>
				<label><input type="radio" name="event_status" value="publish" checked> Publish Now</label>
				<label><input type="radio" name="event_status" value="draft"> Save as Draft</label>
				<label><input type="radio" name="event_status" value="future"> Schedule</label>
				<input type="datetime-local" name="event_publish_date" value="">
			</p>
			<?php else : ?>
			<input type="hidden" name="event_status" value="pending" />
			<p><em><?php echo esc_html__( 'Your event will be submitted for review.', 'artpulse' ); ?></em></p>
			<?php endif; ?>

			<p>
				<button class="ap-form-button nectar-button" type="submit" name="ap_submit_event">Submit Event</button>
			</p>
		</form>
		<?php
		return ob_get_clean();
	}

	public static function maybe_handle_form() {
		if ( ! is_user_logged_in() || ! isset( $_POST['ap_submit_event'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['ap_event_nonce'] ) || ! wp_verify_nonce( $_POST['ap_event_nonce'], 'ap_submit_event' ) ) {
			self::add_notice( __( 'Security check failed.', 'artpulse' ), 'error' );
			self::maybe_redirect();
			return;
		}

		$user_id = get_current_user_id();

		// Validate event data
		$event_title        = sanitize_text_field( $_POST['event_title'] );
		$event_description  = wp_kses_post( $_POST['event_description'] );
		$event_date         = sanitize_text_field( $_POST['event_date'] );
		$start_date         = sanitize_text_field( $_POST['event_start_date'] ?? '' );
		$end_date           = sanitize_text_field( $_POST['event_end_date'] ?? '' );
		$recurrence         = sanitize_text_field( $_POST['event_recurrence_rule'] ?? '' );
		$event_location     = sanitize_text_field( $_POST['event_location'] );
		$venue_name         = sanitize_text_field( $_POST['venue_name'] ?? '' );
		$street             = sanitize_text_field( $_POST['event_street_address'] ?? '' );
		$country            = sanitize_text_field( $_POST['event_country'] ?? '' );
		$state              = sanitize_text_field( $_POST['event_state'] ?? '' );
		$city               = sanitize_text_field( $_POST['event_city'] ?? '' );
		$postcode           = sanitize_text_field( $_POST['event_postcode'] ?? '' );
		$address_json       = wp_unslash( $_POST['address_components'] ?? '' );
		$address_components = json_decode( $address_json, true );
		$address_full       = sanitize_text_field( $_POST['event_address'] ?? '' );
		$start_time         = sanitize_text_field( $_POST['event_start_time'] ?? '' );
		$end_time           = sanitize_text_field( $_POST['event_end_time'] ?? '' );
		$contact_info       = sanitize_text_field( $_POST['event_contact'] ?? '' );
		$rsvp_url           = esc_url_raw( $_POST['event_rsvp_url'] ?? '' );
		$organizer_name     = sanitize_text_field( $_POST['event_organizer_name'] ?? '' );
		$organizer_email    = sanitize_email( $_POST['event_organizer_email'] ?? '' );
                $event_org          = intval( $_POST['event_org'] );
                $event_artists      = isset( $_POST['event_artists'] ) ? array_map( 'intval', (array) $_POST['event_artists'] ) : array();
                $event_type         = intval( $_POST['event_type'] ?? 0 );
                $featured           = isset( $_POST['event_featured'] ) ? '1' : '0';

                if ( empty( $event_description ) ) {
                        self::add_notice( __( 'Please enter an event description.', 'artpulse' ), 'error' );
                        self::maybe_redirect();
                        return;
                }

                // Ensure the selected organization belongs to the current user.
                $org_post = get_post( $event_org );
                if ( ! $org_post || $org_post->post_type !== 'artpulse_org' || (int) $org_post->post_author !== $user_id ) {
                        self::add_notice( 'Invalid organization selected.', 'error' );
                        self::maybe_redirect();
                        return;
                }

                // Validate start and end dates when both provided.
                if ( $start_date && $end_date && strtotime( $start_date ) > strtotime( $end_date ) ) {
                        self::add_notice( 'Start date cannot be later than end date.', 'error' );
                        self::maybe_redirect();
                        return;
                }

                $status_choice = sanitize_text_field( $_POST['event_status'] ?? 'publish' );
                $publish_date  = sanitize_text_field( $_POST['event_publish_date'] ?? '' );
                $can_publish   = current_user_can( 'publish_events' );
                $post_status   = $can_publish ? 'publish' : 'pending';
		$post_date     = null;

		if ( $can_publish ) {
			if ( $status_choice === 'draft' ) {
				$post_status = 'draft';
			} elseif ( $status_choice === 'future' ) {
				$post_status = 'future';
				if ( $publish_date ) {
					$post_date = $publish_date;
				} else {
					self::add_notice( __( 'Please provide a publish date.', 'artpulse' ), 'error' );
					self::maybe_redirect();
					return;
				}
			}
		}

		$data = array(
			'title'              => $event_title,
			'description'        => $event_description,
			'date'               => $event_date,
			'start_date'         => $start_date,
			'end_date'           => $end_date,
			'recurrence'         => $recurrence,
			'location'           => $event_location,
			'venue_name'         => $venue_name,
			'street'             => $street,
			'country'            => $country,
			'state'              => $state,
			'city'               => $city,
			'postcode'           => $postcode,
			'address_components' => $address_components ?: $address_json,
			'address_full'       => $address_full,
			'start_time'         => $start_time,
			'end_time'           => $end_time,
			'contact_info'       => $contact_info,
			'rsvp_url'           => $rsvp_url,
			'organizer_name'     => $organizer_name,
			'organizer_email'    => $organizer_email,
			'org_id'             => $event_org,
			'artists'            => $event_artists,
			'event_type'         => $event_type,
			'featured'           => $featured,
			'post_status'        => $post_status,
			'post_date'          => $post_date,
		);

		$post_id = EventService::create_event( $data, $user_id );

		if ( is_wp_error( $post_id ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Error creating event post: ' . $post_id->get_error_message() );
			}
			self::add_notice( $post_id->get_error_message(), 'error' );
			self::maybe_redirect();
			return;
		}

               // Handle banner and additional image uploads
               $image_ids       = array();
               $image_order     = array();
               $banner_id       = 0;
               $upload_had_error = false;

		if ( isset( $_POST['image_order'] ) ) {
			$image_order = array_map( 'intval', array_filter( explode( ',', (string) $_POST['image_order'] ) ) );
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

                // Handle Banner Upload
               if ( ! empty( $_FILES['event_banner']['name'] ) ) {
                       $attachment_id = media_handle_upload( 'event_banner', $post_id );

                       if ( ! is_wp_error( $attachment_id ) ) {
                               $image_ids[] = $attachment_id;
                               // Set the featured image
                               set_post_thumbnail( $post_id, $attachment_id );
                               $banner_id = $attachment_id;
                       } else {
                               if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                                       error_log( 'Error uploading banner: ' . $attachment_id->get_error_message() );
                               }
                               self::add_notice( __( 'Error uploading banner. Please try again.', 'artpulse' ), 'error' );
                               $upload_had_error = true;
                       }
               }

		// Handle Additional Images Upload
		$files   = array();
		$indices = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$key = 'image_' . $i;
			if ( ! empty( $_FILES[ $key ]['tmp_name'] ) ) {
				$files[ $i - 1 ] = array(
					'name'     => $_FILES[ $key ]['name'],
					'type'     => $_FILES[ $key ]['type'],
					'tmp_name' => $_FILES[ $key ]['tmp_name'],
					'error'    => $_FILES[ $key ]['error'],
					'size'     => $_FILES[ $key ]['size'],
				);
				$indices[]       = $i - 1;
			}
		}

		$order = array_values( array_unique( array_intersect( $image_order, $indices ) ) );
		foreach ( $indices as $idx ) {
			if ( ! in_array( $idx, $order, true ) ) {
				$order[] = $idx;
			}
		}

               foreach ( $order as $idx ) {
                       if ( ! isset( $files[ $idx ] ) ) {
                               continue;
                       }
                       $_FILES['ap_image'] = $files[ $idx ];
                       $attachment_id      = media_handle_upload( 'ap_image', $post_id );
                       if ( ! is_wp_error( $attachment_id ) ) {
                               $image_ids[] = $attachment_id;
                       } else {
                               if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                                       error_log( 'Error uploading additional image: ' . $attachment_id->get_error_message() );
                               }
                               self::add_notice( __( 'Error uploading additional image. Please try again.', 'artpulse' ), 'error' );
                               $upload_had_error = true;
                       }
               }
               unset( $_FILES['ap_image'] );

               // Handle Image Order (reordering logic)
               $final_image_ids = $image_ids;

               if ( ! empty( $image_order ) ) {
                       $reordered = array();

                       // Reorder images based on user-defined order
                       foreach ( $image_order as $image_id ) {
                               if ( in_array( $image_id, $image_ids, true ) ) {
                                       $reordered[] = $image_id;
                               }
                       }

                       // Add any remaining images that weren't in the order (append them)
                       foreach ( $image_ids as $image_id ) {
                               if ( ! in_array( $image_id, $reordered, true ) ) {
                                       $reordered[] = $image_id;
                               }
                       }

                       $final_image_ids = $reordered;
               }

                // Ensure the banner is included in the submission images and handle fallbacks.
                if ( $banner_id ) {
                        // Remove any existing occurrences so the banner can be prepended.
                        $final_image_ids = array_values( array_diff( $final_image_ids, array( $banner_id ) ) );
                        array_unshift( $final_image_ids, $banner_id );
                } elseif ( ! empty( $final_image_ids ) ) {
                        set_post_thumbnail( $post_id, $final_image_ids[0] );
                        $banner_id = $final_image_ids[0];
                }

               // Normalize types for meta updates and persist immediately so logs survive errors.
               $final_image_ids = array_values( array_map( 'intval', (array) $final_image_ids ) );
               $banner_id       = (int) $banner_id;

               // Always record gallery and banner meta before any potential early return.
               update_post_meta( $post_id, '_ap_submission_images', $final_image_ids );
               update_post_meta( $post_id, 'event_banner_id', $banner_id );

               // Handle upload errors after persisting meta data.
               if ( $upload_had_error ) {
                       self::maybe_redirect();
                       return;
               }

		// Success message and redirect
		$message = $post_status === 'pending'
			? __( 'Event submitted successfully! It is awaiting review.', 'artpulse' )
			: __( 'Event submitted successfully!', 'artpulse' );
		self::add_notice( $message, 'success' );
		self::maybe_redirect();
	}
}
