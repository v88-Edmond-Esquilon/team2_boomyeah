<?php
    include_once("application/views/view_helper.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="UX Team 2">
    <meta name="description" content="A great way to describe your documentation tool">
    <title>Boom Yeah | Admin Edit Documentation Page</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= add_file("assets/css/global.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/admin_edit_documentation.css") ?>">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="<?= add_file("assets/js/vendor/Sortable.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/vendor/ux.lib.js") ?>"></script>
</head>
<body>
    <!--- Add #main_navigation --->
    <div id="main_navigation"><?php $this->load->view("partials/main_navigation.php"); ?></div>
    <!--- Add #invite_modal --->
    <div id="invite_modal"><?php $this->load->view("partials/invite_modal.php"); ?></div>
    <div id="wrapper">
        <div class="container">
            <div id="documentations_container">
                <div id="documentation_details">
                    <div class="documentation_placeholder"></div>
                        <div class="documentation_header_container">
                            <div class="documentation_header">
                                <ul id="breadcrumb_list">
                                    <li class="breadcrumb_item"><a href="/docs/edit">Documentation</a></li>
                                    <li class="breadcrumb_item active"><?= $document_data["title"] ?></li>
                                </ul>
                                <div class="divider"></div>
                                <div id="doc_title_access">
                                    <h1 id="doc_title"><?= $document_data["title"] ?></h1>
                                    <!-- Switch -->     
                                    <div class="switch switch_btn">
                                        <label for="set_privacy_switch">
                                            <span class="toggle_text"><?= $document_data["is_private"] ? "Private" : "Public" ?></span>
                                            <input class="toggle_switch" type="checkbox" id="set_privacy_switch" <?= $document_data["is_private"] ? "checked" : "" ?>>
                                            <span class="lever"></span>
                                        </label>
                                    </div>
                                    <a id="invite_collaborator_btn" class="invite_collaborators_btn waves-effect waves-light btn<?= $document_data["is_private"] ? "" : " hidden" ?>" href="#invite_collaborator_modal" data-document_id="<?= $document_data["id"] ?>"><?= $document_data["cache_collaborators_count"] + 1 ?> Collaborators</a>
                                </div>
                                <p class="doc_text_content" id="document_description" contenteditable="true" data-placeholder="Add Description"><?= $document_data["description"] ?></p>
                                <form action="/sections/add" id="section_form" method="post">
                                    <input type="hidden" name="documentation_id" value="<?= $document_data["id"] ?>">
                                    <input type="hidden" name="action" value="create_section">
                                    <div class="group_add_section input-field">
                                        <input name="section_title" id="input_add_section" type="text" class="section_title validate" autofocus>
                                        <label for="input_add_section">Add Section</label>
                                        <button id="submit_add_section_btn" type="submit"></button>
                                    </div>
                                </form>
                                <div class="section_header">
                                    <h2>Sections</h2>
                                </div>
                            </div>
                        </div>
                </div>
                <div id="sections_content">
                    <div class="section_container" id="section_container">
                        <?php if(count($sections)){ ?>
                            <?php $this->load->view("partials/section_block_partial.php", array("all_sections" => $sections)); ?>
                        <?php } ?>
                    </div>
                    <div class="no_sections <?= count($sections) ? "hidden" : "" ?>">
                        <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/empty_illustration.png"
                            alt="Empty Content Illustration">
                        <p>You have no sections yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirmation_modal_remove">
        <div id="confirm_to_remove" class="modal">
            <div class="modal-content">
                <h4>Confirmation</h4>
                <p>Are you sure you want to remove `<span id="section_title_to_remove"></span>`? This will also remove all the modules and tabs in the section.</p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect btn-flat no_btn">No</a>
                <a href="#!" id="remove_confirm" class="modal-close waves-effect btn-flat yes_btn">Yes</a>
            </div>
        </div>
    </div>
    <form id="remove_section_form" action="/sections/remove" method="POST" hidden>
        <input type="hidden" name="action" value="remove_section">
        <input type="hidden" id="remove_section_id" name="section_id" class="section_id">
        <input type="hidden" name="documentation_id" class="documentation_id" value="<?= $document_data["id"] ?>">
    </form>
    <form id="update_section_form" action="/sections/update" method="POST" hidden>
        <input type="hidden" name="section_id" class="section_id" value="">
        <input type="hidden" name="action" value="update_section">
        <input type="hidden" name="update_type" class="update_type" value="">
        <input type="hidden" name="update_value" class="update_value" value="">
    </form>
    <form id="duplicate_section_form" action="/sections/duplicate" method="POST" hidden>
        <input type="hidden" name="section_id" class="section_id" value="">
        <input type="hidden" name="action" value="duplicate_section">
    </form>
    <form id="udpate_documentation_form" action="/docs/update" method="POST" hidden>
        <input type="hidden" name="documentation_id" class="documentation_id" value="<?= $document_data["id"] ?>">
        <input type="hidden" name="update_type" class="update_type" value="">
        <input type="hidden" name="update_value" class="update_value" value="">
    </form>
    <form id="reorder_sections_form" action="/sections/reorder" method="POST">
        <input type="hidden" name="action" value="reorder_sections">
        <input type="hidden" id="sections_order" name="sections_order">
        <input type="hidden" id="documentation_id" name="documentation_id" value="<?= $document_data["id"] ?>">
    </form>
    <?php $this->load->view("partials/confirm_invite_modals.php"); ?>
    <?php $this->load->view("partials/confirm_documentation_modals.php"); ?>

    <!--JavaScript at end of body for optimized loading-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="<?= add_file("assets/js/main_navigation.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_documentation/admin_edit_documentation_fe.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/admin_edit_documentation/admin_edit_documentation_be.js") ?>"></script>
    <script src="<?= add_file("assets/js/invite_modal.js") ?>"></script>
</body>
</html> 