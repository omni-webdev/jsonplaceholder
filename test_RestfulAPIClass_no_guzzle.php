<?php

require_once('RESTfulAPIProject.php');

$jsonPlaceHolder = new JsonPlaceHolderAPI();

$body = array(
    'title'=>'Test title',
    'body'=> 'Test Body',
    'userId'=>1
);

$update_body = array(
    'title'=>'Test Updated Title',
    'body'=> 'Test Updated Body',
    'userId'=>4
);

$patch_body = array(
    'title'=>'Test Patched Title',
);

$filter_by_value = array(
    'userId'=>2
);

$id = 4;

$delete_me_id = 1;

$resource = "comments";

if(($new_resource = $jsonPlaceHolder->createResource($body))===FALSE){
    echo $jsonPlaceHolder->getError();
}

echo 'new resource '.json_encode($new_resource);


if(($resources = $jsonPlaceHolder->listResources())===FALSE){
    echo $jsonPlaceHolder->getError();
}

//echo json_encode($resources);

if(($post_by_id =$jsonPlaceHolder->getPostById($id))===FALSE){
    echo $jsonPlaceHolder->getError();
}
echo "post by id: ".json_encode($post_by_id);

if(($update_resource=$jsonPlaceHolder->updateResource($update_body,$id))===FALSE){
    echo $jsonPlaceHolder->getError();
}

echo 'update: '.json_encode($update_resource);

if(($patch_resource = $jsonPlaceHolder->patchResource($patch_body,$id))===FALSE){
    echo $jsonPlaceHolder->getError();
} 

echo 'patch: '.json_encode($patch_resource);

if(($listNest=$jsonPlaceHolder->listNestByResource($id,$resource))===FALSE){
    echo $jsonPlaceHolder->getError();
}
echo 'list nested data of record: '.json_encode($listNest);
//echo json_encode($listNest);

if(($delete_record = $jsonPlaceHolder->deleteResource($delete_me_id))===FALSE){
    echo $jsonPlaceHolder->getError();
}

echo 'deleted record: '.json_encode($delete_record);

if(($filtered_results = $jsonPlaceHolder->filterByResource($filter_by_value))===FALSE){
    echo $jsonPlaceHolder->getError();
} 

echo 'filtered results: '.json_encode($filtered_results);


$filter_by_value_bad = array(
    'big_show_fry_man'=>2
);

if(($bad_call = $jsonPlaceHolder->filterByResource($filter_by_value_bad))===FALSE){
    echo $jsonPlaceHolder->getError();
} 
echo 'bad call '.$jsonPlaceHolder->getError();
//echo json_encode($filtered_results);

?>

