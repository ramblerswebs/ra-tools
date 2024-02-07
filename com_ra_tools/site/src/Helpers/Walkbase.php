<?php

/*
 * @version 1.0.7
 *
 * 08/12/22 tools walk
 * 30/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 */

// This contains generic code to Add/Delete/Get data or Update an Entity
// (in this case a Ra_wfWalk).
// It was created by /gener/gener.php on 220512 to handle all access to the database.
// It can be extended manually by another class as necessary to over-ride the default Get/Set functions and to
// add entity-specific processing that is similarly independent of database access.
//
// Diagnostic details of the database structure
// 0: Name id, length=10, type=3:N
// 1: Name walk_id, length=11, type=3:N
// 2: Name group_code, length=16, type=253:Y
// 3: Name organising_group, length=16, type=253:Y
// 4: Name walk_date, length=10, type=10:Y
// 5: Name title, length=480, type=253:Y
// 6: Name circular_or_linear, length=4, type=254:Y
// 7: Name start_postcode, length=32, type=253:Y
// 8: Name start_longitude, length=12, type=246:N
// 9: Name start_latitude, length=12, type=246:N
// 10: Name start_gridref, length=48, type=253:Y
// 11: Name start_details, length=480, type=253:Y
// 12: Name start_time, length=20, type=253:Y
// 13: Name meeting_postcode, length=32, type=253:Y
// 14: Name meeting_longitude, length=12, type=246:N
// 15: Name meeting_latitude, length=12, type=246:N
// 16: Name meeting_gridref, length=48, type=253:Y
// 17: Name meeting_time, length=20, type=253:Y
// 18: Name restriction, length=4, type=253:Y
// 19: Name difficulty, length=40, type=253:Y
// 20: Name distance_miles, length=5, type=246:N
// 21: Name distance_km, length=5, type=246:N
// 22: Name walking_time, length=20, type=253:Y
// 23: Name finishing_time, length=20, type=253:Y
// 24: Name contact_display_name, length=200, type=253:Y
// 25: Name contact_membership_no, length=11, type=3:N
// 26: Name contact_email, length=200, type=253:Y
// 27: Name contact_tel1, length=60, type=253:Y
// 28: Name contact_tel2, length=60, type=253:Y
// 29: Name contact_is_walk_leader, length=4, type=254:Y
// 30: Name walk_leader, length=120, type=253:Y
// 31: Name description, length=262140, type=252:Y
// 32: Name additional_notes, length=1020, type=253:Y
// 33: Name pace, length=200, type=253:Y
// 34: Name ascent_feet, length=40, type=253:Y
// 35: Name ascent_metres, length=40, type=253:Y
// 36: Name grade_local, length=40, type=253:Y
// 37: Name route_id, length=40, type=253:Y
// 38: Name state, length=11, type=3:N
// 39: Name notes, length=1020, type=253:Y
// 40: Name published, length=4, type=1:N
// 41: Name created_by, length=11, type=3:N
// 42: Name ordering, length=11, type=3:N
// 43: Name leader_user_id, length=11, type=3:N
// 44: Name finish_time, length=20, type=253:Y
// 45: Name duration, length=11, type=3:N
// 46: Name max_walkers, length=11, type=3:N
// 47: Name count_walkers, length=4, type=1:N
// 48: Name meeting_details, length=480, type=253:Y

namespace Ramblers\Component\Ra_tools\Site\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class Walkbase {

    public $fields_updated;
    public $message;
    public $objHelper;
// database fields
    public $id;
    public $walk_id;
    public $group_code;
    public $organising_group;
    public $walk_date;
    public $title;
    public $circular_or_linear;
    public $start_postcode;
    public $start_longitude;
    public $start_latitude;
    public $start_gridref;
    public $start_details;
    public $start_time;
    public $meeting_postcode;
    public $meeting_longitude;
    public $meeting_latitude;
    public $meeting_gridref;
    public $meeting_time;
    public $restriction;
    public $difficulty;
    public $distance_miles;
    public $distance_km;
    public $walking_time;
    public $finishing_time;
    public $contact_display_name;
    public $contact_membership_no;
    public $contact_email;
    public $contact_tel1;
    public $contact_tel2;
    public $contact_is_walk_leader;
    public $walk_leader;
    public $description;
    public $additional_notes;
    public $pace;
    public $ascent_feet;
    public $ascent_metres;
    public $grade_local;
    public $route_id;
    public $state;
    public $notes;
    public $published;
    public $created_by;
    public $ordering;
    public $leader_user_id;
    public $finish_time;
    public $duration;
    public $max_walkers;
    public $count_walkers;
    public $meeting_details;

    function __construct() {
        $this->objHelper = new ToolsHelper;
        $this->fields_updated = 0;

// initialise to zero all numeric fields
        $this->id = 0;
        $this->walk_id = 0;
        $this->start_longitude = 0;
        $this->start_latitude = 0;
        $this->meeting_longitude = 0;
        $this->meeting_latitude = 0;
        $this->distance_miles = 0;
        $this->distance_km = 0;
        $this->contact_membership_no = 0;
        $this->state = 0;
        $this->published = 0;
        $this->created_by = 0;
        $this->ordering = 0;
        $this->leader_user_id = 0;
        $this->duration = 0;
        $this->max_walkers = 0;
        $this->count_walkers = 0;
    }

    function add() {
        Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
// Prepare the insert query.
        $query
                ->insert($db->quoteName('#__ra_walks'))
                ->set('walk_id = ' . $db->quote($this->walk_id))
                ->set('group_code = ' . $db->quote($this->group_code))
                ->set('organising_group = ' . $db->quote($this->organising_group))
                ->set('walk_date = ' . $db->quote($this->walk_date))
                ->set('title = ' . $db->quote($this->title))
                ->set('circular_or_linear = ' . $db->quote($this->circular_or_linear))
                ->set('start_postcode = ' . $db->quote($this->start_postcode))
                ->set('start_longitude = ' . $db->quote($this->start_longitude))
                ->set('start_latitude = ' . $db->quote($this->start_latitude))
                ->set('start_gridref = ' . $db->quote($this->start_gridref))
                ->set('start_details = ' . $db->quote($this->start_details))
                ->set('start_time = ' . $db->quote($this->start_time))
                ->set('meeting_postcode = ' . $db->quote($this->meeting_postcode))
                ->set('meeting_longitude = ' . $db->quote($this->meeting_longitude))
                ->set('meeting_latitude = ' . $db->quote($this->meeting_latitude))
                ->set('meeting_gridref = ' . $db->quote($this->meeting_gridref))
                ->set('meeting_time = ' . $db->quote($this->meeting_time))
                ->set('restriction = ' . $db->quote($this->restriction))
                ->set('difficulty = ' . $db->quote($this->difficulty))
                ->set('distance_miles = ' . $db->quote($this->distance_miles))
                ->set('distance_km = ' . $db->quote($this->distance_km))
                ->set('walking_time = ' . $db->quote($this->walking_time))
                ->set('finishing_time = ' . $db->quote($this->finishing_time))
                ->set('contact_display_name = ' . $db->quote($this->contact_display_name))
                ->set('contact_membership_no = ' . $db->quote($this->contact_membership_no))
                ->set('contact_email = ' . $db->quote($this->contact_email))
                ->set('contact_tel1 = ' . $db->quote($this->contact_tel1))
                ->set('contact_tel2 = ' . $db->quote($this->contact_tel2))
                ->set('contact_is_walk_leader = ' . $db->quote($this->contact_is_walk_leader))
                ->set('walk_leader = ' . $db->quote($this->walk_leader))
                ->set('description = ' . $db->quote($this->description))
                ->set('additional_notes = ' . $db->quote($this->additional_notes))
                ->set('pace = ' . $db->quote($this->pace))
                ->set('ascent_feet = ' . $db->quote($this->ascent_feet))
                ->set('ascent_metres = ' . $db->quote($this->ascent_metres))
                ->set('grade_local = ' . $db->quote($this->grade_local))
                ->set('route_id = ' . $db->quote($this->route_id))
                ->set('state = ' . $db->quote($this->state))
                ->set('notes = ' . $db->quote($this->notes))
                ->set('published = ' . $db->quote($this->published))
                ->set('created_by = ' . $db->quote($this->created_by))
                ->set('ordering = ' . $db->quote($this->ordering))
                ->set('leader_user_id = ' . $db->quote($this->leader_user_id))
                ->set('finish_time = ' . $db->quote($this->finish_time))
                ->set('duration = ' . $db->quote($this->duration))
                ->set('max_walkers = ' . $db->quote($this->max_walkers))
                ->set('count_walkers = ' . $db->quote($this->count_walkers))
        ;
// Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        $db->execute();
//        echo $query;
        $this->id = $db->insertid();
        if ($this->id == 0) {
            $this->message = "Database error";
            return 0;
        }
        $this->createAudit("Record", "", "created");
        $this->message = "Record created";
        return 1;
    }

    function createAudit($field_name, $old_value, $new_value) {
        if (!$old_value == $new_value) {
            $this->fields_updated++;
            $this->objHelper->createAuditRecord($field_name, $old_value, $new_value, $this->id, "ra_walks");
        }
    }

    function delete($id) {
//        echo 'deleting walk ' . $id;
        if (strval($id) == 0) {
            return 0;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ra_walks_audit'));
        $query->where('object_id=' . $id);
        $db->setQuery($query);
        $result = $db->execute();
//        try {
//            $db->setQuery($query);
//            $result = $db->execute();
//        } catch (Exception $ex) {
//            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
////            return 0;
//        }
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ra_walks'));
        $query->where('id = ' . $id);
        try {
            $db->setQuery($query);
            $result = $db->execute();
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
            echo $this->error . '<br>';
        }
        //return $result;
        return true;
    }

    function getData() {
        $fields_updated = 0;
        if ($this->id == 0) {
            $message = "clsRa_wfWalkGetData - id is zero";
            return 0;
        }

        $sql = "Select ";
        $sql .= "id,";
        $sql .= "walk_id,";
        $sql .= "group_code,";
        $sql .= "organising_group,";
        $sql .= "walk_date,";
        $sql .= "title,";
        $sql .= "circular_or_linear,";
        $sql .= "start_postcode,";
        $sql .= "start_longitude,";
        $sql .= "start_latitude,";
        $sql .= "start_gridref,";
        $sql .= "start_details,";
        $sql .= "start_time,";
        $sql .= "meeting_postcode,";
        $sql .= "meeting_longitude,";
        $sql .= "meeting_latitude,";
        $sql .= "meeting_gridref,";
        $sql .= "meeting_time,";
        $sql .= "restriction,";
        $sql .= "difficulty,";
        $sql .= "distance_miles,";
        $sql .= "distance_km,";
        $sql .= "walking_time,";
        $sql .= "finishing_time,";
        $sql .= "contact_display_name,";
        $sql .= "contact_membership_no,";
        $sql .= "contact_email,";
        $sql .= "contact_tel1,";
        $sql .= "contact_tel2,";
        $sql .= "contact_is_walk_leader,";
        $sql .= "walk_leader,";
        $sql .= "description,";
        $sql .= "additional_notes,";
        $sql .= "pace,";
        $sql .= "ascent_feet,";
        $sql .= "ascent_metres,";
        $sql .= "grade_local,";
        $sql .= "route_id,";
        $sql .= "state,";
        $sql .= "notes,";
        $sql .= "published,";
        $sql .= "created_by,";
        $sql .= "ordering,";
        $sql .= "leader_user_id,";
        $sql .= "finish_time,";
        $sql .= "duration,";
        $sql .= "max_walkers,";
        $sql .= "count_walkers,";
        $sql .= "meeting_details ";
        $sql .= "FROM #__ra_walks ";
        $sql .= "WHERE #__ra_walks.id=" . $this->id;
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $db->setQuery($sql);
        $db->execute();
        $row = $db->loadObject();
        if (is_null($row)) {
            $this->message = 'Invalid SQL query';
            return 0;
        }
        $this->walk_id = $row->walk_id;
        $this->group_code = $row->group_code;
        $this->organising_group = $row->organising_group;
        $this->walk_date = $row->walk_date;
        $this->title = $row->title;
        $this->circular_or_linear = $row->circular_or_linear;
        $this->start_postcode = $row->start_postcode;
        $this->start_longitude = $row->start_longitude;
        $this->start_latitude = $row->start_latitude;
        $this->start_gridref = $row->start_gridref;
        $this->start_details = $row->start_details;
        $this->start_time = $row->start_time;
        $this->meeting_postcode = $row->meeting_postcode;
        $this->meeting_longitude = $row->meeting_longitude;
        $this->meeting_latitude = $row->meeting_latitude;
        $this->meeting_gridref = $row->meeting_gridref;
        $this->meeting_time = $row->meeting_time;
        $this->restriction = $row->restriction;
        $this->difficulty = $row->difficulty;
        $this->distance_miles = $row->distance_miles;
        $this->distance_km = $row->distance_km;
        $this->walking_time = $row->walking_time;
        $this->finishing_time = $row->finishing_time;
        $this->contact_display_name = $row->contact_display_name;
        $this->contact_membership_no = $row->contact_membership_no;
        $this->contact_email = $row->contact_email;
        $this->contact_tel1 = $row->contact_tel1;
        $this->contact_tel2 = $row->contact_tel2;
        $this->contact_is_walk_leader = $row->contact_is_walk_leader;
        $this->walk_leader = $row->walk_leader;
        $this->description = $row->description;
        $this->additional_notes = $row->additional_notes;
        $this->pace = $row->pace;
        $this->ascent_feet = $row->ascent_feet;
        $this->ascent_metres = $row->ascent_metres;
        $this->grade_local = $row->grade_local;
        $this->route_id = $row->route_id;
        $this->state = $row->state;
        $this->notes = $row->notes;
        $this->published = $row->published;
        $this->created_by = $row->created_by;
        $this->ordering = $row->ordering;
        $this->leader_user_id = $row->leader_user_id;
        $this->finish_time = $row->finish_time;
        $this->duration = $row->duration;
        $this->max_walkers = $row->max_walkers;
        $this->count_walkers = $row->count_walkers;
        $this->meeting_details = $row->meeting_details;
        return 1;
    }

    function update() {
        $this->fields_updated = 0;
// get old data
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $sql = "Select * from #__ra_walks where id=" . $this->id;
        $db->setQuery($sql);
        $row = $db->loadObject();
        if (!$row) {
            $this->message = "Unable to find data";
            return 0;
        }
// Must create audit records before record itself is updated,
// otherwise original value will be lost
        $this->createAudit('walk_id', $row->walk_id, $this->walk_id);
        $this->createAudit('group_code', $row->group_code, $this->group_code);
        $this->createAudit('organising_group', $row->organising_group, $this->organising_group);
        $this->createAudit('walk_date', $row->walk_date, $this->walk_date);
        $this->createAudit('title', $row->title, $this->title);
        $this->createAudit('circular_or_linear', $row->circular_or_linear, $this->circular_or_linear);
        $this->createAudit('start_postcode', $row->start_postcode, $this->start_postcode);
        $this->createAudit('start_longitude', $row->start_longitude, $this->start_longitude);
        $this->createAudit('start_latitude', $row->start_latitude, $this->start_latitude);
        $this->createAudit('start_gridref', $row->start_gridref, $this->start_gridref);
        $this->createAudit('start_details', $row->start_details, $this->start_details);
        $this->createAudit('start_time', $row->start_time, $this->start_time);
        $this->createAudit('meeting_postcode', $row->meeting_postcode, $this->meeting_postcode);
        $this->createAudit('meeting_longitude', $row->meeting_longitude, $this->meeting_longitude);
        $this->createAudit('meeting_latitude', $row->meeting_latitude, $this->meeting_latitude);
        $this->createAudit('meeting_gridref', $row->meeting_gridref, $this->meeting_gridref);
        $this->createAudit('meeting_time', $row->meeting_time, $this->meeting_time);
        $this->createAudit('restriction', $row->restriction, $this->restriction);
        $this->createAudit('difficulty', $row->difficulty, $this->difficulty);
        $this->createAudit('distance_miles', $row->distance_miles, $this->distance_miles);
        $this->createAudit('distance_km', $row->distance_km, $this->distance_km);
        $this->createAudit('walking_time', $row->walking_time, $this->walking_time);
        $this->createAudit('finishing_time', $row->finishing_time, $this->finishing_time);
        $this->createAudit('contact_display_name', $row->contact_display_name, $this->contact_display_name);
        $this->createAudit('contact_membership_no', $row->contact_membership_no, $this->contact_membership_no);
        $this->createAudit('contact_email', $row->contact_email, $this->contact_email);
        $this->createAudit('contact_tel1', $row->contact_tel1, $this->contact_tel1);
        $this->createAudit('contact_tel2', $row->contact_tel2, $this->contact_tel2);
        $this->createAudit('contact_is_walk_leader', $row->contact_is_walk_leader, $this->contact_is_walk_leader);
        $this->createAudit('walk_leader', $row->walk_leader, $this->walk_leader);
        $this->createAudit('description', $row->description, $this->description);
        $this->createAudit('additional_notes', $row->additional_notes, $this->additional_notes);
        $this->createAudit('pace', $row->pace, $this->pace);
        $this->createAudit('ascent_feet', $row->ascent_feet, $this->ascent_feet);
        $this->createAudit('ascent_metres', $row->ascent_metres, $this->ascent_metres);
        $this->createAudit('grade_local', $row->grade_local, $this->grade_local);
        $this->createAudit('route_id', $row->route_id, $this->route_id);
        $this->createAudit('state', $row->state, $this->state);
        $this->createAudit('notes', $row->notes, $this->notes);
        $this->createAudit('published', $row->published, $this->published);
        $this->createAudit('created_by', $row->created_by, $this->created_by);
        $this->createAudit('ordering', $row->ordering, $this->ordering);
        $this->createAudit('leader_user_id', $row->leader_user_id, $this->leader_user_id);
        $this->createAudit('finish_time', $row->finish_time, $this->finish_time);
        $this->createAudit('duration', $row->duration, $this->duration);
        $this->createAudit('max_walkers', $row->max_walkers, $this->max_walkers);
        $this->createAudit('count_walkers', $row->count_walkers, $this->count_walkers);
        $this->createAudit('meeting_details', $row->meeting_details, $this->meeting_details);

// Fields to update.
        $fields = array(
            $db->quoteName('walk_id') . ' = ' . $db->quote($this->walk_id),
            $db->quoteName('group_code') . ' = ' . $db->quote($this->group_code),
            $db->quoteName('organising_group') . ' = ' . $db->quote($this->organising_group),
            $db->quoteName('walk_date') . ' = ' . $db->quote($this->walk_date),
            $db->quoteName('title') . ' = ' . $db->quote($this->title),
            $db->quoteName('circular_or_linear') . ' = ' . $db->quote($this->circular_or_linear),
            $db->quoteName('start_postcode') . ' = ' . $db->quote($this->start_postcode),
            $db->quoteName('start_longitude') . ' = ' . $db->quote($this->start_longitude),
            $db->quoteName('start_latitude') . ' = ' . $db->quote($this->start_latitude),
            $db->quoteName('start_gridref') . ' = ' . $db->quote($this->start_gridref),
            $db->quoteName('start_details') . ' = ' . $db->quote($this->start_details),
            $db->quoteName('start_time') . ' = ' . $db->quote($this->start_time),
            $db->quoteName('meeting_postcode') . ' = ' . $db->quote($this->meeting_postcode),
            $db->quoteName('meeting_longitude') . ' = ' . $db->quote($this->meeting_longitude),
            $db->quoteName('meeting_latitude') . ' = ' . $db->quote($this->meeting_latitude),
            $db->quoteName('meeting_gridref') . ' = ' . $db->quote($this->meeting_gridref),
            $db->quoteName('meeting_time') . ' = ' . $db->quote($this->meeting_time),
            $db->quoteName('restriction') . ' = ' . $db->quote($this->restriction),
            $db->quoteName('difficulty') . ' = ' . $db->quote($this->difficulty),
            $db->quoteName('distance_miles') . ' = ' . $db->quote($this->distance_miles),
            $db->quoteName('distance_km') . ' = ' . $db->quote($this->distance_km),
            $db->quoteName('walking_time') . ' = ' . $db->quote($this->walking_time),
            $db->quoteName('finishing_time') . ' = ' . $db->quote($this->finishing_time),
            $db->quoteName('contact_display_name') . ' = ' . $db->quote($this->contact_display_name),
            $db->quoteName('contact_membership_no') . ' = ' . $db->quote($this->contact_membership_no),
            $db->quoteName('contact_email') . ' = ' . $db->quote($this->contact_email),
            $db->quoteName('contact_tel1') . ' = ' . $db->quote($this->contact_tel1),
            $db->quoteName('contact_tel2') . ' = ' . $db->quote($this->contact_tel2),
            $db->quoteName('contact_is_walk_leader') . ' = ' . $db->quote($this->contact_is_walk_leader),
            $db->quoteName('walk_leader') . ' = ' . $db->quote($this->walk_leader),
            $db->quoteName('description') . ' = ' . $db->quote($this->description),
            $db->quoteName('additional_notes') . ' = ' . $db->quote($this->additional_notes),
            $db->quoteName('pace') . ' = ' . $db->quote($this->pace),
            $db->quoteName('ascent_feet') . ' = ' . $db->quote($this->ascent_feet),
            $db->quoteName('ascent_metres') . ' = ' . $db->quote($this->ascent_metres),
            $db->quoteName('grade_local') . ' = ' . $db->quote($this->grade_local),
            $db->quoteName('route_id') . ' = ' . $db->quote($this->route_id),
            $db->quoteName('state') . ' = ' . $db->quote($this->state),
            $db->quoteName('notes') . ' = ' . $db->quote($this->notes),
            $db->quoteName('published') . ' = ' . $db->quote($this->published),
            $db->quoteName('created_by') . ' = ' . $db->quote($this->created_by),
            $db->quoteName('ordering') . ' = ' . $db->quote($this->ordering),
            $db->quoteName('leader_user_id') . ' = ' . $db->quote($this->leader_user_id),
            $db->quoteName('finish_time') . ' = ' . $db->quote($this->finish_time),
            $db->quoteName('duration') . ' = ' . $db->quote($this->duration),
            $db->quoteName('max_walkers') . ' = ' . $db->quote($this->max_walkers),
            $db->quoteName('count_walkers') . ' = ' . $db->quote($this->count_walkers),
            $db->quoteName('meeting_details') . ' = ' . $db->quote($this->meeting_details)
        );

// Conditions for which records should be updated
        $conditions = array(
            $db->quoteName('id') . ' = ' . $this->id
        );
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ra_walks'))->set($fields)->where($conditions);
        $db->setQuery($query);
//        echo $query . '<br>';
        $result = $db->execute();
        if ($result) {
            $this->message = $this->fields_updated . " fields updated";
            return 1;
        }
        $this->message = " Update failed";
        return 0;
    }

    function quote($data) {
// insert backslash before any single quotes
        return str_replace(chr(39), chr(92) . chr(39), $data);
    }

    function set_walk_id($newValue) {
        $this->walk_id = (int) $newValue; // ensure value is numeric
    }

    function set_group_code($newValue) {
        $this->group_code = trim(substr($newValue, 0, 16));
    }

    function set_organising_group($newValue) {
        $this->organising_group = trim(substr($newValue, 0, 16));
    }

    function set_walk_date($newValue) {
        $this->walk_date = substr($newValue, 6, 4) . "-" . substr($newValue, 3, 2) . "-" . substr($newValue, 0, 2);
    }

    function set_title($newValue) {
        $this->title = trim(substr($newValue, 0, 480));
    }

    function set_circular_or_linear($newValue) {
        $this->circular_or_linear = trim(substr($newValue, 0, 4));
    }

    function set_start_postcode($newValue) {
        $this->start_postcode = trim(substr($newValue, 0, 32));
    }

    function set_start_longitude($newValue) {
        $this->start_longitude = $newValue;
    }

    function set_start_latitude($newValue) {
        $this->start_latitude = $newValue;
    }

    function set_start_gridref($newValue) {
        $this->start_gridref = trim(substr($newValue, 0, 48));
    }

    function set_start_details($newValue) {
        $this->start_details = trim(substr($newValue, 0, 480));
    }

    function set_start_time($newValue) {
        $this->start_time = trim(substr($newValue, 0, 20));
    }

    function set_meeting_postcode($newValue) {
        $this->meeting_postcode = trim(substr($newValue, 0, 32));
    }

    function set_meeting_longitude($newValue) {
        $this->meeting_longitude = $newValue;
    }

    function set_meeting_latitude($newValue) {
        $this->meeting_latitude = $newValue;
    }

    function set_meeting_gridref($newValue) {
        $this->meeting_gridref = trim(substr($newValue, 0, 48));
    }

    function set_meeting_time($newValue) {
        $this->meeting_time = trim(substr($newValue, 0, 20));
    }

    function set_restriction($newValue) {
        $this->restriction = trim(substr($newValue, 0, 4));
    }

    function set_difficulty($newValue) {
        $this->difficulty = trim(substr($newValue, 0, 40));
    }

    function set_distance_miles($newValue) {
        $this->distance_miles = $newValue;
    }

    function set_distance_km($newValue) {
        $this->distance_km = $newValue;
    }

    function set_walking_time($newValue) {
        $this->walking_time = trim(substr($newValue, 0, 20));
    }

    function set_finishing_time($newValue) {
        $this->finishing_time = trim(substr($newValue, 0, 20));
    }

    function set_contact_display_name($newValue) {
        $this->contact_display_name = trim(substr($newValue, 0, 200));
    }

    function set_contact_membership_no($newValue) {
        $this->contact_membership_no = (int) $newValue; // ensure value is numeric
    }

    function set_contact_email($newValue) {
        $this->contact_email = trim(substr($newValue, 0, 200));
    }

    function set_contact_tel1($newValue) {
        $this->contact_tel1 = trim(substr($newValue, 0, 60));
    }

    function set_contact_tel2($newValue) {
        $this->contact_tel2 = trim(substr($newValue, 0, 60));
    }

    function set_contact_is_walk_leader($newValue) {
        $this->contact_is_walk_leader = trim(substr($newValue, 0, 4));
    }

    function set_walk_leader($newValue) {
        $this->walk_leader = trim(substr($newValue, 0, 120));
    }

    function set_description($newValue) {
        $this->description = $newValue;
    }

    function set_additional_notes($newValue) {
        $this->additional_notes = trim(substr($newValue, 0, 1020));
    }

    function set_pace($newValue) {
        $this->pace = trim(substr($newValue, 0, 200));
    }

    function set_ascent_feet($newValue) {
        $this->ascent_feet = trim(substr($newValue, 0, 40));
    }

    function set_ascent_metres($newValue) {
        $this->ascent_metres = trim(substr($newValue, 0, 40));
    }

    function set_grade_local($newValue) {
        $this->grade_local = trim(substr($newValue, 0, 40));
    }

    function set_route_id($newValue) {
        $this->route_id = trim(substr($newValue, 0, 40));
    }

    function set_state($newValue) {
        $this->state = (int) $newValue; // ensure value is numeric
    }

    function set_notes($newValue) {
        $this->notes = trim(substr($newValue, 0, 1020));
    }

    function set_published($newValue) {
        $this->published = (int) $newValue; // ensure value is numeric
    }

    function set_created_by($newValue) {
        $this->created_by = (int) $newValue; // ensure value is numeric
    }

    function set_ordering($newValue) {
        $this->ordering = (int) $newValue; // ensure value is numeric
    }

    function set_leader_user_id($newValue) {
        $this->leader_user_id = (int) $newValue; // ensure value is numeric
    }

    function set_finish_time($newValue) {
        $this->finish_time = trim(substr($newValue, 0, 20));
    }

    function set_duration($newValue) {
        $this->duration = (int) $newValue; // ensure value is numeric
    }

    function set_max_walkers($newValue) {
        $this->max_walkers = (int) $newValue; // ensure value is numeric
    }

    function set_count_walkers($newValue) {
        $this->count_walkers = (int) $newValue; // ensure value is numeric
    }

    function set_meeting_details($newValue) {
        $this->meeting_details = trim(substr($newValue, 0, 480));
    }

}
