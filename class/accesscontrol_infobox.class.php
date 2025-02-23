<?php
/*
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
 * Copyright (C) 2024 Philippe GRAND       <philippe.grand@atoo-net.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class for InfoBox in dashboard
 */
class AccessControlInfoBox
{
    /**
     * Name of positions 0=Home, 1=...
     *
     * @return array with list of zones
     */
    public function getListOfPagesForBoxes()
    {
        return array(0=>'Home');
    }

    /**
     * Return array of boxes qualified for area and user.
     *
     * @param DoliDB        $db                 	Database handler
     * @param string        $mode                   'available' or 'activated'
     * @param string        $zone                   Name or area (-1 for all, 0 for Homepage, 1 for xxx, ...)
     * @param User | null   $user                   Object user to filter
     * @param array         $excludelist            Array of box id (box.box_id = boxes_def.rowid) to exclude
     * @param int           $includehidden          Include also hidden boxes
     * @return array of boxes
     */
    public static function listBoxes($db, $mode, $zone, $user = null, $excludelist = array(), $includehidden = 1)
    {
        global $conf;

        $boxes=array();

        $confuserzone='MAIN_BOXES_'.$zone;
        if ($mode == 'activated')   // activated
        {
            $sql = "SELECT b.rowid, b.position, b.box_order, b.fk_user,";
            $sql.= " d.rowid as box_id, d.file, d.note, d.tms";
            $sql.= " FROM ".MAIN_DB_PREFIX."c_accesscontrol_boxes as b, ".MAIN_DB_PREFIX."c_accesscontrol_boxes_def as d";
            $sql.= " WHERE b.box_id = d.rowid";
            $sql.= " AND b.entity IN (0,".$conf->entity.")";
            if ($zone >= 0) $sql.= " AND b.position = ".$zone;
            if (is_object($user)) $sql.= " AND b.fk_user IN (0,".$user->id.")";
            else $sql.= " AND b.fk_user = 0";
            $sql.= " ORDER BY b.box_order";
        }
        else // available
        {
            $sql = "SELECT d.rowid as box_id, d.file, d.note, d.tms";
            $sql.= " FROM ".MAIN_DB_PREFIX."c_accesscontrol_boxes_def as d";
            $sql.= " WHERE d.entity IN (0,".$conf->entity.")";
        }

        dol_syslog(get_class()."::listBoxes get default box list for mode=".$mode." userid=".(is_object($user)?$user->id:'')."", LOG_DEBUG);
        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            $j = 0;
            while ($j < $num)
            {
                $obj = $db->fetch_object($resql);

                if (! in_array($obj->box_id, $excludelist))
                {
                    if (preg_match('/^([^@]+)@([^@]+)$/i', $obj->file, $regs))
                    {
                        $boxname = preg_replace('/\.php$/i', '', $regs[1]);
                        $module = $regs[2];
                        $relsourcefile = "/".$module."/core/boxes/".$boxname.".php";
                    }
                    else {
                        $boxname=preg_replace('/\.php$/i', '', $obj->file);
                        $relsourcefile = "/core/boxes/".$boxname.".php";
                    }

                    dol_include_once($relsourcefile);
                    if (class_exists($boxname))
                    {
                        $box=new $boxname($db, $obj->note);      // Constructor may set properties like box->enabled. obj->note is note into box def, not user params.
                        //$box=new stdClass();

                        // box properties
                        $box->rowid     = (empty($obj->rowid) ? '' : $obj->rowid);
                        $box->id        = (empty($obj->box_id) ? '' : $obj->box_id);
                        $box->position  = ($obj->position == '' ? '' : $obj->position);     // '0' must staty '0'
                        $box->box_order = (empty($obj->box_order) ? '' : $obj->box_order);
                        $box->fk_user   = (empty($obj->fk_user) ? 0 : $obj->fk_user);
                        $box->sourcefile= $relsourcefile;
                        $box->class     = $boxname;

                        if ($mode == 'activated' && ! is_object($user)) // List of activated box was not yet personalized into database
                        {
                            if (is_numeric($box->box_order))
                            {
                                if ($box->box_order % 2 == 1) $box->box_order='A'.$box->box_order;
                                elseif ($box->box_order % 2 == 0) $box->box_order='B'.$box->box_order;
                            }
                        }
                        // box_def properties
                        $box->box_id    = (empty($obj->box_id) ? '' : $obj->box_id);
                        $box->note      = (empty($obj->note) ? '' : $obj->note);

                        // Filter on box->enabled (used for example by box_comptes)
                        // Filter also on box->depends. Example: array("product|service") or array("contrat", "service")
                        $enabled=$box->enabled;
                        if (isset($box->depends) && count($box->depends) > 0)
                        {
                            foreach ($box->depends as $moduleelem)
                            {
                                $arrayelem=explode('|', $moduleelem);
                                $tmpenabled=0;  // $tmpenabled is used for the '|' test (OR)
                                foreach ($arrayelem as $module)
                                {
                                    $tmpmodule=preg_replace('/@[^@]+/', '', $module);
                                    if (! empty($conf->$tmpmodule->enabled)) $tmpenabled=1;
                                    //print $boxname.'-'.$module.'-module enabled='.(empty($conf->$tmpmodule->enabled)?0:1).'<br>';
                                }
                                if (empty($tmpenabled)) // We found at least one module required that is disabled
                                {
                                    $enabled=0;
                                    break;
                                }
                            }
                        }
                        //print '=>'.$boxname.'-enabled='.$enabled.'<br>';

                        //print 'xx module='.$module.' enabled='.$enabled;
                        if ($enabled && ($includehidden || empty($box->hidden))) $boxes[]=$box;
                        else unset($box);
                    }
                    else {
                        dol_syslog("Failed to load box '".$boxname."' into file '".$relsourcefile."'", LOG_WARNING);
                    }
                }
                $j++;
            }
        }
        else {
            dol_syslog($db->lasterror(), LOG_ERR);
            return array('error'=>$db->lasterror());
        }

        return $boxes;
    }

    /**
     * Save order of boxes for area and user.
     *
     * @param DoliDB    $db             Database handler
     * @param string    $zone           Name of area (0 for Homepage, ...)
     * @param string    $boxorder       List of boxes with correct order 'A:123,456,...-B:789,321...'
     * @param int       $userid         Id of user
     * @return int <0 if KO, 0=Nothing done, > 0 if OK
     */
    public function saveboxorder($db, $zone, $boxorder, $userid = 0)
    {
        global $conf;

        $error=0;

        require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

        dol_syslog(get_class()."::saveboxorder zone=".$zone." userid=".$userid);

        if (! $userid || $userid == 0) return 0;

        $user = new User($db);
        $user->id=$userid;

        $db->begin();

        // Save parameters to say user has a dedicated setup
        $tab=array();
        $confuserzone='MAIN_BOXES_'.$zone;
        $tab[$confuserzone]=1;
        if (dol_set_user_param($db, $conf, $user, $tab) < 0)
        {
            $error=$db->lasterror();
            $db->rollback();
            return -3;
        }

        // Delete all lines
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."c_accesscontrol_boxes";
        $sql.= " WHERE entity = ".$conf->entity;
        $sql.= " AND fk_user = ".$userid;
        $sql.= " AND position = ".$zone;

        dol_syslog(get_class()."::saveboxorder", LOG_DEBUG);
        $result = $db->query($sql);
        if ($result)
        {
            $colonnes=explode('-', $boxorder);
            foreach ($colonnes as $collist)
            {
                $part=explode(':', $collist);
                $colonne=$part[0];
                $list=$part[1];
                dol_syslog(get_class()."::saveboxorder column=".$colonne.' list='.$list);

                $i=0;
                $listarray=explode(',', $list);
                foreach ($listarray as $id)
                {
                    if (is_numeric($id))
                    {
                        //dol_syslog("aaaaa".count($listarray));
                        $i++;
                        $ii=sprintf('%02d', $i);
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_accesscontrol_boxes";
                        $sql.= "(box_id, position, box_order, fk_user, entity)";
                        $sql.= " values (";
                        $sql.= " ".$id.",";
                        $sql.= " ".$zone.",";
                        $sql.= " '".$colonne.$ii."',";
                        $sql.= " ".$userid.",";
                        $sql.= " ".$conf->entity;
                        $sql.= ")";

                        dol_syslog(get_class()."::saveboxorder", LOG_DEBUG);
                        $result = $db->query($sql);
                        if ($result < 0)
                        {
                            $error++;
                            break;
                        }
                    }
                }
            }
            if ($error)
            {
                $error=$db->error();
                $db->rollback();
                return -2;
            }
            else {
                $db->commit();
                return 1;
            }
        }
        else {
            $error=$db->lasterror();
            $db->rollback();
            dol_syslog(get_class()."::saveboxorder ".$error);
            return -1;
        }
    }
}
