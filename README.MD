# Club- und Vereinsverwaltung
In diesen Plugin kanns du Clubs und Vereine erstellen, in dem die Charaktere beitreten können. Diese sind in verschiedene Kategorien unterteilt.<br />
Im ACP ist es zudem möglich einzustellen, ob eine Club/Vereinsführung geben soll oder nicht.
<br /><br />
## Variabeln/Links
Link zur Übersicht: misc.php?action=clubandsociety_overview<br />
Link im User CP: usercp.php?action=ownclubandsociety<br />
Variabel im Mod CP Nav<br />
**modcp_nav_users**<br />
```{$clubadmin_nav}```
<br /><br />
## PHP anpassung
Hast du andere Werte im ACP angegeben, was die Kategorien der Clubs angeht, muss in der PHP etwas anpassen. Ersetze Aktivitäten einfach mit der ersten Kategorie, die du angegeben hast. Das sorgt dafür, dass der erste Tab als Default angezeigt wird.
```PHP
          // muss per Hand angepasst werden, so dass es ein Default gibt!
            if($club_overview_cat == 'Aktivitäten'){
                $default = "id=\"defaultOpen\"";
            }
```
<br /><br />
## Templates
clubadministration 	<br />
clubadministration_addformular 	<br />
clubadministration_bit 	<br />
clubadministration_bit_clubmembers 	<br />
clubadministration_category 	<br />
clubadministration_club_options 	<br />
clubadministration_edit 	<br />
clubadministration_modcp 	<br />
clubadministration_modcp_bit 	<br />
clubadministration_modcp_control 	<br />
clubadministration_new_options
<br /><br />
## Datenbank
*clubs* und *club_members*
## CSS
**clubadministration.css** <br />
```CSS
 .club_flex{
	display: flex;
	flex-wrap: wrap;
	justify-content: center;

}

 /* Style the tab */
.club_tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;	
	width: 160px;
	text-align: center;
	display: flex;
flex-wrap: wrap;
justify-content: center;
}

/* Style the buttons that are used to open the tab content */
.club_tab button {
  background: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
	width: 150px;
	border: none;
}

/* Change background color of buttons on hover */
.club_tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.club_tab button.active {
  background-color: #ccc;
		width: 150px;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
	box-sizing: border-box;
	width: 85%;
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
} 
```
