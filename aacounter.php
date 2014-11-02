<?php 

/**
 * Plugin Name: AA Counter
 * Plugin URI: http://wordpress.org/aaplayer
 * Description: AA is a free counter 
 * Version: 1.0
 * Author: aaextention
 * Author URI: http://webdesigncr3ator.com
 * Support Email : contactus.aa@gmail.com
 * License: GPL2
 **/
	
	
	//algoridom
	//make a table ***
	//id , ip , country , visit ****
	//get current visior ip**
	//get country from ip by json from http://ip-api.com/json**
	//check this ip is exists or not **
	//if not than add count**
	//make a widget 
	//make a option page
	
	
	/////////////////////////////
	/////////////////////////////
	///////Making a table////////
		global $wpdb;

		/*
		* We'll set the default character set and collation for this table.
		* If we don't do this, some characters could end up being converted 
		* to just ?'s when saved in our table.
		*/


		 $sql = "CREATE TABLE counter (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		ip varchar(50) NOT NULL,
		country varchar(25) NOT NULL,
		visit int(55),
		UNIQUE KEY id (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		

	///get user ip
		
function aa_getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}
$user_ip = aa_getUserIP();
	
	//get data from api as json formet 
			$json = file_get_contents('http://ip-api.com/json/'.$user_ip);
			$obj = json_decode($json);
			

	//get user country	
		$usr_country = $obj->countryCode;
		
	//check ip exists or not 
		$row = $wpdb->get_row("SELECT * FROM counter WHERE ip='$user_ip'");
	
	//if not than add count
		if($row==NULL){
				$wpdb->insert( 
					'counter', 
					array( 
						'ip' => $user_ip, 
						'country' => $usr_country
					) 
				);
		
		}
	//making a widget
	//sql 
	//SELECT country, count(country) AS "total" FROM counter AS A1 GROUP BY country /
	
	
	/**
 * Adds aa_counter widget.
 */
class aa_counter extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'aa_counter', // Base ID
			__('Visitor Counter', 'text_domain'), // Name
			array( 'description' => __( 'Visitor Counter', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
     	        echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
	
	//widget
		global $wpdb;
			$row = $wpdb->get_results('SELECT country, count(country) AS "total" FROM counter AS A1 GROUP BY country ');
			if(is_array($row)){
				echo "<ul>";
				foreach($row as $r){
					if(!empty($r->country)){
						echo "<li>{$r->country} {$r->total}</li>";
					}
					else{
						echo "<li>Other {$r->total}</li>";
					}
				
				}
				echo "</ul>";
			
			}
	
	
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class aa_counter


// register aa_counter widget
function register_aa_counter() {
    register_widget( 'aa_counter' );
}
add_action( 'widgets_init', 'register_aa_counter' );



	//now time for making a option page
	
	
	
	
	
class aa_MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Counter', 
            'Counter Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Counter Settings</h2>           
            <form method="post" action="">
			
            <?php
				if(isset($_POST['submit'])){
					global $wpdb;
					$row = $wpdb->get_results('DELETE FROM counter');
					echo "Successfully reset";
				}
			// This prints out all hidden setting fields
                submit_button("RESET"); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
	

          
    }

 

}

if( is_admin() )
    $my_settings_page = new aa_MySettingsPage();