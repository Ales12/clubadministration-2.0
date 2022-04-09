<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

// Alerts
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "clubadministrator_alert");
}

function clubadministration_info()
{
    return array(
        "name"			=> "Club- und Vereinsverwaltung",
        "description"	=> "Hier können User selbst Clubs und Vereine anlegen und verwalten.",
        "website"		=> "",
        "author"		=> "Ales",
        "authorsite"	=> "https://github.com/Ales12",
        "version"		=> "1.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function clubadministration_install()
{
    global $db, $cache, $mybb;

    //Datenbank erstellen
    if($db->engine=='mysql'||$db->engine=='mysqli')
    {
        $db->query("CREATE TABLE `".TABLE_PREFIX."clubs` (
          `club_id` int(10) NOT NULL auto_increment,
          `club_name` varchar(500) CHARACTER SET utf8 NOT NULL,
          `club_type` varchar(500) CHARACTER SET utf8 NOT NULL,
          `club_description` text CHARACTER SET utf8 NOT NULL,
          `club_category` varchar(500) CHARACTER SET utf8 NOT NULL,
          `club_leader` int(10) NOT NULL,
          `club_creator` int(10) NOT NULL,
          `club_adminok` int(10)  DEFAULT 0 NOT NULL,
          PRIMARY KEY (`club_id`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());

        $db->query("CREATE TABLE `".TABLE_PREFIX."club_members` (
          `mem_id` int(10) NOT NULL auto_increment,
          `club_id` int(11) NOT NULL,
            `club_uid` int(10) NOT NULL,
            `club_leader` int(10) NOT NULL,
          PRIMARY KEY (`mem_id`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());
    }

    $db->add_column("usergroups", "canaddclub", "tinyint NOT NULL default '1'");
    $db->add_column("usergroups", "canjoinclub", "tinyint NOT NULL default '1'");
    $cache->update_usergroups();

    $setting_group = array(
        'name' => 'clubadministrationsettings',
        'title' => 'Club- & Vereinsverwaltung',
        'description' => 'Hier sind alle Einstellungen für die Club- und Vereinverwaltung.',
        'disporder' => 3, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // A text setting
        'club_category' => array(
            'title' => 'Club- und Vereinskategorie',
            'description' => 'Welche Kategorien soll es an Clubs gehen? (Schüler, Studenten, Erwachsene):',
            'optionscode' => 'text',
            'value' => 'Vereine, Clubs', // Default
            'disporder' => 1
        ),
        // A text setting
        'club_admin' => array(
            'title' => 'Club- und Vereinsleitung',
            'description' => 'Soll es eine Leitung für Clubs und Vereine geben?:',
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 1
        ),

    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

// Don't forget this!
    rebuild_settings();

    //Templates
    $insert_array = array(
        'title' => 'clubadministration',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->clubandsociety_title}</title>
{$headerinclude}

</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead">
	<strong>{$lang->clubandsociety_title}</strong>
	</td>
	</tr>
	<tr><td>
	{$add_clubsociety}
<div class="club_flex">
		{$club_bit}
	
		</div></td>
	</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_addformular',
        'template' => $db->escape_string('<form id="add_clubsociety"  method="post"  action="misc.php?action=clubandsociety_overview">
	<div class="club_flex_formular">
		<div class="thead" style="width: 100%;"><strong>{$lang->clubandsociety_add}</strong></div>
		<div class="club_formular">
			<div class="tcat">	<strong>{$lang->clubandsociety_addtitle}</strong></div>
			<input type="text" class="textbox" placeholder="Der Titel des Clubs/Vereins" name="clubname" style="width: 200px;">
		</div>
			<div class="club_formular">
			<div class="tcat">	<strong>{$lang->clubandsociety_addtype}</strong></div>
				<select name="clubtype">
					<option>Club</option>
					<option>Verein</option>
				</select>
		</div>
			<div class="club_formular">
			<div class="tcat">	<strong>{$lang->clubandsociety_addcat}</strong></div>
			<select name="clubcat">
{$club_cat}
				</select>
		</div>
	{$clubadmin_option}
					<div class="club_formular" style="width: 90%">
			<div class="tcat">	<strong>{$lang->clubandsociety_adddesc}</strong></div>
<textarea class="textarea" name="clubdesc" id="clubdesc" rows="5" cols="30" style="width:99%;" placeholder="Beschreibe hier den Club/Verein. Was macht ihn aus, wo liegt er und von wem wird er geführt?"></textarea>
		</div>
		<div class="club_formular" style="width:90%; text-align:center;"><input type="submit" name="add_clubsociety" id="add" class="button" value="{$lang->clubandsociety_addsubmit}"></div>
	</div>
</form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_addformular_link',
        'template' => $db->escape_string('<div class="innerlist_nav"><a onclick="$(\'#addclub\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->clubandsociety_add}</a>	</div><div class="modal" id="addclub" style="display: none;">{$add_clubsociety}</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_bit',
        'template' => $db->escape_string('<div class="club_body">
	<div class="thead">
		<strong>{$club[\'club_name\']}</strong>
		<div class="smalltext">{$club[\'club_type\']}<br />
			{$club_leader}
		</div></div>
		<div class="club_desc">
			{$club_desc}
		</div>
	<div class="tcat"><strong>{$lang->clubandsociety_overview_member}</strong></div>
	{$club_join}
	{$club_bit_member}
	</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_bit_clubmembers',
        'template' => $db->escape_string('<div>{$club_members} {$club_leave}</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_category',
        'template' => $db->escape_string('<div id="{$club_overview_cat}" class="tabcontent"><strong>{$club_overview_cat}</strong>
	<div class="club_flex">
      {$club_bit}
	</div></div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_club_options',
        'template' => $db->escape_string('<div>
	<a href="{$options_path}&delete={$club_id}">{$lang->clubandsociety_delete}</a> | <a onclick="$(\'#editclub_{$club_id}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->clubandsociety_edit}</a><div class="modal" id="editclub_{$club_id}" style="display: none;">{$edit_clubsociety}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_edit',
        'template' => $db->escape_string('<form id="edit_clubsociety"  method="post"  action="{$options_path}">
	<input type="hidden" value="{$club_id}" name="club_id">
	<input type="hidden" value="{$clubs[\'club_creator\']}" name="club_creator">
	<table cellspacing="2" cellpadding="5" style="margin: auto;">
		<tr><td class="tcat" colspan="2"><strong>{$lang->clubandsociety_add}</strong></td></tr>
		<tr>
			<td class="trow1" width="50%">
				<strong>{$lang->clubandsociety_addtitle}</strong>
			</td>
			<td class="trow2" width="50%"><input type="text" class="textbox" value="{$club_name}" name="clubname" style="width: 200px;">
			</td>	</tr>
			<tr>
						<td class="trow1" width="50%">
				<strong>{$lang->clubandsociety_addtype}</strong>
			</td>
	
			<td class="trow2" width="50%">
				<select name="clubtype">
					{$type_option}
				</select>
			</td>
		</tr>
					<tr>
						<td class="trow1" width="50%">
				<strong>{$lang->clubandsociety_addcat}</strong>
			</td>
	
			<td class="trow2" width="50%">
				<select name="clubcat">
			{$cat_option}
				</select>
			</td>
		</tr>
				{$clubadmin_option}
		<tr><td class="trow1" colspan="2">	<strong>{$lang->clubandsociety_adddesc}</strong></td><tr/>
		<tr>
			<td class="trow2" colspan="2"><textarea class="textarea" name="clubdesc" id="clubdesc" rows="5" cols="30" style="width:99%;" placeholder="Beschreibe hier den Club/Verein. Was macht ihn aus, wo liegt er und von wem wird er geführt?">{$clubs[\'club_description\']}</textarea></td></tr>
		<tr><td colspan="2" class="trow1" align="center"><input type="submit" name="edit_clubsociety" id="add" class="button" value="{$lang->clubandsociety_editsubmit}"></td></tr>
	</table>
</form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_modcp',
        'template' => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->clubandsociety_modcp}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
				<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead"><strong>{$lang->clubandsociety_modcp}</strong></td></tr>
<tr><td class="trow1">
	<div class="club_flex">
		{$club_modcp_bit}
	</div>	</td></tr>
					</table>
			</td>
		</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_modcp_bit',
        'template' => $db->escape_string('<div class="club_body">
	<div class="thead">
		<strong>{$club_name}</strong>
		<div class="smalltext">{$club_type} | {$club_cat}<br />
			{$club_creator}
		</div></div>
		<div class="club_desc">
			{$club_desc}
		</div>
{$club_options}
	</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_modcp_control',
        'template' => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->clubandsociety_modcp_control}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
				<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead"><strong>{$lang->clubandsociety_modcp_control}</strong></td></tr>
<tr><td class="trow1">
	<div class="club_flex">
		{$club_modcp_bit}
	</div>	</td></tr>
					</table>
			</td>
		</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_new_options',
        'template' => $db->escape_string('<div>
	<a href="modcp.php?action=clubandsocietymodcp_control&accept={$club_id}">{$lang->clubandsociety_modcp_accept}</a> | 	<a href="modcp.php?action=clubandsocietymodcp_control&decline={$club_id}">{$lang->clubandsociety_modcp_decline}</a>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_usercp',
        'template' => $db->escape_string('<html>
<head>
<title>{$lang->user_cp} - {$lang->clubandsociety_ucp}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->clubandsociety_ucp}</strong></td>
</tr>
<tr><td class="trow1">
		<div class="club_flex">
		{$club_usercp_bit}
	</div>	
	</td></tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'clubadministration_usercp_bit',
        'template' => $db->escape_string('<div class="club_body">
	<div class="thead">
		<strong>{$club_name}</strong>
		<div class="smalltext">{$club_type} | {$club_cat}<br />
		</div></div>
		<div class="club_desc">
			{$club_desc}
		</div>
{$club_options}
	</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    //CSS einfügen
    $css = array(
        'name' => 'clubadministration.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" =>    ' .club_flex{
	display: flex;
	flex-wrap: wrap;
	justify-content: center;

}

 .club_flex_formular{
		display: flex;
	flex-wrap: wrap;
	justify-content: center;
	width: 50%;
	margin: 5px auto 20px auto;
	
}

.club_formular{
	min-width: 45%;	
	max-width: 90%;
text-align: center;
	margin: 2px 5px 5px 5px;
}

.club_formular .tcat{
	margin-bottom: 10px;	
}

.club_addclub{
	text-align: center;
	text-transform: uppercase;
	padding: 4px;
color: #0072BC;
}

.club_body{
	width: 23%;
	margin: 5px 8px;
}

.club_desc{
	margin: 5px;
	height: 120px;
	overflow: auto;
	scrollbar-width: none !important;
	font-size: 12px;
	text-align: justify;
}  ',
        'cachefile' => $db->escape_string(str_replace('/', '', 'clubadministration.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
// Don't forget this!
    rebuild_settings();
}

function clubadministration_is_installed()
{
    global $db;
    if($db->table_exists("clubs"))
    {
        return true;
    }
    return false;
}

function clubadministration_uninstall()
{
    global $db, $cache;
    if($db->table_exists("clubs"))
    {
        $db->drop_table("clubs");
    }

    if($db->table_exists("club_members"))
    {
        $db->drop_table("club_members");
    }


    if($db->field_exists("canaddclub", "usergroups"))
    {
        $db->drop_column("usergroups", "canaddclub");
    }
    if($db->field_exists("canjoinclub", "usergroups"))
    {
        $db->drop_column("usergroups", "canjoinclub");
    }
    $cache->update_usergroups();
    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='clubadministrationsettings'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='club_category'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='club_admin'");
    $db->delete_query("templates", "title LIKE '%clubadministration%'");
    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'clubadministration.css'");
    $query = $db->simple_select("themes", "tid");
    while($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }
    rebuild_settings();

}

function clubadministration_activate()
{
    global $db, $cache;
    //Alertseinstellungen
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }


        // Dein Club wurde angenommen

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('clubandsociety_accepted'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        // Dein Club wurde abgelehnt

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('clubandsociety_rejected'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);
    }

    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_ipsearch}').'#i', '{$nav_ipsearch} {$clubadmin_nav}');

}

function clubadministration_deactivate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('clubandsociety_accepted');
    }

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('clubandsociety_rejected');
    }
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$clubadmin_nav}')."#i", '', 0);
}

// Backend Hooks
$plugins->add_hook("admin_formcontainer_end", "clubadministration_usergroup_permission");
$plugins->add_hook("admin_user_groups_edit_commit", "clubadministration_usergroup_permission_commit");

// Usergruppen-Berechtigungen
function clubadministration_usergroup_permission()
{
    global $mybb, $lang, $form, $form_container, $run_module;

    if($run_module == 'user' && !empty($form_container->_title) & !empty($lang->misc) & $form_container->_title == $lang->misc)
    {
        $clubadministration_options = array(
            $form->generate_check_box('canaddclub', 1, "Kann Club hinzufügen?", array("checked" => $mybb->input['canaddclub'])),
            $form->generate_check_box('canjoinclub', 1, "Kann Club beitreten?", array("checked" => $mybb->input['canjoinclub'])),
        );
        $form_container->output_row("Clubverwaltung", "", "<div class=\"group_settings_bit\">".implode("</div><div class=\"group_settings_bit\">", $clubadministration_options)."</div>");
    }
}

function clubadministration_usergroup_permission_commit()
{
    global $db, $mybb, $updated_group;
    $updated_group['canaddclub'] = $mybb->get_input('canaddclub', MyBB::INPUT_INT);
    $updated_group['canjoinclub'] = $mybb->get_input('canjoinclub', MyBB::INPUT_INT);
}


$plugins->add_hook('misc_start', 'clubadministration');

// In the body of your plugin
function clubadministration()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $page, $db, $parser, $options, $club_category, $club_cat, $clubadmin_option, $club_leader, $club_bit, $clubmember_uid, $menu;
    global $club_desc, $club_join, $club_members, $club_bit_member, $club_leave;
    $lang->load('clubadministration');

    ///der Parser halt
    require_once MYBB_ROOT."inc/class_parser.php";;
    $parser = new postParser;
    // Do something, for example I'll create a page using the hello_world_template
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    // Einstellungen
    $club_category = $mybb->settings['club_category'];
    $club_admin = $mybb->settings['club_admin'];



    if ($mybb->get_input('action') == 'clubandsociety_overview') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->clubandsociety_title, "misc.php?action=clubandsociety_overview");


        $club_category = explode(", ", $club_category);
        foreach ($club_category as $club){

            $club_cat .= "<option>{$club}</option>";
        }

        if($mybb->usergroup['canaddclub'] == 1){

            if($club_admin == 1){
                $clubadmin_option = "
			<div class=\"club_formular\">
			<div class='tcat'>	<strong>{$lang->clubandsociety_addadmin}</strong></div>
					<select name=\"clubadmin\">
					<option value='0'>Nein</option>
					<option value='1'>Ja</option>
				</select>
		</div>";
            }

            eval("\$add_link = \"" . $templates->get("clubadministration_addformular_link") . "\";");
            eval("\$add_clubsociety = \"" . $templates->get("clubadministration_addformular") . "\";");

            if(isset($mybb->input['add_clubsociety'])){
                if($mybb->usergroup['canmodcp'] == 1){
                    $admin_ok = 1;
                } else{
                    $admin_ok = 0;
                }

                $clubadmin = $_POST['clubadmin'];
                if($clubadmin == 1){
                    $club_leader = (int)$mybb->user['uid'];
                } else{
                    $club_leader = 0;

                }

                $add_clubsociety = array(
                    "club_name" => $db->escape_string($_POST['clubname']),
                    "club_type" => $db->escape_string($_POST['clubtype']),
                    "club_description" => $db->escape_string($_POST['clubdesc']),
                    "club_category" => $db->escape_string($_POST['clubcat']),
                    "club_creator" => $mybb->user['uid'],
                    "club_leader" => $club_leader,
                    "club_adminok" => $admin_ok
                );

                $db->insert_query ("clubs", $add_clubsociety);

                redirect ("misc.php?action=clubandsociety_overview");
            }

        }


        $club_type_filter = "";
        $club_cat_filter = "";
        $club_bit = "";

        if(isset($mybb->input['club_filter'])){
            if($mybb->input['type_filter'] != '') {
                $club_type_filter = "and club_type = '" . $mybb->input['type_filter'] . "'";
            }
            if($mybb->input['cat_filter'] != '') {
                $club_cat_filter = "and club_category  = '" . $mybb->input['cat_filter'] . "'";
            }
            $url_extra = "&type_filter={$mybb->input['type_filter']}&cat_filter={$mybb->input['cat_filter']}&club_filter=Ansicht+filtern";
        }

        $select_clubs = $db->query("SELECT COUNT(*) AS clubs
            FROM ".TABLE_PREFIX."canons
                WHERE club_adminok  = 1
               {$club_type_filter}
               {$club_cat_filter}
        ");

        $count = $db->fetch_field($select_clubs, "clubs");;
        $perpage = 8;
        $page = intval($mybb->input['page']);

        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }
        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $count) {
            $upper = $count;
        }

        $url = "{$mybb->settings['bburl']}/misc.php?action=clubandsociety_overview{$url_extra}";

        // Einmal alle Clubs auslesen, die in der aktuellen Kategorie sind!
        $club_query = $db->query("SELECT *
                FROM ".TABLE_PREFIX."clubs 
                WHERE club_adminok  = 1
               {$club_type_filter}
               {$club_cat_filter}
                ORDER BY club_name ASC
                ");

        while($club = $db->fetch_array($club_query)) {
            $club_leader = "";
            $club_leader = "";
            $club_id = $club['club_id'];
            $club_leader_uid = $club['club_leader'];

            $club_desc = $parser->parse_message($club['club_description'], $options);

            // Gib es einen club/Vereinsführer, dann zeig das doch bitte an :D und weil mit Link hübscher, lese vorher die User bitte aus
            if ($club_admin == 1 and $club['club_leader'] != 0) {
                $leader_query = $db->simple_select("users", "*", "uid=$club_leader_uid");
                $leader = $db->fetch_array($leader_query);

                if($club['club_leader'] == $mybb->user['uid'] or $mybb->usergroup['canmodcp'] == 1){
                    $leader_down = "<a href='misc.php?action=clubandsociety_overview&down_clubleader={$club_id}'>{$lang->clubandsociety_join_leader_down}</a>";
                }

                $username = format_name($leader['username'], $leader['usergroup'], $leader['displaygroup']);
                $club_leader = build_profile_link($username, $leader['uid']);
                $club_leader = "{$lang->clubandsociety_overview_leader}" . $club_leader. $leader_down;
            } elseif($club_admin == 1 and $club['club_leader'] == 0){
                $club_leader = "<a href='misc.php?action=clubandsociety_overview&get_clubleader={$club_id}'>{$lang->clubandsociety_join_leader}</a>";
            } else{
                $club_leader = "";
            }

            if ($mybb->usergroup['canjoinclub'] == 1) {
                $club_join = "<a href='misc.php?action=clubandsociety_overview&club_join={$club_id}'>{$lang->clubandsociety_overview_join}</a>";
            }


            // Clubmitglieder auslesen
            $club_bit_member = "";
            $clubmembers_query = $db->query("SELECT *
                FROM " . TABLE_PREFIX . "club_members cm
                left join " . TABLE_PREFIX . "users u
                on (cm.club_uid = u.uid)
                WHERE club_id = '" . $club_id . "'
                ORDER BY u.username ASC
                ");
            while ($clubmember = $db->fetch_array($clubmembers_query)) {
                $clubmember_uid = $clubmember['club_uid'];
                $username = format_name($clubmember['username'], $clubmember['usergroup'], $clubmember['displaygroup']);
                $club_members = build_profile_link($username, $clubmember['uid']);

                /*
                 * Wenn schon Mitglied, dann sollte die Möglichkeit, beizutreten, nicht mehr angezeigt werden. Leere somit diese Variabel und gebe stattdessen die Möglichkeit den Club/Verein zu verlassen.
                 */
                if ($clubmember_uid == $mybb->user['uid']) {
                    $club_join = "";
                    $club_leave = "<a href='misc.php?action=clubandsociety_overview&club_leave={$club_id}&club_member={$clubmember_uid}'>{$lang->clubandsociety_overview_leave}</a>";
                }

                eval("\$club_bit_member .= \"" . $templates->get("clubadministration_bit_clubmembers") . "\";");
            }


            eval("\$club_bit .= \"" . $templates->get("clubadministration_bit") . "\";");
        }

        // Club Beitreten
        $club_join = $mybb->input['club_join'];
        if($club_join){
            $join_club_array = array(
                "club_id" => (int)$club_join,
                "club_uid" => (int)$mybb->user['uid']
            );

            $db->insert_query ("club_members", $join_club_array);
            redirect ("misc.php?action=clubandsociety_overview");
        }

        // Club verlassen

        $club_leave = $mybb->input['club_leave'];
        if($club_leave){
            $clubmember = $mybb->input['club_member'];
            $db->delete_query("club_members", "club_id ='$club_leave' and club_uid = '$clubmember'");
            redirect ("misc.php?action=clubandsociety_overview");
        }

        // Club führung werden

        $get_clubleader = $mybb->input['get_clubleader'];
        if($get_clubleader){
            $get_clubleader_array = array(
                "club_leader" => $mybb->user['uid'],
            );
            $db->update_query("clubs", $get_clubleader_array, "club_id = $get_clubleader");
            redirect ("misc.php?action=clubandsociety_overview");
        }

        $down_clubleader = $mybb->input['down_clubleader'];
        if($down_clubleader){
            $down_clubleader_array = array(
                "club_leader" => 0,
            );
            $db->update_query("clubs", $down_clubleader_array, "club_id = $down_clubleader");
            redirect ("misc.php?action=clubandsociety_overview");
        }


        eval("\$menu = \"".$templates->get("listen_nav")."\";");
        eval("\$page = \"" . $templates->get("clubadministration") . "\";");
        output_page($page);
    }
}



// mod cp navigation
$plugins->add_hook("modcp_nav", "clubadministration_modcp_nav");
function clubadministration_modcp_nav(){
    global $clubadmin_nav, $lang;
    //Die Sprachdatei
    $lang->load('clubadministration');
    $clubadmin_nav = "<tr><td class=\"trow1 smalltext\"><a href=\"modcp.php?action=clubandsocietymodcp\" class=\"modcp_nav_item modcp_nav_modlogs\">{$lang->clubandsociety_modcp_nav}</a></td></tr>
                       <tr><td class=\"trow1 smalltext\"><a href=\"modcp.php?action=clubandsocietymodcp_control\" class=\"modcp_nav_item modcp_nav_modlogs\">{$lang->clubandsociety_modcp_nav_control}</a></td></tr>";
}

// hier ist dann mal das Mod CP
$plugins->add_hook("modcp_start", "clubadministration_modcp");
function clubadministration_modcp()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $modcp_nav, $db, $page, $club_modcp_bit, $options_path,$type_option,$cat_option;
    //Die Sprachdatei
    $lang->load('clubadministration');

    require_once MYBB_ROOT . "inc/class_parser.php";;
    $parser = new postParser;
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );
    $club_admin = $mybb->settings['club_admin'];

    if ($mybb->get_input('action') == 'clubandsocietymodcp') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->clubandsociety_modcp, "modcp.php?action=clubandsocietymodcp");

        // Einstellungen
        $club_category = $mybb->settings['club_category'];
        $club_category = explode(", ", $club_category);

        $type_array = array(
            "Club" => "Club",
            "Verein" => "Verein",
        );

        $club_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs c
        LEFT JOIN ".TABLE_PREFIX."users u  on (c.club_creator = u.uid)
        where c.club_adminok  = 1
          order by c.club_name ASC
                ");


        while($clubs = $db->fetch_array($club_query)){
            $club_name = "";
            $club_type = "";
            $club_desc = "";
            $club_creator = "";
            $club_cat = "";
            $club_id = "";
            $type_option = "";
            $cat_option = "";

            // lass uns die Variabeln mal füllen
            $club_id = $clubs['club_id'];
            $club_name = $clubs['club_name'];
            $club_type = $clubs['club_type'];
            $club_cat = $clubs['club_category'];
            $username = format_name($clubs['username'], $clubs['usergroup'], $clubs['displaygroup']);
            $club_creator = "{$lang->clubandsociety_modcp_creator}".build_profile_link($username, $clubs['uid']);
            $club_desc = $parser->parse_message($clubs['club_description'], $options);

            // Edit optionen
            if($club_admin == 1) {
                $clubadmin_option = "				<tr>
						<td class='trow1' width='50%'>
				<strong>{$lang->clubandsociety_edit_leader}</strong>
			</td>
	
			<td class='trow2' width='50%'><input type='text' class='textbox' value='{$clubs['club_leader']}' name='club_leader' style='width: 200px;'></td></tr>";
            }
            foreach ($type_array as $type){
                $select = "";
                if($club_type == $type){
                    $select = "selected";
                }
                $type_option .= "<option value='{$type}' {$select}>{$type}</option>";
            }

            foreach ($club_category as $cat){
                $select = "";
                if($club_cat == $cat){
                    $select = "selected";
                }
                $cat_option .= "<option value='{$cat}' {$select}>{$cat}</option>";
            }

            $options_path = "modcp.php?action=clubandsocietymodcp";
            eval("\$edit_clubsociety = \"" . $templates->get("clubadministration_edit") . "\";");
            eval("\$club_options = \"" . $templates->get("clubadministration_club_options") . "\";");
            eval("\$club_modcp_bit .= \"" . $templates->get("clubadministration_modcp_bit") . "\";");
        }


        // Editieren wir mal
        if(isset($mybb->input['edit_clubsociety'])){

            $club_id = $mybb->input['club_id'];

            $edit_clubsociety = array(
                "club_name" => $db->escape_string($mybb->input['clubname']),
                "club_type" => $db->escape_string($mybb->input['clubtype']),
                "club_description" => $db->escape_string($mybb->input['clubdesc']),
                "club_category" => $db->escape_string($mybb->input['clubcat']),
                "club_creator" => (int)$mybb->input['club_creator'],
                "club_leader" => (int)$mybb->input['club_leader'],
                "club_adminok" => 1
            );

            $db->update_query ("clubs", $edit_clubsociety, "club_id = '{$club_id}'");

            redirect ("modcp.php?action=clubandsocietymodcp");
        }

        // Clubs löschen
        $delete_club = $mybb->input['delete'];
        if($delete_club){
            $db->delete_query("clubs", "club_id='{$delete_club}'");
            redirect ("modcp.php?action=clubandsocietymodcp");
        }

        eval("\$page = \"".$templates->get("clubadministration_modcp")."\";");
        output_page($page);
    }


    // neue Clubs/Vereine verwalten
    if ($mybb->get_input('action') == 'clubandsocietymodcp_control') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->clubandsociety_modcp_control, "modcp.php?action=clubandsocietymodcp_control");

        $club_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs c
        LEFT JOIN ".TABLE_PREFIX."users u  
        on (c.club_creator = u.uid)
        where c.club_adminok  = 0
          order by c.club_name ASC
                ");


        while($clubs = $db->fetch_array($club_query)){
            $club_name = "";
            $club_type = "";
            $club_desc = "";
            $club_creator = "";
            $club_cat = "";
            $club_id = "";
            $type_option = "";
            $cat_option = "";

            // lass uns die Variabeln mal füllen
            $club_id = $clubs['club_id'];
            $club_name = $clubs['club_name'];
            $club_type = $clubs['club_type'];
            $club_cat = $clubs['club_category'];
            $username = format_name($clubs['username'], $clubs['usergroup'], $clubs['displaygroup']);
            $club_creator = "{$lang->clubandsociety_modcp_creator}".build_profile_link($username, $clubs['uid']);
            $club_desc = $parser->parse_message($clubs['club_description'], $options);


            eval("\$club_options = \"" . $templates->get("clubadministration_new_options") . "\";");
            eval("\$club_modcp_bit .= \"" . $templates->get("clubadministration_modcp_bit") . "\";");
        }

        $accept_club = $mybb->input['accept'];
        if($accept_club){

            $accept_clubsociety = array(
                "club_adminok" => 1
            );

            $club_infos = $db->simple_select("clubs", "*", "club_id = '{$accept_club}'");
            $club_info = $db->fetch_array($club_infos);

            $club_name = $club_info['club_name'];
            $club_creator = $club_info['club_creator'];
            $club_type = $club_info['club_type'];

            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {

                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('clubandsociety_accepted');
                if ($alertType != NULL && $alertType->getEnabled() and $club_creator != $mybb->user['uid']) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$club_creator, $alertType, $club_name, $club_type);
                    $alert->setExtraDetails([
                        'club' => $club_name,
                        'type' => $club_type
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            $db->update_query ("clubs", $accept_clubsociety, "club_id = '{$accept_club}'");
            redirect ("modcp.php?action=clubandsocietymodcp_control");
        }

        // Clubs löschen
        $delete_club = $mybb->input['decline'];
        if($delete_club){
            $club_infos = $db->simple_select("clubs", "*", "club_id = '{$delete_club}'");
            $club_info = $db->fetch_array($club_infos);

            $club_name = $club_info['club_name'];
            $club_creator = $club_info['club_creator'];
            $club_type = $club_info['club_type'];

            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {

                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('clubandsociety_rejected');
                if ($alertType != NULL && $alertType->getEnabled() and $club_creator != $mybb->user['uid']) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$club_creator, $alertType, $club_name, $club_type);
                    $alert->setExtraDetails([
                        'club' => $club_name,
                        'type' => $club_type
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            $db->delete_query("clubs", "club_id='{$delete_club}'");
            redirect ("modcp.php?action=clubandsocietymodcp_control");
        }

        eval("\$page = \"".$templates->get("clubadministration_modcp_control")."\";");
        output_page($page);
    }

}

$plugins->add_hook("usercp_menu_built", "clubadministration_usercp_nav");
function clubadministration_usercp_nav(){
    global $clubadmin_ucp_nav, $lang;
    //Die Sprachdatei
    $lang->load('clubadministration');
    $clubadmin_ucp_nav = "<tr><td class=\"trow1 smalltext\"><a href=\"usercp.php?action=ownclubandsociety\" class=\"modcp_nav_item modcp_nav_modlogs\">{$lang->clubandsociety_ucp_nav}</a></td></tr>";
}


$plugins->add_hook("usercp_start", "clubadministration_usercp");
function clubadministration_usercp(){
    global $mybb, $db, $cache, $plugins, $templates, $theme, $lang, $header, $headerinclude, $footer, $usercpnav, $options_path, $clubadmin_option;
    //Die Sprachdatei
    $lang->load('clubadministration');

    require_once MYBB_ROOT . "inc/class_parser.php";;
    $parser = new postParser;
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    $uid = $mybb->user['uid'];

    // Einstellungen
    $club_category = $mybb->settings['club_category'];
    $club_category = explode(", ", $club_category);
    $club_admin = $mybb->settings['club_admin'];
    $type_array = array(
        "Club" => "Club",
        "Verein" => "Verein",
    );

    if($mybb->get_input('action') == 'ownclubandsociety'){
        // Add a breadcrumb
        add_breadcrumb($lang->clubandsociety_ucp, "usercp.php?action=ownclubandsociety");
        $own_club_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs
        where club_creator = '".$uid."'
        order by club_name ASC
    ");

        while($clubs = $db->fetch_array($own_club_query)){

            $club_name = "";
            $club_type = "";
            $club_desc = "";
            $club_cat = "";
            $club_id = "";
            $type_option = "";
            $cat_option = "";

            // lass uns die Variabeln mal füllen
            $club_id = $clubs['club_id'];
            $club_name = $clubs['club_name'];
            $club_type = $clubs['club_type'];
            $club_cat = $clubs['club_category'];
            $club_desc = $parser->parse_message($clubs['club_description'], $options);

            // Edit optionen


            if($club_admin == 1) {
                $clubadmin_option = "				<tr>
						<td class='trow1' width='50%'>
				<strong>{$lang->clubandsociety_edit_leader}</strong>
			</td>
			<td class='trow2' width='50%'><input type='text' class='textbox' value='{$clubs['club_leader']}' name='club_leader' style='width: 200px;'></td></tr>";
            }
            foreach ($type_array as $type){
                $select = "";
                if($club_type == $type){
                    $select = "selected";
                }
                $type_option .= "<option value='{$type}' {$select}>{$type}</option>";
            }

            foreach ($club_category as $cat){
                $select = "";
                if($club_cat == $cat){
                    $select = "selected";
                }
                $cat_option .= "<option value='{$cat}' {$select}>{$cat}</option>";
            }

            $options_path = "usercp.php?action=ownclubandsociety";
            eval("\$edit_clubsociety = \"" . $templates->get("clubadministration_edit") . "\";");
            eval("\$club_options = \"" . $templates->get("clubadministration_club_options") . "\";");
            eval("\$club_usercp_bit .= \"" . $templates->get("clubadministration_usercp_bit") . "\";");
        }


        // Editieren wir mal
        if(isset($mybb->input['edit_clubsociety'])){

            $club_id = $mybb->input['club_id'];

            $edit_clubsociety = array(
                "club_name" => $db->escape_string($mybb->input['clubname']),
                "club_type" => $db->escape_string($mybb->input['clubtype']),
                "club_description" => $db->escape_string($mybb->input['clubdesc']),
                "club_category" => $db->escape_string($mybb->input['clubcat']),
                "club_creator" => (int)$mybb->input['club_creator'],
                "club_leader" => (int)$mybb->input['club_leader'],
                "club_adminok" => 1
            );

            $db->update_query ("clubs", $edit_clubsociety, "club_id = '{$club_id}'");

            redirect ("usercp.php?action=ownclubandsociety");
        }

        // Clubs löschen
        $delete_club = $mybb->input['delete'];
        if($delete_club){
            $db->delete_query("clubs", "club_id='{$delete_club}'");
            redirect ("usercp.php?action=ownclubandsociety");
        }


        eval("\$page = \"".$templates->get("clubadministration_usercp")."\";");
        output_page($page);
    }


}


// Eigene Clubs im User CP verwalten


// Benachrichtungen rausschicken
function clubadministrator_alert() {
    global $mybb, $lang;
    $lang->load('clubadministration');
    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_clubAcceptedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {

            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->clubandsociety_accepted,
                $outputAlert['from_user'],
                $alertContent['type'],
                $alertContent['club'],
                $outputAlert['dateline']
            );
        }

        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_clubAcceptedFormatter($mybb, $lang, 'clubandsociety_accepted')
        );
    }

    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_clubRejectedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {

            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->clubandsociety_rejected,
                $outputAlert['from_user'],
                $alertContent['type'],
                $alertContent['club'],
                $outputAlert['dateline']
            );
        }

        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_clubRejectedFormatter($mybb, $lang, 'clubandsociety_rejected')
        );
    }

}
