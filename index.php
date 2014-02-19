<?php

$baseUri = "http://10.0.10.160/logstash-2014.02.19/_search";
$search_port = "9200";

function GenerateFilter($Field, $Value) {
    $Add['fquery']['query']['field'][$Field]['query'] = $Value;
    $Add['fquery']['_cache'] = 1;
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

// $request = '{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"*"}}]}},"filter":{"bool":{"must":[{"match_all":{}},{"range":{"@timestamp":{"from":1392821619767,"to":1392821679768}}},{"bool":{"must":[{"match_all":{}}]}}]}}}},"highlight":{"fields":{},"fragment_size":2147483647,"pre_tags":["@start-highlight@"],"post_tags":["@end-highlight@"]},"size":500,"sort":[{"@timestamp":{"order":"desc"}}]}';
$request = '{"facets":{"0":{"date_histogram":{"field":"@timestamp","interval":"1s"},"global":true,"facet_filter":{"fquery":{"query":{"filtered":{"query":{"query_string":{"query":"*"}},"filter":{"bool":{"must":[{"match_all":{}},{"range":{"@timestamp":{"from":1392821619767,"to":1392821679768}}},{"fquery":{"query":{"field":{"tags":{"query":"(\"mikrotik\" AND \"Filtered\")"}}},"_cache":true}},{"bool":{"must":[{"match_all":{}}]}}]}}}}}}}},"size":0}';

$Request = json_decode($request, true);

//$Filters[] = GenerateFilter("tags", "_grokparsefailure");
$Filters[] = GenerateFilter("src_ip", "10.0.0.100");
$Filters1[] = GenerateFilter("query", "AAAA");

//$Request = AddFilter($Request, "must", $Filters);
//$Request = AddFilter($Request, "mustNot", $Filters1);

//print_r($Request);

$json_doc = BuildQuery($Request);

print_r($json_doc);

$ci = curl_init();
curl_setopt($ci, CURLOPT_URL, $baseUri);
curl_setopt($ci, CURLOPT_PORT, $search_port);
curl_setopt($ci, CURLOPT_TIMEOUT, 200);
curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ci, CURLOPT_POSTFIELDS, $json_doc);
$response = curl_exec($ci);

print_r(json_decode($response));
