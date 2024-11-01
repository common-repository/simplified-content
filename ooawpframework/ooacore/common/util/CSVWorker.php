<?php

include_once OOA_CORE_ROOT . "/common/util/ArrayUtils.php";

/**
 * Generic CSV Worker class.  This facilitates the creation and parsing of CSV data
 *
 * Class CSVWorker
 */
class CSVWorker {


    /**
     * Convert and stream the supplied data structure into CSV format.
     *
     * This is designed to be very tolerant in terms of data structure supplied as an array where each entry may be one of the following:
     *
     * 1) A regular numerical array for each column value
     * 2) An associative array keyed in by column names which may optionally be supplied in the columns array
     * 3) An object with members which optionally match column names supplied in the columns array
     *
     * The columns array should be supplied as an associative array where the name is the key (matching a data item) and the label
     * is the value which is returned as the first row if includeColumnHeaders is supplied.
     *
     * @param array $data
     * @param array $columns
     * @param bool $includeColumnHeaders
     * @param string $delimiter
     * @param string $enclosure
     */
    public function streamAsCSV($data = array(), $columns = array(), $includeColumnHeaders = true, $filename = "data.csv", $delimiter = ",", $enclosure = '"') {

        // Open output
        $out = fopen('php://output', 'w');

        // Set header if not already sent
        if (!headers_sent()) {
            header("Content-Type: text/csv");
            header('Content-Disposition:attachment; filename="' . $filename . '"');
        }

        // Calculate columns if applicable
        if (!$columns) {
            $columns = array();
            foreach ($data as $row) {
                if (is_array($row) && ArrayUtils::isAssociative($row)) {
                    $columns = array_merge($columns, array_keys($row));
                } else if (is_object($row) && $row instanceof SerialisableObject) {
                    $columns = array_merge($columns, array_keys($row->__getSerialisablePropertyMap()));
                }
            }

            // Now upper case and split on words
            $newColumns = array();
            foreach ($columns as $column) {
                $newColumns[$column] = trim(ucwords(preg_replace("/([A-Z])/", " $1", $column)));
            }

            $columns = $newColumns;
        }


        // Ensure columns are in standard format.
        if (!ArrayUtils::isAssociative($columns)) {
            $columns = array_combine($columns, $columns);
        }

        // If we have columns, include them if required
        if (sizeof($columns) > 0 && $includeColumnHeaders) {
            fputcsv($out, $columns, $delimiter, $enclosure);
        }

        // Loop through the data in turn and convert to CSV
        foreach ($data as $row) {

            // If we have columns specified
            if (sizeof($columns) > 0) {
                $rowData = array();
                foreach ($columns as $name => $label) {
                    if (is_array($row)) {
                        if (ArrayUtils::isAssociative($row)) $rowData[] = isset($row[$name]) ? $row[$name] : ""; else {
                            $rowData = $row;
                            break;
                        }
                    } else if (is_object($row) && $row instanceof SerialisableObject) {
                        $rowData[] = $row->__getSerialisablePropertyValue($name);
                    }
                }
            } else {
                $rowData = $row;
            }

            fputcsv($out, $rowData, $delimiter, $enclosure);
        }

        fclose($out);


    }


    /**
     * Wrapper to the above streaming method which captures the output as a buffer and returns as a string
     *
     * @param array $data
     * @param array $columns
     * @param bool $includeColumnHeaders
     * @param string $delimiter
     * @param string $enclosure
     */
    public function convertToCSVString($data = array(), $columns = array(), $includeColumnHeaders = true, $delimiter = ",", $enclosure = '"') {

        ob_start();
        $this->streamAsCSV($data, $columns, $includeColumnHeaders, null, $delimiter, $enclosure);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;

    }


}