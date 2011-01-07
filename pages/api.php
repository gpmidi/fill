<?php

/**
 * This file is the switching system which
 * moves API requests across to the appropriate file within the api folder.
 * 
 * New files must be created within api/ before adding a switch here!
 * 
 * This file also takes the ARRAY output from each individual function
 * and then outputs it in the correct format.
 */
// URI format!
// /api/outputType/filename/extra/arguments/
/* define('YAML_OUT', yamlout);
  define('JSON_OUT', json_encode);
  define('XML_OUT', xmlout);

  define('YAML_MIME', 'application/x-yaml');
  define('JSON_MIME', 'application/json');
  define('XML_MIME', 'text/xml'); */

/**
 * This is an array defining which pages should be allowed!
 */
$allowable = array('example');

$allowableOutputs = array(
	'yaml' => array('mime' => 'application/x-yaml', 'function' => 'yamlout'),
	'json' => array('mime' => 'application/json', 'function' => 'json_encode'),
	'xml' => array('mime' => 'text/xml', 'function' => 'xmlout')
		);

// Hooray, let's do the actual thinging

$output = strtolower($hr_URI[2]);
$page = strtolower($hr_URI[3]);

if (in_array($output, array_keys($allowableOutputs)))
{
	header('Content-type: ' . $allowableOutputs[$output]['mime']);
	$parseToOutput = $allowableOutputs[$output]['function'];
}
if (isset($hr_URI[4]) && $hr_URI[4] == 'debug')
header('Content-type: text/html');

if (in_array($page, $allowable))
{
	require_once(HR_ROOT . '/pages/api/' . $page . '.php');
	$output = executeAPI(array_slice($hr_URI, 4));
	exit($parseToOutput($output));
}

// Parser functions:
/**
 * yamlout takes an array
 * and returns yaml!
 * 
 * Except it doesn't - yaml_emit does that
 * 
 * @param array $arrayToParse associative array containing content to YAMLise
 * @return string YAML output
 */
function yamlout($arrayToParse) {
	return yaml_emit($arrayToParse);
}

/**
 * xmlout takes an array and returns XML :)
 *
 * @param array $arrayToParse associative array containing content to XMLise
 * @return string XML output
 */
function xmlout($arrayToParse) {
	$xml = new XmlWriter();
	$xml->openMemory();
	$xml->startDocument('1.0', 'UTF-8');
	$xml->startElement('root');
	foreach ($data as $key => $value)
	{
		if (is_array($value))
		{
			$xml->startElement($key);
			write($xml, $value);
			$xml->endElement();
			continue;
		}
		$xml->writeElement($key, $value);
	}
	$xml->endElement();
	return $xml->outputMemory(true);

	// from snippets.dzone.com/post/show/3391
}
