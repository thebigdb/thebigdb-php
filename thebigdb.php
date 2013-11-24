<?php

class TheBigDB {

  public $version = "0.2.0";

  public $default_configuration = array(
    "api_key" => null,
    "use_ssl" => false,
    "verify_ssl_certificates" => false, # Not yet implemented
    "before_request_execution" => null, # Not yet implemented
    "after_request_execution" => null, # Not yet implemented
    "api_host" => "api.thebigdb.com",
    "api_port" => 80,
    "api_version" => "1",
  );

  public $configuration = array();
  public $client_user_agent = array();
  public $user_agent = null;
  public $response = null;

  function __construct($options = array()){

    # Set basic configuration
    $this->configuration = array_merge($this->default_configuration, $options);

    $this->configuration["api_port"] = ($this->configuration["use_ssl"] === true) ? 443 : 80;

    if(isset($options["api_port"])){
      $this->configuration["api_port"] = $options["api_port"];
    }

    # to be able to use shortcuts like $thebigdb->api_key = "foobar";
    foreach($this->configuration as $key => $value){
      $this->$key = $value;
    }

    # Prepare standard requests headers
    $this->user_agent = "TheBigDB PHPWrapper/{$this->version}";

    $this->client_user_agent = json_encode(array(
      "publisher" => "thebigdb",
      "version" => $this->version,
      "language" => "php",
      "language_version" => PHP_VERSION
    ));
    
  }


  ##############################
  # Shortcuts to actions on Statements
  ##############################

  # GET
  function search($nodes, $other_params = array()){
    $params = array_merge(array("nodes" => $nodes), $other_params);
    return $this->execute_request("get", "/statements/search", $params);
  }

  function show($id, $other_params = array()){
    $params = array_merge(array("id" => $id), $other_params);
    return $this->execute_request("get", "/statements/show", $params);
  }

  # POST
  function create($nodes, $other_params = array()){
    $params = array_merge(array("nodes" => $nodes), $other_params);
    return $this->execute_request("post", "/statements/create", $params);
  }

  function upvote($id, $other_params = array()){
    $params = array_merge(array("id" => $id), $other_params);
    return $this->execute_request("post", "/statements/upvote", $params);
  }

  function downvote($id, $other_params = array()){
    $params = array_merge(array("id" => $id), $other_params);
    return $this->execute_request("post", "/statements/downvote", $params);
  }


  ##############################
  # Other actions
  ##############################

  function user($action, $params){
    return $this->execute_request("get", "/users/".$action, $params);
  }


  ##############################
  # Engine
  ##############################


  function execute_request($method, $path, $params = array()){
    $method = strtolower($method);

    if($this->use_ssl){
      $scheme = "https";
    } else {
      $scheme = "http";
    }

    $url = "{$scheme}://{$this->api_host}:{$this->api_port}";
    $url = $url."/v{$this->api_version}";

    if(substr($path, 0, 1) != "/"){
      $path = "/{$path}";
    }

    $url = $url.$path;

    if($this->api_key){
      $params["api_key"] = $this->api_key;
    }

    if($method == "get"){
      $url = $url."?".$this->serialize_query_params($params);
    }

    $curl = curl_init($url);
    
    if($method == "post"){
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    if($this->use_ssl){
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, array("User-Agent: {$this->user_agent}", "X-TheBigDB-Client-User-Agent: {$this->client_user_agent}"));

    $json_response = curl_exec($curl);
    curl_close($curl);

    $this->response = json_decode($json_response);

    return $this->response;
  }

  # $this->serialize_query_params(array("house" => "brick and mortar", "animals" => array("cat", "dog"), "computers" => array("cool" => true, "drives" => array("hard", "flash"))))
  # => house=brick%20and%20mortar&animals%5B0%5D=cat&animals%5B1%5D=dog&computers%5Bcool%5D=1&computers%5Bdrives%5D%5B0%5D=hard&computers%5Bdrives%5D%5B1%5D=flash
  # which will be read by the server as:
  # => house=brick%20and%20mortar&animals[]=cat&animals[]=dog&computers[cool]=1&computers[drives][]=hard&computers[drives][]=flash
  function serialize_query_params($params, $prefix = null){
    $ret = array();

    foreach($params as $key => $value){
      $param_key = $prefix ? $prefix."[".$key."]" : $key;

      if(is_array($value) || is_object($value)){
        $ret[] = $this->serialize_query_params($value, $param_key);
      } else {
        $ret[] = urlencode($param_key)."=".str_replace("+", "%20", urlencode($value));
      }
    }
    return implode("&", $ret);
  }

}

?>