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
        $mform = $this->_form;
        $days = (int) ($this->_customdata['days'] ?? 0);

        $mform->addElement('html', html_writer::div(
            get_string('warningmsg', 'tool_clearbackupfiles'),
            'alert alert-warning'
        ));

        $mform->addElement(
            'date_selector',
            'cutoffdate',
            get_string('cutoffdate', 'tool_clearbackupfiles'),
            ['optional' => true]
        );
        $mform->addHelpButton('cutoffdate', 'cutoffdate', 'tool_clearbackupfiles');

        $defaultkey = $days > 0 ? 'cutoffdefault' : 'cutoffdefaultall';
        $mform->addElement('static', 'cutoffdefault', '', get_string($defaultkey, 'tool_clearbackupfiles', $days));

        $runmodes = [
            $mform->createElement('radio', 'runmode', '', get_string('runmodenow', 'tool_clearbackupfiles'), 'now'),
            $mform->createElement('radio', 'runmode', '', get_string('runmodequeue', 'tool_clearbackupfiles'), 'queue'),
        ];
        $mform->addGroup($runmodes, 'runmodegroup', get_string('runmode', 'tool_clearbackupfiles'), '<br>', false);
        $mform->setDefault('runmode', 'now');
        $mform->setType('runmode', PARAM_ALPHA);
        $mform->addHelpButton('runmodegroup', 'runmode', 'tool_clearbackupfiles');

        $this->add_action_buttons(true, get_string('delete'));
    }

    /**
     * Validate the optional cutoff date.
     *
     * @param array $data Submitted data
     * @param array $files Submitted files
     * @return array Errors keyed by element name
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['cutoffdate']) && $data['cutoffdate'] > time()) {
            $errors['cutoffdate'] = get_string('cutoffdatefuture', 'tool_clearbackupfiles');
        }

        return $errors;
    }
}
