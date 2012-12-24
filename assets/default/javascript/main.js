
App.widgets.HooksWrap = function () {
    return {
        init: function (wrapper_id) {
            var wrapper = $("#" + wrapper_id);
            var days_off_table = wrapper.find("table.form");
            var new_row_counter = 0;
            var init_day_off_row = function (row) {
                row.find("td.options a.remove_day_off").click(function () {
                    if (confirm(App.lang("Are you sure that you want to delete this URL?"))) {
                        row.remove();
                        if (days_off_table.find("tr.day_off_row").length < 1) {
                            days_off_table.hide();
                            $("#no_days_off_message").show()
                        }
                    }
                    return false
                })
            };
            days_off_table.find("tr.day_off_row").each(function () {
                init_day_off_row($(this))
            });
            wrapper.find("a.button_add").click(function () {
                 $(".web_hook_url").css({"width": "300"});
                new_row_counter++;
                var row = $('<tr class="day_off_row"><td class="name"><input name="webhooks[]" id = "text-'+new_row_counter+'" type="text" class = web_hook_url/></td><td><input type = "button" onclick = test_url("text-'+new_row_counter+'","button-'+new_row_counter+'") value = "Test" id = "button-'+new_row_counter+'" ></td><td class="options right"><a href="#" title="' + App.lang("Remove Hook URL") + '" class="remove_day_off"><img src="' + App.Wireframe.Utils.imageUrl("/icons/12x12/delete.png", "environment") + '" alt="" /></a></td></tr>');
                days_off_table.append(row);
               
                init_day_off_row(row);
                days_off_table.oddEven({
                    selector: "tr.day_off_row"
                }).show();
                $("#no_days_off_message").hide();
                row.find("td.name input")[0].focus();
                return false
            })
        }
    }
}();

 
