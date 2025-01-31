<?php
    for($sections_index = 0; $sections_index < count($all_sections); $sections_index++){
        $section = $all_sections[$sections_index]; ?>

        <div id="section_<?= $section['id'] ?>" class="section_block">
            <?php if($_SESSION["user_level_id"] == USER_LEVEL["ADMIN"]) { ?>    
                <input type="hidden" name="section_id" value="<?= $section['id'] ?>" class="section_id">
                <input type="hidden" name="original_section_title" value="<?= $section['title'] ?>">
                <div class="drag_handle"></div>
                <div class="section_title autoheight"><?= $section['title'] ?></div>
                <ul id="section_more_actions_<?= $section['id'] ?>" class="more_action_list">
                    <li class="edit_title_btn"><a href="#!" data-section_id="<?= $section['id'] ?>" class="section_block_icon edit_title_icon"></a></li>
                    <li><a href="#!" data-section_id="<?= $section['id'] ?>" class="section_block_icon duplicate_icon"></a></li>
                    <li><a href="#confirm_to_remove" class="section_block_icon remove_icon modal-trigger remove_btn" data-section_id="<?= $section['id'] ?>" data-section_title="<?= $section['title'] ?>"  data-documentation_action="remove"></a></li>
                </ul>
            <?php } else { ?> 
                <div class="section_details">
                    <div class="section_title tooltipped" data-tooltip="<?= $section["title"] ?>"><?= $section["title"] ?></div>
                </div>
            <?php }?>
        </div>
<?php 
    }   
?>