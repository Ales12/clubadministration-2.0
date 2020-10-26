<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


function clubadministration_info()
{
    return array(
        "name"			=> "Clubverwaltung",
        "description"	=> "Hier können User selbst Clubs anlegen und verwalten.",
        "website"		=> "",
        "author"		=> "Ales",
        "authorsite"	=> "",
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
          `club_description` text CHARACTER SET utf8 NOT NULL,
          `club_category` varchar(500) CHARACTER SET utf8 NOT NULL,
          `club_creator` int(10) NOT NULL,
          `club_adminok` int(10)  DEFAULT 0 NOT NULL,
          PRIMARY KEY (`club_id`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());

        $db->query("CREATE TABLE `".TABLE_PREFIX."club_members` (
          `mem_id` int(10) NOT NULL auto_increment,
          `club_id` int(11) NOT NULL,
            `uid` int(10) NOT NULL,
                 `club_leader` int(10) NOT NULL,
          PRIMARY KEY (`mem_id`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());
    }

    $db->add_column("usergroups", "canaddclub", "tinyint NOT NULL default '1'");
    $db->add_column("usergroups", "canjoinclub", "tinyint NOT NULL default '1'");
    $cache->update_usergroups();

    $setting_group = array(
        'name' => 'clubadministrationsettings',
        'title' => 'Clubverwaltung',
        'description' => 'Hier sind alle Einstellungen für die Clubübersicht.',
        'disporder' => 3, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // A text setting
        'club_category' => array(
            'title' => 'Clubkategorie',
            'description' => 'Welche Kategorien soll es an Clubs gehen? (Schüler, Studenten, Erwachsene):',
            'optionscode' => 'text',
            'value' => 'Schüler, Studenten, Erwachsene', // Default
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
        'title'        => 'clubadministration',
        'template'    => $db->escape_string('	<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->clubs}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->club_welcome}</strong></td>
</tr>
<tr>
		<td class="trow1" align="center" valign="top" width="90%">
			{$clubadd_formular}
		</td>
		</tr>
	<tr><td align="center">
		{$clubs}
	</td>
		<tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_add',
        'template'    => $db->escape_string('<form id="add_club" method="post" action="misc.php?action=clubs">
		<table width="90%"><tr><td class="thead" colspan="3"><strong>{$lang->club_add}</strong></td></tr>
			<tr><td class="trow1" align="center"><strong>{$lang->club_name}</strong></strong></td><td class="trow1" align="center"><strong>{$lang->club_desc}</strong><td class="trow1" align="center"><strong>{$lang->club_cat}</strong></td></tr>
			<tr><td class="trow2" align="center"><input type="text" name="club_name" id="club_name" placeholder="Name des Clubs" class="textbox" style="width: 200px; height: 25px;" required /> </td>
			<td class="trow2" align="center"><textarea class="textarea" name="club_description" id="club_description" rows="3" cols="10" style="width: 95%">Beschreibe hier kurz deinen Club.</textarea></td>
			<td class="trow2" align="center"><select name="club_category" required style="width: 200px; height: 25px;">
					<option value="%">Kategorie wählen</option>
{$club_category}
					</select> 
				</td>
			</tr>
			<tr><td class="tcat" colspan="3" align="center"><input type="submit" name="addclub" value="Club hinzufügen" id="submit" class="button"></td></tr>
		</table>
</form>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_bit_ownclubs',
        'template'    => $db->escape_string('<tr><td class="thead" colspan="3">Clubgründungen von {$chara}</td></tr>
<tr><td class="tcat"><strong>{$lang->club_name}</strong></td>
	<td class="tcat"><strong>{$lang->club_desc}</strong></td>
	<td class="tcat"><strong>{$lang->club_cat}</strong></td>
</tr>
{$club_own_bit}'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_clubs',
        'template'    => $db->escape_string('<div style="height: 250px; width: 180px; margin: 5px 10px;">
	<div class="tcat" align="center"><strong>{$club_name}</div>
		<div class="trow2">{$club_desc}</div>
		<div class="tcat"><strong>{$lang->club_leader}</strong></div>
		<div class="trow1">{$get_leader}
			{$club_member_leader}
		</div>
				<div class="tcat"><strong>{$lang->club_members}</strong></div>
		<div class="trow1">{$get_member}
			{$club_member}
		</div>
</div>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_clubs_cat',
        'template'    => $db->escape_string('<table width="90%">
	<tr><td class="tcat"><strong>{$club_cat_overiew}</strong></td></tr>
	<tr><td><div style="display: flex; flex-wrap: wrap;">
		{$club_bit}
		</div>
		</td></tr>
</table>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_members',
        'template'    => $db->escape_string('<div>&raquo; {$user}</div>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_ownclubs',
        'template'    => $db->escape_string('	<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->club_own}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->club_own}</strong></td>
</tr>
	<tr><td align="center">
		<table width="100%">
		{$club_bit_own}

		</table></td>
		<tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_ownclubs_bit',
        'template'    => $db->escape_string('<tr>
	<td class="trow1">{$club_name}
		<br />{$club_edit}</td>
		<td class="trow1">{$club_desc}</td>
		<td class="trow1">{$club_cat}</td>
</tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_ownclubs_edit',
        'template'    => $db->escape_string('	<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->club_edit}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->club_edit}</strong></td>
</tr>
<tr>
		<td class="trow1" align="center" valign="top" width="90%">
<form id="edit_club" method="post" action="misc.php?action=ownclubs_edit&club={$club_id}">
	<input type="hidden" name="club_name" id="club_name" value="{$club_id}" class="textbox" />
		<table width="90%">
			<tr>
				<td class="trow1" align="center"><strong>{$lang->club_name}</strong></td>	
				<td class="trow2" align="center"><input type="text" name="club_name" id="club_name" value="{$club_name}" class="textbox" style="width: 200px; height: 25px;" required /> </td>	
			</tr>
			<tr>
				<td class="trow1" align="center"><strong>{$lang->club_desc}</strong></td>
				<td class="trow2" align="center"><textarea class="textarea" name="club_description" id="club_description" rows="3" cols="10" style="width: 95%">{$club_desc}</textarea></td></tr>
				<tr>
					<td class="trow1" align="center"><strong>{$lang->club_cat}</strong></td>		
					<td class="trow2" align="center"><select name="club_category" required style="width: 200px; height: 25px;">
					<option value="%">Kategorie wählen</option>
						{$club_category}
					</select> 
				</td>
			</tr>
			<tr><td class="tcat" colspan="2" align="center"><input type="submit" name="editclub" value="Club editieren" id="submit" class="button"></td></tr>
		</table>
</form>
		</td>
		</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_modcp',
        'template'    => $db->escape_string('<html>
<head>
	<title>{$mybb->settings[\'bbname\']} - {$lang->club_modcp}</title>
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
						<td class="thead"><h1>{$lang->club_modcp}</h1></td>
					</tr>
						<tr><td class="tcat"><h2>{$lang->club_new}</h2></td></tr>
						<tr><td>
							<table width="100%">
							<tr><td class="tcat"><h2>{$lang->club_name}</h2></td>
	<td class="tcat"><h2>{$lang->club_desc}</h2></td>
	<td class="tcat"><h2>{$lang->club_cat}</h2></td>
									<td class="tcat"><h2>{$lang->club_option}</h2></td></tr>
								{$modcp_club_bit}
							</table>
							</td></tr>
												<tr><td class="tcat"><h2>{$lang->club_all}</h2></td></tr>
						<tr><td>
							<table width="100%">
							<tr><td class="tcat"><h2>{$lang->club_creator}</h2></td>
								<td class="tcat"><h2>{$lang->club_name}</h2></td>
	<td class="tcat"><h2>{$lang->club_desc}</h2></td>
	<td class="tcat"><h2>{$lang->club_cat}</h2></td>
									<td class="tcat"><h2>{$lang->club_options}</h2></td></tr>
								{$modcp_club_all}
							</table>
							</td></tr>
				</table>
			</td>
		</tr>
	</table>
{$footer}
</body>
</html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_modcp_check',
        'template'    => $db->escape_string('<tr>
	<td class="trow1">{$club_name}</td>
		<td class="trow1">{$club_desc}</td>
		<td class="trow1">{$club_cat}</td>
		<td class="trow1">{$club_ok} # {$club_no}
			</td>
</tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_modcp_clubadmin_nav',
        'template'    => $db->escape_string('<tr><td class="trow1 smalltext"><a href="modcp.php?action=clubadministration" class="modcp_nav_item modcp_nav_banning">Clubs Verwalten</a></td></tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title'        => 'clubadministration_modcp_all',
        'template'    => $db->escape_string('<tr>
	<td class="trow1" align="center">{$user}</td>
	<td class="trow1" align="center">{$club_name}</td>
		<td class="trow1" align="justify">{$club_desc}</td>
		<td class="trow1" align="center">{$club_cat}</td>
		<td class="trow1" align="center">{$club_edit} # {$club_delete}
			</td>
</tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
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

    $db->delete_query("templates", "title LIKE '%clubadministration%'");
    rebuild_settings();

}

function clubadministration_activate()
{
    global $db;
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('<navigation>')."#i", '{$clubadministration_alert} <navigation>');
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$modcp_nav_users}')."#i", '{$modcp_nav_users}{$clubadmin_modcp}');

}

function clubadministration_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$clubadministration_alert}')."#i", '', 0);
    find_replace_templatesets("modcp_nav", "#".preg_quote('{$clubadmin_modcp}')."#i", '', 0);
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
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $page, $db, $parser, $options, $club_category, $club_overview_category, $get_leader, $get_member, $member_uid;
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

    if($mybb->get_input('action') == 'clubs')
    {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb('Club Übersicht', "misc.php?action=clubs");

        $club_cat_setting = $mybb->settings['club_category'];

        $club_cats = explode(", ", $club_cat_setting);
        $uid = $mybb->user['uid'];

        foreach ($club_cats as $club_cat){
            $club_category .= "<option value='{$club_cat}'>{$club_cat}</option>";
        }

        //Formular Clubs hinzufügen
        if($mybb->usergroup['canaddclub'] == 1){
            eval("\$clubadd_formular = \"".$templates->get("clubadministration_add")."\";");
        }


        //Club in die Datenbank eintragen
        if($_POST['addclub']){

            //Wenn das Team Einträge erstellt, dann wink doch einfach durch. Sonst bitte nochmal zum Prüfung :D
            if($mybb->usergroup['canmodcp'] == '1'){
                $accepted = 1;
            } else {
                $accepted = 0;
            }

            $new_entry = array(
                "club_name" => $db->escape_string($_POST['club_name']),
                "club_description" => $db->escape_string($_POST['club_description']),
                "club_category" => $db->escape_string($_POST['club_category']),
                "club_creator" => (int) $mybb->user['uid'],
                "club_adminok" => $accepted
            );

            $db->insert_query("clubs", $new_entry);
            redirect("misc.php?action=clubs");
        }


        //Wir möchten jetzt jede Clubkategorie einzeln
        foreach ($club_cats as $club_cat_overiew){
            $club_bit = "";

            //Clubs auslesen

            $club_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs
        WHERE club_category = '".$club_cat_overiew."'
        AND club_adminok = 1
    ");

            while($club = $db->fetch_array($club_query)){
                $clubid = "";
                $clubid = $club['club_id'];
                $club_name = $club['club_name'];
                $club_desc = $parser->parse_message($club['club_description'], $options);

                //leer machen
                $club_member_leader = "";
                $club_member = "";
                //Und unsere Mitglieder, sowie die Clubführung
                $member_query = $db->query("SELECT *
            FROM ".TABLE_PREFIX."club_members cm
            LEFT JOIN ".TABLE_PREFIX."users u
            on (u.uid=cm.uid)
            WHERE cm.club_id = '".$clubid."'
            Order by username asc
            ");


                while($member = $db->fetch_array($member_query)){
                    $get_member = "";
                    $get_leader = "";
                    $club_leave = "";
                    $username = format_name($member['username'], $member['usergroup'], $member['displaygroup']);
                    $user = build_profile_link($username, $member['uid']);

                    if($mybb->user['uid'] == $member['uid']){
                        $club_leave = "<a href='misc.php?action=clubs&leave={$clubid}&uid={$member['uid']}'><i class=\"fas fa-sign-out-alt\" title='Austreten'></i></a>";
                    }

                    if($member['club_leader'] == 1){
                        eval("\$club_member_leader .= \"".$templates->get("clubadministration_members")."\";");
                    }
                    eval("\$club_member .= \"".$templates->get("clubadministration_members")."\";");
                }
                if($mybb->usergroup['canjoinclub'] == 1){
                    if($mybb->user['uid'] != $member['uid']){
                        $get_leader = "<a href='misc.php?action=clubs&get_leader={$clubid}'>&raquo; Clubführung werden</a>";
                        $get_member = "<a href='misc.php?action=clubs&get_member={$clubid}'>&raquo; Mitglied werden</a>";
                    } else{
                        $get_leader = "";
                        $get_member = "";
                    }
                }

                eval("\$club_bit .= \"".$templates->get("clubadministration_clubs")."\";");

            }
            eval("\$clubs .= \"".$templates->get("clubadministration_clubs_cat")."\";");
        }



        //Clubleader werden
        $get_leader = $mybb->input['get_leader'];
        $get_member = $mybb->input['get_member'];
        $leave = $mybb->input['leave'];
        if($get_leader){

            $new_member = array(
                "club_id" => (int)$get_leader,
                "uid" => (int)$mybb->user['uid'],
                "club_leader" => 1
            );
            $db->insert_query("club_members", $new_member);
            redirect("misc.php?action=clubs");
        }
        if($get_member){

            $new_member = array(
                "club_id" => (int)$get_member,
                "uid" => (int)$mybb->user['uid'],
            );
            $db->insert_query("club_members", $new_member);
            redirect("misc.php?action=clubs");
        }

        if($leave){
            $member_uid = $mybb->user['uid'];

            $db->delete_query("club_members", "club_id = '".$leave."' and uid ='".$member_uid."'");
            redirect("misc.php?action=clubs");
        }

        eval("\$page = \"".$templates->get("clubadministration")."\";");
        output_page($page);
    }

    if($mybb->get_input('action') == 'ownclubs') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb('Eigene Clubs', "misc.php?action=ownclubs");

        //welcher user ist online
        $this_user = intval ($mybb->user['uid']);

//für den fall nicht mit hauptaccount online
        $as_uid = intval ($mybb->user['as_uid']);

// suche alle angehangenen accounts
        if ($as_uid == 0) {
            $select = $db->query ("SELECT * FROM " . TABLE_PREFIX . "users WHERE (as_uid = $this_user) OR (uid = $this_user) ORDER BY username ASC");
        } else if ($as_uid != 0) {
//id des users holen wo alle angehangen sind
            $select = $db->query ("SELECT * FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid) OR (uid = $this_user) OR (uid = $as_uid) ORDER BY username ASC");
        }
        while ($row = $db->fetch_array ($select)) {
            $chara = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $uid = $row['uid'];
            $club_name = "";
            $club_desc = "";
            $club_cat = "";

            $club_own_bit = "";

            //Clubs auslesen
            $club_select = $db->query("SELECT *
            from ".TABLE_PREFIX."clubs
            where club_creator = '".$uid."'
            order by club_name ASC
            ");

            while($club = $db->fetch_array($club_select)){
                $club_edit = "<a href='misc.php?action=ownclubs_edit&club={$club['club_id']}'>&raquo; Bearbeiten</a>";

                $club_name = $club['club_name'];
                $club_desc = $parser->parse_message($club['club_description'], $options);
                $club_cat = $club['club_category'];

                eval("\$club_own_bit .= \"".$templates->get("clubadministration_ownclubs_bit")."\";");
            }
            eval("\$club_bit_own .= \"".$templates->get("clubadministration_bit_ownclubs")."\";");
        }


        eval("\$page = \"".$templates->get("clubadministration_ownclubs")."\";");
        output_page($page);
    }

    if($mybb->get_input('action') == 'ownclubs_edit') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb('Club editieren', "misc.php?action=ownclubs_edit");
        $club_id = $mybb->input['club'];

        //Clubs auslesen
        $club_select = $db->query("SELECT *
            from " . TABLE_PREFIX . "clubs
            where club_id = '" . $club_id . "'

            ");

        $club = $db->fetch_array($club_select);
        $club_cat_setting = $mybb->settings['club_category'];
        $club_name = $club['club_name'];
        $club_desc = $club['club_description'];
        $club_cat = $club['club_category'];
        $club_cats = explode(", ", $club_cat_setting);

        foreach ($club_cats as $club_cat){
            if($club_cat == $club_cat){
                $select = "selected=\"selected\"";
            } else {
                $select = "";
            }
            $club_category .= "<option value='{$club_cat}' {$select}>{$club_cat}</option>";
        }


        //Club in die Datenbank eintragen
        if($_POST['editclub']){
            $club_id = $mybb->input['club'];
            $new_entry = array(
                "club_name" => $db->escape_string($mybb->input['club_name']),
                "club_description" => $db->escape_string($mybb->input['club_description']),
                "club_category" => $db->escape_string($mybb->input['club_category']),
            );

            $db->update_query("clubs", $new_entry, "club_id = '".$club_id."'");
            redirect("misc.php?action=ownclubs");
        }

        eval("\$page = \"" . $templates->get("clubadministration_ownclubs_edit") . "\";");
        output_page($page);
    }
}


$plugins->add_hook("modcp_nav", "clubadministration_modcp_nav");


function clubadministration_modcp_nav(){
    global $clubadmin_modcp, $templates;

    eval("\$clubadmin_modcp = \"" . $templates->get("clubadministration_modcp_clubadmin_nav") . "\";");
}

/*
 * Hier kannst du die Orte bearbeiten
 */
$plugins->add_hook("modcp_start", "clubadministration_modcp");
function clubadministration_modcp()
{

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $application, $db, $page,$modcp_nav, $club_ok, $club_no, $club_edit, $club_delete, $club_category;
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();
    require_once MYBB_ROOT . "inc/class_parser.php";;
    $parser = new postParser;
    $lang->load('clubadministration');

    if ($mybb->get_input('action') == 'clubadministration') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb('Clubverwaltung', "modcp.php?action=clubadministration");
        $options = array(
            "allow_html" => 1,
            "allow_mycode" => 1,
            "allow_smilies" => 1,
            "allow_imgcode" => 1,
            "filter_badwords" => 0,
            "nl2br" => 1,
            "allow_videocode" => 0
        );

        $select = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs c
        LEFT JOIN ".TABLE_PREFIX."users u
        on (c.club_creator = u.uid)
        WHERE club_adminok = 0
        ORDER BY club_name ASC
        ");

        while($club = $db->fetch_array($select)){
            $club_name = "";
            $club_desc = "";
            $club_cat = "";

            $username = format_name($club['username'], $club['usergroup'], $club['displaygroup']);
            $user = build_profile_link($username, $club['uid']);
            $uid = $club['uid'];
            $club_name = $club['club_name'];
            $club_desc = $parser->parse_message($club['club_description'], $options);
            $club_cat = $club['club_category'];
            $club_id = $club['club_id'];

            //Annehmen oder Ablehnen?
            $club_ok = "<a href='modcp.php?action=clubadministration&clubok={$club_id}'>annehmen</a>";
            $club_no = "<a href='modcp.php?action=clubadministration&clubno={$club_id}'>ablehnen</a>";
            eval("\$modcp_club_bit .= \"".$templates->get("clubadministration_modcp_check")."\";");
        }

        $club_ok = $mybb->input['clubok'];
        $team = $mybb->user['uid'];

        if($club_ok){

            $pm_change = array(
                "subject" => "{$lang->club_ok}",
                "message" => "{$lang->club_ok_text}",
                //to: wer muss die anfrage bestätigen
                "fromid" => $uid,
                //from: wer hat die anfrage gestellt
                "toid" => $team
            );
            // $pmhandler->admin_override = true;
            $pmhandler->set_data ($pm_change);
            if (!$pmhandler->validate_pm ())
                return false;
            else {
                $pmhandler->insert_pm ();
            }
            $db->query("UPDATE ".TABLE_PREFIX."clubs SET club_adminok = 1 where club_id = '".$club_ok."'");
            redirect("modcp.php?action=clubadministration");
        }

        $club_no= $mybb->input['clubno'];
        if($club_no){

            $pm_change = array(
                "subject" => "{$lang->club_no}",
                "message" => "{$lang->club_no_text}",
                //to: wer muss die anfrage bestätigen
                "fromid" => $uid,
                //from: wer hat die anfrage gestellt
                "toid" => $team
            );
            // $pmhandler->admin_override = true;
            $pmhandler->set_data ($pm_change);
            if (!$pmhandler->validate_pm ())
                return false;
            else {
                $pmhandler->insert_pm ();
            }
            redirect("modcp.php?action=clubadministration");
        }


        //Alle Clubs auflisten
        $club_select = $db->query("SELECT *
        FROM ".TABLE_PREFIX."clubs c
        LEFT JOIN ".TABLE_PREFIX."users u
        on (c.club_creator = u.uid)
        WHERE club_adminok = 1
        ");

        while($club = $db->fetch_array($club_select)){
            $club_name = "";
            $club_desc = "";
            $club_cat = "";

            $username = format_name($club['username'], $club['usergroup'], $club['displaygroup']);
            $user = build_profile_link($username, $club['uid']);
            $uid = $club['uid'];
            $club_name = $club['club_name'];
            $club_desc = $parser->parse_message($club['club_description'], $options);
            $club_cat = $club['club_category'];
            $club_id = $club['club_id'];

            //Club editieren oder Löschen
            $club_edit = "<a href='modcp.php?action=clubadministration_edit&clubedit={$club_id}'>editieren</a>";
            $club_delete = "<a href='modcp.php?action=clubadministration&clubdelete={$club_id}'>löschen</a>";
            eval("\$modcp_club_all .= \"".$templates->get("clubadministration_modcp_all")."\";");
        }

//Club löschen
        $club_delete = $mybb->input['clubdelete'];
        if($club_delete){
            $db->update_query("clubs",  "club_id = '".$club_delete."'");
            redirect("modcp.php?action=clubadministration");
        }


        eval("\$page = \"" . $templates->get("clubadministration_modcp") . "\";");
        output_page($page);
    }
    if ($mybb->get_input('action') == 'clubadministration_edit') {


        // Add a breadcrumb
        add_breadcrumb('Club Editieren', "modcp.php?action=clubadministration_edit");
        $club_id = $mybb->input['clubedit'];

        //Clubs auslesen
        $club_select = $db->query("SELECT *
            from " . TABLE_PREFIX . "clubs
            where club_id = '" . $club_id . "'

            ");

        $club = $db->fetch_array($club_select);
        $club_cat_setting = $mybb->settings['club_category'];
        $club_name = $club['club_name'];
        $club_desc = $club['club_description'];
        $club_category = $club['club_category'];
        $club_creator = $club['club_creator'];

        $club_cats = explode(", ", $club_cat_setting);
        $club_cat_setting = $mybb->settings['club_category'];

        $club_cats = explode(", ", $club_cat_setting);
        foreach ($club_cats as $club_cat){
            if($club_cat == $club_category){
                $select = "selected=\"selected\"";
            } else {
                $select = "";
            }
            $club_category .= "<option value='{$club_cat}' {$select}>{$club_cat}</option>";
        }


        //Club in die Datenbank eintragen
        if($_POST['editclub']){
            $club_id = $mybb->input['club'];
            $new_entry = array(
                "club_creator" => (int) $mybb->input['club_name'],
                "club_name" => $db->escape_string($mybb->input['club_name']),
                "club_description" => $db->escape_string($mybb->input['club_description']),
                "club_category" => $db->escape_string($mybb->input['club_category']),
            );

            $db->update_query("clubs", $new_entry, "club_id = '".$club_id."'");
            redirect("mmodcp.php?action=clubadministration_edit");
        }


        eval("\$page = \"" . $templates->get("clubadministration_modcp_edit") . "\";");
        output_page($page);
    }
}

$plugins->add_hook('global_intermediate', 'global_clubadministration_alert');

function global_clubadministration_alert(){
    global $db, $mybb, $clubadministration_alert, $templates;

    $select = $db->query("SELECT *
    FROM ".TABLE_PREFIX."clubs
    where club_adminok = 0
    ");

    $count = mysqli_num_rows ($select);

    if($count > 0){
        if($mybb->usergroup['canmodcp'] == 1){
            eval("\$clubadministration_alert = \"" . $templates->get("clubadministration_alert") . "\";");
        }
    }

}