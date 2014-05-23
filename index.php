<?php
include_once("users.php");
include_once("config.php");

# JSON-POST must be read through php://input
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$baseUri = "http://" . $config['ES_HOST'] . $_SERVER["REQUEST_URI"];

$ini = parse_ini_file ($config['INI'], true);

$filters = array();
BuildUser($ini, $_SERVER['REMOTE_USER'], $filters);

function GenerateFilter($Field, $Value) {
    $Add['fquery']['query']['field'][$Field]['query'] = $Value;
    $Add['fquery']['_cache'] = true;
    return $Add;
}

function AddFilter($Original, $Type, $Filters) {
    // Which type of _search are we looking at, we need to add the filter to the correct location
    if (isset ($Original['query']) || array_key_exists('query', $Original)) {
	// This is a regular query
	// Check to see the type is allready defined - if not, define it.
	if (!isset($Original['query']['filtered']['filter']['bool'][$Type]) || !array_key_exists($Type, $Original['query']['filtered']['filter']['bool'])) {
	    $Original['query']['filtered']['filter']['bool'][$Type] = array();
	}
	array_push($Original['query']['filtered']['filter']['bool'][$Type], $Filters);
    } elseif (isset ($Original['facets']) || array_key_exists('facets', $Original)) {
	// This is a facet query
	for ($i=0; $i<count($Original['facets']); ++$i) {
	    if(!isset($Original['facets'][$i]['facet_filter']['fquery']['query']['filtered']['filter']['bool'][$Type]) || !array_key_exists($Type, $Original['facets'][$i]['facet_filter']['fquery']['query']['filtered']['filter']['bool'])) {
		$Original['facets'][$i]['facet_filter']['fquery']['query']['filtered']['filter']['bool'][$Type] = array();
	    }
	    array_push($Original['facets'][$i]['facet_filter']['fquery']['query']['filtered']['filter']['bool'][$Type], $Filters);
	}
    } else {
	// Something we don't know about yet - probably should raise an error or something
	die ("A search query we don't understand");
    }
    return $Original;
}

function BuildQuery($Original) {
    // Facets mix and match objects and arrays, so a strait json_encode won't work with es
    if (isset ($Original['facets']) || array_key_exists('facets', $Original)) {
	// if we're a Facet, lets rebuild the first array as objects
	$NewQuery = new stdClass();
	foreach ($Original['facets'] as $key => $value) {
	    $NewQuery->$key = $value;
	}
	$Original['facets'] = $NewQuery;
    }
    return json_encode($Original);
}

function DoFilters(&$Request, $Filters) {
    // Look for any "must" filters and build them
    foreach ($Filters as $Filter) {
	$Request = AddFilter($Request, $Filter['type'], GenerateFilter($Filter['field'], $Filter['value']));
    }
    return $Request;
}

$Request = $_POST;

// Build the query
DoFilters($Request, $filters);
$json_doc = BuildQuery($Request);

// Send the new request to the backend
// TODO: Should probably look at attempting different backends
$ci = curl_init();
curl_setopt($ci, CURLOPT_URL, $baseUri);
curl_setopt($ci, CURLOPT_PORT, $config['ES_PORT']);
curl_setopt($ci, CURLOPT_TIMEOUT, 200);
curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
header('Content-Type: application/json');
$response = curl_exec($ci);

// Relay the response back to the client
echo $response;
