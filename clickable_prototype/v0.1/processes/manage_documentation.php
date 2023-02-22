<?php
    session_start();
    include_once("../config/connection.php");
    include_once("../config/constants.php");
    include_once("./partial_helper.php");

    if(isset($_POST["action"])){
        $response_data = array("status" => false, "result" => [], "error"  => null);

        switch ($_POST["action"]) {
            case "get_documentations": {
                // Declare initial variables and values
                $get_documentations_html  = "";
                $get_documentations_query = "SELECT id, title, is_archived, is_private, cache_collaborators_count FROM documentations WHERE workspace_id = {$_SESSION["workspace_id"]}";

                // Modify initial query
                if($_POST["is_archived"] == "{$_NO}"){
                    $documentations_order = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION["workspace_id"]};");
                    $documentations_order = $documentations_order["documentations_order"];

                    $get_documentations_query .= " AND is_archived = {$_POST["is_archived"]} ORDER BY FIELD (id, {$documentations_order});";
                } else {
                    $get_documentations_query .= " AND is_archived = {$_POST["is_archived"]};";
                }

                // Run MySQL query
                $get_documentations = fetch_all($get_documentations_query);

                // Generate HTML
                for($documentations_index = 0; $documentations_index < count($get_documentations); $documentations_index++){
                    $get_documentations_html .= get_include_contents("../views/partials/document_block_partial.php", $get_documentations[$documentations_index]);
                }
    
                $response_data["status"] = true;
                $response_data["result"]["html"] = $get_documentations_html;

                break;
            }
            case "remove_documentation": {
                if($_SESSION["user_level_id"] == $_USER_LEVEL["admin"]){
                    run_mysql_query("DELETE FROM documentations WHERE id = {$_POST["remove_documentation_id"]};");

                    /* Remove remove_documentation_id in documentations_order and update documentations_order in workpsaces table */
                    $documentations_order = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION["workspace_id"]};");
                    $documentations_order = explode(",", $documentations_order["documentations_order"]);
                    
                    $documentation_index = array_search($_POST["remove_documentation_id"], $documentations_order);
                    
                    if($documentation_index !== FALSE){
                        unset($documentations_order[$documentation_index]);

                        $documentations_order = implode(",", $documentations_order);
                        run_mysql_query("UPDATE workspaces SET documentations_order = '{$documentations_order}' WHERE id = {$_SESSION["workspace_id"]};");
                    }

                    $response_data["status"] = true;
                    $response_data["result"]["documentation_id"] = $_POST["remove_documentation_id"];
                } 
                else {
                    $response_data["error"] = "You are not allowed to do this action!";
                }

                break;
            }
            case "create_documentation": {
                if(isset($_POST["document_title"])){
                    $document_title = escape_this_string($_POST["document_title"]);

                    $insert_document_record = run_mysql_query("
                        INSERT INTO documentations (user_id, workspace_id, title, is_archived, is_private, cache_collaborators_count, created_at, updated_at) 
                        VALUES ({$_SESSION["user_id"]}, {$_SESSION["workspace_id"]}, '{$document_title}', {$_NO}, {$_YES}, {$_ZERO_VALUE}, NOW(), NOW())
                    ");

                    if($insert_document_record != $_ZERO_VALUE){
                        $workspace = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION["workspace_id"]};");
                        $new_documents_order = (strlen($workspace["documentations_order"])) ? $workspace["documentations_order"].','. $insert_document_record : $insert_document_record;

                        $update_workspace_docs_order = run_mysql_query("UPDATE workspaces SET documentations_order = '{$new_documents_order}' WHERE id = {$_SESSION["workspace_id"]}");

                        if($update_workspace_docs_order){
                            $response_data["status"] = true;
                            $response_data["result"]["document_id"] = $insert_document_record;
                        }
                    }
                }
                else{
                    $response_data["error"] = "Document title is required!";
                }

                break;
            }
            case "update_documentation": {
                if(isset($_POST["update_type"]) && isset($_POST["documentation_id"])){
                    $document = fetch_record("SELECT id FROM documentations WHERE id = {$_POST["documentation_id"]}");

                    if(count($document) > $_ZERO_VALUE){
                        if( in_array($_POST["update_type"], ["title", "is_archived", "is_private"]) ){
                            $update_value = escape_this_string($_POST["update_value"]);
                            $update_document = run_mysql_query("UPDATE documentations SET {$_POST["update_type"]} = '{$update_value}' WHERE id = {$_POST["documentation_id"]}");
                            
                            if($update_document){
                                $updated_document = fetch_record("SELECT id, title, is_archived, is_private, cache_collaborators_count FROM documentations WHERE id = {$_POST["documentation_id"]}");
                                $response_data["status"] = true;
                                $response_data["result"]["documentation_id"] = $updated_document["id"];
                                $response_data["result"]["update_type"] = $_POST["update_type"];

                                if($_POST["update_type"] == "is_private"){
                                    $response_data["result"]["html"] = get_include_contents("../views/partials/document_block_partial.php", $updated_document);
                                }
                                elseif($_POST["update_type"] == "is_archived" ){
                                    $workspace = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION["workspace_id"]}");
                                    $documentation_order_array = explode(",", $workspace["documentations_order"]);
                                    $new_documents_order = NULL;

                                    if($_POST["update_value"] == $_YES){
                                        if (($key = array_search($_POST["documentation_id"], $documentation_order_array)) !== false) {
                                            unset($documentation_order_array[$key]);
                                        }

                                        $new_documents_order = implode(",", $documentation_order_array);
                                    }
                                    else {
                                        $new_documents_order = (strlen($workspace["documentations_order"])) ? $workspace["documentations_order"].','. $_POST["documentation_id"] : $_POST["documentation_id"];
                                    }

                                    $update_workspace = run_mysql_query("UPDATE workspaces SET documentations_order = '{$new_documents_order}' WHERE id = {$_SESSION["workspace_id"]}");
                                }
                            }
                        }
                    }
                }
                else{
                    $response_data["error"] = "Missing required params: documentation_id and update_type.";
                }

                break;
            }
            case "duplicate_documentation": {
                // Fetch documentation
                $documentation_id     = (int)$_POST['documentation_id'];
                $get_documentation    = fetch_record("SELECT id, title, description, sections_order, is_archived, is_private FROM documentations WHERE id = {$documentation_id};");
                $document_title       = escape_this_string($get_documentation['title']);
                $document_description = escape_this_string($get_documentation['description']);

                // Create new documentation
                $duplicate_documentation = run_mysql_query("INSERT INTO documentations (user_id, workspace_id, title, description, sections_order, is_archived, is_private, created_at, updated_at) 
                    VALUES ({$_SESSION['user_id']}, {$_SESSION['workspace_id']}, 'Copy of {$document_title}', '{$document_description}', '{$get_documentation['sections_order']}', 
                    {$get_documentation['is_archived']}, {$get_documentation['is_private']}, NOW(), NOW());
                ");

                // TODO: Create sections, pages, and tabs

                // Get documentations_order and insert newly created documentation_id
                $get_workspace        = fetch_record("SELECT documentations_order FROM workspaces WHERE id = {$_SESSION['workspace_id']};");
                $documentations_order = explode(",", $get_workspace["documentations_order"]);

                for($document_index=0; $document_index < count($documentations_order); $document_index++){
                    if($documentation_id == $documentations_order[$document_index]){
                        array_splice($documentations_order, $document_index + 1, 0, "{$duplicate_documentation}");
                    }
                }

                // Convert array to comma-separated string and update documentations_order of documentations_order
                $documentations_order = implode(",", $documentations_order);
                run_mysql_query("UPDATE workspaces SET documentations_order = '{$documentations_order}' WHERE id = {$_SESSION['workspace_id']};");

                // Fetch newly created documentation and generate html
                $get_documentation  = fetch_record("SELECT id, title, is_archived, is_private, cache_collaborators_count FROM documentations WHERE id = {$duplicate_documentation};");
                $documentation_html = get_include_contents("../views/partials/document_block_partial.php", $get_documentation);

                $response_data["status"]                     = true;
                $response_data["result"]["documentation_id"] = $duplicate_documentation;
                $response_data["result"]["html"]             = $documentation_html;

                break;
            }
            case "reorder_documentations": {
                run_mysql_query("UPDATE workspaces SET documentations_order = '{$_POST['documentations_order']}' WHERE id = {$_SESSION["workspace_id"]}");

                $response_data["status"] = true;
            }
        }
    }

    echo json_encode($response_data);
?>