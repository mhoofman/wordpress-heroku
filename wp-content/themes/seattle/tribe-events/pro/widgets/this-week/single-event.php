<?php
/**
 * This Week Single Event
 * This file loads the this week widget single event
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/widgets/this-week/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>
<div id="tribe-events-event-<?php echo esc_attr( $event->ID ); ?>" class="<?php tribe_events_event_classes( $event->ID ) ?> tribe-this-week-event" >

	<h2 class="entry-title summary">
		<a href="<?php echo esc_url( tribe_get_event_link( $event->ID ) ); ?>" rel="bookmark"><?php echo esc_html( $event->post_title ); ?></a>
	</h2>

	<div class="duration">
		<?php echo tribe_events_event_schedule_details( $event->ID ) ?>
	</div>
	<div class="fn org tribe-venue">
		<?php echo tribe_get_venue ( $event->ID ); ?>
	</div>
	<div class="fn org tribe-address">
		<?php echo tribe_get_map_link_html ( $event->ID ); ?>
	</div>

</div>