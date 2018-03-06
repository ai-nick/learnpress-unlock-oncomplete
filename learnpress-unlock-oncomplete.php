<?php 
/*
Plugin Name: LearnPress Restrict Until Complete
Plugin URI: https://github.com/nickwilliamsnewby/LearnPress-Learning-Path-Dashboard
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

    protected $_meta_boxes = array();

    protected $_post_type = '';

    protected $_tab_slug = 'lp-unlock-oncomplete';


    function __construct(){
        $this->_post_type = 'lp_unlock_oncomplete_cpt';
        $this->_tab_slug = sanitize_title( __( 'lp-unlock-oncomplete', 'learnpress' ) );
        $this->_plugin_template_path = LP_UNLOCK_ONCOMPLETE_PATH.'/template/';
        $this->_plugin_url  = untrailingslashit( plugins_url( '/', LP_UNLOCK_ONCOMPLETE_FILE ) );

        //add_action('init', array($this, 'create_learning_path'));
        add_action( 'load-post.php', array( $this, 'add_learning_path_meta_boxes' ), 0 );
        add_action( 'load-post-new.php', array( $this, 'add_learning_path_meta_boxes' ), 0 );
    }


//add metaboxes to the custom post type learn_press_learning_path_cpt
public function add_unlock_oncomplete_meta_boxes() {
    $prefix                                        = '_lp_';
    new RW_Meta_Box(
        apply_filters( 'learn_press_unlock_oncomplete_mb', array(
                'title'      => __( 'Learning Press Courses To Unlock Page', 'learnpress' ),
                'post_types' =>'post',
                'context'    => 'normal',
                'priority'   => 'high',
                'fields'     => array(
                    array(
                        'name'        => __( 'Courses', 'learnpress' ),
                        'id'          => "_lp_unlock_oncomplete",
                        'type'        => 'post',
                        'post_type'   => LP_COURSE_CPT,
                        //'multiple'    => true,
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
    // load our text domain, not implemented currently but should for translation reasons
    static function load_text_domain() {
		if ( function_exists( 'learn_press_load_plugin_text_domain' ) ) {
			learn_press_load_plugin_text_domain( LP_LPATH_DASH_PATH, 'learnpress-learningpath-dashboard' );
		}
	}
}
//create an instance of our add - ons main class 
add_action( 'learn_press_loaded', array( 'LP_Addon_Unlock_OnComplete', 'instance' ) );
