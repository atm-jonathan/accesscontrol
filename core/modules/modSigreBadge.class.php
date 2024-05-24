<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2024 		Philippe GRAND 			<contact@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   sigrebadge     Module SigreBadge
 *  \brief      SigreBadge module descriptor.
 *
 *  \file       htdocs/sigrebadge/core/modules/modSigreBadge.class.php
 *  \ingroup    sigrebadge
 *  \brief      Description and activation file for module SigreBadge
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module SigreBadge
 */
class modSigreBadge extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 321900; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'sigrebadge';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleSigreBadgeName' not found (SigreBadge is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleSigreBadgeDesc' not found (SigreBadge is name of module).
		$this->description = "SigreBadgeDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "SigreBadgeDescription";

		// Author
		$this->editor_name = 'ATOO.NET';
		$this->editor_url = 'https://atoo-net.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where SIGREBADGE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-map-marked-alt';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array('/sigrebadge/css/sigrebadge.css.php'),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/sigrebadge/js/sigrebadge.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/sigrebadge/temp","/sigrebadge/subdir");
		$this->dirs = array("/sigrebadge/temp","/sigrebadge/codezone");

		// Config pages. Put here list of php page, stored into sigrebadge/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@sigrebadge");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array();
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("sigrebadge@sigrebadge");

		// Prerequisites
		$this->phpmin = array(7, 0); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'SigreBadgeWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('SIGREBADGE_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('SIGREBADGE_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();
		$r = 0;

		$this->const[$r][0] = "SIGREBADGE_MYBADGE_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "standard_mybadge";
		$this->const[$r][3] = 'Name of PDF model of sigrebadge';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "SIGREBADGE_MYBADGE_ADDON_NUMBER";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_mybadge_standard";
		$this->const[$r][3] = 'Name for numbering manager for sigrebadge';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "SIGREBADGE_CODEZONE_ADDON_NUMBER";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_codezone_standard";
		$this->const[$r][3] = 'Name for numbering manager for codezone';
		$this->const[$r][4] = 0;
		$r++;

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isModEnabled("sigrebadge")) {
			$conf->sigrebadge = new stdClass();
			$conf->sigrebadge->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@sigrebadge:$user->hasRight('sigrebadge', 'read'):/sigrebadge/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@sigrebadge:$user->hasRight('othermodule', 'read'):/sigrebadge/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in sale order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		/* Example:
		 $this->dictionaries=array(
		 'langs'=>'sigrebadge@sigrebadge',
		 // List of tables we want to see into dictonnary editor
		 'tabname'=>array("table1", "table2", "table3"),
		 // Label of tables
		 'tablib'=>array("Table1", "Table2", "Table3"),
		 // Request to select fields
		 'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
		 // Sort order
		 'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
		 // List of fields (result of select to show dictionary)
		 'tabfield'=>array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields to edit a record)
		 'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields for insert)
		 'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
		 // Name of columns with primary key (try to always name it 'rowid')
		 'tabrowid'=>array("rowid", "rowid", "rowid"),
		 // Condition to show each dictionary
		 'tabcond'=>array(isModEnabled('sigrebadge'), isModEnabled('sigrebadge'), isModEnabled('sigrebadge')),
		 // Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 'tabhelp'=>array(array('code'=>$langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), array('code'=>$langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), ...),
		 );
		 */
		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array();

		/* END MODULEBUILDER DICTIONARIES */

		// Boxes/Widgets
		// Add here list of php file(s) stored in sigrebadge/core/boxes that contains a class to show a widget.
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'sigrebadgewidget1.php@sigrebadge',
			//      'note' => 'Widget provided by SigreBadge',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/sigrebadge/class/mybadge.class.php',
			//      'objectname' => 'MyBadge',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => 'isModEnabled("sigrebadge")',
			//      'priority' => 50,
			//  ),
		);
		/* END MODULEBUILDER CRON */
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'isModEnabled("sigrebadge")', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'isModEnabled("sigrebadge")', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 0 + 1);
		$this->rights[$r][1] = 'Read MyBadge object of SigreBadge';
		$this->rights[$r][4] = 'mybadge';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 1 + 1);
		$this->rights[$r][1] = 'Create/Update MyBadge object of SigreBadge';
		$this->rights[$r][4] = 'mybadge';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 2 + 1);
		$this->rights[$r][1] = 'Delete MyBadge object of SigreBadge';
		$this->rights[$r][4] = 'mybadge';
		$this->rights[$r][5] = 'delete';
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 3 + 1);
		$this->rights[$r][1] = 'Read CodeZone object of SigreBadge';
		$this->rights[$r][4] = 'codezone';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 4 + 1);
		$this->rights[$r][1] = 'Create/Update CodeZone object of SigreBadge';
		$this->rights[$r][4] = 'codezone';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 5 + 1);
		$this->rights[$r][1] = 'Delete CodeZone object of SigreBadge';
		$this->rights[$r][4] = 'codezone';
		$this->rights[$r][5] = 'delete';
		$r++;
		
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'ModuleSigreBadgeName',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'',
			'url'=>'/sigrebadge/dashboard.php',
			'langs'=>'sigrebadge@sigrebadge', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'isModEnabled("sigrebadge")', // Define condition to show or hide menu entry. Use 'isModEnabled("sigrebadge")' if entry must be visible if module is enabled.
			'perms'=>'1', // Use 'perms'=>'$user->hasRight("sigrebadge", "mybadge", "read")' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU MYBADGE */
		/*
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Left menu entry
			'titre'=>'MyBadge',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'mybadge',
			'url'=>'/sigrebadge/sigrebadgeindex.php',
			'langs'=>'sigrebadge@sigrebadge',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")', // Define condition to show or hide menu entry. Use 'isModEnabled("sigrebadge")' if entry must be visible if module is enabled.
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "read")',
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'New_MyBadge',
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'sigrebadge_mybadge_new',
			'url'=>'/sigrebadge/mybadge_card.php?action=create',
			'langs'=>'sigrebadge@sigrebadge',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")', // Define condition to show or hide menu entry. Use 'isModEnabled("sigrebadge")' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "write")'
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'List_MyBadge',
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'sigrebadge_mybadge_list',
			'url'=>'/sigrebadge/mybadge_list.php',
			'langs'=>'sigrebadge@sigrebadge',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")', // Define condition to show or hide menu entry. Use 'isModEnabled("sigrebadge")' if entry must be visible if module is enabled.
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "read")'
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		*/
		/*LEFTMENU MYBADGE*/
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge',
			'type'=>'left',
			'titre'=>'MyBadge',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'mybadge',
			'url'=>'/sigrebadge/mybadge_list.php',
			'langs'=>'sigrebadge@sigrebadge',
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "read")',
			'target'=>'',
			'user'=>2,
		);
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge',
            'type'=>'left',
            'titre'=>'ListMyBadge',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_mybadge_list',
            'url'=>'/sigrebadge/mybadge_list.php',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "read")',
            'target'=>'',
            'user'=>2,
        );
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge',
            'type'=>'left',
            'titre'=>'NewMyBadge',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_mybadge_new',
            'url'=>'/sigrebadge/mybadge_card.php?action=create',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge", "write")',
            'target'=>'',
            'user'=>2
        );

		/*END LEFTMENU MYBADGE*/
		/*LEFTMENU MYBADGE_LOG*/
		/*$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge',
			'type'=>'left',
			'titre'=>'Mybadge_log',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'mybadge_log',
			'url'=>'/sigrebadge/mybadge_log_list.php',
			'langs'=>'sigrebadge@sigrebadge',
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge_log", "read")',
			'target'=>'',
			'user'=>2,
		);
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge_log',
            'type'=>'left',
            'titre'=>'List Mybadge_log',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_mybadge_log_list',
            'url'=>'/sigrebadge/mybadge_log_list.php',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge_log", "read")',
            'target'=>'',
            'user'=>2,
        );
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=mybadge_log',
            'type'=>'left',
            'titre'=>'New Mybadge_log',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_mybadge_log_new',
            'url'=>'/sigrebadge/mybadge_log_card.php?action=create',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "mybadge_log", "write")',
            'target'=>'',
            'user'=>2
        );*/

		/*END LEFTMENU MYBADGE_LOG*/
		/* END MODULEBUILDER LEFTMENU MYBADGE */
		/*LEFTMENU CODEZONE*/
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=sigrebadge',
			'type'=>'left',
			'titre'=>'CodeZone',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'sigrebadge',
			'leftmenu'=>'codezone',
			'url'=>'/sigrebadge/codezone_list.php',
			'langs'=>'sigrebadge@sigrebadge',
			'position'=>1000+$r,
			'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "codezone", "read")',
			'target'=>'',
			'user'=>2,
		);
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=codezone',
            'type'=>'left',
            'titre'=>'ListCodeZone',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_codezone_list',
            'url'=>'/sigrebadge/codezone_list.php',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "codezone", "read")',
            'target'=>'',
            'user'=>2,
        );
        $this->menu[$r++]=array(
            'fk_menu'=>'fk_mainmenu=sigrebadge,fk_leftmenu=codezone',
            'type'=>'left',
            'titre'=>'NewCodeZone',
            'mainmenu'=>'sigrebadge',
            'leftmenu'=>'sigrebadge_codezone_new',
            'url'=>'/sigrebadge/codezone_card.php?action=create',
            'langs'=>'sigrebadge@sigrebadge',
            'position'=>1000+$r,
            'enabled'=>'isModEnabled("sigrebadge")',
			'perms'=>'$user->hasRight("sigrebadge", "codezone", "write")',
            'target'=>'',
            'user'=>2
        );

		/*END LEFTMENU CODEZONE*/
		// Exports profiles provided by this module
		$r = 1;

		/* BEGIN MODULEBUILDER EXPORT MYBADGE */	
		$langs->load("sigrebadge@sigrebadge");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='MyBadgeLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='fa-map-marked-alt';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyBadge'; 
		$keyforclassfile='/sigrebadge/class/mybadge.class.php'; 
		$keyforelement='MyBadge';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';

		$this->export_fields_array[$r] = array(
			't.rowid' => 'Id',
			't.ref' => 'Reference',
			's.nom' => 'ThirdPartyName',
			'p.ref' => "RefBadge",
			't.date_creation' => 'DateCreation',
			't.date_validation' => 'DateValidation',
			't.codeuid' => 'CodeUID',
			't.status' => 'Status'
		);
		$this->export_TypeFields_array[$r] = array(
			't.ref' => "Text",
			's.nom' => "Text",
			'p.ref' => "Text",
			't.date_creation' => "Text",
			't.date_validation' => "Text",
			't.codeuid' => "Text",
			't.status' => "Text"
		);
		$this->export_entities_array[$r] = array(
			's.nom' => "company",
			't.ref' => "MyBadge",
			'p.ref' => "product",
			't.date_creation' => "MyBadge",
			't.date_validation' => "MyBadge",
			't.codeuid' => "MyBadge",
			't.status' => "MyBadge"
	);
		
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'sigrebadge_mybadge as t';
		$this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'sigrebadge_mybadge_extrafields as extrabadge ON extrabadge.fk_object = t.rowid';
		$this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as s ON t.fk_soc = s.rowid';
		$this->export_sql_end[$r].=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON t.fk_product = p.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('mybadge').')';

		$keyforselect='sigrebadge_mybadge'; 
		$keyforaliasextra='extrabadge'; 
		$keyforelement='MyBadge';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';

		$r++; 
		/* END MODULEBUILDER EXPORT MYBADGE */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT MYBADGE */
		$langs->load("sigrebadge@sigrebadge");
		$this->import_code[$r] = $this->rights_class . '_' . $r;
		$this->import_label[$r] = 'MyBadgeLines'; // Translation key (used only if key ImportDataset_xxx_z not found)
		$this->import_icon[$r] = 'fa-map-marked-alt'; // Icon for import

		// Define import tables and keys
		$this->import_tables_array[$r] = array('t' => MAIN_DB_PREFIX . 'sigrebadge_mybadge', 'extra' => MAIN_DB_PREFIX . 'sigrebadge_mybadge_extrafields');
		$this->import_tables_creator_array[$r] = array('t' => 'fk_user_author'); // Field to store the user ID who imports the data

		// Set the path for class file
		$keyforclass = 'MyBadge';
		$keyforclassfile = '/sigrebadge/class/mybadge.class.php';
		$keyforelement = 'mybadge@sigrebadge';
		include DOL_DOCUMENT_ROOT . '/core/commonfieldsinimport.inc.php';

		// Define the mapping for hidden fields (usually technical IDs)
		$this->import_fieldshidden_array[$r] = array('extra.fk_object' => 'lastrowid-' . MAIN_DB_PREFIX . 'sigrebadge_mybadge');

		// Regular expressions for data validation during import
		$this->import_regex_array[$r] = array();

		// Example values for guidance
		$this->import_examplevalues_array[$r] = array(
			't.rowid' => '123',
			't.ref' => 'BadgeRef001',
			's.nom' => 'Company ABC',
			'p.ref' => 'Product123',
			't.date_creation' => '2020-01-01',
			't.date_validation' => '2020-01-15',
			't.status' => '1'
		);

		// Keys for updating existing records
		$this->import_updatekeys_array[$r] = array('t.ref' => 'Reference');

		// Conversion rules for importing data
		$this->import_convertvalue_array[$r] = array(
			't.ref' => array(
				'rule' => 'none', // No conversion needed, just an example
				'class' => 'MyBadge',
				'path' => '/sigrebadge/class/mybadge.class.php'
			),
			// Add more conversion rules as needed
		);

		// Optional SQL commands to run after the import is done
		$this->import_run_sql_after_array[$r] = array();
		$r++;
		/* END MODULEBUILDER IMPORT MYBADGE */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		//$result = $this->_load_tables('/install/mysql/', 'sigrebadge');
		$result = $this->_load_tables('/sigrebadge/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$result1=$extrafields->addExtraField('codezone', "Code Zone", 'sellist', 1,  3, 'sigrebadge_mybadge', 0, 0, '', unserialize('a:1:{s:7:"options";a:1:{s:30:"sigrebadge_codezone:label:rowid";N;}}'), 1, '', 0, 0, '', '', 'sigrebadge@sigrebadge', 'isModEnabled("sigrebadge")');
		//$result2=$extrafields->addExtraField('sigrebadge_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'sigrebadge@sigrebadge', 'isModEnabled("sigrebadge")');
		//$result3=$extrafields->addExtraField('sigrebadge_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'sigrebadge@sigrebadge', 'isModEnabled("sigrebadge")');
		//$result4=$extrafields->addExtraField('sigrebadge_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'sigrebadge@sigrebadge', 'isModEnabled("sigrebadge")');
		//$result5=$extrafields->addExtraField('sigrebadge_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'sigrebadge@sigrebadge', 'isModEnabled("sigrebadge")');

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('sigrebadge');
		$myTmpObjects = array();
		$myTmpObjects['MyBadge'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'MyBadge') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_mybadges.odt';
				$dirodt = DOL_DATA_ROOT.'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_mybadges.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
