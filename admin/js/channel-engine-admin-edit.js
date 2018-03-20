function r(f){/in/.test(document.readyState)?setTimeout('r('+f+')',9):f()}
// use like
r(function(){
    var dropdown            = document.getElementById('order_status');
    var overlay             = document.getElementById('_overlay_track_and_trace');
    var trace_textbox       = document.getElementById('_shipping_ce_track_and_trace');
    var shipping_dropdown   = document.getElementById('_shipping_ce_shipping_method');
    var altmethod_textbox   = document.getElementById('_shipping_ce_shipping_method_other');
    var overlay_textbox     = document.getElementById('_overlay_track_and_trace_textbox');
    var overlay_button      = document.getElementById('_overlay_track_and_trace_button');
    var overlay_dropdown    = document.getElementById('_overlay_shipment_method');
    var overlay_alttextbox  = document.getElementById('_overlay_alt_shipment_method');
    var edit_address_array  = document.querySelectorAll(".edit_address");
    var edit_address_button = edit_address_array[edit_address_array.length - 2];

    overlay_dropdown.innerHTML = shipping_dropdown.innerHTML;
    if(overlay_dropdown.value === "Other")
        overlay_alttextbox.classList.remove("hidden");
    else
        overlay_alttextbox.classList.add("hidden");

    dropdown.onchange = function(){
        if(dropdown.value === "wc-completed" && trace_textbox.value === ""){
            overlay.classList.remove("hidden");
        }
        else {
            overlay.classList.add("hidden");
        }
    };
    overlay_dropdown.onchange = function(){
        shipping_dropdown.value = overlay_dropdown.value;
        shipping_dropdown.onchange();

        if(overlay_dropdown.value === "Other")
            overlay_alttextbox.classList.remove("hidden");
        else
            overlay_alttextbox.classList.add("hidden");
    };
    overlay_alttextbox.value = altmethod_textbox.value;
    overlay_alttextbox.onchange = function(){
        altmethod_textbox.value = overlay_alttextbox.value;
    };
    overlay_textbox.onchange = function(){
        edit_address_button.click();
        trace_textbox.value = overlay_textbox.value;
    };
    overlay_button.onclick = function(){
        overlay.classList.add("hidden");
    };

    var shipping_other      = document.getElementById('_shipping_ce_shipping_method_other').parentElement;
    if(shipping_dropdown.value === "Other")
        shipping_other.classList.remove("hidden");
    else
        shipping_other.classList.add("hidden");
    shipping_dropdown.onchange = function(){
        if(shipping_dropdown.value === "Other")
            shipping_other.classList.remove("hidden");
        else
            shipping_other.classList.add("hidden");
    }
});