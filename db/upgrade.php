<?php

/**
 * *************************************************************************
 * *                  Apply     Enrol                                     **
 * *************************************************************************
 * @copyright   emeneo.com                                                **
 * @link        emeneo.com                                                **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
 */

function xmldb_enrol_apply_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    /// Add a new column newcol to the mdl_myqtype_options
    if ($oldversion < 2014082600) {
        // Define table enrol_apply to be created.
        $table = new xmldb_table('enrol_apply');

        // Adding fields to table enrol_apply.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('enrolid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('q1', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('q2', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table enrol_apply.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for enrol_apply.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, '2014082600', 'enrol', 'apply');
    }

    return true;
}