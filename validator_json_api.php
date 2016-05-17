<?php 

/**
 * The standard's JSON API was based on http://jsonapi.org/ document.
 * author: cuongnq@greenglobal.vn
 * version: 1.0
 */

Class Validator_JSON_API {
    
    // define variable use in class
    private $JSON_API;


    public function __construct($string) {
        // set value for variable JSON_API
        $this->JSON_API = $string;
    }
    
    /**
     * Check validator JSON API
     */
    public function checkValidator() {
        // decode json from input string
        $jsonObject = json_decode($this->JSON_API);
        // get check validator JSON API String input
        $isValidJson = $this->isValidJson();
        $errors = new stdClass();
        $merge_errors = array();
        if($isValidJson !== true) {
            $errors->errors = $isValidJson;
            return json_encode($errors);
        }
        
        if($this->isValidDataErrorsMeta() !== true) {
            $merge_errors[]= $this->isValidDataErrorsMeta();
        } else if($this->coexistDataErrors() !== true) {
            $merge_errors[]= $this->coexistDataErrors();
        }
        
        // check links in top level
        if(isset($jsonObject->links) && $this->isValidLinks($jsonObject->links) !== true) {
            $merge_errors[]= $this->isValidLinks($jsonObject->link);
        }
        
        // check jsonapi in top level
        if($this->is_valid_jsonapi($jsonObject->jsonapi) !== true) {
            $merge_errors[] = $this->is_valid_jsonapi();
        }
        // check errors in top level
        if($this->isValidErrors() !== true) {
            $merge_errors[] = $this->isValidErrors();
        }
        // check meta in top level
        if($this->isValidMeta() !== true) {
            $merge_errors[] = $this->isValidMeta();
        }
        // check included in top level
        if($this->isValidIncluded() !== true) {
            $merge_errors[] = $this->isValidIncluded();
        }
        // check included and data in top level
        if($this->isValidIncludedData() !== true) {
            $merge_errors[] = $this->isValidIncludedData();
        }  
        // check data in top level
        if($this->isValidData() !== true) {
            $merge_errors[] = $this->isValidData();
        }
        // if JSON API has error then display error on screen
        if(!empty($merge_errors)) {
            $errors->errors = $merge_errors;
            return json_encode($errors);
        }
        $success = new stdClass();
        $success->type = "success";
        $success->id = "1";
        $success->title = "Congratulation !";
        $success->detail = "Your JSON API pass the exam !";
        $errors->data = $success;
        return json_encode($errors);
        
        
        
    }
    
    /**
     * If a document does not contain a top-level data key, the included member MUST NOT be present either.
     * @return \stdClass
     */
    function isValidIncludedData() {
        $jsonObject = json_decode($this->JSON_API);
        if(!isset($jsonObject->data)) {
            if(isset($jsonObject->included)) {
                // define object variable errors
                $errors = new stdClass();
                // define self object in links
                $self = new stdClass();
                // define titel error
                $errors->title = "Error on top level";
                // define detail error
                $errors->detail = "If a document does not contain a top-level 'data' key, the included member MUST NOT be present either.";
                // define self in links
                $self->self = "http://jsonapi.org/format/#document-top-level";
                // define links reference in error
                $errors->links = $self;
                return $errors;
            }
        }
        return true;
    }
    
    /**
     * check included on top level
     * @return boolean|\stdClass
     */
    function isValidIncluded() {
        $jsonObject = json_decode($this->JSON_API);
        //included: an array of resource objects that are related to the primary data and/or each other (“included resources”).
        if(isset($jsonObject->included) && !is_array($jsonObject->included)) {
            // define object variable errors
            $errors = new stdClass();
            // define self object in links
            $self = new stdClass();
            // define titel error
            $errors->title = "Error on included top level";
            // define detail error
            $errors->detail = "The included is not an included array.";
            // define self in links
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        $array = $jsonObject->included;
        // check resource in included
        foreach($array as $_array) {
            // if resource does not contain id and type display error
            if(!isset($_array->id) && !isset($_array->type)) {
                // define object variable errors
                $errors = new stdClass();
                // define self object in links
                $self = new stdClass();
                // define titel error
                $errors->title = "Error on included top level";
                // define detail error
                $errors->detail = "The resource objects in 'included' key does not contain both 'id' and 'type' key value.";
                // define self in links
                $self->self = "http://jsonapi.org/format/#document-top-level";
                // define links reference in error
                $errors->links = $self;
                return $errors;
                
            }
            // check attributes in resource
            if(isset($_array->attributes) 
                    && $this->isValidAttributes($_array->attributes) !== true) {
                return $this->isValidAttributes($_array->attributes);
            }
            // check relationships in resource
            if(isset($_array->relationships) 
                    && $this->isValidRelationships($_array->relationships) !== true) {
                return $this->isValidRelationships($_array->relationships);
            }
        }
        
        return true;
    }
    
    /**
     * Check valid of jsonapi in Top Level
     * @return boolean|\stdClass
     */
    function is_valid_jsonapi() {
        $jsonObject = json_decode($this->JSON_API);
        //A document MAY contain any of these top-level members "jsonapi" an object describing the server’s implementation
        if(isset($jsonObject->jsonapi) && !is_object($jsonObject->jsonapi)) {
            // define object variable errors
            $errors = new stdClass();
            // define self object in links
            $self = new stdClass();
            // define titel error
            $errors->title = "Error on jsonapi top level";
            // define detail error
            $errors->detail = "The jsonapi is not an jsonapi object.";
            // define self in links
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    /**
     * Check exist at least one of the following top-level members: data, errors, meta
     * @return boolean|\stdClass
     */
    function isValidDataErrorsMeta() {
        $jsonObject = json_decode($this->JSON_API);
        if(!isset($jsonObject->data) && !isset($jsonObject->errors) 
                && !isset($jsonObject->meta)) {
            $errors = new stdClass();
            $errors->title = "Error on Top Level";
            $errors->detail = "A document MUST contain at least one of the following top-level members: data, errors, meta";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    /**
     * check validator JSON API
     * @return boolean or object
     */
    function isValidJson() { 
        $jsonObject = json_decode($this->JSON_API);
        if ($jsonObject === null && json_last_error() !== JSON_ERROR_NONE) {
            $errors = new stdClass();
            $errors->title = "Error on input string";
            $errors->detail = "The input string is not an JSON API";
            return $errors;
        }
        return true;
    }
    
    /**
     * 
     * @return boolean|\stdClass
     */
    function coexistDataErrors() {
        $jsonObject = json_decode($this->JSON_API);
        if(isset($jsonObject->data) && isset($jsonObject->errors)) {
            $errors = new stdClass();
            $errors->title = "Error on 'data' and 'errors' top level";
            $errors->detail = "The members 'data' and 'errors' MUST NOT coexist in the same document.";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    /**
     * Check Top Level Data
     * @return boolean|\stdClass
     */
    function isValidData() {
        $jsonObject = json_decode($this->JSON_API);
        if(isset($jsonObject->data) && !empty($jsonObject->data)) {
            if(is_array($jsonObject->data)) {
                $array_data = $jsonObject->data;
                $object_data = $array_data[0];
            } elseif(is_object($jsonObject->data)) {
                $object_data = $jsonObject->data;
            } else {
                $errors = new stdClass();
                $errors->title = "Error on data Top Level";
                $errors->detail = "Top Level 'data' is not an data array or data object";
                $self = new stdClass();
                $self->self = "http://jsonapi.org/format/#document-top-level";
                // define links reference in error
                $errors->links = $self;
                return $errors;
            }
            // check exist both type and id in resource
            $list_error = array();
            if(!isset($object_data->type) || !isset($object_data->id)) {
                $errors = new stdClass();
                $errors->title = "Error on data Top Level";
                $errors->detail = "A resource identifier object MUST contain type and id members.";
                $self = new stdClass();
                $self->self = "http://jsonapi.org/format/#document-resource-identifier-objects";
                // define links reference in error
                $errors->links = $self;
                $list_error[] = $errors;
            }
            // check attributes in resource
            if(isset($object_data->attributes) 
                    && $this->isValidAttributes($object_data->attributes) !== true) {
                $list_error[] = $this->isValidAttributes($object_data->attributes);
            }
            // check relationships in resource
            if(isset($object_data->relationships) 
                    && $this->isValidRelationships($object_data->relationships) !== true) {
                $list_error[] = $this->isValidRelationships($object_data->relationships);
            }
            
            if(!empty($list_error)) {
                $sdt->error = $list_error;
                return $sdt;
            }
        }
        return true;
    }
    
    /**
     * Check relationships in resource
     * @param type $relationships
     * @return boolean|\stdClass
     */
    function isValidRelationships($relationships) {
        $error_relationships = array();
        if(!is_object($relationships)) {
            $errors = new stdClass();
            $errors->title = "Error on relationships key";
            $errors->detail = "The value of the relationships key MUST be an relationships object";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-resource-object-relationships";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        // check links, data, meta in relationships.
        if(!isset($relationships->links) && !isset($relationships->data) && !isset($relationships->meta)) {
            $errors = new stdClass();
            $errors->title = "Error on relationships key";
            $errors->detail = "A “relationship object” MUST contain at least one of the following: links, data, meta";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-resource-object-relationships";
            // define links reference in error
            $errors->links = $self;
            return  $errors;
        }
        // check links in relationships
        if(isset($relationships->links) && $this->isValidLinks($relationships->links) !== true) {
            return $this->isValidLinks($relationships->links);
        }
        // check data in relationships
        if(!empty($relationships->data)) {
            if(!is_object($relationships->data)) {
                $errors = new stdClass();
                $errors->title = "Error on relationships key";
                $errors->detail = "'data' key in relationship is not an data object";
                $self = new stdClass();
                $self->self = "http://jsonapi.org/format/#document-resource-object-relationships";
                // define links reference in error
                $errors->links = $self;
                return  $errors;
            }
            if(!isset($relationships->data->type) && !isset($relationships->data->id)) {
                $errors = new stdClass();
                $errors->title = "Error on relationships key";
                $errors->detail = "'data' key in relationship MUST contain at least one of the following: type and id";
                $self = new stdClass();
                $self->self = "http://jsonapi.org/format/#document-resource-object-relationships";
                // define links reference in error
                $errors->links = $self;
                return  $errors;
            } 
        }
        // check meta in relationships
        if(!empty($relationships->meta)) {
            if(!is_object($relationships->meta)) {
                $errors = new stdClass();
                $errors->title = "Error on relationships key";
                $errors->detail = "'meta' key in relationship is not an meta object";
                $self = new stdClass();
                $self->self = "http://jsonapi.org/format/#document-resource-object-relationships";
                // define links reference in error
                $errors->links = $self;
                return  $errors;
            }
        }
        return true;
        
    }
    
    /**
     * Check attributes in resource
     * @param type $attributes
     * @return \stdClass
     */
    function isValidAttributes($attributes) {
        if(!is_object($attributes)) {
            $errors = new stdClass();
            $errors->title = "Error on attributes key";
            $errors->detail = "The value of the attributes key MUST be an attributes object";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-resource-object-attributes";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    /**
     * Check Top Level Errors
     * @return boolean|\stdClass
     */
    function isValidErrors() {
        $jsonObject = json_decode($this->JSON_API);
        if(isset($jsonObject->errors) && !is_array($jsonObject->errors)) {
            $errors = new stdClass();
            $errors->title = "Error on errors Top Level";
            $errors->detail = "Top Level 'errors' is not an errors array";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    /**
     * Check Top Level Meta
     * @return boolean|\stdClass
     */
    function isValidMeta() {
        $jsonObject = json_decode($this->JSON_API);
        if(isset($jsonObject->meta) && !is_object($jsonObject->meta)) {
            $errors = new stdClass();
            $errors->title = "Error on meta top level";
            $errors->detail = "Top Level 'meta' is not an meta object";
            $self = new stdClass();
            $self->self = "http://jsonapi.org/format/#document-top-level";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
    
    
    /**
     * Check links in JSON API
     * @param type object $links
     * @return boolean
     */
    function isValidLinks($links) {
        // define object variable errors
        $errors = new stdClass();
        // define self object in links
        $self = new stdClass();
        // The value of each links member MUST be an object (a “links object”) 
        if(!is_object($links)) {
            // define titel error
            $errors->title = "Error on links";
            // define detail error
            $errors->detail = "The links is not an links object.";
            // define self in links
            $self->self = "http://jsonapi.org/format/#document-links";
            // define links reference in error
            $errors->links = $self;
            return $errors;
        }
        return true;
    }
}