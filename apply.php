<?php
/**
 * *************************************************************************
 * *                  Apply	Enrol   				                      **
 * *************************************************************************
 * @copyright   emeneo.com                                                **
 * @link        emeneo.com                                                **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
*/
require ('../../config.php');
require_once($CFG->dirroot.'/enrol/renderer.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot.'/lib/outputcomponents.php');
require_once ('lib.php');

$site = get_site ();
$systemcontext = get_context_instance ( CONTEXT_SYSTEM );

$id = required_param ( 'id', PARAM_INT ); // course id
$course = $DB->get_record ( 'course', array ('id' => $id ), '*', MUST_EXIST );
$context = get_context_instance ( CONTEXT_COURSE, $course->id, MUST_EXIST );

require_login ( $course );
require_capability ( 'moodle/course:enrolreview', $context );

$PAGE->set_url ( '/enrol/apply.php', array ('id' => $course->id ) );
//$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout ( 'admin' );
$PAGE->set_heading ( $course->fullname );

$PAGE->navbar->add ( get_string ( 'confirmusers', 'enrol_apply' ) );
$PAGE->set_title ( "$site->shortname: " . get_string ( 'confirmusers', 'enrol_apply' ) );

if (isset ( $_POST ['enrolid'] )) {
	if ($_POST ['enrolid']) {
		if ($_POST ['type'] == 'confirm') {
			confirmEnrolment ( $_POST ['enrolid'] );
		} elseif ($_POST ['type'] == 'wait') {
			waitEnrolment ( $_POST ['enrolid'] );
		} elseif ($_POST ['type'] == 'cancel') {
			cancelEnrolment ( $_POST ['enrolid'] );
		}
		redirect ( "$CFG->wwwroot/enrol/apply/apply.php?id=" . $id . "&enrolid=" . $_GET ['enrolid'] );
	}
}

$enrols = getAllEnrolment ($id);

//get the record of this enrol instance in order to check if the questions are set
$enrol_instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'apply', 'id'=>$_GET ['enrolid']), '*', MUST_EXIST);

echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( 'confirmusers', 'enrol_apply' ) );
echo get_string('confirmusers_desc', 'enrol_apply');
echo '<form id="frmenrol" method="post" action="apply.php?id=' . $id . '&enrolid=' . $_GET ['enrolid'] . '">';
echo '<input type="hidden" id="type" name="type" value="confirm">';

echo '<table style="width: 100%; font-size: small;margin-bottom: 2em;" class="generalbox editcourse boxalignleft">';
echo '<tr class="header">';
echo '<th  style="width: auto" scope="col">&nbsp;</th>';
echo '<th  style="width: auto" scope="col">' . get_string ( 'applyuser', 'enrol_apply' ) . '</th>';
echo '<th  style="width: auto" scope="col">' . get_string ( 'applydate', 'enrol_apply' ) . '</th>';

//If the questions are set in the settings, the col will be displayed
if ($enrol_instance->customint1 == 1){
    echo '<th  style="width: 35%" scope="col">' . get_string ( 'q1', 'enrol_apply' ) . '</th>';
}
if ($enrol_instance->customint2 == 1){
    echo '<th  style="width: 35%" scope="col">' . get_string ( 'q2', 'enrol_apply' ) . '</th>';
}

echo '</tr>';
foreach ( $enrols as $enrol ) {
	$picture = get_user_picture($enrol->userid);
	if ($enrol->status == 2) {
		echo '<tr style="vertical-align: top; background-color: #ccc;">';
	} else {
		echo '<tr style="vertical-align: top;">';
	}
    echo '<td><input type="checkbox" name="enrolid[]" value="' . $enrol->id . '"></td>';
	echo '<td>' . $OUTPUT->render($picture) .' '. $enrol->firstname . ' ' . $enrol->lastname .'<br /><br />'. $enrol->email .'</td>';
    echo '<td>' . date ( "Y-m-d", $enrol->timecreated ) . '</td>';

    //If the questions are set in the settings, the col will be displayed
    if ($enrol_instance->customint1 == 1){
        echo '<td>' . $enrol->q1 . '</td>';
    }
    if ($enrol_instance->customint2 == 1){
        echo '<td>' . $enrol->q2 . '</td>';
    }
	echo '</tr>';
}
echo '</table>';
echo '<p>';
echo '<input type="button" value="' . get_string ( 'btnconfirm', 'enrol_apply' ) . '" onclick="doSubmit(\'confirm\');">';
echo '<input type="button" value="' . get_string ( 'btnwait', 'enrol_apply' ) . '" onclick="doSubmit(\'wait\');">';
echo '<input type="button" value="' . get_string ( 'btncancel', 'enrol_apply' ) . '" onclick="doSubmit(\'cancel\');">';
echo '<input type="button" onclick="history.back();" value="'. get_string ( 'back' ) . '">';
echo '</p>';
echo '</form>';
echo '<script>function doSubmit(type){
	document.getElementById("type").value=type;
	document.getElementById("frmenrol").submit();
}</script>';
echo $OUTPUT->footer ();


function get_user_picture($userid){
	global $DB;

    $extrafields[] = 'lastaccess';
    $ufields = user_picture::fields('u', $extrafields);
	$sql = "SELECT DISTINCT $ufields FROM {user} u where u.id=$userid";
          
    $user = $DB->get_record_sql($sql);
	return new user_picture($user);
}
