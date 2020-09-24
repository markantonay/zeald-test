<?php

require_once('vendor/autoload.php');
use Illuminate\Support;
use LSS\Array2Xml;

// global connection object
global $mysqli_db;
$mysqli_db = new mysqli('localhost', 'root', '', $database ?: 'employees');

/**
 * Execute a query & return the resulting data as an array of assoc arrays
 * @param string $sql query to execute
 * @return boolean|array array of associative arrays - query results for select
 *     otherwise true or false for insert/update/delete success
 */
function query($sql) {
    global $mysqli_db;
    $result = $mysqli_db->query($sql);
    if (!is_object($result)) {
        return $result;
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Debug method - dumps a print_r of any passed variables and exits
 * @param mixed any number of variables you wish to inspect
 */
function e() {
    $args = func_get_args();
    global $noexit;
    foreach ($args as $arg) {
        $out = print_r($arg, 1);
        echo '<pre>' . $out . '</pre><hr />';
    }
    if (!$noexit) {
        $bt = debug_backtrace();
        exit('<i>Called from: ' . $bt[0]['file'] . ' (' . ($bt[1]['class'] ? $bt[1]['class'] . ':' : '') . $bt[1]['function'] . ')</i>');
    }
}

/**
 * Fail method - used in testing to output a decent failure message
 * @param string message the message to output
 */
function fail($message) {
    exit('<div style="display: inline-block; color: #a94442; background: #f2dede; border: solid 1px #ebccd1; font-family: Helvetica, Arial; size: 16px; padding: 15px;">Test failed: ' . $message . '</div>');
}

/**
 * Pass method - used in testing to output a decent pass message
 * @param string message the message to output
 */
function pass($message) {
    exit('<div style="display: inline-block; color: #3c763d; background: #dff0d8; border: solid 1px #d6e9c6; font-family: Helvetica, Arial; size: 16px; padding: 15px;">Test failed: ' . $message . '</div>');
}

/**
 * Display an array of assoc arrays (query resultset) as an HTML table
 * Depends on Laravel collections class being available
 * @param array an array of assoc arrays
 * @return string HTML table
 */
function asTable($data) {
    if (!$data || !is_array($data) || !count($data)) {
        return 'Sorry, no matching data was found';
    }
    $data = collect($data);
    $styles = '<style type="text/css">body{font:16px Roboto,Arial,Helvetica,Sans-serif}td,th{padding:4px 8px}th{background:#eee;font-weight:500}tr:nth-child(odd){background:#f4f4f4}</style>';

    // extract headings
    // replace underscores with space & ucfirst each word for a decent heading
    $headings = collect($data->get(0))->keys();
    $headings = $headings->map(function($item, $key) {
        return collect(explode('_', $item))
            ->map(function($item, $key) {
                return ucfirst($item);
            })
            ->join(' ');
    });
    $headings = '<tr><th>' . $headings->join('</th><th>') . '</th></tr>';

    // output data
    $rows = [];
    foreach ($data as $dataRow) {
        $row = '<tr>';
        foreach ($dataRow as $key => $value) {
            $row .= '<td>' . $value . '</td>';
        }
        $row .= '</tr>';
        $rows[] = $row;
    }
    $rows = implode('', $rows);
    return $styles . '<table>' . $headings . $rows . '</table>';
}



function format($data, $format = 'html') {
        
    // return the right data format
    switch($format) {
        case 'xml':
            header('Content-type: text/xml');
            
            // fix any keys starting with numbers
            $keyMap = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
            $xmlData = [];
            foreach ($data->all() as $row) {
                $xmlRow = [];
                foreach ($row as $key => $value) {
                    $key = preg_replace_callback('(\d)', function($matches) use ($keyMap) {
                        return $keyMap[$matches[0]] . '_';
                    }, $key);
                    $xmlRow[$key] = $value;
                }
                $xmlData[] = $xmlRow;
            }
            $xml = Array2XML::createXML('data', [
                'entry' => $xmlData
            ]);
            return $xml->saveXML();
            break;
        case 'json':
            header('Content-type: application/json');
            return json_encode($data->all());
            break;
        case 'csv':
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv";');
            if (!$data->count()) {
                return;
            }
            $csv = [];
            
            // extract headings
            // replace underscores with space & ucfirst each word for a decent headings
            $headings = collect($data->get(0))->keys();
            $headings = $headings->map(function($item, $key) {
                return collect(explode('_', $item))
                    ->map(function($item, $key) {
                        return ucfirst($item);
                    })
                    ->join(' ');
            });
            $csv[] = $headings->join(',');

            // format data
            foreach ($data as $dataRow) {
                $csv[] = implode(',', array_values($dataRow));
            }
            return implode("\n", $csv);
            break;
        default: // html
            if (!$data->count()) {
                return htmlTemplate('Sorry, no matching data was found');
            }
            
            // extract headings
            // replace underscores with space & ucfirst each word for a decent heading
            $headings = collect($data->get(0))->keys();
            $headings = $headings->map(function($item, $key) {
                return collect(explode('_', $item))
                    ->map(function($item, $key) {
                        return ucfirst($item);
                    })
                    ->join(' ');
            });
            $headings = '<tr><th>' . $headings->join('</th><th>') . '</th></tr>';

            // output data
            $rows = [];
            foreach ($data as $dataRow) {
                $row = '<tr>';
                foreach ($dataRow as $key => $value) {
                    $row .= '<td>' . $value . '</td>';
                }
                $row .= '</tr>';
                $rows[] = $row;
            }
            $rows = implode('', $rows);
            return htmlTemplate('<table>' . $headings . $rows . '</table>');
            break;
    }
}


 // wrap html in a standard template
function htmlTemplate($html) {
        return '
<html>
<head>
<style type="text/css">
    body {
        font: 16px Roboto, Arial, Helvetica, Sans-serif;
    }
    td, th {
        padding: 4px 8px;
    }
    th {
        background: #eee;
        font-weight: 500;
    }
    tr:nth-child(odd) {
        background: #f4f4f4;
    }
</style>
</head>
<body>
    ' . $html . '
</body>
</html>';

}
