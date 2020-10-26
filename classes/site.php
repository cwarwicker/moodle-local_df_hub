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
 * Site class
 *
 * This specifies general methods to run on the site, not related specifically to any of the plugins in the suite,
 * such as checking which plugins are installed, getting site name, url, etc...
 *
 * @copyright   2019 onwards Conn Warwicker
 * @package     local_df_hub
 * @version     1.0
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */

namespace local_df_hub;

defined('MOODLE_INTERNAL') or die();

class site
{

    /**
     * @var Site's title
     */
    private $title;

    /**
     * @var Site's URL
     */
    private $url;

    /**
     * @var Site's web directory
     */
    private $dir;

    /**
     * @var Site's data directory
     */
    private $data;

    /**
     * Site's Moodle version
     * @var false|string
     */
    private $version;

    /**
     * Site's build version
     * @var mixed
     */
    private $build;

    /**
     * Array of plugins in the DF suite which are installed
     * @var array
     */
    private $plugins = array();

    /**
     * Array of available plugins in the suite
     * @var array
     */
    private $availablePlugins = array(
        'assignfeedback_gradetracker',
        'block_df_dashboard',
        'block_elbp',
        'block_elbp_timetable',
        'block_gradetracker',
        'block_quick_course',
        'block_quick_user',
        'local_parentportal'
    );

    /**
     * site constructor.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct() {

        global $CFG;

        $siteCourse = \get_site();

        $this->title = $siteCourse->fullname;
        $this->url = $CFG->wwwroot;
        $this->dir = $CFG->dirroot;
        $this->data = $CFG->dataroot;
        $this->version = \moodle_major_version();
        $this->build = \get_config(null, 'version');

        // Check plugins
        $this->plugins = $this->loadInstalledPlugins();

    }

    /**
     * Get site title
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Get site url
     * @return mixed
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * Get site web directory
     * @return mixed
     */
    public function getDir() {
        return $this->dir;
    }

    /**
     * Get site data directory
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get site moodle version
     * @return false|string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Get site build version
     * @return mixed
     */
    public function getBuild() {
        return $this->build;
    }

    /**
     * Check which of the available plugins are installed and load them into the plugins array
     * @return array
     * @throws \dml_exception
     */
    public function loadInstalledPlugins() {

        $plugins = array();

        foreach ($this->availablePlugins as $component) {

            // Is it installed?
            $version = \get_config($component, 'version');
            if ($version !== false) {
                $plugins[] = $component;
            }

        }

        return $plugins;

    }

    /**
     * Get the array of installed plugins
     * @return array
     */
    public function getInstalledPlugins() {
        return $this->plugins;
    }

    /**
     * Get the version of a particular plugin
     * @param string $component
     * @return mixed
     * @throws \dml_exception
     */
    public function getPluginVersion($component) {
        return \get_config($component, 'version');
    }

    /**
     * Get the directory of a particular plugin
     * @param string $component
     * @return string
     */
    private function getPluginDirectory($component) {

        list($plugintype, $pluginname) = \core_component::normalize_component($component);
        return \core_component::get_plugin_directory($plugintype, $pluginname);

    }

    /**
     * Get the stats from a particular plugin
     * @param string $component
     * @return array
     */
    public function getPluginStats($component) {

        $stats = array();

        $directory = $this->getPluginDirectory($component);

        $file = $directory . '/classes/df_hub/stats.php';
        if (file_exists($file)) {
            include($file);
        }

        return $stats;

    }

    /**
     * Get the date this was last updated, in an optional date format or just unix timestamp
     * @param string|false $format
     * @return string
     * @throws \dml_exception
     */
    public function getLastUpdated($format = false) {
        $unix = \get_config('local_df_hub', 'lastupdate');
        return ($unix && $format) ? date($format, $unix) : $unix;
    }

}
