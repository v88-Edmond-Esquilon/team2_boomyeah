document.addEventListener("DOMContentLoaded", function(){
    ux("body")
        .on("blur", ".document_title", (event) => {
            /* Check if empty title; Revert to old title if empty */
            if(!event.target.hasAttribute("readonly")){
                ux(event.target.closest(".edit_title_form")).trigger("submit");
            } 
        })
        .on("submit", ".edit_title_form", onChangeDocumentationTitle)
        .on("submit", "#duplicate_documentation_form", onSubmitDuplicateForm)
        .on("click", ".duplicate_icon", duplicateDocumentation)
        .on("click", "#add_documentation_btn", (event) => {
            ux("#add_documentation_form").trigger("submit");
        })
        .on("click", ".set_to_private_icon", async function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            
            let document_id = ux(event.target).attr("data-document_id");
            showConfirmPrivacyModal(document_id, 1, "#confirm_to_private"); 
        })
        /* Switch Active/Archive view */
        .on("click", ".switch_view_btn", switchDocumentationView)
        .on("submit", "#get_documentations_form", getDocumentations)
        .on("submit", "#add_documentation_form", onSubmitAddDocumentationForm)

        /* Archive & Unarchive of documentation */
        .on("click", "#archive_confirm", submitArchiveDocumentation)

        /* Remove documentation */
        .on("click", "#remove_confirm", submitRemoveDocumentation)

        /* Change Privacy*/
        .on("submit", "#change_document_privacy_form", onSubmitChangePrivacy)
        .on("click", ".change_privacy_yes_btn", onSubmitChangePrivacy)

        /* Reorder Documentations */
        .on("submit", "#reorder_documentations_form", submitReorderDocumentations)
});

function onSubmitDuplicateForm(event){
    event.preventDefault();
    event.stopImmediatePropagation();
    
    let duplicate_form   = ux("#duplicate_documentation_form");

    duplicate_form.post(duplicate_form.attr("action"), duplicate_form.serialize(), (response_data) => {
        if(response_data.status){
            // Append duplicated documentation
            let documentation           = document.getElementById(`document_${response_data.result.documentation_id}`);
            let duplicate_element       = response_data.result.html;
            
            documentation.insertAdjacentHTML('afterend', duplicate_element);
            let duplicate_documentation = document.getElementById(`document_${response_data.result.duplicate_id}`);

            addAnimation(duplicate_documentation, "animate__fadeIn");

            initializeMaterializeDropdown();
        }
        else{
            /* TODO: Use error handling prepare by FE */
            alert("Failed to duplicate documentation.");
        }
    });

    return false;
}

function duplicateDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let documentation  = event.target;
    let document_id    = documentation.dataset.document_id;
    let duplicate_form = ux("#duplicate_documentation_form");
    duplicate_form.find(".documentation_id").val(document_id);
    duplicate_form.trigger("submit");
}

async function showConfirmPrivacyModal(document_id, update_value = 0, modal_type = "#confirm_to_private"){
    let change_document_privacy_form = $("#change_document_privacy_form");

    change_document_privacy_form.find("#documentation_id").val(document_id);
    change_document_privacy_form.find("#update_value").val(update_value);


    let confirm_modal = document.querySelector(modal_type);
    var instance = M.Modal.getInstance(confirm_modal);

    ux(confirm_modal).find(".documentation_title").text(ux("#document_" + document_id).find(".document_title").val())
    instance.open();
}

function onSubmitAddDocumentationForm(event){
    event.preventDefault();
    let add_document_form = ux(event.target);
    let input_document_title = ux("#input_add_documentation").val();

    if(input_document_title){
        /** Use AJAX to generate new documentation */
        ux().post(add_document_form.attr("action"), add_document_form.serialize(), (response_data) => {
            if(response_data.status){
                if(response_data.result.html){
                    ux("#documentations").append(response_data.result.html);
                    initializeMaterializeDropdown();
                }

                /* Redirect in admin edit document page. */
                ux("#add_documentation_form").self().reset();
                
                // location.href = `/docs/${response_data.result.documentation_id}/edit`;
            }
            else{
                let add_documentation_input = ux(".group_add_documentation");
                add_documentation_input.addClass("input_error");
                addAnimation(".group_add_documentation", "animate__animated animate__headShake");
            }
        }, "json");
        
        return;
    }
    else{
        let add_documentation_input = ux(".group_add_documentation");

        add_documentation_input.addClass("input_error").addClass("animate__animated animate__headShake");
        add_documentation_input.on("animationend", () => {
            add_documentation_input.removeClass("animate__animated animate__headShake");
        });
    }
}

function initializeMaterializeDropdown(){
    let elems = document.querySelectorAll('.more_action_btn');
    M.Dropdown.init(elems, {
        alignment: 'left',
        coverTrigger: false,
        constrainWidth: false
    });
}

function onChangeDocumentationTitle(event){
    event.preventDefault();
    let edit_doc_title_form = $(event.target);
    let document_title_input = edit_doc_title_form.find(".document_title");
    let parent_document_block = edit_doc_title_form.closest(".document_block");
    parent_document_block.removeClass("error");
    
    if(document_title_input.val()){
        document_title_input.attr("readonly", "");
        let edit_title_form = ux(event.target);
        let original_value = edit_doc_title_form.find("[name=original_value]").val();

        if(original_value != edit_doc_title_form.find("[name=update_value]").val()){
            /** Use AJAX to generate new documentation */
            ux().post(edit_title_form.attr("action"), edit_title_form.serialize(), (response_data) => {
                if(response_data.status){
                    /* TODO: Improve UX after success updating of title. Add animation. */
                    parent_document_block.addClass("animate__animated animated_blinkBorder").removeClass("error");
                    
                    setTimeout(() => {
                        parent_document_block.removeClass("animate__animated animated_blinkBorder");
                    }, 480);
                }
                else{
                    let document_block = parent_document_block.attr("id");
                    let original_data = original_value;
                    document_title_input.self().blur();
                    document_title_input.val(original_data);
                    addAnimation(`#${document_block}`, "animate__animated animate__headShake");
                }
            }, "json");
        }
    }
    else{
        parent_document_block.addClass("error");

        parent_document_block.addClass("input_error").addClass("animate__animated animate__headShake");
        parent_document_block.on("animationend", () => {
            parent_document_block.removeClass("animate__animated animate__headShake");
        });
    }
    return;
}

function switchDocumentationView(event){
    let switch_view_btn = event.target;
    let container       = $(switch_view_btn).closest(".container");
    let docs_view_btn   = $(container).find("#docs_view_btn")[0];
    let form            = ux("#get_documentations_form");
    let is_archived     = parseInt(switch_view_btn.dataset.is_archived);
    let active_div      = is_archived ? document.getElementById("archived_documents") : document.getElementById("documentations");
    let hidden_div      = is_archived ? document.getElementById("documentations"): document.getElementById("archived_documents");

    docs_view_btn.innerText = switch_view_btn.innerText;
    active_div.classList.remove("hidden");
    hidden_div.innerHTML = "";
    hidden_div.classList.add("hidden");

    /* Update form value */
    form.find("#is_archived").val(is_archived ? "1" : "0");
    form.trigger("submit");
}


function onSubmitChangePrivacy(event){
    let privacy_form = ux("#change_document_privacy_form");
    
    /** Use AJAX to change documentation privacy */
    privacy_form.post(privacy_form.attr("action"), privacy_form.serialize(), (response_data) => {
        if(response_data.status){
            /* TODO: Improve UX after success updating. Add animation to indication the replace with the updated . */
            $(`#document_${response_data.result.documentation_id}`).replaceWith(response_data.result.html);
            $(`#document_${response_data.result.documentation_id}`).addClass("animate__animated animated_blinkBorder").removeClass("error");
                
            setTimeout(() => {
                $(`#document_${response_data.result.documentation_id}`).removeClass("animate__animated animated_blinkBorder");
                initializeMaterializeDropdown();
            }, 1280);
        }
    }, "json");

    return false;
}

function submitArchiveDocumentation(event){
    let archive_document_form = ux("#archive_form");

    archive_document_form.post(archive_document_form.attr("action"), archive_document_form.serialize(), (response_data) => {
        if(response_data.status){
            /* TODO: Improve UX after success updating. Add animation to remove the archived document from the list. */
            let documentation = $(`#document_${response_data.result.documentation_id}`);

            documentation.addClass("animate__animated animate__fadeOut");
            documentation.on("animationend", () => {
                documentation.remove();
            });

            // appearEmptyDocumentation();
            if(response_data.result.hasOwnProperty("no_documentations_html")){
                let documentations_div = (response_data.result.is_archived === "1") ? "#documentations" : "#archived_documents";
    
                $(documentations_div).html(response_data.result.no_documentations_html);
            }
        }
        else{
            /* TODO: Improve UX after error. Add animation red border. */
            alert(response_data.error);
        }
    }, "json");
    
    return;
}

function submitRemoveDocumentation(event){
    event.stopImmediatePropagation();
    event.preventDefault();

    let form      = ux("#remove_documentation_form");
    let form_data = form.serialize(); 
    
    if(form.find("#remove_is_archived").val() == "1"){
        form_data.append("archived_documentations", ux("#archived_documents").findAll(".document_block").length - 1);
    }

    form.post(form.attr("action"), form_data, (response_data) => {
        if(response_data.status){
            let documentation = document.getElementById(`document_${response_data.result.documentation_id}`);

            addAnimation(documentation, "animate__fadeOut");
            documentation.addEventListener("animationend", () => {
                documentation.remove();

                /* Check if we need to display no documentations message */
                if(response_data.result.hasOwnProperty("no_documentations_html")){
                    let documentations_div = (response_data.result.is_archived == "0") ? "documentations" : "archived_documents";
                    document.getElementById(documentations_div).innerHTML = response_data.result.no_documentations_html;
                }
            });
        }
        else{
            /* TODO: Use error handling prepare by FE */
            alert("Failed to remove documentation.");
        }
    });

    return false;
}

function getDocumentations(event){
    event.preventDefault();
    let form = ux("#get_documentations_form");

    form.post(form.attr("action"), form.serialize(), (response_data) => {
        if(response_data.status){
            let documentations_div = "documentations";
            document.getElementById("input_add_documentation").disabled = false;
            
            if(response_data.result.is_archived == "1"){
                documentations_div = "archived_documents";
                document.getElementById("input_add_documentation").disabled = true;
            }

            document.getElementById(documentations_div).innerHTML = response_data.result.html;
        }
        else{
            /* TODO: Design for displaying error */
        }

        initializeMaterializeDropdown();
    });

    return false;
}

function updateDocumentationsOrder(documentations){
    let documentation_children = documentations.children;
    let form = ux("#reorder_documentations_form");
    var new_documentations_order = "";

    /* Get documentation_id from documentation_children */
    for(let index=0; index < documentation_children.length; index++){
        new_documentations_order += (index == (documentation_children.length - 1)) ? `${documentation_children[index].id.split("_")[1]}` : `${documentation_children[index].id.split("_")[1]},`;
    }

    /* Update form value and submit form */
    form.find("#documentations_order").val(new_documentations_order);
    form.trigger("submit");
}

function submitReorderDocumentations(event){
    event.preventDefault();
    let form = ux("#reorder_documentations_form");

    form.post(form.attr("action"), form.serialize(), (response_data) => {
        console.log(response_data);
        if(!response_data.status){
            alert("An error occured while reordering documentations!");
        }
    });

    return false;
}