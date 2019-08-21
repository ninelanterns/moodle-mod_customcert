<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * This file contains the customcert element text's core interaction API.
 *
 * @package    customcertelement_text
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_text;

defined('MOODLE_INTERNAL') || die();

/**
 * The customcert element text's core interaction API.
 *
 * @package    customcertelement_text
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \mod_customcert\element {

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \mod_customcert\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $mform->addElement('textarea', 'text', get_string('text', 'customcertelement_text'));
        $mform->setType('text', PARAM_RAW);
        $mform->addHelpButton('text', 'text_placeholder', 'customcertelement_text');

        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * customcert_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the text
     */
    public function save_unique_data($data) {
        return $data->text;
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        \mod_customcert\element_helper::render_content($pdf, $this, $this->get_text($user->id));
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        return \mod_customcert\element_helper::render_html_content($this, $this->get_text());
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \mod_customcert\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $element = $mform->getElement('text');
            $element->setValue($this->get_data());
        }
        parent::definition_after_data($mform);
    }

    /**
     * Helper function that returns the text.
     *
     * @return string
     */
    protected function get_text($user_id=0) {
        if (!$user_id) {
            global $USER;
            $user_id = $USER->id;
        }
        $course_id = \mod_customcert\element_helper::get_courseid($this->get_id());
        $context = \mod_customcert\element_helper::get_context($this->get_id());
        $data = $this->replace_placeholder($this->get_data(), $course_id, $user_id);
        return format_text($data, FORMAT_HTML, ['context' => $context]);
    }

    protected function replace_placeholder($text, $course_id, $user_id) {
        $matches = array();
        if (!preg_match_all('/@{([^}]+)}/', $text, $matches)) {
            return $text;
        }

        $replaced = $this->get_all_fields($course_id, $user_id);
        foreach ($matches[1] as $field) {
            if (empty($replaced[$field])) {
                return '';
            }
        }
        $replacevalues = $this->encapsulate_placeholder($replaced);
        return str_replace(array_keys($replacevalues), array_values($replacevalues), $text);
    }

    protected function get_all_fields($course_id, $user_id) {
        global $DB;
        $object = new \stdClass();

        $course = get_course($course_id);
        customfield_load_data($course, 'course', 'course');
        $durationfields = $DB->get_fieldset_select('course_info_field', 'shortname', 'datatype=:datatype', array('datatype' => 'duration'));
        foreach ($durationfields as $field) {
            $course->{"customfield_$field"} = self::translate_duration($course->{"customfield_$field"});
        }
        $this->merge_with_prefix($object, $course, 'course');

        $user = \core_user::get_user($user_id);
        profile_load_data($user);
        $this->merge_with_prefix($object, $user, 'user');

        return (array)$object;
    }

    protected function merge_with_prefix($source, $object, $prefix) {
        foreach ($object as $field => $value) {
            $source->{$prefix.'_'.$field} = $value;
        }
    }

    protected function encapsulate_placeholder($replaced) {
        $replacevalues = array();
        foreach ($replaced as $field => $value) {
            $replacevalues['@{'.$field.'}'] = $value;
        }
        return $replacevalues;
    }

    private static function translate_duration($seconds, $short = false) {
        $out = '';

        if ($short) {
            $out = intval($seconds) . ' '  . (intval($seconds) > 1 ? 'seconds' : 'second') . ' ';
            if ((intval($seconds) % MINSECS) == 0) {
                $minutes = (intval($seconds) / MINSECS);
                $out = $minutes . ' '  . ($minutes > 1 ? 'minutes' : 'minute') . ' ';
                if ((intval($seconds) % HOURSECS) == 0) {
                    $hours = (intval($seconds) / HOURSECS);
                    $out = $hours . ' ' . ($hours > 1 ? 'hours' : 'hour') . ' ';
                    if ((intval($seconds) % DAYSECS) == 0) {
                        $days = (intval($seconds) / DAYSECS);
                        $out = $days . ' ' . ($days > 1 ? 'days' : 'day') . ' ';
                        if ((intval($seconds) % WEEKSECS) == 0) {
                            $weeks = (intval($seconds) / WEEKSECS);
                            $out = $weeks . ' ' . ($weeks > 1 ? 'weeks' : 'week') . ' ';
                        }
                    }
                }
            }
            return $out;
        }
        /* days */
        $days = intval(intval($seconds) / (HOURSECS*24));
        if($days > 0){
            $out .= $days . ' ' . ($days > 1 ? 'days' : 'day') . ' ';
        }

        /* hours */
        $hours = (intval($seconds) / HOURSECS) % 24;
        if($hours > 0){
            $out .= $hours . ' ' . ($hours > 1 ? 'hours' : 'hour') . ' ';
        }

        /* minutes */
        $minutes = (intval($seconds) / MINSECS) % 60;
        if($minutes > 0){
            $out .= $minutes . ' '  . ($minutes > 1 ? 'minutes' : 'minute') . ' ';
        }

        /* seconds */
        $sec = intval($seconds) % MINSECS;
        if ($sec > 0){
            $out .= $sec . ' '  . ($sec > 1 ? 'seconds' : 'second') . ' ';
        }

        return $out;
    }
}
