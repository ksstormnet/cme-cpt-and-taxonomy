/**
 * Fix for Personas admin menu functionality
 *
 * Since we're hiding the submenu with CSS, this script ensures
 * clicking on the main menu item correctly navigates to the personas listing.
 *
 * @package
 * @version    1.3.0
 */

jQuery(document).ready(function ($) {
	// Make the Personas menu item redirect directly to the list page
	$('#adminmenu li.menu-top.menu-icon-persona > a.menu-top').attr(
		'href',
		'edit.php?post_type=persona'
	);

	// Remove any click event handlers that might interfere with direct navigation
	$('#adminmenu li.menu-top.menu-icon-persona > a.menu-top').off('click');

	// Add a direct click handler
	$('#adminmenu li.menu-top.menu-icon-persona > a.menu-top').on(
		'click',
		function (e) {
			e.preventDefault();
			window.location.href = 'edit.php?post_type=persona';
		}
	);
});
