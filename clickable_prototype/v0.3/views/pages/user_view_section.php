<?php
    session_start();

    // Sample admin session
    $_SESSION["user_id"]       = 1;
    $_SESSION["user_level_id"] = 9;
    $_SESSION["workspace_id"]  = 1;
    // END

    include_once("../../processes/partial_helper.php");  
    include_once("../view_helper.php");  
    include_once("../../config/constants.php");
    $document_title = (isset($_GET["document_title"])) ? htmlspecialchars_decode( $_GET["document_title"] ) : "Employee Handbook";
    $section_title = (isset($_GET["section_title"])) ? htmlspecialchars_decode( $_GET["section_title"] ) : "About Company";

    $edit_section_module_file_path = "../../assets/json/edit_section_module_data.json";
    $user_view_section_module_data = load_json_file($edit_section_module_file_path);
    $view_type = (isset($_GET["view_type"])) ?  htmlspecialchars_decode( $_GET["view_type"] ) : "Edit View";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Company - Employee Handbook | BoomYEAH</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <link rel="stylesheet" href="<?= add_file("assets/css/vendor/redactorx.min.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/vendor/animate.min.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/user_view_section.css") ?>">
    
    <script src="<?= add_file("assets/js/vendor/ux.lib.js") ?>"></script>
    <script src="<?= add_file("assets/js/constants.js") ?>"></script>
</head>
<body>
    <div id="main_navigation"><?php include_once("../partials/main_navigation.php"); ?></div>
    <div id="wrapper" class="container">
        <div id="view_section_content">
            <div id="section_summary">
                <div class="breadcrumbs">
                    <ul id="breadcrumbs_list">
                        <li class="breadcrumb_item"><a href="<?= BASE_FILE_URL?>views/pages/user_documentation.php">Documentations</a></li class="breadcrumb_item">
                        <li class="breadcrumb_item"><a href="<?= BASE_FILE_URL?>views/pages/user_view_documentation.php">Employee Handbook</a></li class="breadcrumb_item">
                        <li class="breadcrumb_item active"><span><?= $section_title ?></span></li>
                    </ul>
                    <div class="row_placeholder"></div>
                    <?php if(isset($_GET["view_type"])){ ?>
                        <a href="<?= BASE_FILE_URL?>views/pages/admin_edit_section.php" id="preview_section_btn">Back to edit</a>
                    <?php } ?>
                </div>
                <div class="section_details">
                    <h1 id="section_title"><?= $section_title ?></h1>
                    <p id="section_short_description">Village 88 Inc. is a US-Delaware corporation which focuses on incubating companies and providing IT consultancy services to companies in the US. V88 also has a remote branch in San Fernando, La Union, Philippines registered in Securities and Exchange Commission as 457Avenue Inc.</br></br>Village 88 Inc. was founded in 2011 while 457Avenue Inc. registered in the Philippines in 2013. It is the company’s vision to provide world-class IT education to brilliant individuals with less IT-career opportunity due to lack of industry experience or exposure. So far, Village 88, Inc. (V88) has produced 30+ talented software engineers from the Philippines who now worked with the company in incubating and launching businesses that bring a positive impact to the world.</br></br>Since 2011, V88 has incubated Coding Dojo, Hacker Hero, Data Compass, and helped start numerous start-ups including Alumnify, SpotTrender, MatrixDS, and others.</p>
                </div>
            </div>
            <div id="section_pages">
                <?php
                    foreach($user_view_section_module_data["fetch_admin_module_data"] as $module_key => $module_data){
                        $modules_array = array("modules" => array($module_data), "module_count" => $module_key + 1, "total_modules" => count($user_view_section_module_data["fetch_admin_module_data"]));
                        if($modules_array){
                            load_view("../partials/user_section_page_content_partial.php", $modules_array);
                        }
                        else{
                            //when no data display nothing
                        }
                    }
                ?>
                </div>
                <!-- Mobile View: Progress bar corresponds to the section_page_content not the section_page_tab  -->
            </div>
            <div id="mobile_section_pages_controls">
                <div class="row_placeholder"></div>
                <div id="page_btns">
                    <button id="prev_page_btn" type="button" class="page_btn hidden"></button>
                    <button id="next_page_btn" type="button" class="page_btn"></button>
                </div>
            </div>
            <div id="clone_section_page">
                <?php include_once("../partials/clone_section_page.php"); ?>
            </div>
        </div>
    </div>
    <div id="comment_actions_container">
        <div class="comment_actions_menu">
            <button type="button" class="comment_action_btn edit_btn">Edit</button>
            <button type="button" class="comment_action_btn remove_btn">Remove</button>
        </div>
    </div>
    <div id="mobile_comments_slideout" class="sidenav">
        <div id="comments_list_container">
            <ul id="user_comments_list" class="comments_list"></ul>
        </div>
        <div class="mobile_tab_comments tab_comments comment_container">
            <form action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" class="mobile_add_comment_form add_comment_form">
                <div class="comment_field">
                    <input type="hidden" name="action" value="add_tab_post" class="action">
                    <input type="hidden" name="tab_id" class="tab_id" value="">
                    <input type="hidden" name="post_id" class="post_id" value="">
                    <div class="comment_message_content input-field col s12">
                        <label for="mobile_comment_message">Write a comment</label>
                        <textarea name="post_comment" id="mobile_comment_message" class="materialize-textarea comment_message"></textarea>
                    </div>
                </div>
                <div class="comment_btn">
                    <button type="submit" class="mobile_comment_btn"></button>
                </div>
            </form>
        </div>
    </div>
    <div id="modals_container">
        <?php include_once("../partials/confirm_action_modals.php"); ?>
    </div>
    
    <form id="fetch_tab_posts_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="fetch_tab_posts" class="action">
        <input type="hidden" name="tab_id" class="tab_id">
    </form>
    
    <form id="fetch_mobile_posts_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="fetch_tab_posts">
        <input type="hidden" name="tab_id" class="tab_id">
    </form>
    
    <form id="fetch_post_comments_form" action="<?= BASE_FILE_URL ?>processes/manage_documentation.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="fetch_post_comments">
        <input type="hidden" name="post_id" class="post_id">
    </form>
    <script src="<?= add_file("assets/js/vendor/redactorx.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_section/admin_edit_section_fe.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_section/admin_edit_section_be.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/module_tab_comments/module_tab_comments_fe.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/module_tab_comments/module_tab_comments_be.js") ?>"></script>
</body>
</html>