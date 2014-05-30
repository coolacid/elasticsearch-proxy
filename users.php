<?php

function BuildUser($ini, $user, &$result) {
    // First, lets check that the user is defined
    if (!isset ($ini[$user]) || !array_key_exists($user, $ini)) {
	die("INI: Group/User not found");
    }
    // Loop though the defines for each line of the user
    foreach ($ini[$user] as $key => $value) {
	if ($key == "group") {
	    // Adding a group to the stack
	    // See if we're an array
	    if (is_array($value)) {
		// We are, so we need to loop through each one
		foreach ($value as $group) {
		    //echo "Adding Group: " . $group . "\n";
		    BuildUser($ini, $group, $result);
		}
	    } else {
		// We only have one, so just build the one
		BuildUser($ini, $value, $result);
	    }
	} elseif ($key == "filter") {
	    // Adding a filter to the stack
	    // Filters are arrays, so lets see if it has a type
	    if (is_array($value) && (!isset ($value['type']) || !array_key_exists('type',$value))) {
		// We don't so assume we are a list of filters and loop though each one
		foreach ($value as $filter) {
		    //echo "Adding Filter: " . $filter . "\n";
		    $result[] = $ini[$filter];
		}
	    } else {
		// We only have one, so just build the one
		$result[] = $ini[$value];
	    }
	} else {
	    die ("INI: Unknown option in user definition");
	}
    }
    return $result;
}

//$ini = parse_ini_file ("users.ini", true);
//BuildUser($ini, "jakendall", $result);

// print_r($result);
