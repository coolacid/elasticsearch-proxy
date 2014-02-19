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
	// TODO - Not supporting it yet, so, frack it, don't add the filter ;)
	$i=0; // No-op
    } else {
	// Something we don't know about yet - probably should raise an error or something
	die ("A search query we don't understand");
    }
    return $Original;
}

$request = '{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"*"}}]}},"filter":{"bool":{"must":[{"match_all":{}},{"fquery":{"query":{"field":{"tags":{"query":"(\"mikrotik\")"}}},"_cache":true}},{"range":{"@timestamp":{"from":1392774174534,"to":1392774474534}}},{"bool":{"must":[{"match_all":{}}]}}]}}}},"highlight":{"fields":{},"fragment_size":2147483647,"pre_tags":["@start-highlight@"],"post_tags":["@end-highlight@"]},"size":500,"sort":[{"@timestamp":{"order":"desc"}}]}';

$Request = json_decode($request, true);

//$Filters[] = GenerateFilter("tags", "_grokparsefailure");
$Filters[] = GenerateFilter("src_ip", "10.0.0.100");
$Filters1[] = GenerateFilter("query", "AAAA");

$Request = AddFilter($Request, "must", $Filters);
$Request = AddFilter($Request, "mustNot", $Filters1);

$json_doc = json_encode($Request);

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
