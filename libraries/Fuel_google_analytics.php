<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the
 * Codeigniter framework (http://codeigniter.com)
 */

// ------------------------------------------------------------------------

/**
 * Fuel Google Analytics object
 *
 * @package        FUEL CMS
 * @subpackage    Libraries
 * @category    Libraries
 */

// --------------------------------------------------------------------

// load in Analytics library
require_once ('Analytics.php');

class Fuel_google_analytics extends Fuel_advanced_module {
    
    public $name = "google_analytics"; // the folder name of the module
    public $start_date = 0; // the start date of what to display
    public $end_date = 0; // the start date of what to display
    protected $_analytics = NULL; // the analytics object
    
    
    /**
     * Constructor - Sets preferences
     *
     * The constructor can be passed an array of config values
     */
    function __construct($params = array()) {
        parent::__construct();
        $this->initialize($params);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Initialize the backup object
     *
     * Accepts an associative array as input, containing backup preferences.
     * Also will set the values in the config as properties of this object
     *
     * @access    public
     * @param    array    config preferences
     * @return    void
     */
    function initialize($params) {
        parent::initialize($params);
        $this->set_params($this->_config);
        $analytics_config = array('username' => $this->config('email'), 'password' => $this->config('password'));
        $this->_analytics = new Analytics($analytics_config);
        $this->_analytics->setProfileById('ga:' . $this->config('profile_id'));
        $this->_analytics->setDateRange($this->start_date, $this->end_date);
    }
    
    // --------------------------------------------------------------------
    
    function browsers() {
        $browsers = $this->_analytics->getBrowsers();
        $totals = array();
        
        $tt_total = 0;
        foreach($browsers as $browser => $count) {
            $version_pos = (strrpos($browser, ' Version'));
            $parsed_browser = (substr($browser, 0, $version_pos));
            if (!empty($parsed_browser)) {
                $tt_total = $tt_total + $count;
                if (isset($totals[$parsed_browser])) $totals[$parsed_browser] = $totals[$parsed_browser] + $count;
                else $totals[$parsed_browser] = $count;
                
            }
        }
        // Process Down
        
        $final = array();
        //print_p($browsers);
        foreach($totals as $k => $v) {
            // Convert Totals to percents
            $percent = round(($totals[$k] / $tt_total) * 100, 2);
            if ($percent > 1) { // Skip anything too small
                $final[] = array('label' => $k, 'data' => $percent);
            }
        }
        return ($final);
        
    }

/*    
    function device() {
        $device = $this->_analytics->getMobileDeviceInfo();
        // Process Down
        //print_p($device);
        
    }
*/    
    
    function os() {
        $aos = $this->_analytics->getOperatingSystem();
        
        $totals = array();
        
        $tt_total = 0;
        foreach($aos as $os => $count) {
            
            $parsed_os = $os;
            if (!empty($parsed_os)) {
                $tt_total = $tt_total + $count;
                if (isset($totals[$parsed_os])) $totals[$parsed_os] = $totals[$parsed_os] + $count;
                else $totals[$parsed_os] = $count;
                
            }
        }
        // Process Down
        $final = array();
        //print_p($browsers);
        foreach($totals as $k => $v) {
            // Convert Totals to percents
            $percent = round(($totals[$k] / $tt_total) * 100, 2);
            if ($percent > 1) { // Skip anything too small
                $final[] = array('label' => $k, 'data' => $percent);
            }
        }
        return($final);        
    }
    
    function visit() {
        
        $visit = $this->_analytics->getTimeOnSite();
        // Process Down
        //print_p($visit);
        
        
        
    }
    
    
    
    
    
    /**
     * Returns an array of visit plot points
     *
     * @access    public
     * @return    array
     */
    function visits() {
        $visits = $this->_analytics->getVisitors();
        $points = $this->_create_plot_array($visits);
        return $points;
    }
    
    // --------------------------------------------------------------------
    
    /**
     *Returns an array of view plot points
     *
     * @access    public
     * @return    public
     */
    function views() {
        $views = $this->_analytics->getPageviews();
        $points = $this->_create_plot_array($views);
        return $points;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Returns an array of plot points
     *
     * @access    protected
     * @param    array    data to be converted to plot points
     * @return    array
     */
    protected function _create_plot_array($data) {
        $views = $this->_analytics->getPageviews();
        $points = array();
        foreach($data as $date => $visit) {
            $year = substr($date, 0, 4);
            $month = substr($date, 4, 2);
            $day = substr($date, 6, 2);
            $utc = mktime(date('h') + 1, NULL, NULL, $month, $day, $year) * 1000;
            $points[] = array($utc, $visit);
        }
        return $points;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Returns the Analytics API object
     *
     * @access    public
     * @return    object
     */
    function analytics() {
        return $this->_analytics();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Magic method that simply calls the analytcs object
     *
     * @access    public
     * @return    mixed
     */
    function __call($method, $params) {
        return call_user_func_array(array($this->_analytics, $method), $params);
    }
}
