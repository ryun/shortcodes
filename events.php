<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Events_Shortcodes {

    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();

        Events::register('public_controller', array($this, 'run'));

    }

    public function run()
    {
        $this->ci->load->library('shortcodes/shortcodes');
    }

}

/* End of file events.php */