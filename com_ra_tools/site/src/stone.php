<?php

//$filename = JPATH_ROOT . '/components/com_ra_tools/google.csv';
$filename_in = JPATH_ROOT . '../google.csv';
$handle_in = fopen($filename_in, "r");
if ($handle_in == 0) {
    JFactory::getApplication()->enqueueMessage('Cannot open imput file: ' . $filename_in, 'error');
    return 0;
}

//$filename_out = "/tmp/download_" . $code . '_' . (new Date())->format('YmdHis') . ".csv";
$filename_out = '../download_' . (new Date())->format('YmdHis') . '.csv';
$handle_out = fopen($filename_out, 'w'); //open file for writing
If ($handle_out === false) {
    echo 'Cannot open output file: ' . $filename_out . '<br>';
    echo print_r(error_get_last(), true);
    return;
}
$header = array('Date', 'Description', 'Additional details', 'Website Link', 'Walk leaders', 'Linear or Circular',
    'Start time', 'Starting location', 'Starting postcode', 'Starting gridref', 'Starting location details', 'Meeting time',
    'Meeting location', 'Meeting postcode', 'Meeting gridref', 'Meeting location details', 'Est finish time',
    'Finishing location', 'Finishing postcode', 'Finishing gridref', 'Finishing location details', 'Difficulty',
    'Distance km', 'Distance miles', 'Ascent metres', 'Ascent feet', 'Dog friendly', 'Introductory walk', 'No stiles',
    'Family-friendly', 'Wheelchair accessible', 'Accessible by public transport', 'Car parking available',
    'Car sharing available', 'Coach trip', 'Refreshments available (Pub/cafe)', 'Toilets available');

fputcsv($handle_out, $header, ',', '"');

while (($data = fgetcsv($handle_in, 1000, ",")) !== FALSE) {
    $record_count++;
    $leader = $data[2];
    $date = $data[3];
    $title = $data[5];
    $description = $data[5];
    $distance = $data[6];
    $ascent = $data[7];
    $grade = substr($data[4]);
    $grid_ref = $data[11];
    $start_details = $data[12];
    $start_time = $data[13];
    $shape = substr($data[14], 0, 1);
    $end_grid_ref = $data[15];
    $end_details = $data[16];
    $meet_details = $data[17];
    $meet_time = $data[18];
// 8 = Lunch
// 9 = Map
//19 = car share
    $extra_details = ' Lunch details: ' . $data[8] . ',';
    if ($data[9] != 'Unknown') {
        $extra_details .= ', Map ' . $data[9];
    }
    if ($data[19] != '') {
        $extra_details .= ', Car share: ' . $data[19];
    }

    echo ' description =' . $description;
    echo ' distance = ' . $distance;
    echo ' ascent = ' . $ascent;
    echo 'extra_details; = ' . $extra_details;
    echo 'grade = ' . $grade;
    echo ' grid_ref = ' . $grid_ref;
    echo 'start-_details = ' . $start_details;
    echo ' start_time = ' . $start_time;

    echo 'shape = ' . $shape;
    echo 'end_grid_ref = ' . $end_grid_ref;
    echo ' end_details = ' . $end_details;
    echo 'meet_time = ' . $smeet_time;

    $output[] = $date; //A,Date
    $output[] = $title; //B,Title
    $output[] = $$description;  //C,Description
    $output[] = $extra_details;  //D,Additional details
    $output[] = ''; //E,Website Link
    $output[] = $leader; //F,Walk leaders
    $output[] = $shape; //G,Linear or Circular
    $output[] = $start_time; //H,Start time
    $output[] = $start_details; //I,Starting location
    $output[] = ''; //J,Starting postcode
    $output[] = $grid_ref; //K,Starting gridref
    $output[] = $start_details; //L,Starting location details
    $output[] = $meet_time; //M,Meeting time
    $output[] = $meet_details; //N,Meeting location
    $output[] = ''; //O,Meeting postcode
    $output[] = ''; //P,Meeting gridref
    $output[] = $meet_details; //Q,Meeting location details
    $output[] = ''; //R,Est finish time
    $output[] = ''; //S,Finishing location
    $output[] = ''; //T,","
    $output[] = $end_grid_ref; //U,Finishing gridref
    $output[] = $end_details; //V,Finishing location details
    $output[] = $xgrade; //W,Difficulty
    $output[] = ''; //X,Distance km
    $output[] = $distance; //Y,Distance miles
    $output[] = ''; //Z,Ascent metres
    $output[] = $ascent; //AA,Ascent feet
    $output[] = ''; //AB,Dog friendly
    $output[] = ''; //AC,Introductory walk
    $output[] = ''; //AD,No stiles
    $output[] = ''; //AE,Family-friendly
    $output[] = ''; //AF,Wheelchair accessible
    $output[] = ''; //AG,Accessible by public transport
    $output[] = ''; //AH,Car parking available
    $output[] = ''; //AI,Car sharing available
    $output[] = ''; //AJ,Coach trip
    $output[] = ''; //AK,Refreshments available (Pub/cafe)
    $output[] = ''; //AL,Toilets available

    fputcsv($handle_out, $output, ',', '"');
}

// Close the files
fclose($handle_in);
fclose($handle_out);
//echo $this->num_rows . ' rows written to ' . $filename . ', click to download<br>';
echo '<b>Data written to ' . $filename . ', click to download</b><br>';
echo '<a href="' . $filename . '" class="link-button button-p0110">Download walks as CSV</a>';

