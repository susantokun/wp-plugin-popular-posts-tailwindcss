<?php

/**
 * Plugin Name: Popular Posts TailwindCSS
 * Plugin URI: https://github.com/susantokun/wp-plugin-popular-posts-tailwindcss.git
 * Description: Popular Posts Custom with Tailwind CSS.
 * Version: 2.0
 * Author: Susantokun
 * Author URI: https://www.susantokun.com/
 */

class SPopularPostsTailwindCSS extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            's_popular_posts_tailwindcss',
            __('Popular Posts TailwindCSS', 'susantokun'),
            ['description' => __('Your site&#8217;s most popular posts.', 'susantokun'), ]
        );
    }

    public function widget($args, $instance)
    {
        $title  = apply_filters('widget_title', $instance['title']);
        $number = (!empty($instance['number'])) ? absint($instance['number']) : 5;
        if (!$number) {
            $number = 5;
        }
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

        $r = new WP_Query(
            apply_filters(
                'widget_posts_args',
                [
                    'posts_per_page' => $number,
                    'meta_key'       => 'susantokun_post_views_count',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                ],
                $instance
            )
        );
        if (!$r->have_posts()) {
            return;
        }
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        // output
        echo '<ul>';
        foreach ($r->posts as $popular_post) {
            $post_title = get_the_title($popular_post->ID);
            $title      = (!empty($post_title)) ? $post_title : __('(no title)', 'susantokun');
            if ($show_date) : ?>
                <li class="with-date">
                    <a href="<?php the_permalink($popular_post->ID); ?>"><?php echo $title; ?></a>
                    <div class="widget-date">
                        <span class="mr-1 text-2xs icon-calendar"></span>
                        <span class="text-xs"><?php echo get_the_date('j F Y', $popular_post->ID); ?></span>
                        <span class="ml-2 mr-1 text-xs icon-eye"></span>
                        <span class="text-xs"><?php echo susantokun_get_post_views($popular_post->ID); ?></span>
                    </div>
                </li>
            <?php else : ?>
                <li>
                    <a href="<?php the_permalink($popular_post->ID); ?>"><?php echo $title; ?></a>
                </li>
        <?php endif;
        }
        echo '</ul>';
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title     = isset($instance['title']) ? esc_attr($instance['title']) : __('Popular Posts', 'susantokun');
        $number    = isset($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false; ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
            <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?'); ?></label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance              = $old_instance;
        $instance['title']     = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number']    = (int) $new_instance['number'];
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        return $instance;
    }
}

function register_SPopularPostsTailwindCSS()
{
    register_widget('SPopularPostsTailwindCSS');
}
add_action('widgets_init', 'register_SPopularPostsTailwindCSS');
