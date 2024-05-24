<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024 Philippe GRAND <contact@atoo-net.com>
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

use Luracast\Restler\RestException;

dol_include_once('/sigrebadge/class/mybadge.class.php');



/**
 * \file    sigrebadge/class/api_sigrebadge.class.php
 * \ingroup sigrebadge
 * \brief   File for API management of mybadge.
 */

/**
 * API class for sigrebadge mybadge
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class SigreBadgeApi extends DolibarrApi
{
	/**
	 * @var MyBadge $mybadge {@type MyBadge}
	 */
	public $mybadge;

	/**
	 * Constructor
	 *
	 * @url     GET /
	 *
	 */
	public function __construct()
	{
		global $db;
		$this->db = $db;
		$this->mybadge = new MyBadge($this->db);
	}

	/*begin methods CRUD*/
	/*CRUD FOR MYBADGE*/

	/**
	 * Get properties of a mybadge object
	 *
	 * Return an array with mybadge informations
	 *
	 * @param	int		$id				ID of mybadge
	 * @return  Object					Object with cleaned properties
	 *
	 * @url	GET mybadges/{id}
	 *
	 * @throws RestException 401 Not allowed
	 * @throws RestException 404 Not found
	 */
	public function get($id)
	{
		if (!DolibarrApiAccess::$user->rights->sigrebadge->mybadge->read) {
			throw new RestException(401);
		}

		$result = $this->mybadge->fetch($id);
		if (!$result) {
			throw new RestException(404, 'MyBadge not found');
		}

		if (!DolibarrApi::_checkAccessToResource('mybadge', $this->mybadge->id, 'sigrebadge_mybadge')) {
			throw new RestException(401, 'Access to instance id='.$this->mybadge->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->mybadge);
	}


	/**
	 * List mybadges
	 *
	 * Get a list of mybadges
	 *
	 * @param string		   $sortfield			Sort field
	 * @param string		   $sortorder			Sort order
	 * @param int			   $limit				Limit for list
	 * @param int			   $page				Page number
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param string		   $properties			Restrict the data returned to theses properties. Ignored if empty. Comma separated list of properties names
	 * @return  array                               Array of order objects
	 *
	 * @throws RestException
	 *
	 * @url	GET /mybadges/
	 */
	public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '', $properties = '')
	{
		global $db, $conf;

		$obj_ret = array();
		$tmpobject = new MyBadge($this->db);

		if (!DolibarrApiAccess::$user->rights->sigrebadge->mybadge->read) {
			throw new RestException(401);
		}

		$socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : '';

		$restrictonsocid = 0; // Set to 1 if there is a field socid in table of object

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if ($restrictonsocid && !DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) {
			$search_sale = DolibarrApiAccess::$user->id;
		}

		$sql = "SELECT t.rowid";
		if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) {
			$sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
		}
		$sql .= " FROM ".MAIN_DB_PREFIX.$tmpobject->table_element." AS t LEFT JOIN ".MAIN_DB_PREFIX.$tmpobject->table_element."_extrafields AS ef ON (ef.fk_object = t.rowid)"; // Modification VMR Global Solutions to include extrafields as search parameters in the API GET call, so we will be able to filter on extrafields

		if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) {
			$sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale
		}
		$sql .= " WHERE 1 = 1";

		// Example of use $mode
		//if ($mode == 1) $sql.= " AND s.client IN (1, 3)";
		//if ($mode == 2) $sql.= " AND s.client IN (2, 3)";

		if ($tmpobject->ismultientitymanaged) {
			$sql .= ' AND t.entity IN ('.getEntity($tmpobject->element).')';
		}
		if ($restrictonsocid && (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socid) || $search_sale > 0) {
			$sql .= " AND t.fk_soc = sc.fk_soc";
		}
		if ($restrictonsocid && $socid) {
			$sql .= " AND t.fk_soc = ".((int) $socid);
		}
		if ($restrictonsocid && $search_sale > 0) {
			$sql .= " AND t.rowid = sc.fk_soc"; // Join for the needed table to filter by sale
		}
		// Insert sale filter
		if ($restrictonsocid && $search_sale > 0) {
			$sql .= " AND sc.fk_user = ".((int) $search_sale);
		}
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		$i = 0;
		if ($result) {
			$num = $this->db->num_rows($result);
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$tmp_object = new MyBadge($this->db);
				if ($tmp_object->fetch($obj->rowid)) {
					$obj_ret[] = $this->_filterObjectProperties($this->_cleanObjectDatas($tmp_object), $properties);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving mybadge list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Create mybadge object
	 *
	 * @param array $request_data   Request datas
	 * @return int  ID of mybadge
	 *
	 * @throws RestException
	 *
	 * @url	POST mybadges/
	 */
	public function post($request_data = null)
	{
		if (!DolibarrApiAccess::$user->rights->sigrebadge->mybadge->write) {
			throw new RestException(401);
		}

		// Check mandatory fields
		$result = $this->_validate($request_data);

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again whith the caller
				$this->mybadge->context['caller'] = $request_data['caller'];
				continue;
			}

			$this->mybadge->$field = $this->_checkValForAPI($field, $value, $this->mybadge);
		}

		// Clean data
		// $this->mybadge->abc = sanitizeVal($this->mybadge->abc, 'alphanohtml');

		if ($this->mybadge->create(DolibarrApiAccess::$user)<0) {
			throw new RestException(500, "Error creating MyBadge", array_merge(array($this->mybadge->error), $this->mybadge->errors));
		}
		return $this->mybadge->id;
	}

	/**
	 * Update mybadge
	 *
	 * @param int   $id             Id of mybadge to update
	 * @param array $request_data   Datas
	 * @return int
	 *
	 * @throws RestException
	 *
	 * @url	PUT mybadges/{id}
	 */
	public function put($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->rights->sigrebadge->mybadge->write) {
			throw new RestException(401);
		}

		$result = $this->mybadge->fetch($id);
		if (!$result) {
			throw new RestException(404, 'MyBadge not found');
		}

		if (!DolibarrApi::_checkAccessToResource('mybadge', $this->mybadge->id, 'sigrebadge_mybadge')) {
			throw new RestException(401, 'Access to instance id='.$this->mybadge->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again whith the caller
				$this->mybadge->context['caller'] = $request_data['caller'];
				continue;
			}

			$this->mybadge->$field = $this->_checkValForAPI($field, $value, $this->mybadge);
		}

		// Clean data
		// $this->mybadge->abc = sanitizeVal($this->mybadge->abc, 'alphanohtml');

		if ($this->mybadge->update(DolibarrApiAccess::$user, false) > 0) {
			return $this->get($id);
		} else {
			throw new RestException(500, $this->mybadge->error);
		}
	}

	/**
	 * Delete mybadge
	 *
	 * @param   int     $id   MyBadge ID
	 * @return  array
	 *
	 * @throws RestException
	 *
	 * @url	DELETE mybadges/{id}
	 */
	public function delete($id)
	{
		if (!DolibarrApiAccess::$user->rights->sigrebadge->mybadge->delete) {
			throw new RestException(401);
		}
		$result = $this->mybadge->fetch($id);
		if (!$result) {
			throw new RestException(404, 'MyBadge not found');
		}

		if (!DolibarrApi::_checkAccessToResource('mybadge', $this->mybadge->id, 'sigrebadge_mybadge')) {
			throw new RestException(401, 'Access to instance id='.$this->mybadge->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		if ($this->mybadge->delete(DolibarrApiAccess::$user) == 0) {
			throw new RestException(409, 'Error when deleting MyBadge : '.$this->mybadge->error);
		} elseif ($this->mybadge->delete(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, 'Error when deleting MyBadge : '.$this->mybadge->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'MyBadge deleted'
			)
		);
	}


	/**
	 * Validate fields before create or update object
	 *
	 * @param	array		$data   Array of data to validate
	 * @return	array
	 *
	 * @throws	RestException
	 */
	private function _validate($data)
	{
		$mybadge = array();
		foreach ($this->mybadge->fields as $field => $propfield) {
			if (in_array($field, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat')) || $propfield['notnull'] != 1) {
				continue; // Not a mandatory field
			}
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$mybadge[$field] = $data[$field];
		}
		return $mybadge;
	}

	/*END CRUD FOR MYBADGE*/
	/*end methods CRUD*/

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 * Clean sensible object datas
	 *
	 * @param   Object  $object     Object to clean
	 * @return  Object              Object with cleaned properties
	 */
	protected function _cleanObjectDatas($object)
	{
		// phpcs:enable
		$object = parent::_cleanObjectDatas($object);

		unset($object->rowid);
		unset($object->canvas);

		/*unset($object->name);
		unset($object->lastname);
		unset($object->firstname);
		unset($object->civility_id);
		unset($object->statut);
		unset($object->state);
		unset($object->state_id);
		unset($object->state_code);
		unset($object->region);
		unset($object->region_code);
		unset($object->country);
		unset($object->country_id);
		unset($object->country_code);
		unset($object->barcode_type);
		unset($object->barcode_type_code);
		unset($object->barcode_type_label);
		unset($object->barcode_type_coder);
		unset($object->total_ht);
		unset($object->total_tva);
		unset($object->total_localtax1);
		unset($object->total_localtax2);
		unset($object->total_ttc);
		unset($object->fk_account);
		unset($object->comments);
		unset($object->note);
		unset($object->mode_reglement_id);
		unset($object->cond_reglement_id);
		unset($object->cond_reglement);
		unset($object->shipping_method_id);
		unset($object->fk_incoterms);
		unset($object->label_incoterms);
		unset($object->location_incoterms);
		*/

		// If object has lines, remove $db property
		if (isset($object->lines) && is_array($object->lines) && count($object->lines) > 0) {
			$nboflines = count($object->lines);
			for ($i = 0; $i < $nboflines; $i++) {
				$this->_cleanObjectDatas($object->lines[$i]);

				unset($object->lines[$i]->lines);
				unset($object->lines[$i]->note);
			}
		}

		return $object;
	}
}
