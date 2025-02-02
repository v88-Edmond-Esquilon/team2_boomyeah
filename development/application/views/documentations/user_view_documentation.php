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
    <title>Boom Yeah | User View Documentation Page</title>
    <link rel="shortcut icon" href="<?= add_file("assets/images/favicon.ico") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= add_file("assets/css/global.css") ?>">
    <link rel="stylesheet" href="<?= add_file("assets/css/user_view_documentation.css") ?>">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="<?= add_file("assets/js/vendor/jquery-3.6.3.min.js") ?>"></script>
    <script src="<?= add_file("assets/js/vendor/ux.lib.js") ?>"></script>
</head>
<body>
    <!--- Add #main_navigation --->
    <div id="main_navigation"><?php $this->load->view("partials/main_navigation.php", array("view_page" => "Documentations")); ?></div>
    <div id="wrapper">
        <div class="container">
            <ul id="breadcrumb_list">
                <li class="breadcrumb_item"><a href="/docs">Documentation</a></li>
                <li class="breadcrumb_item mobile_breadcrumb"><a href="/docs"></a></li>
                <li class="breadcrumb_item active"><?= $document_data["title"] ?></li>
            </ul>
            <div class="divider"></div>
            <div id="doc_title_access">
                <h1 id="doc_title"><?= $document_data["title"] ?></h1>
            </div>
            <p class="doc_text_content"><?= $document_data["description"] ?></p>
            <div class="section_header">
                <h2>Sections</h2>
            </div>
            <div id="sections_content">
                <div class="section_container" id="section_container">
                    <?php if(count($sections)){ ?>
                        <?php $this->load->view("partials/section_block_partial.php", array("all_sections" => $sections)); ?>
                    <?php } ?>
                </div>
                <div class="no_sections <?= count($sections) ? 'hidden' : '' ?>">
                    <img src="https://village88.s3.us-east-1.amazonaws.com/boomyeah_v2/empty_illustration.png"
                        alt="Empty Content Illustration">
                    <p>You have no sections yet</p>
                </div>
            </div>
        </div>
    </div>
    <!--JavaScript at end of body for optimized loading-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="<?= add_file("assets/js/main_navigation.js") ?>"></script>
    <script src="<?= add_file("assets/js/custom/user_view_documentation/user_view_documentation.js") ?>"></script>
</body>
</html> 