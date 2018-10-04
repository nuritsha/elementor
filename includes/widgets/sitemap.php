<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor sitemap widget.
 *
 * Elementor widget that displays the sitemap.
 *
 * $since 2.2.5
 */
class Widget_Sitemap extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve sitemap widget name.
	 *
	 * $since 2.2.5
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'sitemap';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve sitemap widget title.
	 *
	 * $since 2.2.5
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Sitemap', 'elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve sitemap widget icon.
	 *
	 * $since 2.2.5
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-sitemap';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the video widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.2.5
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'basic' ]; //todo: change to 'general'?
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.2.5
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'sitemap' ];
	}

	/**
	 * Register sitemap widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * $since 2.2.5
	 * @access protected
	 */
	protected function _register_controls() {


		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Sitemap', 'elementor' ),
                'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
            'map_pages',
            [
                'label' => __( 'Add pages to sitemap', 'elementor' ),
                'type' => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'map_posts',
            [
                'label' => __( 'Add posts grouped by category to sitemap', 'elementor' ),
                'type' => Controls_Manager::SWITCHER,
            ]
        );

        $this->add_control(
            'map_cpts',
            [
                'label' => __('Add custom post types to sitemap', 'elementor'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_cpt_array(),
                'default' => 'None',
            ]
        );

        $this->add_control(
            'exclude_pages',
            [
                'label' => __('Pages to exclude', 'elementor'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_pages_array(),
            ]
        );

        $this->add_control(
            'exclude_cats',
            [
                'label' => __('Categories to exclude', 'elementor'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_cats_array(),
            ]
        );

        $this->add_control(
			'password_protected',
			[
				'label' => __( 'Include Password protected posts & pages', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
			]
		);

        $this->add_control(
            'add_nofollow',
            [
                'label' => __( 'Add nofollow to links', 'elementor' ),
                'type' => Controls_Manager::SWITCHER,
            ]
        );

		$this->end_controls_section();

	}

    /**
     * @param string $default_none
     * @return array
     */
	private function get_cpt_array($default_none='none'){
        $args = array(
            'public'   => true,
            '_builtin' => false
        );

        $avail_cpts = get_post_types($args,'objects');

        $avail_cpts_arr = array($default_none=>__('None','elementor'));

        foreach ($avail_cpts as $cpt){
            $avail_cpts_arr[$cpt->ID] = $cpt->name;
        }

        return $avail_cpts_arr;
    }

    /**
      * @return array
     */
    private function get_cats_array(){

        $avail_cats = get_categories();

        $avail_cats_arr = array();

        foreach ($avail_cats as $avail_cat){
            $avail_cats_arr[$avail_cat->term_id] = $avail_cat->name . '[' .$avail_cat->term_id. ']';
        }

        return $avail_cats_arr;
    }

    /**
     * @return array
     */
    private function get_pages_array(){

        $avail_pages = get_pages();

        $avail_pages_arr = array();

        foreach ($avail_pages as $avail_page){
            $avail_pages_arr[$avail_page->ID] = $avail_page->post_title;
        }

        return $avail_pages_arr;
    }

    /**
	 * Render sitemap widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * $since 2.2.5
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

        $is_title_displayed = true;
        $is_category_title_wording_displayed = true;
        $display_post_only_once = false;
        $add_nofollow = $settings['add_nofollow']==='yes';
        $include_password_protected = $settings['password_protected']==='yes';
        $sort_categories=null;
        $sort=null;
        $order=null;
        $exclude_pages = $settings['exclude_pages'];
        $exclude_cats =  $settings['exclude_cats'];


        echo '<div class = nurit>';
        foreach ($exclude_pages as $key => $val){
            echo '- key: ' . $key . ' val: '.$val.'<br>';
        }
        echo '</div>';

        echo '<div class="elementor-sitemap">';

        if($settings['map_pages']) {
            echo $this->build_sitemap_pages($exclude_pages,$add_nofollow);
        }
        if($settings['map_posts']) {
            echo $this->build_sitemap_posts_by_cat($exclude_cats,$add_nofollow);
        }

        //todo: map cpts

        echo '</div>';


    }

    /**
     * @param array $exclude_cats
     * @param bool $add_nofollow
     * @param bool $is_title_displayed
     * @return string
     */
    private function build_sitemap_posts_by_cat($exclude_cats,$add_nofollow=false,$is_title_displayed = true){


        $return_str = '';

        $args = array();

        if(!empty($exclude_cats)){
            $args['exclude'] = $exclude_cats;
        }
        // Get the categories
        $cats = get_categories($args);

        // check it's not empty
        if (empty($cats)) {
            return '';
        }

// Get the categories
        $cats = $this->build_multi_array($cats);

// add content
        if ($is_title_displayed == true) {
            $return_str .= '<div class="elementor-sitemap-posts-title">' . __('Posts', 'elementor') . '</div>';
        }
        $return_str .= $this->sitemap_html_from_multi_array($cats,$add_nofollow);

        return $return_str;

    }

    private function build_multi_array( array $arr = array() , $parent = 0 ) {

        // check if not empty
        if (empty($arr)) {
            return array();
        }

        $pages = array();
        // go through the array
        foreach($arr as $k => $page) {
            if ($page->parent == $parent) {
                $page->sub = isset($page->sub) ? $page->sub : $this->build_multi_array($arr, $page->cat_ID);
                $pages[] = $page;
            }
        }

        return $pages;
    }

    private function sitemap_html_from_multi_array($cats,
                                                   $display_nofollow,
                                                   $useUL = true,
                                                   $display_post_only_once = true,
                                                   $display_cat_wording = true,
                                                   $category_title_wording_seperator = ': ',
                                                   $exclude_pages = array(),
                                                   $sort=null,
                                                   $order=null ) {

        // check if not empty
        if (empty($cats)) {
            return '';
        }

        $html = '';
        if ($useUL === true) {
            $html .= '<ul class="elementor-sitemap-posts-list">';
        }

        // display a nofollow attribute ?
        $attr_nofollow = ($display_nofollow==true ? ' rel="nofollow"' : '');

        // List all the categories
        foreach ($cats as $page) {
            // define category title & link:
            $cat_wording = $display_cat_wording ? sprintf('%s%s',__('Category','elementor'),$category_title_wording_seperator) : '';
            $category_link_display = sprintf('%s<a %s href="%s">%s</a>',$cat_wording,$attr_nofollow,get_category_link($page->cat_ID),$page->name);

            $html .= '<li><div class="elementor-sitemap-category-title">'.$category_link_display.'</div>';

            $post_by_cat = $this->sitemap_html_post_by_cat($page->cat_ID, $display_post_only_once, $display_nofollow, $exclude_pages, $sort, $order);

            // List of posts for this category
            $category_recursive = '';
            if (!empty($page->sub)) {
                // Use recursive function to get the childs categories
                $category_recursive = $this->sitemap_html_from_multi_array( $page->sub, $display_nofollow, $display_post_only_once, $display_cat_wording,
                    $display_nofollow, $exclude_pages, $sort, $order );
            }

            // display if it exist
            if ( !empty($post_by_cat) || !empty($category_recursive) ) {
                $html .= '<ul class="elementor-sitemap-posts-list">';
            }
            if ( !empty($post_by_cat) ) {
                $html .= $post_by_cat;
            }
            if ( !empty($category_recursive) ) {
                $html .= $category_recursive;
            }
            if ( !empty($post_by_cat) || !empty($category_recursive) ) {
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        if ($useUL === true) {
            $html .= '</ul>';
        }
        return $html;
    }

    private function sitemap_html_post_by_cat($cat_id, $display_post_only_once=true, $display_nofollow=false, $sitemap_exclude_pages=array(), $sort=null, $order=null ) {

        global $the_post_id;

        // init
        $html = '';

        // define the way the pages should be displayed
        $args = array();
        $args['numberposts'] = 999999;
        $args['cat'] = $cat_id;

        // exclude some pages ?
        if (!empty($sitemap_exclude_pages)) {
            $args['exclude'] = $sitemap_exclude_pages;
        }

        // change the sort order
        if ($sort!==null) {
            $args['orderby'] = $sort;
        }
        if ($order!==null) {
            $args['order'] = $order;
        }

        // List of posts for this category
        $the_posts = get_posts( $args );

        // check if not empty
        if (empty($the_posts)) {
            return '';
        }

        $attr_nofollow = ($display_nofollow==true) ? 'rel="nofollow"' : '';

         // list the posts
        foreach ( $the_posts as $the_post ) {
            // Display the line of a post
            $get_category = get_the_category($the_post->ID);

            // Display the post only if it is on the deepest category
            if ( $get_category[0]->cat_ID == $cat_id ) {

                // get post ID
                $the_post_id = $the_post->ID;

                // replace the ID by the real value
                $html .= '<li class="elementor-sitemap-post">'.
                    sprintf('<a %s href="%s">%s</a>',$attr_nofollow,get_permalink($the_post_id),get_the_title($the_post_id))
                    .'</li>';
            }
        }

        return $html;
    }


    /**
     * Add nofollow attribute to the links of the wp_list_pages() functions
     *
     * @param str $output content
     * @return str
     */
    public function elementor_sitemap_add_no_follow_to_links($output) {
        //return wp_rel_nofollow($output);
        return str_replace('<a href=', '<a rel="nofollow" href=',  $output);
    }

    private function build_sitemap_pages($exclude_pages,
                                         $display_nofollow,
                                         $is_title_displayed = true,
                                         $is_get_only_private = false,
                                         $sort = null) {

        // init
        $return_str = '';

        if ($display_nofollow==true) {
            add_filter('wp_list_pages', array($this,'elementor_sitemap_add_no_follow_to_links'));
        }

        // define the way the pages should be displayed
        $args = array();
        $args['title_li'] = '';
        $args['echo']     = '0';

        // change the sort
        if ($sort!==null) {
            $args['sort_column'] = $sort;
        }

        // exclude some pages ?
        if (!empty($exclude_pages)) {
          $args['exclude'] = implode(',',$exclude_pages);
        }

        // get only the private content
        if ($is_get_only_private==true) {
            $args['post_status'] = 'private';
        }

        // get data
        $list_pages = wp_list_pages($args);

        // check it's not empty
        if (empty($list_pages)) {
            return '';
        }

        // add content
        if ($is_title_displayed==true) {
            $return_str .= '<h2 class="elementor-sitemap-pages-title">'.__('Pages', 'elementor').'</h2>';
        }
        $return_str .= '<ul class="elementor-sitemap-pages-list">';
        $return_str .= $list_pages;
        $return_str .= '</ul>';

        // return content
        return $return_str;
    }

}


