<?php

/**
 * Plugin Name: Susantokun - Popular Posts Tailwind CSS
 * Plugin URI: https://github.com/susantokun/wp-plugin-popular-posts-tailwindcss.git
 * Description: Popular Posts Custom with Tailwind CSS.
 * Version: 1.0
 * Author: Susantokun
 * Author URI: https://www.susantokun.com/
 */

class WP_Widget_Popular_Posts_Custom extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname'                   => 'widget_popular_entries',
            'description'                 => __('Your site&#8217;s most popular Posts Custom.'),
            'customize_selective_refresh' => true,
        );
        parent::__construct('popular-posts', __('Susantokun - Popular Posts Custom'), $widget_ops);
        $this->alt_option_name = 'widget_popular_entries';

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    public function widget($args, $instance)
    {
        if (! isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        $title = (! empty($instance['title'])) ? $instance['title'] : __('POS-POS POPULER');

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $number = (! empty($instance['number'])) ? absint($instance['number']) : 5;
        if (! $number) {
            $number = 5;
        }
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

        $r = new WP_Query(
            apply_filters(
                'widget_posts_args',
                array(
                    'posts_per_page' => $number, 
                    'meta_key' => 'susantokun_post_views_count', 
                    'orderby' => 'meta_value_num', 
                    'order' => 'DESC'
                ),
                $instance
            )
        );

        if (! $r->have_posts()) {
            return;
        } ?>
		<?php echo $args['before_widget']; ?>
		<?php
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        } ?>
			<?php foreach ($r->posts as $popular_post) : ?>
				<?php
                $post_title   = get_the_title($popular_post->ID);
        $title        = (! empty($post_title)) ? $post_title : __('(no title)');
        $aria_current = '';

        if (get_queried_object_id() === $popular_post->ID) {
            $aria_current = ' aria-current="page"';
        } ?>
				<div class="w-full text-sm truncate">
					<a class="font-medium hover:text-blue-light" title="<?php echo $title; ?>" href="<?php the_permalink($popular_post->ID); ?>"<?php echo $aria_current; ?>><?php echo $title; ?></a>
					<?php if ($show_date) : ?>
						<div class="w-full text-xs flex items-center">
              <svg class="h-3 w-auto inline-block pr-1" aria-hidden="true" focusable="false" data-prefix="far" data-icon="calendar" class="svg-inline--fa fa-calendar fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 64h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V160h352v298c0 3.3-2.7 6-6 6z"></path></svg>
              <?php echo get_the_date('', $popular_post->ID); ?>
              <svg class="h-3 w-auto inline-block pl-2 pr-1" aria-hidden="true" focusable="false" data-prefix="far" data-icon="eye" class="svg-inline--fa fa-eye fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M288 144a110.94 110.94 0 0 0-31.24 5 55.4 55.4 0 0 1 7.24 27 56 56 0 0 1-56 56 55.4 55.4 0 0 1-27-7.24A111.71 111.71 0 1 0 288 144zm284.52 97.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400c-98.65 0-189.09-55-237.93-144C98.91 167 189.34 112 288 112s189.09 55 237.93 144C477.1 345 386.66 400 288 400z"></path></svg>
              <?php echo susantokun_get_post_views($popular_post->ID); ?>
            </div>
					<?php endif; ?>
				</div>
        <div class="h-px mx-auto bg-body w-full opacity-75 my-2"></div>
			<?php endforeach; ?>
		<?php
        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance)
    {
        $instance              = $old_instance;
        $instance['title']     = sanitize_text_field($new_instance['title']);
        $instance['number']    = (int) $new_instance['number'];
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        return $instance;
    }

    public function form($instance)
    {
        $title     = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number    = isset($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false; ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
		<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?'); ?></label></p>
		<?php
    }
}

function susantokun_set_post_views($postID) {
    $count_key = 'susantokun_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function susantokun_track_post_views ($post_id) {
    if ( !is_single() ) return;
    if ( empty ( $post_id) ) {
        global $post;
        $post_id = $post->ID;    
    }
    susantokun_set_post_views($post_id);
}
add_action( 'wp_head', 'susantokun_track_post_views');

function susantokun_get_post_views($postID){
    $count_key = 'susantokun_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count;
}

// echo susantokun_get_post_views(get_the_ID());

function register_WP_Widget_Popular_Posts_Custom()
{
    register_widget('WP_Widget_Popular_Posts_Custom');
}
add_action('widgets_init', 'register_WP_Widget_Popular_Posts_Custom');