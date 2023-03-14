(function(){
    let swipe_value = 0;
    let is_comments_displayed = false;
    let swipe_timeout = null;
    let is_mobile_reply_open = false;
    let active_comment_item = null;
    let has_scrolled = false;

    document.addEventListener("DOMContentLoaded", async (event) => {
        let document_element = event.target;
        
        ux("#section_pages").findAll("ul.comments_list").forEach((comments_list) => {
            if(!comments_list.classList.contains("replies_list")){
                ux(comments_list).findAll(".comment_container").forEach((comment_container) => {
                    showRepliesCount(comment_container);
                });
            }
        });
        document.addEventListener("click", onElementClick);

        document.addEventListener("scroll", (event) => {
            if(!ux("#prev_page_btn").self().classList.contains("onload") && !has_scrolled){
                if(window.scrollY >= document.body.scrollHeight - (window.innerHeight + (window.innerHeight/4))){
                    ux("#prev_page_btn").addClass("onload");
                    has_scrolled = true;
                }
            }
        })
        
        setTimeout(() => {
            ux("body").on("submit", "#remove_comment_form", onConfirmDeleteComment);
        }, 480);

        /** Mobile Device events */
        document.addEventListener("touchstart", function (event){
            swipe_value = event.touches.item(0).clientX;
        });
        document.addEventListener("touchend", function (event){
            swipe_value = 0;
            animateSwipe();
        });
        
        document.addEventListener("touchmove", function (event){
            clearTimeout(swipe_timeout);
            let event_swipe_value = (event.touches.item(0).clientX);
            let mobile_comments_slideout = ux("#mobile_comments_slideout");
            let swipe_direction = (swipe_value > (event_swipe_value)) ? "left" : "right";

            if(is_comments_displayed){
                if(swipe_value > (event_swipe_value + SWIPE_OFFSET)){
                    if(mobile_comments_slideout.self().classList.contains("active")){
                        mobile_comments_slideout.removeClass("active");
                        let mobile_comment_message = mobile_comments_slideout.find(".mobile_add_comment_form .comment_message");
                        mobile_comment_message.self().value = "";
                        mobile_comment_message.self().blur();
                        
                        /** Wait for comments sidenav to completely hide */
                        setTimeout(() => {
                            is_comments_displayed = false;
                        }, 480);
                    }
                } else {
                    if(!mobile_comments_slideout.self().classList.contains("active")){
                        mobile_comments_slideout.addClass("active");
                    }
                }
            } else {
                let swipe_amount = swipe_value - event_swipe_value;
                
                /** Check swipe only on section pages */
                if(event.target.closest("#section_pages") || event.target.closest("#mobile_section_pages_controls")){
                    if(Math.abs(swipe_amount) > (SWIPE_OFFSET / 2)){
                        animateSwipe(swipe_direction);
                    }
                    
                    if(Math.abs(swipe_amount) > SWIPE_OFFSET){
                        swipe_timeout = setTimeout(() => {
                            onSwipe(swipe_direction);
                        }, 148);
                    }
                }

            }

        });
        
        ux("body")
            .on("keydown", ".comment_message", onCommentMessageKeypress)
            .on("click", ".show_comments_btn", showTabComments)
            .on("click", ".toggle_reply_form_btn", showReplyForm)
            .on("click", ".toggle_replies_btn", showRepliesList)
            .on("click", ".mobile_comment_btn", (event) => {
                event.stopImmediatePropagation();
                onSubmitComment(event.target.closest(".mobile_add_comment_form"))
            });
    });
    
    async function animateSwipe(swipe_direction = ""){
        let active_section_page = ux("#section_pages .section_page_content.active");
        await active_section_page.removeClass("right");
        await active_section_page.removeClass("left");
        
        if(swipe_direction){
            active_section_page.addClass(swipe_direction);

            if(swipe_direction == "left"){
                ux("#prev_page_btn").removeClass("onload");
            }
        }
    }

    function onSwipe(swipe_direction){
        if(!is_comments_displayed){
            /** Move to prev/next section tab */
            if(swipe_direction == "right" && !ux("#prev_page_btn").self().classList.contains("hidden")){
                ux("#prev_page_btn").self().click();
            }

            if(swipe_direction == "left" && !ux("#next_page_btn").self().classList.contains("hidden")){
                ux("#next_page_btn").self().click();
            }
        }
    }
    
    function onElementClick(event){
        let event_target = event.target;
        let avoid_classes = ["comment_actions_toggle", "edit_btn", "remove_btn"];

        if( avoid_classes.some(avoid_class => event_target.classList.contains(avoid_class)) ){
            event.preventDefault();
            event.stopImmediatePropagation();
            toggleCommentActions(event);
            onEditComment(event);

            showConfirmaDeleteComment(event);
        } else {
            closeCommentActions();
        }
    }

    function showRepliesList(event){
        event.stopImmediatePropagation();
        let show_replies_btn = event.target.closest(".toggle_replies_btn");
        let comment_item = event.target.closest(".comment_item");
        let replies_list  = ux(comment_item).find(".replies_list");
        
        if(!replies_list.self().classList.contains("show")){
            addAnimation(replies_list.self(), "animate__zoomIn");

            replies_list.addClass("show");
            ux(show_replies_btn).addClass("hidden");
        }
    }

    async function showReplyForm(event){
        event.stopImmediatePropagation();
        let comment_item = event.target.closest(".comment_item");
        let label_text = "Replying to " + ux(comment_item).find(".user_name").text();

        if(ux(".active_comment_item").self()){
            await ux(".active_comment_item").removeClass("active_comment_item");
        }
    
        if((CLIENT_WIDTH > MOBILE_WIDTH)){
            let reply_form = ux(comment_item).find(".add_reply_form");
            
            if(!reply_form.self()){
                reply_form = ux(comment_item.closest(".replies_list").closest(".comment_item").querySelector(".add_reply_form"));
                reply_form.find("label").text(label_text);
            }
            
            if(!reply_form.self().classList.contains("show")){
                reply_form.addClass("show");
                addAnimation(reply_form.self(), "animate__zoomIn");
            }
    
            reply_form.find(".comment_message").self().focus();
        } else {
            is_mobile_reply_open = true;

            ux(comment_item).addClass("active_comment_item");
            let mobile_add_comment_form = ux(".mobile_add_comment_form");
            mobile_add_comment_form.find("label").text(label_text);
            mobile_add_comment_form.find("textarea").self().focus();
        }
    }

    function closeCommentActions(){
        ux(document).findAll(".comment_actions_toggle").forEach((element) => ux(element).removeClass("active"));
        ux("#comment_actions_container").removeClass("active");
        (!is_mobile_reply_open && ux(".active_comment_item").self()) && ux(".active_comment_item").removeClass("active_comment_item");
    }

    function showRepliesCount(comment_container){
        let comments_list = ux(comment_container).find(".replies_list");

        if(comments_list.self()){
            let reply_count = comments_list.findAll(".comment_item").length;
            let replies_text = reply_count + ` ${(reply_count == 1) ? "reply" : "replies"}`;
            ux(comment_container).find(".reply_count").text(replies_text);
        }
    }

    async function showTabComments(event){
        event.preventDefault();
        let mobile_comments_slideout = ux("#mobile_comments_slideout");
        mobile_comments_slideout.find("#user_comments_list").self().innerHtml = "";

        if(!mobile_comments_slideout.self().classList.contains("active")){
            // await include("#user_comments_list" , `${relative_view_path}/global/user_view_section_comments.html`);
            mobile_comments_slideout.addClass("active");
            is_comments_displayed = true;

            ux("#mobile_comments_slideout").findAll("ul.comments_list").forEach((comments_list) => {
                if(!comments_list.classList.contains("replies_list")){
                    ux(comments_list).findAll(".comment_container").forEach((comment_container) => {
                        showRepliesCount(comment_container);
                    });
                }
            });
        }
    }

    function showConfirmaDeleteComment(event){
        event.stopImmediatePropagation();
        let event_target = event.target;

        if(event_target.classList.contains("remove_btn")){
            let remove_comment_modal = ux("#confirm_remove_comment_modal");
            let modal_instance = M.Modal.getInstance(remove_comment_modal);
            modal_instance.open();
            ux("#remove_comment_form").on("submit", onConfirmDeleteComment);

            /** Determine active_comment_item */
            active_comment_item = (CLIENT_WIDTH > MOBILE_WIDTH) ? event_target.closest(".comment_item") : ux(".active_comment_item").self();
        }
    }

    function toggleCommentActions(event){
        let event_target = event.target;

        if(event_target.classList.contains("comment_actions_toggle")){
            let viewport_width = document.documentElement.clientWidth;

            if(viewport_width > MOBILE_WIDTH){
                if(event_target.classList.contains("active")){
                    event_target.classList.remove("active");
                } else {
                    event_target.classList.add("active");
                }
            } else {
                event.stopImmediatePropagation();

                if(ux(".active_comment_item").self()){
                    ux(".active_comment_item").removeClass("active_comment_item");
                }
                ux("#comment_actions_container").addClass("active");
                ux(event_target.closest(".comment_item")).addClass("active_comment_item");
            }
        }
    }

    function closeEditCommentForm(event){
        let edit_comment_form = ("type" in event) ? event.target.closest(".edit_comment_form") : event;

        /** Close edit form */
        edit_comment_form.remove();
        ux(".mobile_tab_comments").removeClass("hidden");
    }
})();