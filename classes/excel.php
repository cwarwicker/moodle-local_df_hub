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
 * The class is used to generate and read excel spreadsheets
 *
 * @package     local_df_hub
 * @copyright   2011-2017 Bedford College, 2017 onwards Conn Warwicker
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */
namespace local_df_hub;

use core_useragent;
use MoodleExcelWorkbook;
use MoodleExcelWorksheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/lib/excellib.class.php');

class excel extends MoodleExcelWorkbook {

    /**
     * Get the PHPSpreadsheet object from the Moodle class
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet|\PhpSpreadsheet
     */
    public function getSpreadsheet() {
        return $this->objspreadsheet;
    }

    /**
     * Overwrite the parent add_worksheet method to add an excel_sheet instead of MoodleExcelWorksheet
     * @param string $name
     * @return excel_sheet|MoodleExcelWorksheet
     */
    public function addWorksheet($name = '') {
        return new excel_sheet($name, $this->objspreadsheet);
    }

    /**
     * Save the spreadsheet into a file instead of just displaying it for download
     * @param $file
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function save($file) {

        foreach ($this->objspreadsheet->getAllSheets() as $sheet) {
            $sheet->setSelectedCells('A1');
        }

        $this->objspreadsheet->setActiveSheetIndex(0);

        $objwriter = IOFactory::createWriter($this->objspreadsheet, $this->type);
        $objwriter->save($file);

    }

    /**
     * Serve the generated file to the web browser.
     * @return void
     */
    public function serve() {

        $mimetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $filename = preg_replace('/\.xlsx?$/i', '', $this->filename);
        $filename = $filename.'.xlsx';

        if (core_useragent::is_ie() || core_useragent::is_edge()) {
            $filename = rawurlencode($filename);
        } else {
            $filename = s($filename);
        }

        header('Content-Type: '.$mimetype);
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        $objwriter = IOFactory::createWriter($this->objspreadsheet, $this->type);
        $objwriter->save('php://output');

    }

}
