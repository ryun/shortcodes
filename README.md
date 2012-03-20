Shortcode Parser
================

Install
-------

To install just upload to Pyrocms in the "Add-ons" section (like any other module), and edit your themes output. Here is an example below parsing the page modules layout theme (system/cms/themes/base/views/modules/pages/page.php)

Template Integration
--------------------

**Before:**

	echo $page->body;

**After:**
	
	echo Short_Parser::parse($page->body);

Shortcodes overview
-------------------

	// Example shortcode function
	function hello_world($attr, $content) {
		$defaults = array('title' => 'Hello World!');
	
		$vars  = array_merge($defaults, $atts);
		return 'The message: ' . $vars['title'];
	}

	// Register new shortcode function
	Short_Parser::register('hello_world', 'hello_world');

	// Or a class method
	Short_Parser::register('hello_world', Array('ExampleClassName', 'example_method');

	// Remove individual shorecodes
	Short_Parser::unregister('hello_world');

	// Clear all shortcodes
	Short_Parser::clear();

