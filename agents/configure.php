<?php
/**
 * change parameters for agents
 *
 * Use this script to modify following parameters:
 *
 * - [code]with_referrals[/code] - if set to Yes, show referrals to everybody.
 * Else show referrals only to associates.
 *
 * - [code]debug_messages[/code] - if set to Yes, then processed messages will be saved
 * for debugging purpose.
 * Used in [script]agents/messages.php[/script].
 *
 * - [code]mail_queues[][/code] - An array of mail accounts to poll.
 * Each entry has a name, and following attributes: server network address, account name, account password,
 * allowed senders, security match, default section, processing options, processing hooks, prefix boundary, suffix boundary.
 * Used in [script]agents/messages.php[/script].
 *
 * - [code]uploads_nick_name[/code] - the nick name to be impersonated for tools that do not transmit user information (handx weblog).
 * Used in [script]agents/uploads.php[/script].
 *
 * - [code]uploads_anchor[/code] - the section where to post articles by default.
 * Used in [script]agents/uploads.php[/script].
 *
 * Configuration information is saved into [code]parameters/agents.include.php[/code].
 * If YACS is prevented to write to the file, it displays parameters to allow for a manual update.
 *
 * The file [code]parameters/agents.include.php.bak[/code] can be used to restore
 * the active configuration before the last change, if necessary.
 *
 * If the file [code]demo.flag[/code] exists, the script assumes that this instance
 * of YACS runs in demonstration mode.
 * In this mode the edit form is displayed, but parameters are not saved in the configuration file.
 *
 * @author Bernard Paques [email]bernard.paques@bigfoot.com[/email]
 * @author GnapZ
 * @reference
 * @tester Guillaume Perez
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// common definitions and initial processing
include_once '../shared/global.php';

// load localized strings
i18n::bind('agents');

// load the skin
load_skin('agents');

// the path to this page
$context['path_bar'] = array( 'control/' => i18n::s('Control Panel') );

// the title of the page
$context['page_title'] = i18n::s('The configuration panel for agents');

// the back button
$context['page_menu'] = array_merge($context['page_menu'], array( 'agents/' => i18n::s('The index page for agents') ));

// do it again
if(isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST'))
	$context['page_menu'] = array_merge($context['page_menu'], array( 'agents/configure.php' => i18n::s('Edit') ));

// anonymous users are invited to log in or to register
if(!Surfer::is_logged())
	Safe::redirect($context['url_to_home'].$context['url_to_root'].'users/login.php?url='.urlencode('agents/configure.php'));

// only associates can proceed
elseif(!Surfer::is_associate()) {
	Safe::header('Status: 403 Forbidden', TRUE, 403);
	Skin::error(i18n::s('You are not allowed to perform this operation.'));

// display the input form
} elseif(isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {

	// load current parameters, if any
	Safe::load('parameters/agents.include.php');

	// the form
	$context['text'] .= '<form method="post" action="'.$context['script_url'].'" id="main_form"><div>';

	// the splash message
	$context['text'] .= '<p>'.i18n::s('Use this page to configure agents, that is, scripts executed in the background without any human interaction.')."</p>\n";

	// referrals
	$context['text'] .= Skin::build_block(i18n::s('Referrals'), 'title');

	// with referrals
	$label = i18n::s('Display referrals:');
	$input = '<input type="radio" name="with_referrals" value="N"';
	if(!isset($context['with_referrals']) || ($context['with_referrals'] != 'Y'))
		$input .= ' checked="checked"';
	$input .= EOT.' '.i18n::s('only to associates');
	$input .= BR.'<input type="radio" name="with_referrals" value="Y"';
	if(isset($context['with_referrals']) && ($context['with_referrals'] == 'Y'))
		$input .= ' checked="checked"';
	$input .= EOT.' '.i18n::s('to every surfer');
	$context['text'] .= '<p>'.$label.BR.$input."</p>\n";

	// inbound mail queues
	$context['text'] .= Skin::build_block(i18n::s('Inbound messages'), 'title');

	// debug_messages
	$checked = '';
	if(isset($context['debug_messages']) && ($context['debug_messages'] == 'Y'))
		$checked = 'checked="checked" ';
	$context['text'] .= '<p><input type="checkbox" name="debug_messages" value="Y" '.$checked.'/> '.i18n::s('Debug mail protocol in temporary/debug.txt, and file each message processed by agents/messages.php. Use this option for troubleshooting only.')."</p>\n";

	// introduction to the queue list
	$context['text'] .= '<p>'.i18n::s('Below is the list of mail accounts from which messages can be fetched and posted in the database.')."</p>\n";

	// list existing queues
	if(isset($context['mail_queues']) && is_array($context['mail_queues'])) {
		$count = 0;
		foreach($context['mail_queues'] as $name => $attributes) {

			// queues list
			$count++;
			$context['text'] .= '<p><b>'.sprintf(i18n::s('Queue #%d'), $count)."</b></p>\n";

			list($server, $account, $password, $allowed, $match, $section, $options, $hooks, $prefix, $suffix) = $attributes;

			$label = i18n::s('Nick name');
			$input = '<input type="text" name="mail_queue_names[]" size="32" value="'.encode_field($name).'" maxlength="64" />';
			$hint = i18n::s('Delete to suppress this queue entry');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Server name');
			$input = '<input type="text" name="mail_queue_servers[]" size="45" value="'.encode_field($server).'" maxlength="255" />';
			$hint = i18n::s('Use either the network name (e.g., \'pop.foo.bar\') or the IP address of the mail server');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Account name');
			$input = '<input type="text" name="mail_queue_accounts[]" size="45" value="'.encode_field($account).'" maxlength="255" />';
			$hint = i18n::s('The POP3 user name');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Password');
			$input = '<input type="password" name="mail_queue_passwords[]" size="45" value="'.encode_field($password).'" maxlength="255" />';
			$hint = i18n::s('The POP3 password');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Allowed senders');
			$input = '<input type="text" name="mail_queue_allowed[]" size="45" value="'.encode_field($allowed).'" maxlength="255" />';
			$hint = i18n::s('A list of e-mail addresses allowed to post to this queue, or \'any_member\', \'any_subscriber\', or \'anyone\' (do not replicate associates addresses)');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Security match');
			$input = '<input type="text" name="mail_queue_matches[]" size="45" value="'.encode_field($match).'" maxlength="255" />';
			$hint = i18n::s('A regularity expression to be matched by incoming messages to be accepted (e.g., \'X-Originating-IP:  [21.18.33.9]\')');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Default section');
			$input = '<input type="text" name="mail_queue_sections[]" size="45" value="'.encode_field($section).'" maxlength="255" />';
			$hint = i18n::s('Nickname or id of the default section for new pages (e.g., \'45\')');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Preamble boundary');
			$input = '<input type="text" name="mail_queue_prefixes[]" size="45" value="'.encode_field($prefix).'" maxlength="255" />';
			$hint = i18n::s('Everything before this string, and the string itself, is removed');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Signature boundary');
			$input = '<input type="text" name="mail_queue_suffixes[]" size="45" value="'.encode_field($suffix).'" maxlength="255" />';
			$hint = i18n::s('The boundary used to locate signatures (e.g., \'___\' for Yahoo mail)');
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Processing options');
			$input = '<input type="text" name="mail_queue_options[]" size="45" value="'.encode_field($options).'" maxlength="255" />';
			$hint = i18n::s('You may combine several keywords:').' with_apop, no_reply, auto_publish';
			$fields[] = array($label, $input, $hint);

			$label = i18n::s('Processing hooks');
			$input = '<input type="text" name="mail_queue_hooks[]" size="45" value="'.encode_field($hooks).'" maxlength="255" />';
			$hint = i18n::s('Hook id(s) to be used on each message fetched from this queue');
			$fields[] = array($label, $input, $hint);

			// put the set of fields in the page
			$context['text'] .= Skin::build_form($fields);
			$fields = array();
		}
	}

	// append one queue
	$context['text'] .= '<p><b>'.i18n::s('Add a mail queue')."</b></p>\n";

	$label = i18n::s('Nick name');
	$input = '<input type="text" name="mail_queue_names[]" size="32" maxlength="64" />';
	$hint = i18n::s('Use a short nick name');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Server name');
	$input = '<input type="text" name="mail_queue_servers[]" size="45" maxlength="255" />';
	$hint = i18n::s('Use either the network name (e.g., \'pop.foo.bar\') or the IP address of the mail server');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Account name');
	$input = '<input type="text" name="mail_queue_accounts[]" size="45" maxlength="255" />';
	$hint = i18n::s('The POP3 user name');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Password');
	$input = '<input type="password" name="mail_queue_passwords[]" size="45" maxlength="255" />';
	$hint = i18n::s('The POP3 password');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Allowed senders');
	$input = '<input type="text" name="mail_queue_allowed[]" size="45" maxlength="255" />';
	$hint = i18n::s('A list of e-mail addresses allowed to post to this queue, or \'any_member\', \'any_subscriber\', or \'anyone\' (do not replicate associates addresses)');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Security match');
	$input = '<input type="text" name="mail_queue_matches[]" size="45" maxlength="255" />';
	$hint = i18n::s('A regularity expression to be matched by incoming messages to be accepted (e.g., \'X-Originating-IP:  [21.18.33.9]\')');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Default section');
	$input = '<input type="text" name="mail_queue_sections[]" size="45" maxlength="255" />';
	$hint = i18n::s('Nickname or id of the default section for new pages (e.g., \'45\')');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Preamble boundary');
	$input = '<input type="text" name="mail_queue_prefixes[]" size="45" maxlength="255" />';
	$hint = i18n::s('Everything before this string, and the string itself, is removed');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Signature boundary');
	$input = '<input type="text" name="mail_queue_suffixes[]" size="45" maxlength="255" />';
	$hint = i18n::s('The boundary used to locate signatures (e.g., \'___\' for Yahoo mail)');
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Processing options');
	$input = '<input type="text" name="mail_queue_options[]" size="45" maxlength="255" />';
	$hint = i18n::s('You may combine several keywords:').' with_apop, no_reply, auto_publish';
	$fields[] = array($label, $input, $hint);

	$label = i18n::s('Processing hooks');
	$input = '<input type="text" name="mail_queue_hooks[]" size="45" maxlength="255" />';
	$hint = i18n::s('Hook id(s) to be used on each message fetched from this queue');
	$fields[] = array($label, $input, $hint);

	// put the set of fields in the page
	$context['text'] .= Skin::build_form($fields);
	$fields = array();

	// uploads
	$context['text'] .= Skin::build_block(i18n::s('Inbound files'), 'title');

	// the splash message
	$context['text'] .= i18n::s('<p>Following parameters are used for files uploaded to this server, for example via FTP. These do not apply to pages submitted through web forms or w.bloggar.</p>')."\n";

	// uploads_nick_name
	$label = i18n::s('Author nick name or id');
	$input = '<input type="text" name="uploads_nick_name" size="40" value="'.encode_field(isset($context['uploads_nick_name']) ? $context['uploads_nick_name'] : '').'" maxlength="255" />';
	$hint = i18n::s('To impersonate the default user that has uploaded a file');
	$fields[] = array($label, $input, $hint);

	// uploads_anchor
	$label = i18n::s('Section');
	$input = '<input type="text" name="uploads_anchor" size="40" value="'.encode_field(isset($context['uploads_anchor']) ? $context['uploads_anchor'] : '').'" maxlength="255" />';
	$hint = i18n::s('The section to post new pages (e.g., \'section:2343\')');
	$fields[] = array($label, $input, $hint);

	// build the form
	$context['text'] .= Skin::build_form($fields);
	$fields = array();

	// the submit button
	$context['text'] .= Skin::build_box(i18n::s('Save parameters'), '<p>'.Skin::build_submit_button(i18n::s('Submit'), i18n::s('Press [s] to submit data'), 's').'</p>'."\n");

	// end of the form
	$context['text'] .= '</div></form>';

// no modifications in demo mode
} elseif(file_exists($context['path_to_root'].'parameters/demo.flag')) {

	// remind the surfer
	$context['text'] .= '<p>'.i18n::s('This instance of YACS runs in demonstration mode. For security reasons configuration parameters cannot be changed in this mode.')."</p>\n";

// save updated parameters
} else {

	// backup the old version
	Safe::unlink($context['path_to_root'].'parameters/agents.include.php.bak');
	Safe::rename($context['path_to_root'].'parameters/agents.include.php', $context['path_to_root'].'parameters/agents.include.php.bak');

	// build the new configuration file
	$content = '<?php'."\n"
		.'// This file has been created by the configuration script agents/configure.php'."\n"
		.'// on '.gmdate("F j, Y, g:i a").' GMT, for '.Surfer::get_name().'. Please do not modify it manually.'."\n"
		.'global $context;'."\n";
	if(isset($_REQUEST['uploads_anchor']))
		$content .= '$context[\'uploads_anchor\']=\''.addcslashes($_REQUEST['uploads_anchor'], "\\'")."';\n";
	if(isset($_REQUEST['uploads_nick_name']))
		$content .= '$context[\'uploads_nick_name\']=\''.addcslashes($_REQUEST['uploads_nick_name'], "\\'")."';\n";
	if(isset($_REQUEST['debug_messages']))
		$content .= '$context[\'debug_messages\']=\''.addcslashes($_REQUEST['debug_messages'], "\\'")."';\n";
	for($index = 0; $index < count($_REQUEST['mail_queue_names']); $index++) {
		$name	= addcslashes($_REQUEST['mail_queue_names'][$index], "\\'");
		$server = addcslashes($_REQUEST['mail_queue_servers'][$index], "\\'");
		$account	= addcslashes($_REQUEST['mail_queue_accounts'][$index], "\\'");
		$password	= addcslashes($_REQUEST['mail_queue_passwords'][$index], "\\'");
		$allowed	= addcslashes($_REQUEST['mail_queue_allowed'][$index], "\\'");
		$match	= addcslashes($_REQUEST['mail_queue_matches'][$index], "\\'");
		$section	= addcslashes($_REQUEST['mail_queue_sections'][$index], "\\'");
		$options	= addcslashes($_REQUEST['mail_queue_options'][$index], "\\'");
		$hooks	= addcslashes($_REQUEST['mail_queue_hooks'][$index], "\\'");
		$prefix = addcslashes($_REQUEST['mail_queue_prefixes'][$index], "\\'");
		$suffix = addcslashes($_REQUEST['mail_queue_suffixes'][$index], "\\'");
		if($name && $server && $account) {
			$content .= '$context[\'mail_queues\'][\''.$name.'\']=array(\''.$server.'\', \''.$account.'\', \''.$password.'\', \''
				.$allowed.'\', \''.$match.'\', \''.$section.'\', \''.$options.'\', \''.$hooks.'\', \''.$prefix.'\', \''.$suffix."');\n";
		}
	}
	if(isset($_REQUEST['with_referrals']))
		$content .= '$context[\'with_referrals\']=\''.addcslashes($_REQUEST['with_referrals'], "\\'")."';\n";
	$content .= '?>'."\n";

	// open the parameters file
	if(!Safe::file_put_contents('parameters/agents.include.php', $content)) {

		Skin::error(sprintf(i18n::s('ERROR: Impossible to write to the file %s. The configuration has not been saved.'), 'parameters/agents.include.php'));

		// allow for a manual update
		$context['text'] .= '<p style="text-decoration: blink;">'.sprintf(i18n::s('To actually change the configuration, please copy and paste following lines by yourself in file %s.'), 'parameters/agents.include.php')."</p>\n";

	// job done
	} else {
		$context['text'] .= '<p>'.sprintf(i18n::s('The following configuration has been saved into the file %s.'), 'parameters/agents.include.php')."</p>\n";

		// purge the cache
		Cache::clear();

		// remember the change
		$label = sprintf(i18n::c('%s has been updated'), 'parameters/agents.include.php');
		$description = $context['url_to_home'].$context['url_to_root'].'agents/configure.php';
		Logger::remember('agents/configure.php', $label, $description);

	}

	// display updated parameters
	$context['text'] .= Skin::build_box(i18n::s('Configuration parameters'), Safe::highlight_string($content), 'folder');

	// what's next?
	$context['text'] .= '<p>'.i18n::s('What do you want to do now?')."</p>\n";

	// follow-up commands
	$menu = array();

	// offer to change it again
	$menu = array_merge($menu, array( 'agents/configure.php' => i18n::s('Edit parameters again') ));

	// back to the control panel
	$menu = array_merge($menu, array( 'control/' => i18n::s('Go to the Control Panel') ));

	// display follow-up commands
	$context['text'] .= Skin::build_list($menu, 'menu_bar');

}

// render the skin
render_skin();

?>