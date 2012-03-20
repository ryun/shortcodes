<?php

class Short_Parser {

    private static $instances = array();
    protected static $shortcodes = array();


    /**
     * Register a shortcode, and attach it to a PHP callback.
     *     *
     * @param string $shortcode The shortcode tag to map to the callback - normally in lowercase_underscore format.
     * @param callback $callback The callback to replace the shortcode with.
     */
    public function register($shortcode, $callback)
    {
        if (is_callable($callback))
            self::$shortcodes[$shortcode] = $callback;
    }

    /**
     * Check if a shortcode has been registered.
     *
     * @param string $shortcode
     * @return bool
     */
    public function registered($shortcode)
    {
        return array_key_exists($shortcode, self::$shortcodes);
    }

    /**
     * Remove a specific registered shortcode.
     *
     * @param string $shortcode
     */
    public function unregister($shortcode)
    {
        if ($this->registered($shortcode))
            unset(self::$shortcodes[$shortcode]);
    }

    /**
     * Remove all registered shortcodes.
     */
    public function clear()
    {
        self::$shortcodes = array();
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Parse a string, and replace any registered shortcodes within it with the result of the mapped callback.
     *
     * @param string $content
     * @return string
     */
    public function parse($content)
    {
		echo 'test';
        if (!self::$shortcodes)
            return $content;
        $shortcodes = implode('|', array_map('preg_quote', array_keys(self::$shortcodes)));
        $pattern = "/(.?)\[($shortcodes)(.*?)(\/)?\](?(4)|(?:(.+?)\[\/\s*\\2\s*\]))?(.?)/s";
		echo $pattern;
		return preg_replace_callback($pattern, array('Short_Parser', 'handleShortcode'), $content);
    }

    protected function handleShortcode($matches)
    {
        $prefix = $matches[1];
        $suffix = $matches[6];
        $shortcode = $matches[2];

        // allow for escaping shortcodes [[shortcode]]
        if ($prefix == '[' && $suffix == ']')
        {
            return substr($matches[0], 1, -1);
        }

        $attributes = array(); // Parse attributes into into this array.

        if (preg_match_all('/(\w+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/', $matches[3], $match, PREG_SET_ORDER))
        {
            foreach ($match as $attribute)
            {
                if (!empty($attribute[4]))
                {
                    $attributes[strtolower($attribute[1])] = $attribute[4];
                }
                elseif (!empty($attribute[3]))
                {
                    $attributes[strtolower($attribute[1])] = $attribute[3];
                }
            }
        }

        return $prefix . call_user_func(self::$shortcodes[$shortcode], $attributes, $matches[5], 'Short_Parser', $shortcode) . $suffix;
    }

}

class shortcodes {

    private static $instance = array();
    private static $reg = array();

    public static function rand($pre = false, $entropy = false)
    {
        if (!$pre)
            $pre = rand();
        return uniqid($pre, $entropy);
    }

    public static function init()
    {
        self::register_tabs();
    }

    /*
     * Tabs
     * ***************************************************** */

    function tabs($attr, $content)
    {
        self::$reg['tab_count'] = 0;
        $return = '';
        if (!isset($attr['name']))
            $attr['name'] = self::rand('tabs-');
        Short_Parser::parse($content);
        // dump( self::$reg['tabs']);
        if (isset(self::$reg['tabs']) && is_array(self::$reg['tabs']))
        {
            foreach (self::$reg['tabs'] as $id => $tab)
            {
                $id = $attr['name'] . '-' . $id;
                $tabs[] = '<li><a href="#' . $id . '">' . $tab['title'] . '</a></li>';
                $panes[] = '<div id="' . $id . '">' . $tab['content'] . '</div>';
            }
            $return .= '<div class="tabs"><ul>' . implode('', $tabs) . '</ul>' . implode("\n", $panes) . '</div>';
            if (!isset(self::$reg['tabs_script']))
            {
                $return .= '<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" type="text/css" rel="stylesheet" />';
                //$return .= '<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/themes/smoothness/jquery-ui.css" type="text/css" rel="stylesheet" />';
                $return .= '<script type="text/javascript" src="http://127.0.0.1/uapp/system/cms/themes/pyrocms/js/jquery/jquery-ui.min.js"></script>';
                $return .= '<script>$(function() {$( ".tabs" ).tabs();});</script>';
                self::$reg['tabs_script'] = true;
            }
        }
        // Unset globals
        unset(self::$reg['tabs'], self::$reg['tab_count']);

        return $return;
    }

    function tab($attr, $content)
    {
        extract($attr);
        self::$reg['tabs'][] = array('title' => sprintf($title, self::$reg['tab_count']), 'content' => Short_Parser::parse($content));
    }

    function toggle($attr, $content)
    {
      extract(array_merge(array(
                'title' => false,
                'open' => false,
                'fancy' => false,
                'icon' => true,
                'type' => ''), $attr));
        $classes = 'toggle-slide';
        if ($fancy) $classes .= ' alert-message '.$fancy;
        //toggle-body
        if (!$title) return;
        $icon = ($icon === TRUE) ? '<span class="sprite sp-plus"></span>':'';
        return '<h5 class="'.$classes.'">'.$icon.ucfirst($title).'</h5><div class="toggle-body">'. $content.'</div>';
    }
    /*
     * Accordion
     * ******************************************** */

    function spoiler($atts, $content = null)
    {
        extract(array_merge(array(
                    'title' => 'Spoiler title',
                    'open' => false,
                    'style' => 1), $atts));

        $open_display = ( $open ) ? ' style="display:block"' : '';

        return '<h3><a href="#">' . $title . '</a></h3><div class="spoiler-content"' . $open_display . '>' . Short_Parser::parse($content) . '</div>';
    }

    function accordion($atts = null, $content = null)
    {
        $return = '<div class="accordion">' . Short_Parser::parse($content) . '</div>';
        if (!isset(self::$reg['accord_script']))
        {
            $return .= '<script>$(function() {$( ".accordion" ).accordion();});</script>';
            self::$reg['accord_script'] = true;
        }
        return $return;
    }

    function alertblock_shortcode($atts, $content){
        $atts['block'] = true;
        return self::alert_shortcode($atts, $content);
    }
    function alert_shortcode($atts = null, $content = null)
    {
        extract(array_merge(array(
                'title' => false,
                'close' => false,
                'block' => false,
                'type' => ''), $atts));
        $out = '';
        $classes = 'alert-message';
        if ($block)
        {
            $classes .= ' block-message';
        }
        if ($type)
        {
            $classes .= ' ' . $type;
        }
        if ($title)
        {
            $out = '<h5>'.$title.'</h5>';
        }
        $out .= '<p>'. Short_Parser::parse($content) .'</p>';
        if ($close)
        {
            $out .= '<a class="close">X</a>';
        }
        return '<div class="'.$classes.'">' . $out . '</div>';

    }

    function register_tabs()
    {
        Short_Parser::register('tabs', array('shortcodes', 'tabs'));
        Short_Parser::register('tab', array('shortcodes', 'tab'));
        Short_Parser::register('accordion', array('shortcodes', 'accordion'));
        Short_Parser::register('panel', array('shortcodes', 'spoiler'));
        Short_Parser::register('alert', array('shortcodes', 'alert_shortcode'));
        Short_Parser::register('block', array('shortcodes', 'alertblock_shortcode'));
        Short_Parser::register('toggle', array('shortcodes', 'toggle'));
    }

}

shortcodes::init();