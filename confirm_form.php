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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Class confirm_form
 * @package   tool_clearbackupfiles
 * @copyright 2015 Shubhendra Doiphode
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirm_form extends moodleform {

    /**
     * Add elements to this form.
     */
    public function definition() {
        $warningmsg = get_string('warningmsg', 'tool_clearbackupfiles');

        $mform = $this->_form;

        $mform->addElement('html', $warningmsg);

        $this->add_action_buttons(true, get_string('delete'));
    }
}
