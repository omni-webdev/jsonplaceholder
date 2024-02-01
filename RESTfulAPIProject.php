<?php

class JsonPlaceHolderAPI {

	private $baseUrl;
    //normally construct creds here but aren't necessary for jsonplaceholder
	//private $apiKey;

    private $ermsg;
    private $curlHandle;
    private function error($msg) { $this->ermsg = $msg; return FALSE; }
    public function getError() { return $this->ermsg; }

    public function __construct() {
        $this->baseUrl = "https://jsonplaceholder.typicode.com"; //$config['base_url'];
        $this->curlHandle = curl_init();
    }
    
    public function __destruct() {
        curl_close($this->curlHandle);
    }

    /* makes the curl call to the endpoint */
    private function callAPI($method, $endpoint, $data, $params){
        $url = $this->baseUrl . $endpoint;
        //$url .= '?api_key=' . $this->apiKey;
        if (!empty($params)) {
            $query = http_build_query($params);
            $url = $this->baseUrl . $endpoint . '?' . $query;
        } else {
            $url = $this->baseUrl . $endpoint;
        }
    
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json; charset=UTF-8"],
            CURLOPT_CUSTOMREQUEST  => $method,
        ];
        
    
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt_array($this->curlHandle, $options);
        $response = curl_exec($this->curlHandle);

        //$response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if(curl_errno($this->curlHandle)){
            $error_msg = 'Curl Error: ' . curl_error($this->curlHandle);
            $this->error($error_msg);
        }
    
        if($httpcode != 200 && $httpcode != 201){
    
            $this->error('API call failed with HTTP response code: ' . $httpcode, $httpcode);
        }
        if (!$response || !is_string($response) || is_null(json_decode($response))) {
            $this->error('Invalid JSON response');
        }
    
        $decoded_response = json_decode($response, true);
        if(json_last_error() !== JSON_ERROR_NONE){
            $this->error('JSON Decoding Error: ' . json_last_error_msg());
        }
    
        return $decoded_response;
    }

    
    /* validates value type and format */
    private function validate($type, $value){
        switch($type){
            case 'id':
                if (is_numeric($value) || is_int($value)) {
                    return $value;
                } elseif(preg_match('/^\d+$/', $value)) {
                    return $value;
                }else{
                    return $this->error('Invalid ID');
                }
                //return $value;
            case 'string':
                return trim($value) != "" ? filter_var($value, FILTER_SANITIZE_STRING) : $this->error('Invalid String');
            // Add more cases for different types of data as needed
            default:
                return $this->error('Invalid Validation Type');
        }
    }

    /* Gets resource by postId  url/posts/{$id} */
	public function getPostById($id){

        $post_id = $this->validate('id', $id);
        if (!$post_id) {
            return FALSE;
        }

		return $this->callAPI('GET', "/posts/{$post_id}", [], []);
	
	}

    /* lists all resources */
	public function listResources(){

        return $this->callAPI('GET', "/posts", [], []);
    }

    /* Creates resource postId  
        $body = array(
            'title'=>'Test title',
            'body'=> 'Test Body',
            'userId'=>1
        );
    */
    public function createResource($body){
        $data = array();

        if(empty($body)){
            return $this->error('missing data');
        }

        $data['userId'] = $this->validate('id', $body['userid'] ?? null);
        $data['title'] = $this->validate('string', $body['title'] ?? null);
        $data['body'] = $this->validate('string', $body['body'] ?? null);

        if (!$data['userId'] && !$data['title'] && !$data['body']) {
            return FALSE;
        }

        return $this->callAPI('POST', "/posts", $data, []);
    }
    /* Updates resource postId  url/posts/{$id} */
    public function updateResource($body,$id){

        $data = array();

        $post_id = $this->validate('id', $id);
        if (!$post_id) {
            return FALSE;
        }

        if(empty($body)){
            return $this->error('missing data');
        }

        $data['userId'] = $this->validate('id', $body['userid'] ?? null);
        $data['title'] = $this->validate('string', $body['title'] ?? null);
        $data['body'] = $this->validate('string', $body['body'] ?? null);

        if (!$data['userId'] || !$data['title'] || !$data['body']) {
            return FALSE;
        }

        

        return $this->callAPI('PUT', "/posts/{$id}", $data, []);
    }
    /* Updates Post via patch & postId  url/posts/{$id} */
    public function patchResource($body,$id){

        $data = array();

        $post_id = $this->validate('id', $id);
        if (!$post_id) {
            return FALSE;
        }

        if(empty($body)){
            return $this->error('missing data');
        }
        $data['userId'] = $this->validate('id', $body['userid'] ?? null);
        $data['title'] = $this->validate('string', $body['title'] ?? null);
        $data['body'] = $this->validate('string', $body['body'] ?? null);

        if (!$data['userId'] && !$data['title'] && !$data['body']) {
            return FALSE;
        }


        return $this->callAPI('PATCH', "/posts/{$post_id}", $data, []);
    }

    /* Deletes Post via postId  url/posts/{$id} */
    public function deleteResource($id){

        $post_id = $this->validate('id', $id);
        if (!$post_id) {
            return FALSE;
        }

        return $this->callAPI('DELETE', "/posts/{$post_id}", [], []);
    }


     /* lists resources via filter url/posts/userId?= */
    public function filterByResource($params){

        $postParams = array();
        foreach ($params as $key => $value) {
            if ($key !== null && trim($key) != "" && $value !== null && trim($value) != "") {
                $postParams[filter_var($key, FILTER_SANITIZE_STRING)] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }

        
        return $this->callAPI('GET', "/posts", [], $postParams);
    }


     /* lists nested resources via url/posts/{$id}/{$resource} 
     
        $id = 4;

        $resource = "comments";
     
     */
    public function listNestByResource($id,$resource){

        $post_id = $this->validate('id', $id);
        if (!$post_id) {
            return FALSE;
        }
        $post_resource = $this->validate('string', $resource ?? null);
        if (!$post_resource){
            return $this->error('invalid resource name');
        }
        
        return $this->callAPI('GET', "/posts/{$post_id}/{$post_resource}", [], []);
    }
}