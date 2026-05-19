<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page shows all course enrolment options for current user.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('courseid', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);
$cd = required_param('cd',PARAM_RAW);
$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);


require_login();

$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
//$PAGE->navbar->add(get_string('enrolmentoptions','enrol'));

echo $OUTPUT->header();

//TODO: find if future enrolments present and display some info
$a= $USER->firstname.' '. $USER->lastname;?>


 <div style="text-align:center;margin-top:50px;position:absolute;width:100%;height:100%;top:0px;left:0px;">
   <div class="checkmark-circle">
       <div class="background"></div>
       <div class="checkmark draw"></div>
   </div>
   <h1>Certificate of course completion!</h1>  
   <p>This certifies that <?=fullname($USER);?> has successfully completed the training programme for <?=$course->fullname;?></p>
   <p>Course Completed Date <?=date('Y-m-d',$cd);?></p>
   
         <?php 

            $modinfo = get_fast_modinfo($course->id);
            $sections = $modinfo->get_section_info_all();
            $completion = new \completion_info($course);
            $data = [];
            foreach ($sections as $sectionnum => $sectioninfo) {
                $sectioninfo = $modinfo->get_section_info($sectionnum);
                
                $name = get_section_name($course->id, $sectioninfo);
                $unithours = $sectioninfo->unithours;
            
                $totalhours = 0;
                foreach ($modinfo->sections[$sectionnum] as $cmid) {
                    $cm = $modinfo->cms[$cmid];
                    $completiondata = $completion->get_data($cm, false, $USER->id);
                    $iscomplete = $completiondata->completionstate == COMPLETION_COMPLETE;

                    if($iscomplete){
                        $totalhours +=$unithours;
                    }
                    
                    
                }
                $data[] = [
                        'name' => $name,
                        'totalhours' => $totalhours
                ];
                                            
            }


                ?>
   <?php foreach($data as $datum){?>
        <div class="row">
            <div class="col-md-8">
                <b><?=$datum['name'];?></b>
        
            </div>
            <div class="col-md-4">
            <b><?=$datum['totalhours'];?></b>
        
            </div>
        </div>
    <?php }?>

    <thead><tr>
        <th class="header c0" scope="col">
            hello I am testing
            </th><th class="header c1" scope="col">
            hello I am testing
        </th></tr></thead>
   
   <div>   
 


<?php echo $OUTPUT->footer();
