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
 * The main newmodule configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_newmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_newmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_newmodule_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('newmodulename', 'newmodule'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');


        // Adding the standard "title" field.
        $mform->addElement('text', 'title', get_string('newmodulenameen', 'newmodule'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'newmodulename', 'newmodule');
        // add admin login checkbox
        $mform->addElement('checkbox', 'op_login_first', get_string('op_login_first', 'newmodule'));
        // add guest_login checkbox
        $mform->addElement('checkbox', 'guest_login', get_string('guest_login', 'newmodule'));
        $mform->addElement('editor', 'descr', get_string('description', 'newmodule'));
        $mform->setType('descr', PARAM_RAW);


        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
		$mform->addElement('text', 'max_users', get_string('max_users', 'newmodule'), array('size' => '23'));
		if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('max_users', PARAM_TEXT);
        } else {
            $mform->setType('max_users', PARAM_CLEANHTML);
        }
		$mform->addElement('text', 'timelimit', get_string('time_limit', 'newmodule'), array('size' => '23'));
		if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('timelimit', PARAM_TEXT);
        } else {
            $mform->setType('timelimit', PARAM_CLEANHTML);
        }
		$mform->addHelpButton('timelimit', 'time_limit', 'newmodule');
		//$mform->addRule('timelimit', get_string('regex_err', 'newmodule'), 'numeric', null, 'client');
        // Adding the rest of newmodule settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        // $mform->addElement('static', 'label1', 'newmodulesetting1', 'Your newmodule fields go here. Replace me!');
        $bigbluebuttonbn = null;
        $course = null;

        $mform->addElement('header', 'newmodulefieldset', get_string('newmodulefieldset', 'newmodule'));
        $mform->addElement('static', 'label2', 'newmodulesetting2', 'Your newmodule fields go here. Replace me!');
        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }



}
