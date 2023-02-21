<div id="document_<?= $id ?>" class="document_block">
    <form action="/" method="POST" class="document_details edit_title_form">
        <input type="text" name="document_title" value="<?= $title ?>" id="" class="document_title" readonly="">
        <?php if($is_private){ ?>
            <button class="invite_collaborators_btn modal-trigger <?= ($is_archived) ? 'archived_disabled' : '' ?>" href="#modal1">&nbsp;<?= $cache_collaborators_count ?></button>
        <?php } ?>
    </form>
    <div class="document_controls">
        <?php if($is_private){ ?>
            <button class="access_btn modal-trigger <?= ($is_archived) ? 'archived_disabled' : '' ?> set_privacy_btn" href="#confirm_to_public" data-document_id="<?= $id ?>" data-document_privacy="private"></button>
        <?php } ?>
        <button class="more_action_btn dropdown-trigger" data-target="document_more_actions_<?= $id ?>">⁝</button>
        <!-- Dropdown Structure -->
        <ul id="document_more_actions_<?= $id ?>" class="dropdown-content more_action_list_private more_action_list_public">
            <?php if(!$is_archived){ ?>
                <!-- <li><a href="#confirm_to_archive" class="archive_icon modal-trigger archive_btn" data-document_id="<?= $id ?>" data-documentation_action="unarchive">Unarchive</a></li> -->
                <li class="edit_title_btn"><a href="#!" class="edit_title_icon">Edit Title</a></li>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#!" class="duplicate_icon">Duplicate</a></li>
                <li class="divider" tabindex="-1"></li>
                <li><a href="#confirm_to_archive" class="archive_icon modal-trigger archive_btn" data-document_id="<?= $id ?>" data-documentation_action="archive">Archive</a></li>
                <?php if($is_private){ ?>
                    <li class="divider" tabindex="-1"></li>
                    <li><a href="#modal1" class="invite_icon modal-trigger">Invite</a></li>
                <?php } ?>
                <li class="divider" tabindex="-1"></li>
                <?php if($is_private){ ?>
                    <li><a href="#confirm_to_public" class="set_to_public_icon modal-trigger set_privacy_btn" data-document_id="<?= $id ?>" data-document_privacy="private">Set to Public</a></li>
                <?php } else{ ?>
                    <li><a href="#confirm_to_private" class="set_to_private_icon modal-trigger set_privacy_btn" data-document_id="<?= $id ?>" data-document_privacy="public">Set to Private</a></li>
                <?php } ?>
            <?php }else{ ?>
            <?php } ?>
            <li class="divider" tabindex="-1"></li>
            <li><a href="#confirm_to_remove" class="remove_icon modal-trigger remove_btn" data-document_id="<?= $id ?>" data-documentation_action="remove">Remove</a></li>
        </ul>
    </div>
</div>