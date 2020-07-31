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
 * Set of global functions to be used across the suite of DF plugins, to avoid code-duplication.
 *
 * @copyright   2019 onwards Conn Warwicker
 * @package     local_df_hub
 * @version     1.0
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */

defined('MOODLE_INTERNAL') or die();

/**
 * Convert to html entities
 * @param string $txt
 * @param bool $nl2br Whether or not to run the text through nl2br()
 * @return string
 */
function df_html($txt, $nl2br = false) {
    return ($nl2br) ? nl2br(  htmlspecialchars($txt, ENT_QUOTES) ) : htmlspecialchars($txt, ENT_QUOTES);
}

/**
 * Get an image URL from Moodle
 * This used to be using the normal $OUTPUT->image_url() or $OUTPUT->pix_url(), but it doesn't work in AJAX calls, as $OUTPUT is not initialised, so changed to $PAGE->theme
 * @param string $imagename
 * @param string $component
 * @return string
 */
function df_image_url($imagename, $component = 'moodle') {

    global $PAGE;

    if (method_exists($PAGE->theme, 'image_url')) {
        return $PAGE->theme->image_url($imagename, $component);
    } else {
        return $PAGE->theme->pix_url($imagename, $component);
    }

}

/**
 * Get how long ago a timestamp was
 * http://phppot.com/php/php-time-ago-function/
 * @param int $timestamp
 * @return string
 */
function df_time_ago($timestamp) {

    if ($timestamp < 1) {
        return "never";
    }

    $strTime = array("second", "minute", "hour", "day", "month", "year");
    $length = array(60, 60, 24, 30, 12, 10);

    $currentTime = time();
    if ($currentTime >= $timestamp) {
        $diff = time() - $timestamp;
        for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
            $diff = $diff / $length[$i];
        }
        $diff = round($diff);
        return $diff . " " . get_string('time:' . $strTime[$i], 'local_df_hub') . "(s) ago ";
    }

}

/**
 * This function acts as a replacement for optional_param_array, but also allows for multidimensional arrays, instead of just one level.
 *
 * @param string $parname the name of the page parameter we want
 * @param mixed $default the default value to return if nothing is found
 * @param string $type expected type of parameter
 * @param array $parent If passed through, this is the array which will be used, rather than checking in POST and GET
 * @return array
 * @throws coding_exception
 */
function df_optional_param_array_recursive($parname, $default, $type, $parent = null) {

    // The majority of this function is core code taken from optional_param_array.
    // If no parent is passed through, because we have not yet hit a multidimensional array, use the core code from optional_param_array and go through the normal process.
    // Otherwise, just use the array we passed through and clean that instead.
    if (is_null($parent)) {

        if (func_num_args() != 3 or empty($parname) or empty($type)) {
            throw new coding_exception('df_optional_array_param requires $parname, $default + $type to be specified (parameter: ' . $parname . ')');
        }

        // POST has precedence.
        if (isset($_POST[$parname])) {
            $param = $_POST[$parname];
        } else if (isset($_GET[$parname])) {
            $param = $_GET[$parname];
        } else {
            return $default;
        }

        if (!is_array($param)) {
            debugging('df_optional_array_param() expects array parameters only: ' . $parname);
            return $default;
        }

    } else {
        $param = $parent;
    }

    $result = array();

    foreach ($param as $key => $value) {

        if (!preg_match('/^[a-z0-9_ \-]+$/i', $key)) {
            debugging('Invalid key name in df_optional_array_param() detected: ('.$key.'), parameter: '.$parname);
            continue;
        }

        // If the value is an array, recursively go down through the levels, cleaning them all and keep the array
        // in the return value.
        if (is_array($value)) {
            $result[$key] = df_optional_param_array_recursive($parname, $default, $type, $value);
        } else {
            $result[$key] = clean_param($value, $type);
        }

    }

    return $result;

}