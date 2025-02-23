<?php
/* Copyright (C) 2005-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2024 	   Philippe GRAND 		<contact@atoo-net.com>
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
 * or see https://www.gnu.org/
 */

/**
 *  \file       htdocs/core/modules/sigrebadge/mod_mybadge_standard.php
 *  \ingroup    sigrebadge
 *  \brief      File of class to manage MyBadge numbering rules standard
 */
dol_include_once('/sigrebadge/core/modules/sigrebadge/modules_mybadge.php');


/**
 *	Class to manage the Standard numbering rule for MyBadge
 */
class mod_mybadge_standard extends ModeleNumRefMyBadge
{
	/**
	 * Dolibarr version of the loaded document
	 * @var string
	 */
	public $version = 'dolibarr'; // 'development', 'experimental', 'dolibarr'

	public $prefix;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string name
	 */
	public $name = 'standard';

	public function __construct()
    {
        global $conf;
        // Initialisation dynamique dans le constructeur
        $this->prefix = getDolGlobalString('SIGREBADGE_PREFIX') ? getDolGlobalString('SIGREBADGE_PREFIX') : 'DefaultPrefix';
    }


	/**
	 *  Return description of numbering module
	 *
	 *  @param      Translate   $langs Translate Object
	 *  @return     string             Text with description
	 */
	public function info($langs)
	{
		return $langs->trans("SimpleNumRefModelDesc", $this->prefix);
	}


	/**
	 *  Return an example of numbering
	 *
	 *  @return     string      Example
	 */
	public function getExample()
	{
		$yearMonth = dol_print_date(dol_now(), '%y%m');
		return $this->prefix.$yearMonth."-0001";
	}


	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 *  @param  Object		$object		Object we need next value for
	 *  @return boolean     			false if conflict, true if ok
	 */
	public function canBeActivated($object)
	{
		global $conf, $langs, $db;

		$coyymm = '';
		$max = '';

		$posindice = strlen($this->prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql .= " FROM ".MAIN_DB_PREFIX."sigrebadge_mybadge";
		$sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
		if ($object->ismultientitymanaged == 1) {
			$sql .= " AND entity = ".$conf->entity;
		} elseif ($object->ismultientitymanaged == 2) {
			// TODO
		}

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$coyymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($coyymm && !preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i', $coyymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
	}

	/**
	 * 	Return next free value
	 *
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	public function getNextValue($object)
	{
		global $db, $conf;

		// Utilisez le préfixe de l'objet si défini, sinon utilisez le préfixe par défaut de la classe
		$prefix = !empty($object->prefix) ? getDolGlobalString('SIGREBADGE_PREFIX') : $this->prefix;

		// first we get the max value
		$posindice = strlen($prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql .= " FROM ".MAIN_DB_PREFIX."sigrebadge_mybadge";
		$sql .= " WHERE ref LIKE '".$db->escape($prefix)."____-%'";
		if ($object->ismultientitymanaged == 1) {
			$sql .= " AND entity = ".$conf->entity;
		} elseif ($object->ismultientitymanaged == 2) {
			// TODO
		}
		
		$resql = $db->query($sql);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj) {
				$max = intval($obj->max);
			} else {
				$max = 0;
			}
		} else {
			dol_syslog("mod_mybadge_standard::getNextValue", LOG_DEBUG);
			return -1;
		}

		$date = $object->date_creation;
		$yymm = dol_print_date($date, "%y%m");

		if ($max >= (pow(10, 4) - 1)) {
			$num = $max + 1; // If counter > 9999, we do not format on 4 chars, we take number as it is
		} else {
			$num = sprintf("%04s", $max + 1);
		}
		
		dol_syslog("mod_mybadge_standard::getNextValue return ".$prefix.$yymm."-".$num);
		return $prefix.$yymm."-".$num;
	}
}
