<?php 
/*
Plugin Name: LearnPress Restrict Until Complete
Plugin URI: https://github.com/nickwilliamsnewby/learnpress-unlock-oncomplete
Description: adds meta boxes to all pages/posts to restrict access until an lpUser completes the selected course
Author: Nicholas Williams
Version: 1.0.0
Author URI: http://williamssoftwaresolutions.com
Tags: learnpress
Text Domain: learnpress
*/

if (!defined('ABSPATH')) {
    exit;
}

if(! defined( 'LP_ULOCK_ONCOMPLETE_PATH' ) ) define('LP_UNLOCK_ONCOMPLETE_PATH', dirname( __FILE__ ) );
if(! defined( 'LP_UNLOCK_ONCOMPLETE_FILE' ) ) define('LP_UNLOCK_ONCOMPLETE_FILE', ( __FILE__ ) );


if ( !defined('ABSPATH')) {
    exit;
}


class LP_Addon_Unlock_OnComplete{

	/**
	 * @var object
	 */
	private static $_instance = false;

	/**
	 * @var string
	 */
	private $_plugin_url = '';

	/**
	 * @var string
	 */
    private $_plugin_template_path = '';

    //protected $_meta_boxes = array();

    //protected $_post_type = '';



    function __construct(){
        //$this->_post_type = 'lp_unlock_oncomplete_cpt';
        $this->_tab_slug = sanitize_title( __( 'lp-unlock-oncomplete', 'learnpress' ) );
        $this->_plugin_template_path = LP_UNLOCK_ONCOMPLETE_PATH.'/template/';
        $this->_plugin_url  = untrailingslashit( plugins_url( '/', LP_UNLOCK_ONCOMPLETE_FILE ) );

        add_action('wp', array($this, 'restrict_until_complete_maybe2'));
        add_action('wp', array($this, 'add_menu_filter'));
        add_action( 'load-post.php', array( $this, 'add_unlock_oncomplete_meta_boxes' ), 0 );
        add_action( 'load-post-new.php', array( $this, 'add_unlock_oncomplete_meta_boxes' ), 0 );
        //add_filter('nav_menu_link_attributes', array($this,'lp_unlock_lock_nav_links_maybe'), 10, 3);
    }

    function add_menu_filter(){
        add_filter('nav_menu_link_attributes', array($this,'lp_unlock_lock_nav_links_maybe'), 10, 3);
    }

    function lp_unlock_lock_nav_links_maybe($atts, $item, $args){
        if( $args->menu == 'primary' ){
            $id = $item->object_id;
            $itemUnlocked = $this->lp_unlock_check_ze_page($id);
            if (!$itemUnlocked){
                $atts['style'] = 'display: none;';
            }
        }
        return $atts;
    }
/*
this function is to be called upon the 'wp' hook
it will check if the current page has chosen to be locked until the completetion 
of a course(s), if so and the course is not completed by the current use it will 
redirect the user to the home page
*/
    function restrict_until_complete_maybe2(){
        $pID = get_the_ID();
        $unlocked = $this->lp_unlock_check_ze_page($pID);
        if (!$unlocked){
            wp_redirect(get_site_url());
            exit;
        }

    }

    function lp_unlock_check_ze_page($cPageId){
        $cUser = learn_press_get_current_user();
        $unlockCourses = get_post_meta($cPageId, '_lp_unlock_oncomplete', false);
        $isUnlocked = true;
        if(sizeof($unlockCourses)<1){
            return $isUnlocked;
        } else {
            $userID = get_current_user_id();
            for($i = 0; $i < sizeof($unlockCourses[0]); $i++){
            $courseID = $unlockCourses[0][$i];
            $courseObj = LP_Course::get_course($courseID);
            $eval = $courseObj->evaluate_course_results($userID);
            if ($eval < $courseObj->passing_condition){
                $isUnlocked = false;
                }
            }
        }
        return $isUnlocked;
    }
//add metaboxes to the page editor to lock page by a course or courses
public function add_unlock_oncomplete_meta_boxes() {
    $prefix                                        = '_lp_';
    new RW_Meta_Box(
        apply_filters( 'learn_press_unlock_oncomplete_mb', array(
                'title'      => __( 'Learning Press Courses To Unlock Page', 'learnpress' ),
                'post_types' =>'page',
                'context'    => 'normal',
                'priority'   => 'high',
                'fields'     => array(
                    array(
                        'name'        => __( 'Courses', 'learnpress' ),
                        'id'          => "_lp_unlock_oncomplete",
                        'type'        => 'post',
                        'post_type'   => LP_COURSE_CPT,
                        'field_type'  => 'select',
                        'description' => 'Courses that need to be completed to access page',
                        'placeholder' => __( 'Course to Complete', 'learnpress' ),
                        'clone'       => true,
                        'sort_clone'  => true,
                        'std'         => ''
                    )
                )
            )
        )
    );
    }

	/**
	 * @return bool|LP_Addon_Unlock_OnComplete
	 */
	static function instance() {
		if ( !self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
    // load our text domain, not implemented currently but probably should for translation reasons
    static function load_text_domain() {
		if ( function_exists( 'learn_press_load_plugin_text_domain' ) ) {
			learn_press_load_plugin_text_domain( LP_LPATH_DASH_PATH, 'learnpress-learningpath-dashboard' );
		}
	}
}
//create an instance of our add - ons main class 
add_action( 'learn_press_loaded', array( 'LP_Addon_Unlock_OnComplete', 'instance' ) );
