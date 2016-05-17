# validator_json_api
The standard's JSON API was based on http://jsonapi.org/ document

1. Installation

require_once('validator_json_api.php');

into your project.

2. Usage

$validator_json_api = new Validator_JSON_API($string);

$string is string your JSON API for checking.

$validator_json_api->checkValidator();