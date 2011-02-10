<?php
/**
 * @package View All Pages
 * @author Michael Pretty
 * @version 0.1.0
 */
/*
Plugin Name: View All Pages
Plugin URI: http://vocecommunications.com/services/web-development/wordpress/plugins/view-all-pages/
Description: Adds a "All Pages" link to the wp_link_pages output that will show all pages for a post on singe page.
Author: Michael Pretty (prettyboymp)
Version: 0.1.1
Author URI: http://voceconnect.com
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

class View_All_Pages {
	public function initialize() {
		add_action('parse_query', array($this, 'on_parse_query'));
		add_action('the_post', array($this, 'on_action_the_post'));
		add_filter('wp_link_pages_args', array($this, 'filter_wp_link_pages_args'));
	}

	/**
	 * Sets up a special query_var to mark to view all pages before query_posts converts the empty page var into 0
	 * making it indistinguishable
	 *
	 * @param WP_Query $wp_query
	 */
	public function on_parse_query($wp_query) {
		if(isset($wp_query->query_vars['page']) && '0' === trim($wp_query->query_vars['page'], '/')) {
			$wp_query->query_vars['all-pages'] = true;
		}
	}

	public function on_action_the_post($post) {
		global $id, $authordata, $day, $currentmonth, $page, $pages, $multipage, $more, $numpages;
		if(true === get_query_var('all-pages') && $multipage) {
			$page = 0;
			$pages[-1] = $post->post_content; //we're setting it to -1 since WP uses $page - 1 to pull from the pages array
		}
	}

	public function filter_wp_link_pages_args($r) {
		global $multipage, $post, $more, $page;
		if($multipage) {
			$text = str_replace('%', isset($r['allpageslink']) ? $r['allpageslink'] : __('All Pages', 'view-all-pages'), $r['pagelink']);
			if(true === get_query_var('all-pages')) {
				$link = '';
				$link_close = '';
			} else {
				if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) ) {
					$link = '<a href="' . add_query_arg('page', 0, get_permalink()) . '">';
				} elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID ) {
					$link = '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit('page/0', 'single_paged'). '">';
				} else {
					$link = '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit('0', 'single_paged') . '">';
				}
				$link_close = '</a>';
			}
			$all_pages_link = "{$link}{$r['link_before']}{$text}{$r['link_after']}{$link_close}";

			if ( 'number' == $r['next_or_number']) {
				$r['after'] = ' ' . $all_pages_link . $r['after'];
			} else {
				$r['before'] .= $all_pages_link;
				if(true === get_query_var('all-pages')) {
					//hack to keep Previous page link from going to /-1/ since WP checks if the $page == 1 instead of $page < 2
					$page = 1;
					$r['before'] .= '<a href="' . get_permalink() . '">' .$r['link_before']. (isset($r['firstpagelink']) ? $r['firstpagelink'] : __('First page', 'view-all-pages') ) . $r['link_after'] . '</a>';
				}
			}
		}
		return $r;
	}

}
add_action('init', array(new View_All_Pages(), 'initialize'));
