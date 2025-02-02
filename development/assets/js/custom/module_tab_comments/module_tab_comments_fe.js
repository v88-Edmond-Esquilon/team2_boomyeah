let is_mobile_reply_open = false;
let is_comments_displayed = false;
(function(){
    let swipe_value = 0;
    let swipe_timeout = null;
    let has_scrolled = false;
    
    document.addEventListener("DOMContentLoaded", async (event) => {
        let document_element = event.target;

        M.FormSelect.init(ux(".section_dropdown").self());
        
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

        /** Mobile Device events */
        document.addEventListener("touchstart", function (event){
            swipe_value = event.touches.item(0).clientX;
        });
        document.addEventListener("touchend", function (event){
            swipe_value = 0;
            // animateSwipe();
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
            }
        });
        
        ux("body")
            .on("keydown", ".comment_message", onCommentMessageKeypress)
            .on("click", ".toggle_reply_form_btn", showReplyForm)
            ;

        M.Sidenav.init(ux("#mobile_comments_slideout").self());
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
            showEditComment(event);

            showConfirmaDeleteComment(event);
        } else {
            closeCommentActions();
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
            let mobile_add_reply_form = ux(".mobile_add_reply_form");
            mobile_add_reply_form.addClass("show");
            ux(".mobile_add_comment_form").removeClass("show");
            mobile_add_reply_form.find("label").text(label_text);
            mobile_add_reply_form.find("textarea").self().focus();
            mobile_add_reply_form.find(".action").val("add_post_comment");
            mobile_add_reply_form.find(".post_id").val(ux(event.target).data("target_comment"));
        }
    }

    function toggleCommentActions(event){
        let event_target = event.target;

        if(event_target.classList.contains("comment_actions_toggle")){
            let viewport_width = document.documentElement.clientWidth;

            if(viewport_width > MOBILE_WIDTH){
                ux(event_target).conditionalClass("active", !event_target.classList.contains("active"));
            } else {
                event.stopImmediatePropagation();

                if(ux(".active_comment_item").self()){
                    ux(".active_comment_item").removeClass("active_comment_item");
                }

                let comment_id = ux(event_target).data("target_comment");
                let is_post = ux(event_target).data("is_post");

                let comment_actions = ux("#comment_actions_container");
                let edit_btn = comment_actions.find(".edit_btn");
                let remove_btn = comment_actions.find(".remove_btn");
                
                edit_btn.attr("data-target_comment", comment_id);
                edit_btn.attr("data-is_post", is_post);
                
                comment_actions.addClass("active");
                remove_btn.attr("data-target_comment", comment_id);
                remove_btn.attr("data-is_post", is_post);
                
                ux(event_target.closest(".comment_item")).addClass("active_comment_item");
            }
        }
    }
})();