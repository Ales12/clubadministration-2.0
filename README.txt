# clubadministration
##Templates

//clubadministration	

<html>
<head>
<title>{$mybb->settings['bbname']} - {$lang->clubs}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead"><h1>{$lang->club_welcome}</h1></td>
</tr>
<tr>

			{$clubadd_formular}
		
	<tr>
<td class="thead"><h1>{$lang->club_view}</h1></td>
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
</html>

//clubadministration_add
<tr>
		<td class="trow1" align="center" valign="top" width="90%">
			<form id="add_club" method="post" action="misc.php?action=clubs">
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
</form>
<br />
		</td>
		</tr>
<tr><td class="trow1" align="center" valign="top" width="90%">
	<h2><a href="misc.php?action=ownclubs">Übersicht der eigenen Clubs</a></h2>
	</td>
	</tr>
  
  //clubadministration_alert
  <div class="red_alert"><a href='modcp.php?action=clubadministration'>
Aktuell sind {$count} offene Clubs vorhanden. </a>
</div>

//clubadministration_bit_ownclubs
<tr><td class="thead" colspan="3"><h1>Clubgründungen von {$chara}</h1></td></tr>
<tr><td class="tcat" width="25%"><h2>{$lang->club_name}</h2></td>
	<td class="tcat"><h2>{$lang->club_desc}</h2></td>
	<td class="tcat" width="25%"><h2>{$lang->club_cat}</h2></td>
</tr>
{$club_own_bit}

//clubadministration_clubs
<div style="width: 240px; margin: 5px 10px; font-weight: normal;">
	<div class="tcat" align="center"><h2>{$club_name}</h2></div>
		<div class="trow2" style="text-align: justify; padding: 3px;">{$club_desc}</div>
		<div class="tcat"><strong>{$lang->club_leader}</strong></div>
	<div class="trow1"  style="text-align: center; padding: 3px;"><b>{$get_leader}</b>
			{$club_member_leader}
		</div>
				<div class="tcat"><strong>{$lang->club_members}</strong></div>
	<div class="trow1" style="text-align: center; padding: 3px;"><b>{$get_member}</b>
			{$club_member}
		</div>
</div>

//clubadministration_clubs_cat
<table width="90%">
	<tr><td class="tcat"><h2>{$club_cat_overiew}</h2></td></tr>
	<tr><td><div style="display: flex; flex-wrap: wrap;">
		{$club_bit}
		</div>
		</td></tr>
</table>

// clubadministration_members
<div>&raquo; {$user}  {$club_leave}</div>

// clubadministration_modcp
<html>
<head>
	<title>{$mybb->settings['bbname']} - {$lang->club_modcp}</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
					<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
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
</html>

// clubadministration_modcp_all
<tr>
	<td class="trow1" align="center">{$user}</td>
	<td class="trow1" align="center">{$club_name}</td>
		<td class="trow1" align="justify">{$club_desc}</td>
		<td class="trow1" align="center">{$club_cat}</td>
		<td class="trow1" align="center">{$club_edit} # {$club_delete}
			</td>
</tr>

// clubadministration_modcp_check
<tr>
	<td class="trow1">{$club_name}</td>
		<td class="trow1">{$club_desc}</td>
		<td class="trow1">{$club_cat}</td>
		<td class="trow1">{$club_ok} # {$club_no}
			</td>
</tr>

//clubadministration_modcp_clubadmin_nav
<tr><td class="trow1 smalltext"><a href="modcp.php?action=clubadministration" class="modcp_nav_item modcp_nav_banning">Clubs Verwalten</a></td></tr>

// clubadministration_modcp_edit
	<html>
<head>
<title>{$mybb->settings['bbname']} - {$lang->club_edit}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->club_edit}</strong></td>
</tr>
<tr>
		<td class="trow1" align="center" valign="top" width="90%">
<form id="edit_club" method="post" action="misc.php?action=ownclubs_edit&club={$club_id}">
	<input type="hidden" name="club_name" id="club_name" value="{$club_id}" class="textbox" />
		<table width="90%">
						<tr>
				<td class="trow1" align="center"><strong>{$lang->club_creator}</strong>
					<div class="smalltext"> Gib hier die UID des Cluberstellers ein.</div></td>	
				<td class="trow2" align="center"><input type="number" name="club_creator" id="club_creator" value="{$club_creator}" class="textbox" style="width: 200px; height: 25px;" required /> </td>	
			</tr>
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
</html>

// clubadministration_ownclubs
	<html>
<head>
<title>{$mybb->settings['bbname']} - {$lang->club_own}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->club_own}</strong></td>
</tr>
	<tr><td align="center">
			<h2><a href="misc.php?action=clubs">Zurück zur gesamten Übersicht</a></h2>
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
</html>

// clubadministration_ownclubs_bit
<tr>
	<td class="trow1" align="center">{$club_name}
		<br />{$club_edit}</td>
		<td class="trow1">{$club_desc}</td>
		<td class="trow1">{$club_cat}</td>
</tr>

// clubadministration_ownclubs_edit
	<html>
<head>
<title>{$mybb->settings['bbname']} - {$lang->club_edit}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
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
</html>

### clubadministration_profile
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder tfixed">
	<colgroup>
	<col style="width: 30%;" />
	</colgroup>
	<tr>
		<td class="thead"><strong>{$lang->club_memprofile}</strong></td>
	</tr>
	{$club_memprofile_bit}
</table>
<br />

### clubadministration_profile_bit
<tr><td class="trow1">{$clubtitle} {$club_leader}</td></tr>
