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
 * Strings for component 'customcertelement_text', language 'en'.
 *
 * @package    customcertelement_text
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Text';
$string['privacy:metadata'] = 'The Text plugin does not store any personal data.';
$string['text'] = 'Text';
$string['text_help'] = 'This is the text that will display on the PDF.';
$string['text_placeholder'] = 'Text and placeholder';
$string['text_placeholder_help'] = 'This is the text that will display on the PDF. You can use placeholders for dynamically replace course or user information: <br/>
<ol>
    <li><strong>Course fields</strong>: course information is prefixed with <i>course_</i> for normal field (e.g. @{course_idnumber}) or <i>course_customfield_{shortname}</i> for custom fields (e.g. @{course_customfield_duration})</li>
    <li><strong>User fields</strong>: user information is prefixed with <i>user_</i> for normal fields (e.g. {@user_idnumber}) or <i>user_profile_field_{shortname}</i> for custom fields (e.g. @{user_profile_field_businessunit})</li>
</ol>
Note that if a field is missing, the whole text element will not display altogether.
';