<?php

function date_range($first, $last,$number) //returns the last 7/30 days for graph subtitles
    {
        $step = '+1 day';
        $output_format = 'd/m/Y';
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while( $current <= $last ) 
        {
            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }
        
        $today = date('z');
        print_r( array_slice($dates,$today,$number));
    }

date_range("2015-01-01", "2020-12-31", 7);

    