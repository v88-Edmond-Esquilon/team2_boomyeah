<?php foreach($modules as $module) {
    # This is used to remove [] if tab_ids_order came from JSON_ARRAYAGG(). It will return null if tab_ids_order is from column
    $tab_ids_order = json_decode($module["tab_ids_order"]);

    if(!$tab_ids_order){
        # Run this if tab_ids_order is from column
        $tab_ids_order = explode(",",$module["tab_ids_order"]);
    }

    # Transform tab_ids_order to array if tab_ids_order is an interger after json_decode
    if(is_int($tab_ids_order)){
        $tab_ids_order = array($tab_ids_order);
    }
?>
    <div class="section_page_content" id="module_<?= $module['module_id'] ?>">
        <ul class="section_page_tabs" data-module_id="<?= $module['module_id'] ?>">
            <?php
                # DOCU: $views_path is specified for this file in order for the partial be loaded from FE and BE side
                if($tab_ids_order){
                    $this->load->view("partials/page_tab_item_partial.php", array("section_id" => $module["section_id"], "module_tabs_json" => json_decode($module["module_tabs_json"]), "tab_ids_order" => $tab_ids_order));
                }
            ?>
            <li class="add_page_tab">
                <button class="add_page_btn" type="button" data-module_id="<?= $module['module_id'] ?>">+</button>
            </li>
        </ul>
        <?php
            # DOCU: $views_path is specified for this file in order for the partial be loaded from FE and BE side
            if($tab_ids_order){
                $this->load->view("partials/section_page_tab_partial.php", array("section_id" => $module["section_id"], "module_tabs_json" => json_decode($module["module_tabs_json"]), "tab_ids_order" => $tab_ids_order));
            }
        ?>
    </div>
<?php } ?>
