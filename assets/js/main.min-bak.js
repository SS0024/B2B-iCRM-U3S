function _make_task_checklist_items_deletable() {
    if ("1" == has_permission_tasks_checklist_items_delete) {
        var e = $("body").find(".checklist-templates-wrapper ul.dropdown-menu li").not(":first-child"),
            t = $("body").find(".checklist-templates-wrapper select option").not(":first-child");
        $.each(t, function(t, a) {
            var i = $(a);
            0 === $(e[t]).find(".checklist-item-template-remove").length && $(e[t]).find("a > span.text").after('<small class="checklist-item-template-remove" onclick="remove_checklist_item_template(' + i.attr("value") + '); event.stopPropagation();"><i class="fa fa-remove"></i></small>')
        })
    }
}

function _init_tasks_billable_select(e, t) {
    var a = $("#task_select");
    if (a.length > 0) {
        var i;
        a.find("option").filter(function() {
            return this.value || $.trim(this.value).length > 0 || $.trim(this.text).length > 0
        }).remove(), $.each(e, function(e, t) {
            i = " ", !0 === t.started_timers ? i += 'disabled class="text-danger important" data-subtext="' + appLang.invoice_task_billable_timers_found + '"' : !1 === t.started_timers && "project" != t.rel_type && (i += 'data-subtext="' + t.rel_name + '"'), a.append('<option value="' + t.id + '"' + i + ">" + t.name + "</option>")
        });
        var n = $(".input-group-addon-bill-tasks-help");
        n.find(".popover-invoker").popover("destroy"), n.empty();
        var s;
        s = empty(t) ? appLang.invoice_task_item_project_tasks_not_included : appLang.showing_billable_tasks_from_project + " " + $("#project_id option:selected").text().trim(), n.html('<span class="pointer popover-invoker" data-container=".form-group-select-task_select" data-trigger="click" data-placement="top" data-toggle="popover" data-content="' + s + '"><i class="fa fa-question-circle"></i></span>'), delay(function() {
            (n.attr("info-shown-count") < 3 || void 0 === n.attr("info-shown-count")) && $(".projects-wrapper").is(":visible") && e.length > 0 && (n.attr("info-shown-count", void 0 === n.attr("info-shown-count") ? 1 : parseInt(n.attr("info-shown-count")) + 1), n.find(".popover-invoker").click())
        }, 3500)
    }
    a.selectpicker("refresh")
}

function init_lightbox(e) {
    if ("undefined" != typeof lightbox) {
        var t = {
            showImageNumberLabel: !1,
            resizeDuration: 200,
            positionFromTop: 25
        };
        void 0 !== e && jQuery.extend(t, e), lightbox.option(t)
    }
}

function init_progress_bars() {
    var e = $("body").find(".progress div.progress-bar");
    e.length && e.each(function() {
        var e = $(this),
            t = e.attr("data-percent");
        e.css("width", t + "%"), e.hasClass("no-percent-text") || e.text(t + "%")
    })
}

function get_url_param(e) {
    var t = {};
    return window.location.href.replace(location.hash, "").replace(/[?&]+([^=&]+)=?([^&]*)?/gi, function(e, a, i) {
        t[a] = void 0 !== i ? i : ""
    }), e ? t[e] ? t[e] : null : t
}

function mainWrapperHeightFix() {
    var e = side_bar.height(),
        t = $("#wrapper").find(".content").height();
    setup_menu.css("min-height", $(document).outerHeight(!0) - 126 + "px"), content_wrapper.css("min-height", $(document).outerHeight(!0) - 63 + "px"), t < e && content_wrapper.css("min-height", e + "px"), t < e && e < $(window).height() && content_wrapper.css("min-height", $(window).height() - 63 + "px"), t > e && t < $(window).height() && content_wrapper.css("min-height", $(window).height() - 63 + "px"), is_mobile() && "true" == isRTL && side_bar.css("min-height", $(document).outerHeight(!0) - 63 + "px")
}

function set_body_small() {
    $(this).width() < 769 ? $("body").addClass("page-small") : $("body").removeClass("page-small show-sidebar")
}

function generatePassword(e) {
    for (var t = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", a = "", i = 0, n = t.length; i < 12; ++i) a += t.charAt(Math.floor(Math.random() * n));
    $(e).parents().find("input.password").val(a)
}

function switch_field(e) {
    var t;
    t = 0, !0 === $(e).prop("checked") && (t = 1), requestGet($(e).data("switch-url") + "/" + $(e).data("id") + "/" + t)
}

function _validate_form(e, t, a) {
    var i = $(e);
    if (i.length) {
        i.validate({
            rules: t,
            messages: {
                email: {
                    remote: appLang.email_exists
                }
            },
            ignore: [],
            submitHandler: function(e) {
                if ($(e).hasClass("disable-on-submit") && $(e).find('[type="submit"]').prop("disabled", !0), void 0 === a) return !0;
                a(e)
            }
        });
        var n = i.find("[data-custom-field-required]");
        n.length > 0 && $.each(n, function() {
            if (!$(this).parents("tr.main").length) {
                $(this).rules("add", {
                    required: !0
                });
                var e = $(this).attr("name"),
                    t = $(this).parents(".form-group").find('[for="' + e + '"]');
                t.length > 0 && 0 === t.find(".req").length && t.prepend('<small class="req text-danger">* </small>')
            }
        }), $.each(t, function(e, t) {
            if ("required" == t && !jQuery.isPlainObject(t) || jQuery.isPlainObject(t) && t.hasOwnProperty("required")) {
                var a = i.find('[for="' + e + '"]');
                a.length > 0 && 0 === a.find(".req").length && a.prepend(' <small class="req text-danger">* </small>')
            }
        }), $(document).trigger("app.form-validate", i)
    }
    return !1
}

function delete_option(e, t) {
    confirm_delete() && requestGetJSON("settings/delete_option/" + t).done(function(t) {
        !0 !== t.success && "true" != t.success || $(e).parents(".option").remove()
    })
}

function slideToggle(e, t) {
    var a = $(e);
    a.hasClass("hide") && a.removeClass("hide", "slow"), a.length && a.slideToggle();
    var i = $(".progress-bar").not(".not-dynamic");
    i.length > 0 && (i.each(function() {
        $(this).css("width", "0%"), $(this).text("0%")
    }), init_progress_bars()), "function" == typeof t && t()
}

function alert_float(e, t) {
    var a, i;
    a = $("body").find("float-alert").length, a = "alert_float_" + ++a, i = $('<div id="' + a + '" class="float-alert animated fadeInRight col-xs-11 col-sm-4 alert alert-' + e + '"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="fa fa-bell-o" data-notify="icon"></span><span class="alert-title">' + t + "</span></div>"), $("body").append(i), setTimeout(function() {
        $("#" + a).hide("fast", function() {
            $("#" + a).remove()
        })
    }, 3500)
}

function init_rel_tasks_table(e, t, a) {
    void 0 === a && (a = ".table-rel-tasks");
    var i = $("body").find(a);
    if (0 !== i.length) {
        var n, s = {},
            o = [0];
        n = $("body").find("._hidden_inputs._filters._tasks_filters input"), $.each(n, function() {
            s[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]'
        });
        var l = admin_url + "tasks/init_relation_tasks/" + e + "/" + t;
        "project" == i.attr("data-new-rel-type") && (l += "?bulk_actions=true"), initDataTable(i, l, o, o, s, [i.find("th.duedate").index(), "asc"])
    }
}

function get_datatable_buttons(e) {
    if (("persian" == app_language.toLowerCase() || "arabic" == app_language.toLowerCase()) && 0 === $("body").find("#amiri").length) {
        var t = document.createElement("script");
        t.setAttribute("src", "https://rawgit.com/xErik/pdfmake-fonts-google/master/build/script/ofl/amiri.js"), t.setAttribute("id", "amiri"), document.head.appendChild(t);
        var a = document.createElement("script");
        a.setAttribute("src", "https://rawgit.com/xErik/pdfmake-fonts-google/master/build/script/ofl/amiri.map.js"), document.head.appendChild(a)
    }
    var n = {
            body: function(e, t, a, i) {
                var n = $("<div></div>", e);
                n.append(e), n.find("[data-note-edit-textarea]").length > 0 && (n.find("[data-note-edit-textarea]").remove(), e = n.html().trim()), n.find(".row-options").length > 0 && (n.find(".row-options").remove(), e = n.html().trim()), n.find(".table-export-exclude").length > 0 && (n.find(".table-export-exclude").remove(), e = n.html().trim());
                var s = document.createElement("div");
                return s.innerHTML = e, (s.textContent || s.innerText || "").trim()
            }
        },
        s = [];
    table_export_button_is_hidden() || s.push({
        extend: "collection",
        text: appLang.dt_button_export,
        className: "btn btn-default-dt-options",
        buttons: [{
            extend: "excel",
            text: appLang.dt_button_excel,
            footer: !0,
            exportOptions: {
                columns: [":not(.not-export)"],
                rows: function(t) {
                    return _dt_maybe_export_only_selected_rows(t, e)
                },
                format: n
            }
        }, {
            extend: "csvHtml5",
            text: appLang.dt_button_csv,
            footer: !0,
            exportOptions: {
                columns: [":not(.not-export)"],
                rows: function(t) {
                    return _dt_maybe_export_only_selected_rows(t, e)
                },
                format: n
            }
        }, {
            extend: "pdfHtml5",
            text: appLang.dt_button_pdf,
            footer: !0,
            exportOptions: {
                columns: [":not(.not-export)"],
                rows: function(t) {
                    return _dt_maybe_export_only_selected_rows(t, e)
                },
                format: n
            },
            orientation: "landscape",
            customize: function(t) {
                var a = $(e).DataTable().columns().visible(),
                    n = a.length,
                    s = [],
                    o = 0;
                for (i = 0; i < n; i++) 1 == a[i] && o++;
                setTimeout(function() {
                    if (o <= 5) {
                        for (i = 0; i < o; i++) s.push(735 / o);
                        t.content[1].table.widths = s
                    }
                }, 10), "persian" != app_language.toLowerCase() && "arabic" != app_language.toLowerCase() || (t.defaultStyle.font = Object.keys(pdfMake.fonts)[0]), t.styles.tableHeader.alignment = "left", t.styles.tableHeader.margin = [5, 5, 5, 5], t.pageMargins = [12, 12, 12, 12]
            }
        }, {
            extend: "print",
            text: appLang.dt_button_print,
            footer: !0,
            exportOptions: {
                columns: [":not(.not-export)"],
                rows: function(t) {
                    return _dt_maybe_export_only_selected_rows(t, e)
                },
                format: n
            }
        }]
    });
    var o = $("body").find(".table-btn");
    return $.each(o, function() {
        var t = $(this);
        t.length && t.attr("data-table") && $(e).is(t.attr("data-table")) && s.push({
            text: t.text().trim(),
            className: "btn btn-default-dt-options",
            action: function(e, a, i, n) {
                t.click()
            }
        })
    }), $(e).hasClass("dt-no-serverside") || s.push({
        text: '<i class="fa fa-refresh"></i>',
        className: "btn btn-default-dt-options btn-dt-reload",
        action: function(e, t, a, i) {
            t.ajax.reload()
        }
    }), s
}

function table_export_button_is_hidden() {
    return "to_all" != app_show_table_export_button && ("hide" === app_show_table_export_button || "only_admins" === app_show_table_export_button && 0 == is_admin)
}

function DataTablesOfflineLazyLoadImages(e, t, a) {
    var i = $("img.img-table-loading", e);
    return i.attr("src", i.data("orig")), i.prev("div").addClass("hide"), e
}

function _dt_maybe_export_only_selected_rows(e, t) {
    t = $(t), e = e.toString();
    var a = t.find('thead th input[type="checkbox"]').eq(0);
    if (a && a.length > 0) {
        var i = t.find("tbody tr"),
            n = !1;
        return $.each(i, function() {
            $(this).find('td:first input[type="checkbox"]:checked').length && (n = !0)
        }), n ? t.find("tbody tr:eq(" + e + ') td:first input[type="checkbox"]:checked').length > 0 ? e : null : e
    }
    return e
}

function _table_jump_to_page(e, t) {
    var a = e.DataTable().page.info(),
        n = $("body").find("#dt-page-jump-" + t.sTableId);
    if (n.length && n.remove(), a.pages > 6) {
        var s = $("<select></select>", {
            class: "dt-page-jump-select form-control",
            id: "dt-page-jump-" + t.sTableId
        });
        for (i = 1; i <= a.pages; i++) {
            var o = a.page + 1 === i ? "selected" : "";
            s.append("<option value='" + i + "'" + o + ">" + i + "</option>")
        }
        $("#" + t.sTableId + "_wrapper .dt-page-jump").append(s)
    }
}

function initDatatableOffline(e) {
    initDataTableInline(e)
}

function initDataTableInline(e) {
    var t = void 0 !== e ? e : ".dt-table",
        a = $(t);
    if (0 !== a.length) {
        var i = [10, 25, 50, 100],
            n = [10, 25, 50, 100];
        if (app_tables_pagination_limit = parseFloat(app_tables_pagination_limit), -1 == $.inArray(app_tables_pagination_limit, i) && (i.push(app_tables_pagination_limit), n.push(app_tables_pagination_limit)), i.sort(function(e, t) {
                return e - t
            }), n.sort(function(e, t) {
                return e - t
            }), i.push(-1), n.push(appLang.dt_length_menu_all), a.length) {
            var s, o, l, d, r = {
                language: appLang.datatables,
                processing: !0,
                paginate: !0,
                responsive: !0,
                autoWidth: !1,
                order: [0, "desc"],
                pageLength: app_tables_pagination_limit,
                lengthMenu: [i, n],
                fnRowCallback: DataTablesOfflineLazyLoadImages,
                fnDrawCallback: function(e) {
                    _table_jump_to_page(this, e), 0 === e.aoData.length || 0 === e.aiDisplay.length ? $(e.nTableWrapper).addClass("app_dt_empty") : $(e.nTableWrapper).removeClass("app_dt_empty")
                },
                initComplete: function(e, a) {
                    var i = this.find(".dataTables_empty");
                    (this.hasClass("scroll-responsive") || 1 == app_scroll_responsive_tables) && this.wrap('<div class="table-responsive"></div>'), i.length && i.attr("colspan", this.find("thead th").length), this.parents(".table-loading").removeClass("table-loading");
                    var n = $(t),
                        s = n.find("thead th:last-child"),
                        o = n.find("thead th:first-child");
                    s.text().trim() == appLang.options && s.addClass("not-export"), o.find('input[type="checkbox"]').length > 0 && o.addClass("not-export")
                },
                dom: "<'row'><'row'<'col-md-6'lB><'col-md-6'f>r>t<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>"
            };
            s = $($(this)).attr("data-order-col"), o = $($(this)).attr("data-order-type"), $.each(a, function() {
                $(this).addClass("dt-no-serverside"), l = r, ($(this).hasClass("scroll-responsive") || 1 == app_scroll_responsive_tables) && (l.responsive = !1), s = $(this).attr("data-order-col"), o = $(this).attr("data-order-type"), s && o && (l.order = [
                    [s, o]
                ]), d = get_datatable_buttons(this), l.buttons = d;
                var e = $(this).DataTable(l);
                $(document).on("change", ".dt-page-jump-select", function() {
                    e.page($(this).val() - 1).draw(!1)
                })
            })
        }
    }
}

function initDataTable(e, t, a, i, n, s) {
    var o = "string" == typeof e ? $("body").find("table" + e) : e;
    if (0 === o.length) return !1;
    n = "undefined" == n || void 0 === n ? [] : n, void 0 === s ? s = [
        [0, "asc"]
    ] : 1 === s.length && (s = [s]);
    var l = o.attr("data-default-order");
    if (!empty(l)) {
        var d = JSON.parse(l),
            r = [];
        for (var c in d) o.find("thead th:eq(" + d[c][0] + ")").length > 0 && r.push(d[c]);
        r.length > 0 && (s = r)
    }
    var p = [10, 25, 50, 100],
        _ = [10, 25, 50, 100];
    app_tables_pagination_limit = parseFloat(app_tables_pagination_limit), -1 == $.inArray(app_tables_pagination_limit, p) && (p.push(app_tables_pagination_limit), _.push(app_tables_pagination_limit)), p.sort(function(e, t) {
        return e - t
    }), _.sort(function(e, t) {
        return e - t
    }), p.push(-1), _.push(appLang.dt_length_menu_all);
    var m = {
        language: appLang.datatables,
        processing: !0,
        retrieve: !0,
        serverSide: !0,
        paginate: !0,
        searchDelay: 750,
        bDeferRender: !0,
        responsive: !0,
        autoWidth: !1,
        dom: "<'row'><'row'<'col-md-7'lB><'col-md-5'f>>rt<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
        pageLength: app_tables_pagination_limit,
        lengthMenu: [p, _],
        columnDefs: [{
            searchable: !1,
            targets: a
        }, {
            sortable: !1,
            targets: i
        }],
        fnDrawCallback: function(e) {
            _table_jump_to_page(this, e), 0 === e.aoData.length ? $(e.nTableWrapper).addClass("app_dt_empty") : $(e.nTableWrapper).removeClass("app_dt_empty")
        },
        fnCreatedRow: function(e, t, a) {
            $(e).attr("data-title", t.Data_Title), $(e).attr("data-toggle", t.Data_Toggle)
        },
        initComplete: function(e, t) {
            var a = this,
                i = $(".btn-dt-reload");
            i.attr("data-toggle", "tooltip"), i.attr("title", appLang.dt_button_reload);
            var n = $(".dt-column-visibility");
            n.attr("data-toggle", "tooltip"), n.attr("title", appLang.dt_button_column_visibility), (a.hasClass("scroll-responsive") || 1 == app_scroll_responsive_tables) && a.wrap('<div class="table-responsive"></div>');
            var s = a.find(".dataTables_empty");
            s.length && s.attr("colspan", a.find("thead th").length), is_mobile() && $(window).width() < 400 && a.find('tbody td:first-child input[type="checkbox"]').length > 0 && (a.DataTable().column(0).visible(!1, !1).columns.adjust(), $("a[data-target*='bulk_actions']").addClass("hide")), a.parents(".table-loading").removeClass("table-loading"), a.removeClass("dt-table-loading");
            var o = a.find("thead th:last-child"),
                l = a.find("thead th:first-child");
            o.text().trim() == appLang.options && o.addClass("not-export"), l.find('input[type="checkbox"]').length > 0 && l.addClass("not-export"), mainWrapperHeightFix()
        },
        order: s,
        ajax: {
            url: t,
            type: "POST",
            data: function(e) {
                for (var t in "undefined" != typeof csrfData && (e[csrfData.token_name] = csrfData.hash), n) e[t] = $(n[t]).val();
                o.attr("data-last-order-identifier") && (e.last_order_identifier = o.attr("data-last-order-identifier"))
            }
        },
        buttons: get_datatable_buttons(o)
    };
    (o.hasClass("scroll-responsive") || 1 == app_scroll_responsive_tables) && (m.responsive = !1);
    var u = (o = o.dataTable(m)).DataTable(),
        f = o.find("th.not_visible"),
        h = [];
    if ($.each(f, function() {
            h.push(this.cellIndex)
        }), setTimeout(function() {
            for (var e in h) u.columns(h[e]).visible(!1, !1).columns.adjust()
        }, 10), o.hasClass("customizable-table")) {
        var v = o.find("th.toggleable"),
            b = $("#hidden-columns-" + o.attr("id"));
        try {
            b = JSON.parse(b.text())
        } catch (e) {
            b = []
        }
        $.each(v, function() {
            var e = $(this).attr("id");
            $.inArray(e, b) > -1 && u.column("#" + e).visible(!1)
        }), $("body").on("click", ".buttons-columnVisibility a", function() {
            $(this).blur()
        })
    }
    return o.is(":hidden") && o.find(".dataTables_empty").attr("colspan", o.find("thead th").length), o.on("preXhr.dt", function(e, t, a) {
        t.jqXHR && t.jqXHR.abort()
    }), $(document).on("change", ".dt-page-jump-select", function() {
        u.page($(this).val() - 1).draw(!1)
    }), u
}

function task_single_update_tags() {
    var e = $("#taskTags");
    $.post(admin_url + "tasks/update_tags", {
        tags: e.tagit("assignedTags"),
        task_id: e.attr("data-taskid")
    })
}

function task_attachments_toggle() {
    var e = $("#task-modal");
    e.find(".task_attachments_wrapper .task-attachments-more").toggleClass("hide"), e.find(".task_attachments_wrapper .task-attachments-less").toggleClass("hide")
}

function update_todo_items() {
    var e = $(".unfinished-todos li:not(.no-todos)"),
        t = $(".finished-todos li:not(.no-todos)"),
        a = 1;
    $.each(e, function() {
        $(this).find('input[name="todo_order"]').val(a), $(this).find('input[name="finished"]').val(0), a++
    }), 0 === e.length ? ($(".nav-total-todos").addClass("hide"), $(".unfinished-todos li.no-todos").removeClass("hide")) : e.length > 0 && ($(".unfinished-todos li.no-todos").hasClass("hide") || $(".unfinished-todos li.no-todos").addClass("hide"), $(".nav-total-todos").removeClass("hide").html(e.length)), x = 1, $.each(t, function() {
        $(this).find('input[name="todo_order"]').val(x), $(this).find('input[name="finished"]').val(1), $(this).find('input[type="checkbox"]').prop("checked", !0), a++, x++
    }), 0 === t.length ? $(".finished-todos li.no-todos").removeClass("hide") : t.length > 0 && ($(".finished-todos li.no-todos").hasClass("hide") || $(".finished-todos li.no-todos").addClass("hide"));
    var i = [];
    $.each(e, function() {
        var e = $(this).find(".todo-description");
        e.hasClass("line-throught") && e.removeClass("line-throught"), $(this).find('input[type="checkbox"]').prop("checked", !1), i.push([$(this).find('input[name="todo_id"]').val(), $(this).find('input[name="todo_order"]').val(), $(this).find('input[name="finished"]').val()])
    }), $.each(t, function() {
        var e = $(this).find(".todo-description");
        e.hasClass("line-throught") || e.addClass("line-throught"), i.push([$(this).find('input[name="todo_id"]').val(), $(this).find('input[name="todo_order"]').val(), $(this).find('input[name="finished"]').val()])
    }), data = {}, data.data = i, $.post(admin_url + "todo/update_todo_items_order", data)
}

function delete_todo_item(e, t) {
    requestGetJSON("todo/delete_todo_item/" + t).done(function(t) {
        !0 !== t.success && "true" != t.success || ($(e).parents("li").remove(), update_todo_items())
    })
}

function edit_todo_item(e) {
    requestGetJSON("todo/get_by_id/" + e).done(function(e) {
        var t = $("#__todo");
        t.find('input[name="todoid"]').val(e.todoid), t.find('textarea[name="description"]').val(e.description), t.modal("show")
    })
}

function init_datepicker(e, t, a, i) {
    var n = void 0 === e ? $(".datepicker") : e,
        s = void 0 === t ? $(".datetimepicker") : t;
    0 === s.length && 0 === n.length || ($.each(n, function() {
        var e = $(this),
            t = {
                timepicker: !1,
                scrollInput: !1,
                lazyInit: !0,
                format: app_date_format,
                dayOfWeekStart: app_calendar_first_day
            },
            a = e.attr("data-date-end-date"),
            i = e.attr("data-date-min-date"),
            n = e.attr("data-lazy");
        n && (t.lazyInit = "true" == n), a && (t.maxDate = a), i && (t.minDate = i), e.datetimepicker(t), e.parents(".form-group").find(".calendar-icon").on("click", function() {
            e.focus(), e.trigger("open.xdsoft")
        })
    }), $.each(s, function() {
        var e = $(this),
            t = {
                lazyInit: !0,
                scrollInput: !1,
                validateOnBlur: !1,
                dayOfWeekStart: app_calendar_first_day
            };
        24 == app_time_format ? t.format = app_date_format + " H:i" : (t.format = app_date_format + " g:i A", t.formatTime = "g:i A");
        var a = e.attr("data-date-end-date"),
            i = e.attr("data-date-min-date"),
            n = e.attr("data-lazy");
        n && (opt.lazyInit = "true" == n), a && (t.maxDate = a), i && (t.minDate = i), e.datetimepicker(t), e.parents(".form-group").find(".calendar-icon").on("click", function() {
            e.focus(), e.trigger("open.xdsoft")
        })
    }))
}

function init_tags_inputs() {
    var e = $("body").find("input.tagsinput");
    e.length && e.tagit({
        availableTags: availableTags,
        allowSpaces: !0,
        animate: !1,
        placeholderText: appLang.tag,
        showAutocompleteOnFocus: !0,
        caseSensitive: !1,
        autocomplete: {
            appendTo: "#inputTagsWrapper"
        },
        afterTagAdded: function(e, t) {
            var a = availableTags.indexOf($.trim($(t.tag).find(".tagit-label").text()));
            if (a > -1) {
                var i = availableTagsIds[a];
                $(t.tag).addClass("tag-id-" + i)
            }
            showHideTagsPlaceholder($(this))
        },
        afterTagRemoved: function(e, t) {
            showHideTagsPlaceholder($(this))
        }
    })
}

function init_color_pickers() {
    var e = $("body").find("div.colorpicker-input");
    e.length && e.colorpicker({
        format: "hex"
    })
}

function init_selectpicker() {
    var e = $("body").find("select.selectpicker");
    e.length && e.selectpicker({
        showSubtext: !0
    })
}

function dt_custom_view(e, t, a, i) {
    var n = void 0 === a ? "custom_view" : a;
    if (void 0 !== i) {
        var s = $("._filter_data li.active").not(".clear-all-prevent");
        s.removeClass("active"), $.each(s, function() {
            var e = $(this).find("a").attr("data-cview");
            $('._filters input[name="' + e + '"]').val("")
        })
    }
    do_filter_active(n) != n && (e = ""), $('input[name="' + n + '"]').val(e), $(t).DataTable().ajax.reload()
}

function do_filter_active(e, t) {
    if ("" !== e && void 0 !== e) {
        $('[data-cview="all"]').parents("li").removeClass("active");
        var a = $('[data-cview="' + e + '"]');
        void 0 !== t && (a = $(t + ' [data-cview="' + e + '"]'));
        var i = a.parents("li");
        if (i.hasClass("filter-group")) {
            var n = i.data("filter-group");
            $('[data-filter-group="' + n + '"]').not(i).removeClass("active"), $.each($('[data-filter-group="' + n + '"]').not(i), function() {
                $('input[name="' + $(this).find("a").attr("data-cview") + '"]').val("")
            })
        }
        if (i.not(".dropdown-submenu").hasClass("active")) {
            i.not(".dropdown-submenu").removeClass("active");
            var s = a.parents("li.dropdown-submenu");
            s.length > 0 && 0 === s.find("li.active").length && s.removeClass("active"), e = ""
        } else i.addClass("active");
        return e
    }
    return $("._filters input").val(""), $("._filter_data li.active").removeClass("active"), $('[data-cview="all"]').parents("li").addClass("active"), ""
}

function init_roles_permissions(e, t) {
    if (e = void 0 === e ? $('select[name="role"]').val() : e, !($('.member > input[name="isedit"]').length > 0 && void 0 !== e && void 0 === t) && !0 !== $('input[name="administrator"]').prop("checked") && "" !== e) {
        var a = $("table.roles").find("tr");
        requestGetJSON("misc/get_role_permissions_ajax/" + e).done(function(e) {
            var t, i;
            $.each(a, function() {
                var a = $(this).data("id"),
                    n = $(this);
                0 === e.length && n.find('input[type="checkbox"]').prop("checked", !1), $.each(e, function(e, s) {
                    a == s.permissionid && (t = 1 == s.can_view, i = 1 == s.can_view_own, n.find("[data-can-view]").prop("checked", t), !0 === t && n.find("[data-can-view]").change(), n.find("[data-can-view-own]").prop("checked", i), !0 === i && n.find("[data-can-view-own]").change(), n.find("[data-can-edit]").prop("checked", 1 == s.can_edit), n.find("[data-can-create]").prop("checked", 1 == s.can_create), n.find("[data-can-delete]").prop("checked", 1 == s.can_delete))
                })
            })
        })
    }
}

function hidden_input(e, t) {
    return '<input type="hidden" name="' + e + '" value="' + t + '">'
}

function toggle_small_view(e, t) {
    $("body").toggleClass("small-table");
    var a = $("#small-table");
    if (0 !== a.length) {
        var i = !1;
        a.hasClass("col-md-5") ? (a.removeClass("col-md-5").addClass("col-md-12"), i = !0, $(".toggle-small-view").find("i").removeClass("fa fa-angle-double-right").addClass("fa fa-angle-double-left")) : (a.addClass("col-md-5").removeClass("col-md-12"), $(".toggle-small-view").find("i").removeClass("fa fa-angle-double-left").addClass("fa fa-angle-double-right"));
        var n = $(e).DataTable();
        n.columns(hidden_columns).visible(i, !1), n.columns.adjust(), $(t).toggleClass("hide"), $(window).trigger("resize")
    }
}

function stripTags(e) {
    var t = document.createElement("DIV");
    return t.innerHTML = e, t.textContent || t.innerText || ""
}

function empty(e) {
    if ("number" == typeof e || "boolean" == typeof e) return !1;
    if (null == e) return !0;
    if (void 0 !== e.length) return 0 === e.length;
    var t = 0;
    for (var a in e) e.hasOwnProperty(a) && t++;
    return 0 === t
}

function is_mobile() {
    return app_is_mobile
}

function logout() {
    if ($(".started-timers-top").find("li.timer").length > 0) return system_popup({
        message: " ",
        content: $("#timers-logout-template-warning").html()
    }).find(".popup-message").addClass("hide"), !1;
    window.location.href = site_url + "authentication/logout"
}

function color(e, t, a) {
    return "rgb(" + e + "," + t + "," + a + ")"
}

function buildUrl(e, t) {
    var a = "";
    for (var i in t) {
        var n = t[i];
        a += encodeURIComponent(i) + "=" + encodeURIComponent(n) + "&"
    }
    return a.length > 0 && (e = e + "?" + (a = a.substring(0, a.length - 1))), e
}

function decimalToHM(e) {
    var t = parseInt(Number(e)),
        a = Math.round(60 * (Number(e) - t));
    return (t < 10 ? "0" + t : t) + ":" + (a < 10 ? "0" + a : a)
}

function elFinderBrowser(e, t, a, i) {
    return tinymce.activeEditor.windowManager.open({
        file: admin_url + "misc/tinymce_file_browser",
        title: appLang.media_files,
        width: 900,
        height: 450,
        resizable: "yes"
    }, {
        setUrl: function(t) {
            i.document.getElementById(e).value = t
        }
    }), !1
}

function init_editor(e, t) {
    e = void 0 === e ? ".tinymce" : e;
    var a = $(e);
    if (0 !== a.length) {
        $.each(a, function() {
            $(this).hasClass("tinymce-manual") && $(this).removeClass("tinymce")
        });
        var i = {
            branding: !1,
            selector: e,
            browser_spellcheck: !0,
            height: 400,
            theme: "modern",
            skin: "perfex",
            language: tinymceLang,
            relative_urls: !1,
            inline_styles: !0,
            verify_html: !1,
            cleanup: !1,
            autoresize_bottom_margin: 25,
            valid_elements: "+*[*]",
            valid_children: "+body[style], +style[type]",
            apply_source_formatting: !1,
            remove_script_host: !1,
            removed_menuitems: "newdocument restoredraft",
            forced_root_block: !1,
            autosave_restore_when_empty: !1,
            fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
            setup: function(e) {
                e.on("init", function() {
                    this.getDoc().body.style.fontSize = "12pt"
                })
            },
            table_default_styles: {
                width: "100%"
            },
            plugins: ["advlist autoresize autosave lists link image print hr codesample", "visualblocks code fullscreen", "media save table contextmenu", "paste textcolor colorpicker"],
            toolbar1: "fontselect fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | image link | bullist numlist | restoredraft",
            file_browser_callback: elFinderBrowser
        };
        if ("true" == isRTL && (i.directionality = "rtl"), "true" == isRTL && (i.plugins[0] += " directionality"), void 0 !== t)
            for (var n in t) i[n] = t[n];
        var s = tinymce.init(i);
        return $(document).trigger("app.editor.initialized"), s
    }
}

function _formatMenuIconInput(e) {
    if (void 0 !== e) {
        var t = $(e.target);
        t.val().match(/^fa /) || t.val("fa " + t.val())
    }
}

function showPassword(e) {
    var t = $('input[name="' + e + '"]');
    "password" == $(t).attr("type") && "" !== $(t).val() ? $(t).queue(function() {
        $(t).attr("type", "text").dequeue()
    }) : $(t).queue(function() {
        $(t).attr("type", "password").dequeue()
    })
}

function init_btn_with_tooltips() {
    if (is_mobile()) {
        if (null != navigator.userAgent.match(/iPad/i)) return !1;
        var e = $("._buttons").find(".btn-with-tooltip");
        $.each(e, function() {
            var e = $(this).attr("title");
            void 0 === e && (e = $(this).attr("data-title")), void 0 !== e && ($(this).append(" " + e), $(this).removeClass("btn-with-tooltip"))
        });
        var t = $("._buttons").find(".btn-with-tooltip-group");
        $.each(t, function() {
            var e = $(this).attr("title");
            void 0 === e && (e = $(this).attr("data-title")), void 0 !== e && ($(this).find(".btn").eq(0).append(" " + e), $(this).removeClass("btn-with-tooltip-group"))
        })
    }
}

function do_hash_helper(e) {
    if (void 0 !== history.pushState) {
        var t = {
            Url: window.location.href
        };
        history.pushState(t, "", t.Url), window.location.hash = e
    }
}

function init_form_reminder() {
    var e = $('[id^="form-reminder-"]');
    $.each(e, function(e, t) {
        _validate_form($(t), {
            date: "required",
            staff: "required"
        }, reminderFormHandler)
    })
}

function edit_reminder(e, t) {
    requestGetJSON("misc/get_reminder/" + e).done(function(t) {
        var a = $(".reminder-modal-" + t.rel_type + "-" + t.rel_id);
        0 === a.length && $("body").hasClass("all-reminders") && ((a = $(".reminder-modal--")).find('input[name="rel_type"]').val(t.rel_type), a.find('input[name="rel_id"]').val(t.rel_id)), a.find("form").attr("action", admin_url + "misc/edit_reminder/" + e), a.find("#description").val(t.description), a.find("#date").val(t.date), a.find("#staff").selectpicker("val", t.staff), a.find("#notify_by_email").prop("checked", 1 == t.notify_by_email), a.modal("show")
    })
}

function reminderFormHandler(e) {
    var t = (e = $(e)).serialize();
    return $.post(e.attr("action"), t).done(function(e) {
        "" !== (e = JSON.parse(e)).message && alert_float(e.alert_type, e.message), reload_reminders_tables()
    }), $("body").hasClass("all-reminders") ? $(".reminder-modal--").modal("hide") : $(".reminder-modal-" + e.find('[name="rel_type"]').val() + "-" + e.find('[name="rel_id"]').val()).modal("hide"), !1
}

function reload_reminders_tables() {
    $.each([".table-reminders", ".table-reminders-leads", ".table-my-reminders"], function(e, t) {
        $.fn.DataTable.isDataTable(t) && $("body").find(t).DataTable().ajax.reload()
    })
}

function close_modal_manually(e) {
    (e = 0 === $(e).length ? $("body").find(e) : e = $(e)).fadeOut("fast", function() {
        e.remove(), $("body").find(".modal").is(":visible") || ($(".modal-backdrop").remove(), $("body").removeClass("modal-open"))
    })
}

function toggle_edit_note(e) {
    $("body").find('[data-note-edit-textarea="' + e + '"]').toggleClass("hide"), $("body").find('[data-note-description="' + e + '"]').toggleClass("hide")
}

function edit_note(e) {
    var t = $("body").find('[data-note-edit-textarea="' + e + '"] textarea').val();
    "" !== t && ($.post(admin_url + "misc/edit_note/" + e, {
        description: t
    }).done(function(a) {
        !0 !== (a = JSON.parse(a)).success && "true" != a.success || (alert_float("success", a.message), $("body").find('[data-note-description="' + e + '"]').html(nl2br(t)))
    }), toggle_edit_note(e))
}

function toggle_file_visibility(e, t, a) {
    requestGet("misc/toggle_file_visibility/" + e).done(function(e) {
        1 == e ? $(a).find("i").removeClass("fa fa-toggle-off").addClass("fa fa-toggle-on") : $(a).find("i").removeClass("fa fa-toggle-on").addClass("fa fa-toggle-off")
    })
}

function nl2br(e, t) {
    return (e + "").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, "$1" + (t || void 0 === t ? "<br />" : "<br>") + "$2")
}

function fix_kanban_height(e, t) {
    $("body").find("div.dt-loader").remove();
    var a = $(".kan-ban-content-wrapper");
    a.css("max-height", window.innerHeight - e + "px"), $(".kan-ban-content").css("min-height", window.innerHeight - e + "px");
    var i = parseInt(a.length);
    $(".container-fluid").css("min-width", i * t + "px")
}

function kanban_load_more(e, t, a, i, n) {
    var s, o = [],
        l = $('input[name="search"]').val(),
        d = $(t).attr("data-page"),
        r = $('[data-col-status-id="' + e + '"]').data("total-pages");
    if (d <= r) {
        var c = $('input[name="sort_type"]'),
            p = $('input[name="sort"]').val();
        0 != c.length && "" !== c.val() && (o.sort_by = c.val(), o.sort = p), void 0 !== l && "" !== l && (o.search = l), $.each($("#kanban-params input"), function() {
            "" !== (s = "checkbox" == $(this).attr("type") ? !0 === $(this).prop("checked") ? $(this).val() : "" : $(this).val()) && (o[$(this).attr("name")] = s)
        }), o.status = e, o.page = d, o.page++, requestGet(buildUrl(admin_url + a, o)).done(function(a) {
            d++, $('[data-load-status="' + e + '"]').before(a), $(t).attr("data-page", d), fix_kanban_height(i, n)
        }).fail(function(e) {
            alert_float("danger", e.responseText)
        }), d >= r - 1 && $(t).addClass("disabled")
    }
}

function check_kanban_empty_col(e) {
    var t = $("[data-col-status-id]");
    $.each(t, function(t, a) {
        0 == $(a).find(e).length ? ($(a).find(".kanban-empty").removeClass("hide"), $(a).find(".kanban-load-more").addClass("hide")) : $(a).find(".kanban-empty").addClass("hide")
    })
}

function init_kanban(e, t, a, i, n, s) {
    if (0 !== $("#kan-ban").length) {
        var o, l = [];
        $.each($("#kanban-params input"), function() {
            "" !== (o = "checkbox" == $(this).attr("type") ? !0 === $(this).prop("checked") ? $(this).val() : "" : $(this).val()) && (l[$(this).attr("name")] = o)
        });
        var d = $('input[name="search"]').val();
        void 0 !== d && "" !== d && (l.search = d);
        var r = $('input[name="sort_type"]'),
            c = $('input[name="sort"]').val();
        0 != r.length && "" !== r.val() && (l.sort_by = r.val(), l.sort = c), l.kanban = !0, e = buildUrl(e = admin_url + e, l), delay(function() {
            $("body").append('<div class="dt-loader"></div>'), $("#kan-ban").load(e, function() {
                fix_kanban_height(i, n), void 0 !== s && s(), $(".status").sortable({
                    connectWith: a,
                    helper: "clone",
                    appendTo: "#kan-ban",
                    placeholder: "ui-state-highlight-card",
                    revert: "invalid",
                    scrollingSensitivity: 50,
                    scrollingSpeed: 70,
                    sort: function(e, t) {
                        var a = t.placeholder[0].parentNode;
                        a = $(a).parents(".kan-ban-content-wrapper")[0];
                        var i = $(a).offset();
                        i.top + a.offsetHeight - e.pageY < 20 ? a.scrollTop = a.scrollTop + 60 : e.pageY - i.top < 20 && (a.scrollTop = a.scrollTop - 60), i.left + a.offsetWidth - e.pageX < 20 ? a.scrollLeft = a.scrollLeft + 60 : e.pageX - i.left < 20 && (a.scrollLeft = a.scrollLeft - 60)
                    },
                    change: function() {
                        var e = $(this).closest("ul"),
                            t = $(e).find(".kanban-load-more");
                        $(e).append($(t).detach())
                    },
                    start: function(e, t) {
                        $("body").css("overflow", "hidden"), $(t.helper).addClass("tilt"), $(t.helper).find(".panel-body").css("background", "#fbfbfb"), tilt_direction($(t.helper))
                    },
                    stop: function(e, t) {
                        $("body").removeAttr("style"), $(t.helper).removeClass("tilt"), $("html").off("mousemove", $(t.helper).data("move_handler")), $(t.helper).removeData("move_handler")
                    },
                    update: function(e, a) {
                        t(a, this)
                    }
                }), $(".status").sortable({
                    cancel: ".not-sortable"
                })
            })
        }, 200)
    }
}

function kan_ban_sort(e, t) {
    $('input[name="sort_type"]').val(e);
    var a = $('input[name="sort"]'),
        i = a.val().toLowerCase();
    a.val("asc" == i ? "DESC" : "ASC"), init_kan_ban_sort_icon(a.val(), e), t()
}

function init_kan_ban_sort_icon(e, t) {
    $("body").find(".kanban-sort-icon").remove(), $("body").find("." + t).prepend(" <i class='kanban-sort-icon fa fa-sort-amount-" + e.toLowerCase() + "'></i>")
}

function tilt_direction(e) {
    setTimeout(function() {
        var t = e.position().left,
            a = function(a) {
                a.pageX >= t ? (e.addClass("right"), e.removeClass("left")) : (e.addClass("left"), e.removeClass("right")), t = a.pageX
            };
        $("html").on("mousemove", a), e.data("move_handler", a)
    }, 1e3)
}

function init_newsfeed_form() {
    "undefined" == typeof newsFeedDropzone && $("body").on("submit", "#new-post-form", function() {
        return $.post(this.action, $(this).serialize()).done(function(e) {
            if ((e = JSON.parse(e)).postid) {
                if (newsFeedDropzone.getQueuedFiles().length > 0) return newsFeedDropzone.options.url = admin_url + "newsfeed/add_post_attachments/" + e.postid, void newsFeedDropzone.processQueue();
                newsfeed_new_post(e.postid), clear_newsfeed_post_area()
            }
        }), !1
    }), newsFeedDropzone = new Dropzone("#new-post-form", $.extend({}, _dropzone_defaults(), {
        clickable: ".add-post-attachments",
        autoProcessQueue: !1,
        addRemoveLinks: !0,
        parallelUploads: app_newsfeed_maximum_files_upload,
        maxFiles: app_newsfeed_maximum_files_upload,
        dragover: function(e) {
            $("#new-post-form").addClass("dropzone-active")
        },
        complete: function(e) {},
        drop: function(e) {
            $("#new-post-form").removeClass("dropzone-active")
        },
        success: function(e, t) {
            0 === this.getUploadingFiles().length && 0 === this.getQueuedFiles().length && (newsfeed_new_post((t = JSON.parse(t)).postid), clear_newsfeed_post_area(), this.removeAllFiles())
        }
    }))
}

function clear_newsfeed_post_area() {
    $("#new-post-form textarea").val(""), $("#post-visibility").selectpicker("deselectAll")
}

function load_post_likes(e) {
    track_load_post_likes <= post_likes_total_pages && ($.post(admin_url + "newsfeed/load_likes_modal", {
        page: track_load_post_likes,
        postid: e
    }).done(function(e) {
        track_load_post_likes++, $("#modal_post_likes_wrapper").append(e)
    }), track_load_post_likes >= post_likes_total_pages - 1 && $(".likes_modal .modal-footer").addClass("hide"))
}

function load_comment_likes(e) {
    track_load_comment_likes <= comment_likes_total_pages && ($.post(admin_url + "newsfeed/load_comment_likes_model", {
        page: track_load_comment_likes,
        commentid: e
    }).done(function(e) {
        track_load_comment_likes++, $("#modal_comment_likes_wrapper").append(e)
    }), track_load_comment_likes >= comment_likes_total_pages - 1 && $(".likes_modal .modal-footer").addClass("hide"))
}

function load_more_comments(e) {
    var t = $(e).data("postid"),
        a = $(e).find('input[name="page"]').val(),
        i = $(e).data("total-pages");
    a <= i && ($.post(admin_url + "newsfeed/init_post_comments/" + t, {
        page: a
    }).done(function(i) {
        $(e).data("track-load-comments", a), $('[data-comments-postid="' + t + '"] .load-more-comments').before(i)
    }), a++, $(e).find('input[name="page"]').val(a), a >= i - 1 && ($(e).addClass("hide"), $(e).removeClass("display-block")))
}

function newsfeed_new_post(e) {
    var t = {};
    t.postid = e, $.post(admin_url + "newsfeed/load_newsfeed", t).done(function(e) {
        var t = $("#newsfeed_data").find(".pinned").length;
        if (0 === t) $("#newsfeed_data").prepend(e);
        else {
            var a = $("#newsfeed_data").find(".pinned").eq(t - 1);
            $(a).after(e)
        }
    })
}

function load_newsfeed(e) {
    var t = {};
    t.page = newsfeed_posts_page, void 0 !== e && 0 != e && (t.postid = e);
    var a = $('input[name="total_pages_newsfeed"]').val();
    newsfeed_posts_page <= a && $.post(admin_url + "newsfeed/load_newsfeed", t).done(function(e) {
        newsfeed_posts_page++, $("#newsfeed_data").append(e)
    })
}

function like_post(e) {
    requestGetJSON("newsfeed/like_post/" + e).done(function(t) {
        !0 !== t.success && "true" != t.success || refresh_post_likes(e)
    })
}

function unlike_post(e) {
    requestGetJSON("newsfeed/unlike_post/" + e).done(function(t) {
        !0 !== t.success && "true" != t.success || refresh_post_likes(e)
    })
}

function like_comment(e, t) {
    requestGetJSON("newsfeed/like_comment/" + e + "/" + t).done(function(t) {
        !0 !== t.success && "true" != t.success || $('[data-commentid="' + e + '"]').replaceWith(t.comment)
    })
}

function unlike_comment(e, t) {
    requestGetJSON("newsfeed/unlike_comment/" + e + "/" + t).done(function(t) {
        !0 !== t.success && "true" != t.success || $('[data-commentid="' + e + '"]').replaceWith(t.comment)
    })
}

function add_comment(e) {
    var t = $(e).data("postid");
    $.post(admin_url + "newsfeed/add_comment", {
        content: $(e).val(),
        postid: t
    }).done(function(a) {
        !0 !== (a = JSON.parse(a)).success && "true" != a.success || ($(e).val(""), $("body").find('[data-comments-postid="' + t + '"] .post-comment').length > 0 ? $("body").find('[data-comments-postid="' + t + '"] .post-comment').prepend(a.comment) : refresh_post_comments(t))
    })
}

function remove_post_comment(e, t) {
    requestGetJSON("newsfeed/remove_post_comment/" + e + "/" + t).done(function(t) {
        !0 !== t.success && "true" != t.success || $('.comment[data-commentid="' + e + '"]').remove()
    })
}

function refresh_post_likes(e) {
    requestGet("newsfeed/init_post_likes/" + e + "?refresh_post_likes=true").done(function(t) {
        $('[data-likes-postid="' + e + '"]').html(t)
    })
}

function refresh_post_comments(e) {
    $.post(admin_url + "newsfeed/init_post_comments/" + e + "?refresh_post_comments=true").done(function(t) {
        $('[data-comments-postid="' + e + '"]').html(t)
    })
}

function delete_post(e) {
    confirm_delete() && $.post(admin_url + "newsfeed/delete_post/" + e, function(t) {
        !0 !== t.success && "true" != t.success || $('[data-main-postid="' + e + '"]').remove()
    }, "json")
}

function pin_post(e) {
    requestGetJSON("newsfeed/pin_newsfeed_post/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || window.location.reload()
    })
}

function unpin_post(e) {
    requestGetJSON("newsfeed/unpin_newsfeed_post/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || window.location.reload()
    })
}

function _gen_lead_add_inline_on_select_field(e) {
    var t;
    ($("body").hasClass("leads-email-integration") || $("body").hasClass("web-to-lead-form")) && (e = "lead_" + e), t = '<div id="new_lead_' + e + '_inline" class="form-group"><label for="new_' + e + '_name">' + $('label[for="' + e + '"]').html().trim() + '</label><div class="input-group"><input type="text" id="new_' + e + '_name" name="new_' + e + '_name" class="form-control"><div class="input-group-addon"><a href="#" onclick="lead_add_inline_select_submit(\'' + e + '\'); return false;" class="lead-add-inline-submit-' + e + '"><i class="fa fa-check"></i></a></div></div></div>', $(".form-group-select-input-" + e).after(t), $("body").find("#new_" + e + "_name").focus(), $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop("disabled", !0), $(".inline-field-new").addClass("disabled").css("opacity", .5), $(".form-group-select-input-" + e).addClass("hide")
}

function new_lead_status_inline() {
    _gen_lead_add_inline_on_select_field("status")
}

function new_lead_source_inline() {
    _gen_lead_add_inline_on_select_field("source")
}

function lead_add_inline_select_submit(e) {
    var t = $("#new_" + e + "_name").val().trim();
    if ("" !== t) {
        var a = e;
        e.indexOf("lead_") > -1 && (a = a.replace("lead_", ""));
        var i = {};
        i.name = t, i.inline = !0, $.post(admin_url + "leads/" + a, i).done(function(a) {
            if (!0 === (a = JSON.parse(a)).success || "true" == a.success) {
                var i = $("body").find("select#" + e);
                i.append('<option value="' + a.id + '">' + t + "</option>"), i.selectpicker("val", a.id), i.selectpicker("refresh"), i.parents(".form-group").removeClass("has-error")
            }
        })
    }
    $("#new_lead_" + e + "_inline").remove(), $(".form-group-select-input-" + e).removeClass("hide"), $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop("disabled", !1), $(".inline-field-new").removeClass("disabled").removeAttr("style")
}

function init_lead(e, t) {
    $("#task-modal").is(":visible") && $("#task-modal").modal("hide"), init_lead_modal_data(e, void 0, t) && $("#lead-modal").modal("show")
}

function validate_lead_form() {
    _validate_form($("#lead_form"), {
        name: "required",
        status: {
            required: {
                depends: function(e) {
                    return !($("[lead-is-junk-or-lost]").length > 0)
                }
            }
        },
        source: "required",
        email: {
            email: !0,
            remote: {
                url: admin_url + "leads/email_exists",
                type: "post",
                data: {
                    email: function() {
                        return $('input[name="email"]').val()
                    },
                    leadid: function() {
                        return $('input[name="leadid"]').val()
                    }
                }
            }
        }
    }, lead_profile_form_handler)
}

function validate_lead_convert_to_client_form() {
    var e = {
        firstname: "required",
        lastname: "required",
        password: {
            required: {
                depends: function(e) {
                    if (!1 === $('input[name="send_set_password_email"]').prop("checked")) return !0
                }
            }
        },
        email: {
            required: !0,
            email: !0,
            remote: {
                url: site_url + "admin/misc/contact_email_exists",
                type: "post",
                data: {
                    email: function() {
                        return $('#lead_to_client_form input[name="email"]').val()
                    },
                    userid: ""
                }
            }
        }
    };
    1 == app_company_is_required && (e.company = "required"), _validate_form($("#lead_to_client_form"), e)
}

function lead_profile_form_handler(e) {
    
    var t=(e=$(e)).serialize();
	// alert(admin_url); return false;
    return $("#lead-modal").find('input[name="leadid"]').val(), $(".lead-save-btn").addClass("disabled"), $.post(e.attr("action"), t).done(function(e) {
        "" !== (e = JSON.parse(e)).message && alert_float("success", e.message), e.proposal_warning && 0 != e.proposal_warning ? ($("body").find("#lead_proposal_warning").removeClass("hide"), $("body").find("#lead-modal").animate({
            scrollTop: 0
        }, 800)) : (lead_profile_attachment(e,e.id),_lead_init_data(e, e.id), $.fn.DataTable.isDataTable(".table-leads") && table_leads.DataTable().ajax.reload(null, !1)
		)		
    }).fail(function(e) {
        return alert_float("danger", e.responseText), !1
    }), !1
}


function lead_profile_attachment(e,id){
	if(typeof(leadProfileDropzone) !== 'undefined'){
	  if (leadProfileDropzone.getQueuedFiles().length > 0) {
		leadProfileDropzone.options.url = admin_url + 'leads/add_lead_profile_attachment/' + id;
		leadProfileDropzone.processQueue();
	  } 
	}
	
}



function update_all_proposal_emails_linked_to_lead(e) {
    $.post(admin_url + "leads/update_all_proposal_emails_linked_to_lead/" + e, {
        update: !0
    }).done(function(t) {
        (t = JSON.parse(t)).success && alert_float("success", t.message), init_lead_modal_data(e)
    })
}

function _lead_init_data(e, t) {
    var a = window.location.hash,
        i = $("#lead-modal");
    if ($("#lead_reminder_modal").html(e.leadView.reminder_data), i.find(".data").html(e.leadView.data), i.modal({
            show: !0,
            backdrop: "static"
        }), init_tags_inputs(), init_selectpicker(), init_form_reminder(), init_datepicker(), init_color_pickers(), validate_lead_form(), "#tab_lead_profile" != a && "#attachments" != a && "#lead_notes" != a && "#lead_activity" != a && "#gdpr" != a || (window.location.hash = a), initDataTableInline($("#consentHistoryTable")), "" !== t && void 0 !== t) {
        "undefined" != typeof Dropbox && document.getElementById("dropbox-chooser-lead").appendChild(Dropbox.createChooseButton({
            success: function(e) {
                $.post(admin_url + "leads/add_external_attachment", {
                    files: e,
                    lead_id: t,
                    external: "dropbox"
                }).done(function() {
                    init_lead_modal_data(t)
                })
            },
            linkType: "preview",
            extensions: app_allowed_files.split(",")
        })), "undefined" != typeof leadAttachmentsDropzone && leadAttachmentsDropzone.destroy(), leadAttachmentsDropzone = new Dropzone("#lead-attachment-upload", $.extend({}, _dropzone_defaults(), {
            sending: function(e, a, i) {
                i.append("id", t), 0 === this.getQueuedFiles().length && i.append("last_file", !0)
            },
            success: function(e, t) {
                t = JSON.parse(t), 0 === this.getUploadingFiles().length && 0 === this.getQueuedFiles().length && _lead_init_data(t, t.id)
            }
        })), i.find('.nav-tabs a[href="' + window.location.hash + '"]').tab("show");
        var n = i.find("#lead_activity .feed-item:last-child .text").html();
        void 0 !== n ? i.find("#lead-latest-activity").html(n) : i.find(".lead-latest-activity > .lead-info-heading").addClass("hide"), $("[lead-is-junk-or-lost]").length > 0 && $(".form-group-select-input-status").find(".req").remove()
    }
}

function init_lead_modal_data(e, t, a) {
    var i = (void 0 !== t ? t : "leads/lead/") + (void 0 !== e ? e : "");
    if (!0 === a) {
        var n = "?";
        i.indexOf("?") > -1 && (n += "&"), i += n + "edit=true"
    }
    requestGetJSON(i).done(function(t) {
        _lead_init_data(t, e)
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function print_lead_information() {
    var e = $("#leadViewWrapper").clone(),
        t = e.find(".lead-name").text().trim();
    e.find("p").css("font-size", "100%").css("font", "inherit").css("vertical-align", "baseline").css("margin", "0px"), e.find("h4").css("font-size", "100%"), e.find(".lead-field-heading").css("color", "#777").css("margin-bottom", "3px"), e.find(".lead-field-heading + p").css("margin-bottom", "15px"), e.find("a[href]").contents().unwrap(), params = "width=" + screen.width, params += ", height=" + screen.height, params += ", top=0, left=0", params += ", fullscreen=yes";
    var a = window.open("", t, params);
    a.document.write("<html><head><title>" + t + "</title>"), a.document.write("<style>"), a.document.write('.clearfix:after { clear: both;}.clearfix:before, .clearfix:after { display: table; content: " ";}body { font-family: Arial, Helvetica, sans-serif;color: #444; font-size:13px;}.bold { font-weight: bold !important;}.lead-information-col { float: left; width: 33.33333333%;}'), a.document.write("</style>"), a.document.write("</head><body>"), a.document.write("<h1>" + t + "</h1>"), a.document.write('<div id="#leadViewWrapper">' + e.html() + "</div>"), a.document.write("</body></html>"), a.document.close(), a.focus(), a.print(), a.close()
}

function leads_kanban_sort(e) {
    kan_ban_sort(e, leads_kanban)
}

function leads_kanban_update(e, t) {
    if (t === e.item.parent()[0]) {
        var a = {};
        a.status = $(e.item.parent()[0]).data("lead-status-id"), a.leadid = $(e.item).data("lead-id");
        var i = [],
            n = $(e.item).parents(".leads-status").find("li"),
            s = 1;
        $.each(n, function() {
            i.push([$(this).data("lead-id"), s]), s++
        }), a.order = i, setTimeout(function() {
            $.post(admin_url + "leads/update_lead_status", a).done(function(e) {
                check_kanban_empty_col("[data-lead-id]")
            })
        }, 200)
    }
}

function init_leads_status_sortable() {
    $("#kan-ban").sortable({
        helper: "clone",
        item: ".kan-ban-col",
        update: function(e, t) {
            var a = [],
                i = $(".kan-ban-col"),
                n = 0;
            $.each(i, function() {
                a.push([$(this).data("col-status-id"), n]), n++
            });
            var s = {};
            s.order = a, $.post(admin_url + "leads/update_status_order", s)
        }
    })
}

function leads_kanban(e) {
    init_kanban("leads/kanban", leads_kanban_update, ".leads-status", 315, 360, init_leads_status_sortable)
}

function delete_lead_attachment(e, t, a) {
    confirm_delete() && requestGetJSON("leads/delete_attachment/" + t + "/" + a).done(function(t) {
        !0 !== t.success && "true" != t.success || $(e).parents(".lead-attachment-wrapper").remove()
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function delete_lead_note(e, t, a) {
    confirm_delete() && requestGetJSON("leads/delete_note/" + t + "/" + a).done(function(t) {
        !0 !== t.success && "true" != t.success || $(e).parents(".lead-note").remove()
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function lead_mark_as_lost(e) {
    requestGetJSON("leads/mark_as_lost/" + e).done(function(t) {
        !0 !== t.success && "true" != t.success || (alert_float("success", t.message), $("body").find("tr#lead_" + e).remove(), $("body").find('#kan-ban li[data-lead-id="' + e + '"]').remove()), _lead_init_data(t, t.id)
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function lead_unmark_as_lost(e) {
    requestGetJSON("leads/unmark_as_lost/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || alert_float("success", e.message), _lead_init_data(e, e.id)
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function lead_mark_as_junk(e) {
    requestGetJSON("leads/mark_as_junk/" + e).done(function(t) {
        !0 !== t.success && "true" != t.success || (alert_float("success", t.message), $("body").find("tr#lead_" + e).remove(), $("body").find('#kan-ban li[data-lead-id="' + e + '"]').remove()), _lead_init_data(t, t.id)
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function lead_mark_as(e, t) {
    var a = {};
    a.status = e, a.leadid = t, $.post(admin_url + "leads/update_lead_status", a).done(function(e) {
        table_leads.DataTable().ajax.reload(null, !1)
    })
}

function lead_unmark_as_junk(e) {
    requestGetJSON("leads/unmark_as_junk/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || alert_float("success", e.message), _lead_init_data(e, e.id)
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function convert_lead_to_customer(e) {
    $("#lead-modal").modal("hide"), requestGet("leads/get_convert_data/" + e).done(function(e) {
        $("#lead_convert_to_customer").html(e), $("#convert_lead_to_client_modal").modal({
            show: !0,
            backdrop: "static",
            keyboard: !1
        })
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function leads_bulk_action(e) {
    if (confirm_delete()) {
        var t = $("#mass_delete").prop("checked"),
            a = [],
            i = {};
        if (0 == t || void 0 === t) {
            if (i.lost = $("#leads_bulk_mark_lost").prop("checked"), i.status = $("#move_to_status_leads_bulk").val(), i.assigned = $("#assign_to_leads_bulk").val(), i.source = $("#move_to_source_leads_bulk").val(), i.last_contact = $("#leads_bulk_last_contact").val(), i.tags = $("#tags_bulk").tagit("assignedTags"), i.visibility = $('input[name="leads_bulk_visibility"]:checked').val(), i.assigned = void 0 === i.assigned ? "" : i.assigned, i.visibility = void 0 === i.visibility ? "" : i.visibility, "" === i.status && !1 === i.lost && "" === i.assigned && "" === i.source && "" === i.last_contact && 0 == i.tags.length && "" === i.visibility) return
        } else i.mass_delete = !0;
        var n = table_leads.find("tbody tr");
        $.each(n, function() {
            var e = $($(this).find("td").eq(0)).find("input");
            !0 === e.prop("checked") && a.push(e.val())
        }), i.ids = a, $(e).addClass("disabled"), setTimeout(function() {
            $.post(admin_url + "leads/bulk_action", i).done(function() {
                window.location.reload()
            }).fail(function(e) {
                $("#lead-modal").modal("hide"), alert_float("danger", e.responseText)
            })
        }, 200)
    }
}

function sync_proposals_data(e, t) {
    var a = {},
        i = $("#sync_data_proposal_data");
    a.country = i.find('select[name="country"]').val(), a.zip = i.find('input[name="zip"]').val(), a.state = i.find('input[name="state"]').val(), a.city = i.find('input[name="city"]').val(), a.address = i.find('textarea[name="address"]').val(), a.phone = i.find('input[name="phone"]').val(), a.rel_id = e, a.rel_type = t, $.post(admin_url + "proposals/sync_data", a).done(function(e) {
        alert_float("success", (e = JSON.parse(e)).message), i.modal("hide")
    })
}

function init_table_announcements(e) {
    if (void 0 === e && $("body").hasClass("dashboard")) return !1;
    initDataTable(".table-announcements", admin_url + "announcements", void 0, void 0, "undefined", [1, "desc"])
}

function init_table_tickets(e) {
    if (void 0 === e && ($("body").hasClass("dashboard") || $("body").hasClass("single-ticket"))) return !1;
    if (0 !== $("body").find(".tickets-table").length) {
        var t = {},
            a = $("._hidden_inputs._filters.tickets_filters input"),
            i = $("table.tickets-table thead .ticket_created_column").index();
        $.each(a, function() {
            t[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]'
        }), t.project_id = '[name="project_id"]';
        var n = [0],
            s = admin_url + "tickets";
        if ($("body").hasClass("tickets-page") && (s += "?bulk_actions=true"), _table_api = initDataTable(".tickets-table", s, n, n, t, [i, "desc"]), _table_api && $("body").hasClass("dashboard")) {
            var o = [4, i, 5, 6];
            for (var l in o) _table_api.column(o[l]).visible(!1, !1);
            _table_api.columns.adjust()
        }
    }
}

function init_table_staff_projects(e) {
    if (void 0 === e && $("body").hasClass("dashboard")) return !1;
    if (0 !== $("body").find(".table-staff-projects").length) {
        var t = {},
            a = $("._hidden_inputs._filters.staff_projects_filter input");
        $.each(a, function() {
            t[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]'
        }), initDataTable(".table-staff-projects", admin_url + "projects/staff_projects", "undefined", "undefined", t, [2, "asc"])
    }
}

function do_task_checklist_items_height(e) {
    void 0 === e && (e = $("body").find("textarea[name='checklist-description']")), $.each(e, function() {
        var e = $(this).val();
        $(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth")) && ($(this).height(0).height(this.scrollHeight), $(this).parents(".checklist").height(this.scrollHeight)), "" === e && ($(this).removeAttr("style"), $(this).parents(".checklist").removeAttr("style"))
    })
}

function recalculate_checklist_items_progress() {
    var e = $('input[name="checklist-box"]:checked').length,
        t = $('input[name="checklist-box"]').length,
        a = 0,
        i = $(".task-progress-bar");
    if (0 == t ? ($("body").find(".chk-heading").remove(), $("#task-no-checklist-items").removeClass("hide")) : $("#task-no-checklist-items").addClass("hide"), !(t > 2)) return i.parents(".progress").addClass("hide"), !1;
    i.parents(".progress").removeClass("hide"), a = 100 * e / t, i.css("width", a.toFixed(2) + "%"), i.text(a.toFixed(2) + "%")
}

function remove_checklist_item_template(e) {
    requestGetJSON("tasks/remove_checklist_item_template/" + e).done(function(t) {
        if (!0 === t.success || "true" == t.success) {
            var a = $("body").find("select.checklist-items-template-select"),
                i = a.find('option[value="' + e + '"]').html().trim(),
                n = $("#task-modal .checklist");
            $.each(n, function(e, t) {
                var a = $(t);
                a.find('textarea[name="checklist-description"]').val().trim() == i && a.find(".save-checklist-template").removeClass("hide")
            }), a.find('option[value="' + e + '"]').remove(), a.selectpicker("refresh"), 1 === a.find("option").length && (a.selectpicker("destroy"), $(".checklist-templates-wrapper").addClass("hide"))
        }
    })
}

function save_checklist_item_template(e, t) {
    var a = $('.checklist[data-checklist-id="' + e + '"] textarea').val();
    $.post(admin_url + "tasks/save_checklist_item_template", {
        description: a
    }).done(function(e) {
        e = JSON.parse(e), $(t).addClass("hide");
        var i = $(".checklist-templates-wrapper");
        i.find('select option[value=""]').after('<option value="' + e.id + '">' + a.trim() + "</option>"), i.removeClass("hide"), i.find("select").selectpicker("refresh")
    })
}

function update_checklist_order() {
    var e = [],
        t = $("body").find(".checklist");
    if (0 !== t.length) {
        var a = 1;
        $.each(t, function() {
            e.push([$(this).data("checklist-id"), a]), a++
        });
        var i = {};
        i.order = e, $.post(admin_url + "tasks/update_checklist_order", i)
    }
}

function add_task_checklist_item(e, t) {
    t = void 0 === t ? "" : t, $.post(admin_url + "tasks/add_checklist_item", {
        taskid: e,
        description: t
    }).done(function() {
        init_tasks_checklist_items(!0, e)
    })
}

function update_task_checklist_item(e) {
    var t = $.Deferred();
    return setTimeout(function() {
        var a = e.val();
        a = a.trim();
        var i = e.parents(".checklist").data("checklist-id");
        $.post(admin_url + "tasks/update_checklist_item", {
            description: a,
            listid: i
        }).done(function(n) {
            t.resolve(), !0 === (n = JSON.parse(n)).can_be_template && e.parents(".checklist").find(".save-checklist-template").removeClass("hide"), "" === a && $("#checklist-items").find('.checklist[data-checklist-id="' + i + '"]').remove()
        })
    }, 300), t.promise()
}

function delete_checklist_item(e, t) {
    requestGetJSON("tasks/delete_checklist_item/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || ($(t).parents(".checklist").remove(), recalculate_checklist_items_progress())
    })
}

function init_tasks_checklist_items(e, t) {
    $.post(admin_url + "tasks/init_checklist_items", {
        taskid: t
    }).done(function(t) {
        if ($("#checklist-items").html(t), void 0 !== e) {
            var a = $("#checklist-items").find(".checklist textarea").eq(0);
            "" === a.val() && a.focus()
        }
        recalculate_checklist_items_progress(), update_checklist_order()
    })
}

function _task_attachments_more_and_less_checks() {
    var e = $("body").find(".task_attachments_wrapper"),
        t = e.find(".task-attachment-col"),
        a = $("body").find("#show-more-less-task-attachments-col .task-attachments-more");
    0 === t.length ? e.remove() : 2 == t.length && a.hasClass("hide") ? $("body").find("#show-more-less-task-attachments-col").remove() : 0 !== $(".task_attachments_wrapper .task-attachment-col:visible").length || a.hasClass("hide") || a.click(), $.each($("#task-modal .comment-content"), function() {
        0 === $(this).find(".task-attachment-col").length && $(this).find(".download-all").remove()
    })
}

function remove_task_attachment(e, t) {
    confirm_delete() && requestGetJSON("tasks/remove_task_attachment/" + t).done(function(e) {
        !0 !== e.success && "true" != e.success || $('[data-task-attachment-id="' + t + '"]').remove(), _task_attachments_more_and_less_checks(), e.comment_removed && $("#comment_" + e.comment_removed).remove()
    })
}

function add_task_comment(e) {
    var t = {};
    taskCommentAttachmentDropzone.files.length > 0 ? taskCommentAttachmentDropzone.processQueue(e) : (tinymce.activeEditor ? t.content = tinyMCE.activeEditor.getContent() : (t.content = $("#task_comment").val(), t.no_editor = !0), t.taskid = e, $.post(admin_url + "tasks/add_task_comment", t).done(function(e) {
        _task_append_html((e = JSON.parse(e)).taskHtml), tinymce.remove("#task_comment")
    }))
}

function remove_task_comment(e) {
    confirm_delete() && requestGetJSON("tasks/remove_comment/" + e).done(function(t) {
        !0 !== t.success && "true" != t.success || ($('[data-commentid="' + e + '"]').remove(), $('[data-comment-attachment="' + e + '"]').remove(), _task_attachments_more_and_less_checks())
    })
}

function remove_assignee(e, t) {
    confirm_delete() && requestGetJSON("tasks/remove_assignee/" + e + "/" + t).done(function(e) {
        !0 !== e.success && "true" != e.success || (alert_float("success", e.message), _task_append_html(e.taskHtml))
    })
}

function is_ios() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream
}

function is_ms_browser() {
    return !(!/MSIE/i.test(navigator.userAgent) && !navigator.userAgent.match(/Trident.*rv\:11\./) && !/Edge/i.test(navigator.userAgent))
}

function remove_follower(e, t) {
    confirm_delete() && requestGetJSON("tasks/remove_follower/" + e + "/" + t).done(function(e) {
        !0 !== e.success && "true" != e.success || (alert_float("success", e.message), _task_append_html(e.taskHtml))
    })
}

function mark_complete(e) {
    task_mark_as(5, e)
}

function unmark_complete(e) {
    task_mark_as(4, e, "tasks/unmark_complete/" + e)
}

function task_mark_as(e, t, a) {
    a = void 0 === a ? "tasks/mark_as/" + e + "/" + t : a;
    var i = $("#task-modal").is(":visible");
    a += "?single_task=" + i, $("body").append('<div class="dt-loader"></div>'), requestGetJSON(a).done(function(a) {
        $("body").find(".dt-loader").remove(), !0 !== a.success && "true" != a.success || (reload_tasks_tables(), i && _task_append_html(a.taskHtml), 5 == e && "function" == typeof _maybe_remove_task_from_project_milestone && _maybe_remove_task_from_project_milestone(t), 0 === $(".tasks-kanban").length && alert_float("success", a.message))
    })
}

function task_change_priority(e, t) {
    url = "tasks/change_priority/" + e + "/" + t;
    var a = $("#task-modal").is(":visible");
    url += "?single_task=" + a, requestGetJSON(url).done(function(e) {
        !0 !== e.success && "true" != e.success || (reload_tasks_tables(), a && _task_append_html(e.taskHtml))
    })
}

function task_change_milestone(e, t) {
    url = "tasks/change_milestone/" + e + "/" + t;
    var a = $("#task-modal").is(":visible");
    url += "?single_task=" + a, requestGetJSON(url).done(function(e) {
        !0 !== e.success && "true" != e.success || (reload_tasks_tables(), a && _task_append_html(e.taskHtml))
    })
}

function delete_user_unfinished_timesheet(e) {
    confirm_delete() && requestGetJSON("tasks/delete_user_unfinished_timesheet/" + e).done(function(e) {
        _init_timers_top_html(JSON.parse(e.timers)), reload_tasks_tables()
    })
}

function reload_tasks_tables() {
    $.each([".table-tasks", ".table-rel-tasks", ".table-rel-tasks-leads", ".table-timesheets"], function(e, t) {
        $.fn.DataTable.isDataTable(t) && $(t).DataTable().ajax.reload(null, !1)
    })
}

function make_task_public(e) {
    requestGetJSON("tasks/make_public/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || (reload_tasks_tables(), _task_append_html(e.taskHtml))
    })
}

function new_task(e) {
    e = void 0 !== e ? e : admin_url + "tasks/task";
    var t = $("#lead-modal");
    t.is(":visible") && (-1 === (e += "&opened_from_lead_id=" + t.find('input[name="leadid"]').val()).indexOf("?") && (e = e.replace("&", "?")), t.modal("hide"));
    var a = $("#task-modal");
    a.is(":visible") && a.modal("hide");
    var i = $("#_task_modal");
    i.is(":visible") && i.modal("hide"), requestGet(e).done(function(e) {
        $("#_task").html(e), $("body").find("#_task_modal").modal({
            show: !0,
            backdrop: "static"
        })
    })
}

function showHideTagsPlaceholder(e) {
    var t = e.data("ui-tagit").tagInput,
        a = e.data("ui-tagit").options.placeholderText;
    e.tagit("assignedTags").length > 0 ? t.removeAttr("placeholder") : t.attr("placeholder", a)
}

function new_task_from_relation(e, t, a) {
    void 0 === t && void 0 === a && (a = $(e).data("new-rel-id"), t = $(e).data("new-rel-type")), new_task(admin_url + "tasks/task?rel_id=" + a + "&rel_type=" + t)
}

function edit_task(e) {
    requestGet("tasks/task/" + e).done(function(e) {
        $("#_task").html(e), $("#task-modal").modal("hide"), $("body").find("#_task_modal").modal({
            show: !0,
            backdrop: "static"
        })
    })
}

function task_form_handler(e) {
    tinymce.triggerSave(), $("#_task_modal").find('input[name="startdate"]').prop("disabled", !1), $("#_task_modal input[type=file]").each(function() {
        "" === $(this).val() && $(this).prop("disabled", !0)
    });
    var t = e.action,
        a = new FormData($(e)[0]);
    return $.ajax({
        type: $(e).attr("method"),
        data: a,
        mimeType: $(e).attr("enctype"),
        contentType: !1,
        cache: !1,
        processData: !1,
        url: t
    }).done(function(e) {
        if (!0 !== (e = JSON.parse(e)).success && "true" != e.success || alert_float("success", e.message), $("body").hasClass("project")) {
            var t = window.location.href,
                a = [];
            t = t.split("?");
            var i = get_url_param("group"),
                n = get_url_param("exclude_completed");
            i && (a.group = i), n && (a.exclude_completed = n), a.taskid = e.id, window.location.href = buildUrl(t[0], a)
        } else $("#_task_modal").attr("data-task-created", !0), $("#_task_modal").modal("hide"), init_task_modal(e.id), reload_tasks_tables()
    }).fail(function(e) {
        alert_float("danger", JSON.parse(e.responseText))
    }), !1
}

function system_popup(e) {
    e.content = void 0 === e.content ? "" : e.content;
    var t = $("<div/>", {
            id: "system-popup",
            class: "system-popup"
        }).appendTo("body"),
        a = "";
    return a += '<div class="popup-wrapper fadeIn animated">', a += '<h2 class="popup-message">', a += e.message, a += "</h2>", a += '<div class="popup-content">', a += e.content, a += '<button type="button" class="system-popup-close"> </button>', a += "</div>", a += "</div>", t.html(a).removeClass("hide"), $("body").addClass("system-popup"), t.find(".system-popup-close").on("click", function() {
        var e = this;
        requestGet("misc/clear_system_popup").done(function(a) {
            setTimeout(function() {
                $("body").removeClass("system-popup"), t.fadeOut(400, function() {
                    t.remove()
                }), $(e).off("click")
            }, 50)
        })
    }), t
}

function timer_action(e, t, a) {
    a = void 0 === a ? "" : a;
    var i = $("#timer-select-task");
    if ("" !== t || !i.is(":visible")) {
        if ("" !== a && "0" == t) {
            var n = {
                content: ""
            };
            return n.content += '<div class="row">', n.content += '<div class="form-group"><select id="timer_add_task_id" data-empty-title="' + appLang.search_tasks + '" data-width="60%" class="ajax-search" data-live-search="true">', n.content += "</select></div>", n.content += '<div class="form-group">', n.content += '<textarea id="timesheet_note" placeholder="' + appLang.note + '" style="margin:0 auto;width:60%;" rows="4" class="form-control"></textarea>', n.content += "</div>", n.content += "<button type='button' onclick='timer_action(this,document.getElementById(\"timer_add_task_id\").value," + a + ");return false;' class='btn btn-info'>" + appLang.confirm + "</button>", n.message = appLang.task_stop_timer, system_popup(n).attr("id", "timer-select-task"), init_ajax_search("tasks", "#timer_add_task_id", void 0, admin_url + "tasks/ajax_search_assign_task_to_timer"), !1
        }
        $(e).addClass("disabled");
        var s = {};
        s.task_id = t, s.timer_id = a, s.note = $("body").find("#timesheet_note").val(), s.note || (s.note = "");
        var o = $("#task-modal").is(":visible");
        $.post(admin_url + "tasks/timer_tracking?single_task=" + o, s).done(function(e) {
            e = JSON.parse(e), o && _task_append_html(e.taskHtml), i.is(":visible") && i.find(".system-popup-close").click(), _init_timers_top_html(JSON.parse(e.timers)), $(".popover-top-timer-note").popover("hide"), reload_tasks_tables()
        })
    }
}

function init_task_modal(e) {
    var t = "",
        a = $("#lead-modal"),
        i = $("#_task_modal");
    a.is(":visible") ? (t += "?opened_from_lead_id=" + a.find('input[name="leadid"]').val(), a.modal("hide")) : null != i.attr("data-lead-id") && (t += "?opened_from_lead_id=" + i.attr("data-lead-id")), requestGet("tasks/get_task_data/" + e + t).done(function(e) {
        _task_append_html(e)
    }).fail(function(e) {
        $("#task-modal").modal("hide"), alert_float("danger", e.responseText)
    })
}

function _task_append_html(e) {
    var t = $("#task-modal");
    t.find(".data").html(e), recalculate_checklist_items_progress(), do_task_checklist_items_height(), setTimeout(function() {
        t.modal("show"), t.is(":visible") && init_tags_inputs(), fix_task_modal_left_col_height()
    }, 150)
}

function task_tracking_stats(e) {
    requestGet("tasks/task_tracking_stats/" + e).done(function(e) {
        $("<div/>", {
            id: "tracking-stats"
        }).appendTo("body").html(e), $("#task-tracking-stats-modal").modal("toggle")
    })
}

function init_timers() {
    requestGetJSON("tasks/get_staff_started_timers").done(function(e) {
        _init_timers_top_html(e)
    })
}

function _init_timers_top_html(e) {
    $("#top-timers");
    var t = $("#top-timers").find(".icon-started-timers");
    e.total_timers > 0 ? t.removeClass("hide").html(e.total_timers) : t.addClass("hide"), $("#started-timers-top").html(e.html)
}

function edit_task_comment(e) {
    var t = $('[data-edit-comment="' + e + '"]');
    if (t.next().addClass("hide"), t.removeClass("hide"), !is_ios()) {
        tinymce.remove("#task_comment_" + e);
        var a = _simple_editor_config();
        a.auto_focus = "task_comment_" + e, init_editor("#task_comment_" + e, a), tinymce.triggerSave()
    }
}

function _tinymce_mobile_toolbar() {
    return ["undo", "redo", "styleselect", "bold", "italic", "link", "image", "bullist", "numlist", "forecolor", "fontsizeselect"]
}

function _simple_editor_config() {
    return {
        height: is_mobile() ? 50 : 100,
        menubar: !1,
        autoresize_bottom_margin: 15,
        plugins: ["table advlist codesample autosave" + (is_mobile() ? " " : " autoresize ") + "lists link image textcolor media contextmenu paste"],
        toolbar: "insert formatselect bold forecolor backcolor" + (is_mobile() ? " | " : " ") + "alignleft aligncenter alignright bullist numlist | restoredraft",
        insert_button_items: "image media link codesample",
        toolbar1: ""
    }
}

function cancel_edit_comment(e) {
    var t = $('[data-edit-comment="' + e + '"]');
    tinymce.remove('[data-edit-comment="' + e + '"] textarea'), t.addClass("hide"), t.next().removeClass("hide")
}

function save_edited_comment(e, t) {
    tinymce.triggerSave();
    var a = {};
    a.id = e, a.task_id = t, a.content = $('[data-edit-comment="' + e + '"]').find("textarea").val(), is_ios() && (a.no_editor = !0), $.post(admin_url + "tasks/edit_comment", a).done(function(t) {
        !0 === (t = JSON.parse(t)).success || "true" == t.success ? (alert_float("success", t.message), _task_append_html(t.taskHtml)) : cancel_edit_comment(e), tinymce.remove('[data-edit-comment="' + e + '"] textarea')
    })
}

function fix_task_modal_left_col_height() {
    is_mobile() || $("body").find(".task-single-col-left").css("min-height", $("body").find(".task-single-col-right").outerHeight(!0) + "px")
}

function tasks_kanban_update(e, t) {
    if (t === e.item.parent()[0]) {
        var a = $(e.item.parent()[0]).data("task-status-id"),
            i = $(e.item.parent()[0]).find("[data-task-id]"),
            n = {
                order: []
            },
            s = 0;
        $.each(i, function() {
            n.order.push([$(this).data("task-id"), s]), s++
        }), task_mark_as(a, $(e.item).data("task-id")), check_kanban_empty_col("[data-task-id]"), setTimeout(function() {
            $.post(admin_url + "tasks/update_order", n)
        }, 200)
    }
}

function tasks_kanban() {
    init_kanban("tasks/kanban", tasks_kanban_update, ".tasks-status", 265, 360)
}

function edit_task_inline_description(e, t) {
    tinyMCE.remove("#task_view_description"), $(e).hasClass("editor-initiated") ? $(e).removeClass("editor-initiated") : ($(e).addClass("editor-initiated"), $.Shortcuts.stop(), tinymce.init({
        selector: "#task_view_description",
        theme: "inlite",
        skin: "perfex",
        auto_focus: "task_view_description",
        plugins: "table link paste contextmenu textpattern",
        insert_toolbar: "quicktable",
        selection_toolbar: "bold italic | quicklink h2 h3 blockquote",
        inline: !0,
        table_default_styles: {
            width: "100%"
        },
        setup: function(e) {
            e.on("blur", function(a) {
                e.isDirty() && $.post(admin_url + "tasks/update_task_description/" + t, {
                    description: e.getContent()
                }), setTimeout(function() {
                    e.remove(), $.Shortcuts.start()
                }, 500)
            })
        }
    }))
}

function tasks_bulk_action(e) {
    if (confirm_delete()) {
        var t = [],
            a = {},
            i = $("#mass_delete").prop("checked");
        if (0 == i || void 0 === i) {
            a.status = $("#move_to_status_tasks_bulk_action").val();
            var n = $("#task_bulk_assignees");
            a.assignees = n.length ? n.selectpicker("val") : "";
            var s = $("#tags_bulk");
            a.tags = s.length ? s.tagit("assignedTags") : "";
            var o = $("#task_bulk_milestone");
            if (a.milestone = o.length ? o.selectpicker("val") : "", a.priority = $("#task_bulk_priority").val(), a.priority = void 0 === a.priority ? "" : a.priority, "" === a.status && "" === a.priority && "" === a.tags && "" === a.assignees && "" === a.milestone) return
        } else a.mass_delete = !0;
        var l = $($("#tasks_bulk_actions").attr("data-table")).find("tbody tr");
        $.each(l, function() {
            var e = $($(this).find("td").eq(0)).find("input");
            !0 === e.prop("checked") && t.push(e.val())
        }), a.ids = t, $(e).addClass("disabled"), setTimeout(function() {
            $.post(admin_url + "tasks/bulk_action", a).done(function() {
                window.location.reload()
            })
        }, 200)
    }
}

function load_small_table_item(e, t, a, i, n) {
    var s = $('input[name="' + a + '"]').val();
    "" === s || window.location.hash ? window.location.hash && !e && (e = window.location.hash.substring(1)) : (e = s, $('input[name="' + a + '"]').val("")), void 0 !== e && "" !== e && ($("body").hasClass("small-table") || toggle_small_view(n, t), $('input[name="' + a + '"]').val(e), do_hash_helper(e), $(t).load(admin_url + i + "/" + e), is_mobile() && $("html, body").animate({
        scrollTop: $(t).offset().top + 150
    }, 600))
}

function init_invoice(e) {
    load_small_table_item(e, "#invoice", "invoiceid", "invoices/get_invoice_data_ajax", ".table-invoices")
}

function init_credit_note(e) {
    load_small_table_item(e, "#credit_note", "credit_note_id", "credit_notes/get_credit_note_data_ajax", ".table-credit-notes")
}

function init_estimate(e) {
    load_small_table_item(e, "#estimate", "estimateid", "estimates/get_estimate_data_ajax", ".table-estimates")
}

function init_proposal(e) {
    load_small_table_item(e, "#proposal", "proposal_id", "proposals/get_proposal_data_ajax", ".table-proposals")
}

function init_expense(e) {
    load_small_table_item(e, "#expense", "expenseid", "expenses/get_expense_data_ajax", ".table-expenses")
}

function clear_billing_and_shipping_details() {
    for (var e in billingAndShippingFields) billingAndShippingFields[e].indexOf("country") > -1 ? $('select[name="' + billingAndShippingFields[e] + '"]').selectpicker("val", "") : ($('input[name="' + billingAndShippingFields[e] + '"]').val(""), $('textarea[name="' + billingAndShippingFields[e] + '"]').val("")), "billing_country" == billingAndShippingFields[e] && ($('input[name="include_shipping"]').prop("checked", !1), $('input[name="include_shipping"]').change());
    init_billing_and_shipping_details()
}

function init_billing_and_shipping_details() {
    var e, t = $('input[name="include_shipping"]').prop("checked");
    for (var a in billingAndShippingFields) e = "", billingAndShippingFields[a].indexOf("country") > -1 ? e = $("#" + billingAndShippingFields[a] + " option:selected").data("subtext") : billingAndShippingFields[a].indexOf("shipping_street") > -1 || billingAndShippingFields[a].indexOf("billing_street") > -1 ? $('textarea[name="' + billingAndShippingFields[a] + '"]').length && (e = $('textarea[name="' + billingAndShippingFields[a] + '"]').val().replace(/(?:\r\n|\r|\n)/g, "<br />")) : e = $('input[name="' + billingAndShippingFields[a] + '"]').val(), billingAndShippingFields[a].indexOf("shipping") > -1 && (t || (e = "")), void 0 === e && (e = ""), e = "" !== e ? e : "--", $("." + billingAndShippingFields[a]).html(e);
    $("#billing_and_shipping_details").modal("hide")
}

function record_payment(e) {
    void 0 !== e && "" !== e && $("#invoice").load(admin_url + "invoices/record_invoice_payment_ajax/" + e)
}

function add_item_to_preview(e) {
    requestGetJSON("invoice_items/get_item_by_id/" + e).done(function(e) {
        clear_item_preview_values(), $('.main textarea[name="description"]').val(e.description), $('.main textarea[name="long_description"]').val(e.long_description.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g, " ")), _set_item_preview_custom_fields_array(e.custom_fields), $('.main input[name="quantity"]').val(1);
        var t = [];
        e.taxname && e.taxrate && t.push(e.taxname + "|" + e.taxrate), e.taxname_2 && e.taxrate_2 && t.push(e.taxname_2 + "|" + e.taxrate_2), $(".main select.tax").selectpicker("val", t), $('.main input[name="unit"]').val(e.unit);
        var a = $("body").find('.accounting-template select[name="currency"]'),
            i = a.attr("data-base"),
            n = a.find("option:selected").val(),
            s = $('.main input[name="rate"]');
        if (i == n) s.val(e.rate);
        else {
            var o = e["rate_currency_" + n];
            o && 0 !== parseFloat(o) ? s.val(o) : s.val(e.rate)
        }
        $(document).trigger({
            type: "item-added-to-preview",
            item: e,
            item_type: "item"
        })
    })
}

function _set_item_preview_custom_fields_array(e) {
    for (var t = ["input", "number", "date_picker", "date_picker_time", "colorpicker"], a = 0; a < e.length; a++) {
        var i = e[a];
        if ($.inArray(i.type, t) > -1) $('tr.main td[data-id="' + i.id + '"] input').val(i.value).trigger("change");
        else if ("textarea" == i.type) $('tr.main td[data-id="' + i.id + '"] textarea').val(i.value);
        else if ("select" == i.type || "multiselect" == i.type) empty(i.value) || (n = (n = i.value.split(",")).map(function(e) {
            return e.trim()
        }), $('tr.main td[data-id="' + i.id + '"] select').selectpicker("val", n));
        else if ("checkbox" == i.type && !empty(i.value)) {
            var n = i.value.split(",");
            n = n.map(function(e) {
                return e.trim()
            }), $.each(n, function(e, t) {
                $('tr.main td[data-id="' + i.id + '"] input[type="checkbox"][value="' + t + '"]').prop("checked", !0)
            })
        }
    }
}

function add_task_to_preview_as_item(e) {
    requestGetJSON("tasks/get_billable_task_data/" + e).done(function(t) {
        t.taxname = $("select.main-tax").selectpicker("val");
        var a = $(".main");
        a.find('textarea[name="description"]').val(t.name), a.find('textarea[name="long_description"]').val(t.description), a.find('input[name="quantity"]').val(t.total_hours), a.find('input[name="rate"]').val(t.hourly_rate), a.find('input[name="unit"]').val(""), $('input[name="task_id"]').val(e), $(document).trigger({
            type: "item-added-to-preview",
            item: t,
            item_type: "task"
        })
    })
}

function clear_item_preview_values(e) {
    var t = $("table.items tbody").find("tr:last-child").find("select").selectpicker("val"),
        a = $(".main");
    a.find("textarea").val(""), a.find('td.custom_field input[type="checkbox"]').prop("checked", !1), a.find("td.custom_field input:not(:checkbox):not(:hidden)").val(""), a.find("td.custom_field select").selectpicker("val", ""), a.find('input[name="quantity"]').val(1), a.find("select.tax").selectpicker("val", t), a.find('input[name="rate"]').val(""), a.find('input[name="unit"]').val(""), $('input[name="task_id"]').val(""), $('input[name="expense_id"]').val("")
}

function add_item_to_table(e, t, a, i) {
    if ("" !== (e = void 0 === e || "undefined" == e ? get_item_preview_values() : e).description || "" !== e.long_description || "" !== e.rate) {
        var n = "",
            s = "",
            o = $("body").find("tbody .item").length + 1;
        n += '<tr class="sortable item" data-merge-invoice="' + a + '" data-bill-expense="' + i + '">', n += '<td class="dragger">', isNaN(e.qty) && (e.qty = 1), ("" === e.rate || isNaN(e.rate)) && (e.rate = 0);
        var l = e.rate * e.qty;
        l = accounting.formatNumber(l);
        var d = "newitems[" + o + "][taxname][]";
        $("body").append('<div class="dt-loader"></div>');
        var r = /<br[^>]*>/gi;
        return get_taxes_dropdown_template(d, e.taxname).done(function(a) {
            n += '<input type="hidden" class="order" name="newitems[' + o + '][order]">', n += "</td>", n += '<td class="bold description"><textarea name="newitems[' + o + '][description]" class="form-control" rows="5">' + e.description + "</textarea></td>", n += '<td><textarea name="newitems[' + o + '][long_description]" class="form-control item_long_description" rows="5">' + e.long_description.replace(r, "\n") + "</textarea></td>";
            var i = $("tr.main td.custom_field"),
                d = !1;
            i.length > 0 && $.each(i, function() {
                var e = $(this).clone(),
                    t = "",
                    a = $(this).find("[data-fieldid]"),
                    i = "newitems[" + o + "][custom_fields][items][" + a.attr("data-fieldid") + "]";
                if (a.is(":checkbox")) {
                    var s = $(this).find('input[type="checkbox"]:checked'),
                        l = e.find('input[type="checkbox"]');
                    $.each(l, function(e, t) {
                        var a = Math.random().toString(20).slice(2);
                        $(this).attr("id", a).attr("name", i).next("label").attr("for", a), "1" == $(this).attr("data-custom-field-required") && (d = !0)
                    }), $.each(s, function(t, a) {
                        e.find('input[value="' + $(a).val() + '"]').attr("checked", !0)
                    }), t = e.html()
                } else if (a.is("input") || a.is("textarea")) a.is("input") ? e.find("[data-fieldid]").attr("value", a.val()) : e.find("[data-fieldid]").html(a.val()), e.find("[data-fieldid]").attr("name", i), "1" == e.find("[data-fieldid]").attr("data-custom-field-required") && (d = !0), t = e.html();
                else if (a.is("select")) {
                    "1" == $(this).attr("data-custom-field-required") && (d = !0);
                    var r = $(this).find("select[data-fieldid]").selectpicker("val");
                    r = (r = new Array(r))[0].constructor === Array ? r[0] : r;
                    var c = e.find("select"),
                        p = $("<div/>");
                    c.attr("name", i);
                    var _ = c.clone();
                    p.append(_), $.each(r, function(e, t) {
                        p.find('select option[value="' + t + '"]').attr("selected", !0)
                    }), t = p.html()
                }
                n += '<td class="custom_field">' + t + "</td>"
            }), n += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + o + '][qty]" value="' + e.qty + '" class="form-control">', s = "", e.unit && void 0 !== e.unit || (s = appLang.unit, e.unit = ""), n += '<input type="text" placeholder="' + s + '" name="newitems[' + o + '][unit]" class="form-control input-transparent text-right" value="' + e.unit + '">', n += "</td>", n += '<td class="rate"><input type="number" data-toggle="tooltip" title="' + appLang.item_field_not_formatted + '" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + o + '][rate]" value="' + e.rate + '" class="form-control"></td>', n += '<td class="taxrate">' + a + "</td>", n += '<td class="amount" align="right">' + l + "</td>", n += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + t + '); return false;"><i class="fa fa-trash"></i></a></td>', n += "</tr>", $("table.items tbody").append(n), $(document).trigger({
                type: "item-added-to-table",
                data: e,
                row: n
            }), setTimeout(function() {
                calculate_total()
            }, 15);
            var c = $('input[name="task_id"]').val(),
                p = $('input[name="expense_id"]').val();
            return "" !== c && void 0 !== c && (billed_tasks = c.split(","), $.each(billed_tasks, function(e, t) {
                $("#billed-tasks").append(hidden_input("billed_tasks[" + o + "][]", t))
            })), "" !== p && void 0 !== p && (billed_expenses = p.split(","), $.each(billed_expenses, function(e, t) {
                $("#billed-expenses").append(hidden_input("billed_expenses[" + o + "][]", t))
            })), $("#item_select").hasClass("ajax-search") && "" !== $("#item_select").selectpicker("val") && $("#item_select").prepend("<option></option>"), init_selectpicker(), init_datepicker(), init_color_pickers(), clear_item_preview_values(), reorder_items(), $("body").find("#items-warning").remove(), $("body").find(".dt-loader").remove(), $("#item_select").selectpicker("val", ""), d && $(".invoice-form").length ? validate_invoice_form() : d && $(".estimate-form").length ? validate_estimate_form() : d && $(".proposal-form").length ? validate_proposal_form() : d && $(".credit-note-form").length && validate_credit_note_form(), !0
        }), !1
    }
}

function get_taxes_dropdown_template(e, t) {
    jQuery.ajaxSetup({
        async: !1
    });
    var a = $.post(admin_url + "misc/get_taxes_dropdown_template/", {
        name: e,
        taxname: t
    });
    return jQuery.ajaxSetup({
        async: !0
    }), a
}

function deselect_ajax_search(e) {
    var t = $("select#" + $(e).attr("data-id"));
    t.data("AjaxBootstrapSelect").list.cache = {};
    var a = t.parents(".bootstrap-select");
    t.html("").append('<option value=""></option>').selectpicker("val", ""), a.removeClass("ajax-remove-values-option").find(".ajax-clear-values").remove(), setTimeout(function() {
        t.trigger("selected.cleared.ajax.bootstrap.select", e), t.trigger("change").data("AjaxBootstrapSelect").list.cache = {}
    }, 50)
}

function init_ajax_project_search_by_customer_id(e) {
    init_ajax_search("project", e = void 0 === e ? "#project_id.ajax-search" : e, {
        customer_id: function() {
            return $('select[name="clientid"]').val()
        }
    })
}

function init_ajax_projects_search(e) {
    init_ajax_search("project", e = void 0 === e ? "#project_id.ajax-search" : e)
}

function fixHelperTableHelperSortable(e, t) {
    return t.children().each(function() {
        $(this).width($(this).width())
    }), t
}

function init_items_sortable(e) {
    var t = $("#wrapper").find(".items tbody");
    0 !== t.length && t.sortable({
        helper: fixHelperTableHelperSortable,
        handle: ".dragger",
        placeholder: "ui-placeholder",
        itemPath: "> tbody",
        itemSelector: "tr.sortable",
        items: "tr.sortable",
        update: function() {
            void 0 === e ? reorder_items() : save_ei_items_order()
        },
        sort: function(e, t) {
            var a = $(e.target);
            if (!/html|body/i.test(a.offsetParent()[0].tagName)) {
                var i = e.pageY - a.offsetParent().offset().top - t.helper.outerHeight(!0) / 2;
                t.helper.css({
                    top: i + "px"
                })
            }
        }
    })
}

function save_ei_items_order() {
    var e, t = $(".table.invoice-items-preview.items tbody tr,.table.estimate-items-preview.items tbody tr,.table.credit-note-items-preview.items tbody tr"),
        a = 1,
        i = [];
    if ($(".table.items").hasClass("invoice-items-preview")) e = "invoice";
    else if ($(".table.items").hasClass("estimate-items-preview")) e = "estimate";
    else {
        if (!$(".table.items").hasClass("credit-note-items-preview")) return !1;
        e = "credit_note"
    }
    $.each(t, function() {
        i.push([$(this).data("item-id"), a]), $(this).find("td.item_no").html(a), a++
    }), setTimeout(function() {
        $.post(admin_url + "misc/update_ei_items_order/" + e, {
            data: i
        })
    }, 200)
}

function reorder_items() {
    var e = $(".table.table-main-invoice-edit tbody tr.item,.table.table-main-estimate-edit tbody tr.item, .table.table-main-credit-note-edit tbody tr.item"),
        t = 1;
    $.each(e, function() {
        $(this).find("input.order").val(t), t++
    })
}

function get_item_preview_values() {
    var e = {};
    return e.description = $('.main textarea[name="description"]').val(), e.long_description = $('.main textarea[name="long_description"]').val(), e.qty = $('.main input[name="quantity"]').val(), e.taxname = $(".main select.tax").selectpicker("val"), e.rate = $('.main input[name="rate"]').val(), e.unit = $('.main input[name="unit"]').val(), e
}

function calculate_total() {
    if ($("body").hasClass("no-calculate-total")) return !1;
    var e, t, a, i, n, s, o = {},excel_amount = {},item_qty = {},tax_rates  = {},
        l = 0,
        d = 0,
        r = 1,
        c = 0,
        p = $(".table.table-main-invoice-edit tbody tr.item,.table.table-main-estimate-edit tbody tr.item,.table.table-main-credit-note-edit tbody tr.item"),
        _ = $("#discount_area"),
        m = $('input[name="adjustment"]').val(),
        u = $('input[name="discount_percent"]').val(),
        f = $('input[name="discount_total"]').val(),
        h = $(".discount-total-type.selected"),
        v = $('select[name="discount_type"]').val(),        
        g = $('input[name="servicecharge"]').val(),
        k = $('input[name="packing_and_forwarding"]').val(),
		total_tax_percentage = 0.0,
		total_calculated_tax = 0.0,
		devide_gst = 'no',		
		tax_summry_area_body = $("#tax_summry_area_body"),
		summry_row_number = 1,
		package_forwording_amount = 0;
		if($('input[name="devide_gst"]').length > 0){ devide_gst  = $('input[name="devide_gst"]').val(); }
		
		$(".tax-area").remove();
		$(".tax-summery-row").remove();
		
		if(typeof(g) != 'undefined' && g != 0 ){
			 total_tax_percentage = total_tax_percentage + service_charges_tax_rate;
			 var service_charge_tax =  g * service_charges_tax_rate / 100;
			 total_calculated_tax = total_calculated_tax + service_charge_tax;
			 
			 
			 tax_summery_row = '<tr class="tax-summery-row"><td>'+summry_row_number+'</td><td>GST@' + service_charges_tax_rate + "%(" + service_charges_tax_rate + '%)</td><td>GST@' + service_charges_tax_rate + "%(" + service_charges_tax_rate + '%)</td><td id="qty_id_service_charges">1</td><td id="excel_amount_id_service_charges">'+g+'</td><td id="tax_id_service_charges">'+total_calculated_tax+'</td></tr>',
			 $(tax_summry_area_body).append(tax_summery_row),
			 summry_row_number += 1;
			 d += total_calculated_tax;		 
			 
		}
		
		$.each(p, function() {
		var row_excel_amount = parseFloat($(this).find("td.rate input").val());		
		
        "" === (r = $(this).find("[data-quantity]").val()) && (r = 1, $(this).find("[data-quantity]").val(1)), n = accounting.toFixed($(this).find("td.rate input").val() * r, app_decimal_places), n = parseFloat(n), $(this).find("td.amount").html(accounting.formatNumber(n)), l += n,i = $(this), (a = $(this).find("select.tax").selectpicker("val")) && $.each(a, function(a, la) {
			 
            t = i.find('select.tax [value="' + la + '"]').data("taxrate");
			e = n / 100 * t;
			//total_calculated_tax += e;
			
			/*** NEw
			if(typeof(k) != 'undefined' && k > 0 ){
				var item_packaging_forwording = ((k/l)*(row_excel_amount)) + row_excel_amount;
				//alert(tax_rates[e]);
				var e = item_packaging_forwording / 100 * t;
				alert(e);
				//$("#tax_id_" + slugify(e)).html(accounting.formatNumber(new_tax));
				//$("#excel_amount_id_" + slugify(e)).html(accounting.formatNumber(item_packaging_forwording));
			}else{
				//$("#tax_id_" + slugify(e)).html(t);
				//$("#excel_amount_id_" + slugify(e)).html(accounting.formatNumber(excel_amount[e]));
				
				e = n / 100 * t;
				
			}
			
			
			total_calculated_tax += e;
			****/
			
			o.hasOwnProperty(la) ?(o[la] = o[la] += e , excel_amount[la] = excel_amount[la]+= row_excel_amount, item_qty[la] = item_qty[la] += parseInt(r)) : 0 != t && (s = la.split("|"),  $(".gst-tax-area").remove(),excel_amount[la] = excel_amount[la]+=row_excel_amount,tax_row = '<tr class="tax-area"><td>' + s[0] + "(" + t + '%)</td><td id="tax_id_' + slugify(la) + '"></td></tr>', tax_summery_row = '<tr class="tax-summery-row"><td>'+summry_row_number+'</td><td>' + s[0] + "(" + t + '%)</td><td>' + s[0] + "(" + t + '%)</td><td id="qty_id_'+ slugify(la) +'"></td><td id="excel_amount_id_'+ slugify(la) +'"></td><td id="tax_id_' + slugify(la) + '"></td></tr>',$(tax_summry_area_body).append(tax_summery_row),summry_row_number += 1,total_tax_percentage += parseFloat(s[1]), o[la] = e,excel_amount[la] = row_excel_amount,item_qty[la] = parseInt(r),tax_rates[la] = t)
        })
    }), "" !== u && 0 != u && "before_tax" == v && h.hasClass("discount-type-percent") ? c = l * u / 100 : "" !== f && 0 != f && "before_tax" == v && h.hasClass("discount-type-fixed") && (c = f), $.each(o, function(e, t) {
		"" !== u && 0 != u && "before_tax" == v && h.hasClass("discount-type-percent") ? (total_tax_calculated = t * u / 100, t -= total_tax_calculated) : "" !== f && 0 != f && "before_tax" == v && h.hasClass("discount-type-fixed") && (t -= t * (f / l * 100) / 100) /*,d += t, t = accounting.formatNumber(t)*/;	

		if(typeof(k) != 'undefined' && k > 0 ){
			var item_packaging_forwording = (((k/l)*(excel_amount[e])) + excel_amount[e]) //* item_qty[e];
			//var item_packaging_forwording = (((k/l)*(excel_amount[e]*item_qty[e])) + excel_amount[e]*item_qty[e]) //* item_qty[e];
			d = d + parseFloat(item_packaging_forwording - excel_amount[e]);
			var new_tax = item_packaging_forwording / 100 * tax_rates[e];
			total_calculated_tax += parseFloat(new_tax);
			$("#tax_id_" + slugify(e)).html(accounting.formatNumber(new_tax));
			$("#excel_amount_id_" + slugify(e)).html(accounting.formatNumber(item_packaging_forwording));
		}else{
			$("#tax_id_" + slugify(e)).html(t);
			$("#excel_amount_id_" + slugify(e)).html(accounting.formatNumber(excel_amount[e]*item_qty[e]));
			total_calculated_tax += parseFloat(t);
		}
		
		$("#qty_id_" + slugify(e)).html(item_qty[e]);
    }),d +=total_calculated_tax, d += l, "" !== u && 0 != u && "after_tax" == v && h.hasClass("discount-type-percent") ? c = d * u / 100 : "" !== f && 0 != f && "after_tax" == v && h.hasClass("discount-type-fixed") && (c = f), d -= c, m = parseFloat(m), isNaN(m) || (d += m),g = parseFloat(g), isNaN(g) || /*(l += g),*/ (d += g), k = parseFloat(k) /*, isNaN(k) || /*(l += k),*//*(d += k)*/;
    var y = "-" + accounting.formatNumber(c);	
	
	if(typeof(total_calculated_tax) != 'undefined' && total_calculated_tax > 0){
		tax_summery_total_row = '<tr class="tax-summery-row"><td colspan="5" style="text-align:right;"><span class="bold">Total</span></td><td>'+accounting.formatNumber(total_calculated_tax)+'</td></tr>',
		$(tax_summry_area_body).append(tax_summery_total_row);
	}
	
	
	if(typeof(devide_gst) != 'undefined' && devide_gst === "yes"){
		 var gst_tax_rate = 0;
		 var gst_tax      = 0;
		 if(total_tax_percentage != 0){
			 gst_tax_rate = total_tax_percentage/2;
		 }
		  
		 if(total_calculated_tax != 0){
			 gst_tax = total_calculated_tax/2;
		 }
		 
		
		 $(".gst-tax-area").remove();
		 $(".tax-area").remove();
		 if(gst_tax_rate != 0){
			 tax_row = '<tr class="gst-tax-area"><td>CGST('+accounting.formatNumber(gst_tax_rate)+'%)</td><td id="tax_id_CGST">'+accounting.formatNumber(gst_tax)+'</td></tr>';
			 tax_row += '<tr class="gst-tax-area"><td>SGST('+accounting.formatNumber(gst_tax_rate)+'%)</td><td id="tax_id_CGST">'+accounting.formatNumber(gst_tax)+'</td></tr>';
			 tax_row += '<tr class="gst-tax-area"><td><span class="bold">Total GST('+total_tax_percentage+'%)</span></td><td id="total_gst">' +accounting.formatNumber(total_calculated_tax) +'</td></tr>';
			 $(discount_area).after(tax_row);
		 }		 
	}else if(total_tax_percentage != 0 && total_calculated_tax !=0){
		 $(".gst-tax-area").remove();
		 $(".tax-area").remove();
		 
		 tax_row = '<tr class="tax-area"><td>IGST('+accounting.formatNumber(total_tax_percentage)+'%)</td><td id="tax_id_IGST">' +accounting.formatNumber(total_calculated_tax) +'</td></tr>';
		 tax_row += '<tr class="tax-area"><td><span class="bold">Total GST('+total_tax_percentage+'%)</span></td><td id="total_gst">' +accounting.formatNumber(total_calculated_tax) +'</td></tr>';
		 $(discount_area).after(tax_row);
	}
	
	
	
    $('input[name="discount_total"]').val(accounting.toFixed(c, app_decimal_places)), $(".discount-total").html(y), $(".adjustment").html(accounting.formatNumber(m)), $(".servicecharge").html(accounting.formatNumber(g)),$(".packing_and_forwarding").html(accounting.formatNumber(k)), $(".subtotal").html(accounting.formatNumber(l) + hidden_input("subtotal", accounting.toFixed(l, app_decimal_places))), $(".total").html(format_money(d) + hidden_input("total", accounting.toFixed(d, app_decimal_places))), $(document).trigger("sales-total-calculated")
}

function delete_item(e, t) {
    $(e).parents("tr").addClass("animated fadeOut", function() {
        setTimeout(function() {
            $(e).parents("tr").remove(), calculate_total()
        }, 50)
    }), $('input[name="isedit"]').length > 0 && $("#removed-items").append(hidden_input("removed_items[]", t))
}

function format_money(e) {
    return "after" === app_currency_placement ? accounting.formatMoney(e, {
        format: "%v %s"
    }) : accounting.formatMoney(e)
}

function init_currency_symbol() {
    var e = $("body").find(".accounting-template");
    e.length && (accounting.settings.currency.symbol = e.find('select[name="currency"] option:selected').data("subtext"), calculate_total())
}

function delete_invoice_attachment(e) {
    confirm_delete() && requestGet("invoices/delete_attachment/" + e).done(function(t) {
        1 == t && ($("body").find('[data-attachment-id="' + e + '"]').remove(), init_invoice($("body").find('input[name="_attachment_sale_id"]').val()))
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function delete_credit_note_attachment(e) {
    confirm_delete() && requestGet("credit_notes/delete_attachment/" + e).done(function(t) {
        1 == t && ($("body").find('[data-attachment-id="' + e + '"]').remove(), init_credit_note($("body").find('input[name="_attachment_sale_id"]').val()))
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function delete_estimate_attachment(e) {
    confirm_delete() && requestGet("estimates/delete_attachment/" + e).done(function(t) {
        if (1 == t) {
            $("body").find('[data-attachment-id="' + e + '"]').remove();
            var a = $("body").find('input[name="_attachment_sale_id"]').val();
            $("body").hasClass("estimates-pipeline") ? estimate_pipeline_open(a) : init_estimate(a)
        }
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function delete_proposal_attachment(e) {
    confirm_delete() && requestGet("proposals/delete_attachment/" + e).done(function(t) {
        if (1 == t) {
            var a = $("body").find('input[name="_attachment_sale_id"]').val();
            $("body").find('[data-attachment-id="' + e + '"]').remove(), $("body").hasClass("proposals-pipeline") ? proposal_pipeline_open(a) : init_proposal(a)
        }
    }).fail(function(e) {
        alert_float("danger", e.responseText)
    })
}

function init_invoices_total(e) {
    if (0 !== $("#invoices_total").length) {
        var t = $(".invoices-total-inline"),
            a = $(".invoices-total");
        if (!$("body").hasClass("invoices-total-manual") || void 0 !== e || a.hasClass("initialized"))
            if (t.length > 0 && a.hasClass("initialized")) t.removeClass("invoices-total-inline");
            else {
                a.addClass("initialized");
                var i = $("body").find('select[name="invoices_total_years"]').selectpicker("val"),
                    n = [];
                $.each(i, function(e, t) {
                    "" !== t && n.push(t)
                });
                var s = {
                        currency: $("body").find('select[name="total_currency"]').val(),
                        years: n,
                        init_total: !0
                    },
                    o = $('input[name="project_id"]').val(),
                    l = $('.customer_profile input[name="userid"]').val();
                void 0 !== o ? s.project_id = o : void 0 !== l && (s.customer_id = l), $.post(admin_url + "invoices/get_invoices_total", s).done(function(e) {
                    $("#invoices_total").html(e)
                })
            }
    }
}

function init_estimates_total(e) {
    if (0 !== $("#estimates_total").length) {
        var t = $(".estimates-total");
        if (!$("body").hasClass("estimates-total-manual") || void 0 !== e || t.hasClass("initialized")) {
            t.addClass("initialized");
            var a = $("body").find('select[name="total_currency"]').val(),
                i = $("body").find('select[name="estimates_total_years"]').selectpicker("val"),
                n = [];
            $.each(i, function(e, t) {
                "" !== t && n.push(t)
            });
            var s = "",
                o = "",
                l = $('.customer_profile input[name="userid"]').val(),
                d = $('input[name="project_id"]').val();
            void 0 !== l ? s = l : void 0 !== d && (o = d), $.post(admin_url + "estimates/get_estimates_total", {
                currency: a,
                init_total: !0,
                years: n,
                customer_id: s,
                project_id: o
            }).done(function(e) {
                $("#estimates_total").html(e)
            })
        }
    }
}

function init_expenses_total() {
    if (0 !== $("#expenses_total").length) {
        var e = $("body").find('select[name="expenses_total_currency"]').val(),
            t = $("body").find('select[name="expenses_total_years"]').selectpicker("val"),
            a = [];
        $.each(t, function(e, t) {
            "" !== t && a.push(t)
        });
        var i = "",
            n = $('.customer_profile input[name="userid"]').val();
        void 0 !== i && (i = n);
        var s = "",
            o = $('input[name="project_id"]').val();
        void 0 !== s && (s = o), $.post(admin_url + "expenses/get_expenses_total", {
            currency: e,
            init_total: !0,
            years: a,
            customer_id: i,
            project_id: s
        }).done(function(e) {
            $("#expenses_total").html(e)
        })
    }
}

function validate_invoice_form(e) {
    e = void 0 === e ? "#invoice-form" : e, _validate_form($(e), {
        clientid: {
            required: {
                depends: function() {
                    return !$("select#clientid").hasClass("customer-removed")
                }
            }
        },
        date: "required",
        currency: "required",
        number: {
            required: !0
        }
    }), $("body").find('input[name="number"]').rules("add", {
        remote: {
            url: admin_url + "invoices/validate_invoice_number",
            type: "post",
            data: {
                number: function() {
                    return $('input[name="number"]').val()
                },
                isedit: function() {
                    return $('input[name="number"]').data("isedit")
                },
                original_number: function() {
                    return $('input[name="number"]').data("original-number")
                },
                date: function() {
                    return $('input[name="date"]').val()
                }
            }
        },
        messages: {
            remote: appLang.invoice_number_exists
        }
    })
}

function validate_credit_note_form(e) {
    e = void 0 === e ? "#credit-note-form" : e, _validate_form($(e), {
        clientid: {
            required: {
                depends: function() {
                    return !$("select#clientid").hasClass("customer-removed")
                }
            }
        },
        date: "required",
        currency: "required",
        number: {
            required: !0
        }
    }), $("body").find('input[name="number"]').rules("add", {
        remote: {
            url: admin_url + "credit_notes/validate_number",
            type: "post",
            data: {
                number: function() {
                    return $('input[name="number"]').val()
                },
                isedit: function() {
                    return $('input[name="number"]').data("isedit")
                },
                original_number: function() {
                    return $('input[name="number"]').data("original-number")
                }
            }
        },
        messages: {
            remote: appLang.credit_note_number_exists
        }
    })
}

function validate_estimate_form(e) {
    e = void 0 === e ? "#estimate-form" : e, _validate_form($(e), {
        clientid: {
            required: {
                depends: function() {
                    return !$("select#clientid").hasClass("customer-removed")
                }
            }
        },
        date: "required",
        currency: "required",
        number: {
            required: !0
        }
    }), $("body").find('input[name="number"]').rules("add", {
        remote: {
            url: admin_url + "estimates/validate_estimate_number",
            type: "post",
            data: {
                number: function() {
                    return $('input[name="number"]').val()
                },
                isedit: function() {
                    return $('input[name="number"]').data("isedit")
                },
                original_number: function() {
                    return $('input[name="number"]').data("original-number")
                },
                date: function() {
                    return $("body").find('.estimate input[name="date"]').val()
                }
            }
        },
        messages: {
            remote: appLang.estimate_number_exists
        }
    })
}

function estimates_pipeline_sort(e) {
    kan_ban_sort(e, estimate_pipeline)
}

function proposal_pipeline_sort(e) {
    kan_ban_sort(e, proposals_pipeline)
}

function estimate_pipeline() {
    init_kanban("estimates/get_pipeline", estimates_pipeline_update, ".pipeline-status", 347, 360)
}

function estimates_pipeline_update(e, t) {
    if (t === e.item.parent()[0]) {
        var a = {};
        a.estimateid = $(e.item).data("estimate-id"), a.status = $(e.item.parent()[0]).data("status-id");
        var i = [],
            n = $(e.item).parents(".pipeline-status").find("li"),
            s = 1;
        $.each(n, function() {
            i.push([$(this).data("estimate-id"), s]), s++
        }), a.order = i, check_kanban_empty_col("[data-estimate-id]"), $.post(admin_url + "estimates/update_pipeline", a)
    }
}

function proposals_pipeline_update(e, t) {
    if (t === e.item.parent()[0]) {
        var a = {
            order: []
        };
        a.proposalid = $(e.item).data("proposal-id"), a.status = $(e.item.parent()[0]).data("status-id");
        var i = $(e.item).parents(".pipeline-status").find("li"),
            n = 1;
        $.each(i, function() {
            a.order.push([$(this).data("proposal-id"), n]), n++
        }), check_kanban_empty_col("[data-proposal-id]"), $.post(admin_url + "proposals/update_pipeline", a)
    }
}

function proposals_pipeline() {
    init_kanban("proposals/get_pipeline", proposals_pipeline_update, ".pipeline-status", 347, 360)
}

function proposal_pipeline_open(e) {
    "" !== e && requestGet("proposals/pipeline_open/" + e).done(function(e) {
        var t = $(".proposal-pipeline-modal:visible").length > 0;
        $("#proposal").html(e), t ? $("#proposal").find(".modal.proposal-pipeline-modal").removeClass("fade").addClass("in").css("display", "block") : $(".proposal-pipeline-modal").modal({
            show: !0,
            backdrop: "static",
            keyboard: !1
        })
    })
}

function estimate_pipeline_open(e) {
    "" !== e && requestGet("estimates/pipeline_open/" + e).done(function(e) {
        var t = $(".estimate-pipeline:visible").length > 0;
        $("#estimate").html(e), t ? $("#estimate").find(".modal.estimate-pipeline").removeClass("fade").addClass("in").css("display", "block") : $(".estimate-pipeline").modal({
            show: !0,
            backdrop: "static",
            keyboard: !1
        })
    })
}

function delete_sales_note(e, t) {
    confirm_delete() && requestGetJSON("misc/delete_note/" + t).done(function(t) {
        if (!0 === t.success || "true" == t.success) {
            $(e).parents(".sales-note-wrapper").remove();
            var a = $("#sales-notes-wrapper"),
                i = a.attr("data-total") - 1,
                n = $(".notes-total");
            a.attr("data-total", i), i <= 0 ? n.addClass("hide") : n.html('<span class="badge">' + i + "</span>")
        }
    })
}

function get_sales_notes(e, t) {
    requestGet(t + "/get_notes/" + e).done(function(e) {
        $("#sales_notes_area").html(e);
        var t = $("#sales-notes-wrapper").attr("data-total");
        t > 0 && $(".notes-total").html('<span class="badge">' + t + "</span>").removeClass("hide")
    })
}

function insert_proposal_merge_field(e) {
    tinymce.activeEditor.execCommand("mceInsertContent", !1, $(e).text())
}

function small_table_full_view() {
    $("#small-table").toggleClass("hide"), $(".small-table-right-col").toggleClass("col-md-12 col-md-7"), $(window).trigger("resize")
}

function save_sales_number_settings(e) {
    var t = {};
    t.prefix = $("body").find('input[name="s_prefix"]').val(), $.post($(e).data("url"), t).done(function(e) {
        (e = JSON.parse(e)).success && e.message && (alert_float("success", e.message), $("#prefix").html(t.prefix))
    })
}

function do_prefix_year(e) {
    var t = _split_formatted_date_by_separator(e);
    void 0 !== t && $.each(t, function(e, a) {
        if (4 == a.length) {
            var i = $("#prefix_year");
            if (i.hasClass("format-n-yy")) a = a.substr(-2);
            else if (i.hasClass("format-mm-yyyy")) {
                var n;
                "d-m-Y" == app_date_format || "d/m/Y" == app_date_format || "Y-m-d" == app_date_format || "d.m.Y" == app_date_format ? n = 1 : "m-d-Y" != app_date_format && "m.d.Y" != app_date_format && "m/d/Y" != app_date_format || (n = 0), $("#prefix_month").html(t[n])
            }
            i.html(a)
        }
    })
}

function unformat_date(e) {
    var t = _split_formatted_date_by_separator(e),
        a = 1,
        i = 0,
        n = 2;
    return "d-m-Y" == app_date_format || "d/m/Y" == app_date_format || "d.m.Y" == app_date_format ? (n = 0, a = 1, i = 2) : "m-d-Y" != app_date_format && "m.d.Y" != app_date_format && "m/d/Y" != app_date_format || (n = 1, a = 0, i = 2), t[i] + "-" + t[a] + "-" + t[n]
}

function _split_formatted_date_by_separator(e) {
    var t;
    return e.indexOf(".") > -1 ? t = e.split(".") : e.indexOf("-") > -1 ? t = e.split("-") : e.indexOf("/") > -1 && (t = e.split("/")), t
}

function init_tabs_scrollable() {
    "true" != isRTL ? ($(window).width() <= 768 && $("body").find(".toggle_view").remove(), $(".horizontal-scrollable-tabs").horizontalTabs()) : ($(".arrow-left, .arrow-right").css("display", "none"), $(".horizontal-scrollable-tabs").removeClass("horizontal-scrollable-tabs"), $(".nav-tabs-horizontal").removeClass("nav-tabs-horizontal"))
}

function view_contact_consent(e) {
    requestGet("clients/consents/" + e).done(function(e) {
        $("#consent_data").html(e), initDataTableInline($("#consentHistoryTable")), $("#consentModal").modal("show")
    })
}

function view_lead_consent(e) {
    window.location.hash = "gdpr", init_lead(e)
}

function set_notification_read_inline(e) {
    requestGet("misc/set_notification_read_inline/" + e).done(function() {
        var t = $("body").find('.notification-wrapper[data-notification-id="' + e + '"]');
        t.find(".notification-box,.notification-box-all").removeClass("unread"), t.find(".not-mark-as-read-inline").tooltip("destroy").remove()
    })
}

function mark_all_notifications_as_read_inline() {
    requestGet("misc/mark_all_notifications_as_read_inline/").done(function() {
        var e = $("body").find(".notification-wrapper");
        e.find(".notification-box,.notification-box-all").removeClass("unread"), e.find(".not-mark-as-read-inline").tooltip("destroy").remove()
    })
}

function delete_sale_activity(e) {
    confirm_delete() && requestGet("misc/delete_sale_activity/" + e).done(function() {
        $("body").find('[data-sale-activity-id="' + e + '"]').remove()
    })
}

function view_event(e) {
    void 0 !== e && $.post(admin_url + "utilities/view_event/" + e).done(function(e) {
        $("#event").html(e), $("#viewEvent").modal("show"), init_datepicker(), init_selectpicker(), validate_calendar_form()
    })
}

function delete_event(e) {
    confirm_delete() && requestGetJSON("utilities/delete_event/" + e).done(function(e) {
        !0 !== e.success && "true" != e.success || window.location.reload()
    })
}

function validate_calendar_form() {
    _validate_form($("body").find("._event form"), {
        title: "required",
        start: "required",
        reminder_before: "required"
    }, calendar_form_handler), _validate_form($("body").find("#viewEvent form"), {
        title: "required",
        start: "required",
        reminder_before: "required"
    }, calendar_form_handler)
}

function calendar_form_handler(e) {
    return $.post(e.action, $(e).serialize()).done(function(e) {
        !0 !== (e = JSON.parse(e)).success && "true" != e.success || (alert_float("success", e.message), setTimeout(function() {
            var e = window.location.href;
            e = e.split("?"), window.location.href = e[0]
        }, 500))
    }), !1
}

function confirm_delete() {
    return 0 != confirm(appLang.confirm_action_prompt)
}

function add_hotkey(e, t) {
    $.Shortcuts.add({
        type: "down",
        mask: e,
        handler: t
    })
}

function fetch_notifications(e) {
    requestGetJSON("misc/notifications_check").done(function(e) {
        var t = notifications_wrapper;
        t.html(e.html);
        var a = t.find("ul.notifications").attr("data-total-unread");
        document.title = a > 0 ? "(" + a + ") " + doc_initial_title : doc_initial_title;
        var i = e.notificationsIds;
        if ("firefox" == app_user_browser && i.length > 1) {
            var n = i[0];
            (i = []).push(n)
        }
        setTimeout(function() {
            i.length > 0 && $.each(i, function(e, a) {
                var i = 'li[data-notification-id="' + a + '"]',
                    n = t.find(i);
                $.notify("", {
                    title: appLang.new_notification,
                    body: n.find(".notification-title").text(),
                    requireInteraction: !0,
                    icon: n.find(".notification-image").attr("src"),
                    tag: a,
                    closeTime: "0" != app_dismiss_desktop_not_after ? 1e3 * app_dismiss_desktop_not_after : null
                }).close(function() {
                    requestGet("misc/set_desktop_notification_read/" + a).done(function(e) {
                        var i = t.find(".icon-total-indicator");
                        t.find('li[data-notification-id="' + a + '"] .notification-box').removeClass("unread");
                        var n = i.text();
                        n = n.trim(), (n -= 1) > 0 ? (document.title = "(" + n + ") " + doc_initial_title, i.html(n)) : (document.title = doc_initial_title, i.addClass("hide"))
                    })
                }).click(function(e) {
                    parent.focus(), window.focus(), setTimeout(function() {
                        t.find(i + " .notification-link").addClass("desktopClick").click(), e.target.close()
                    }, 70)
                })
            })
        }, 10)
    })
}

function init_new_task_comment() {
    tinymce.editors.task_comment && tinymce.remove("#task_comment"), "undefined" != typeof taskCommentAttachmentDropzone && taskCommentAttachmentDropzone.destroy(), $("#dropzoneTaskComment").removeClass("hide"), $("#addTaskCommentBtn").removeClass("hide"), taskCommentAttachmentDropzone = new Dropzone("#task-comment-form", $.extend({}, _dropzone_defaults(), {
        uploadMultiple: !0,
        clickable: "#dropzoneTaskComment",
        previewsContainer: ".dropzone-task-comment-previews",
        autoProcessQueue: !1,
        addRemoveLinks: !0,
        parallelUploads: 20,
        maxFiles: 20,
        paramName: "file",
        sending: function(e, t, a) {
            a.append("taskid", $("#addTaskCommentBtn").attr("data-comment-task-id")), tinyMCE.activeEditor ? a.append("content", tinyMCE.activeEditor.getContent()) : a.append("content", $("#task_comment").val())
        },
        success: function(e, t) {
            t = JSON.parse(t), 0 === this.getUploadingFiles().length && 0 === this.getQueuedFiles().length && (_task_append_html(t.taskHtml), tinymce.remove("#task_comment"))
        }
    }));
    var e = _simple_editor_config();
    e.auto_focus = !0, is_ios() || init_editor("#task_comment", e)
}

function init_ajax_search(e, t, a, i) {
    var n = $("body").find(t);
    if (n.length) {
        var s = {
            ajax: {
                url: void 0 === i ? admin_url + "misc/get_relation_data" : i,
                data: function() {
                    var t = {};
                    return t.type = e, t.rel_id = "", t.q = "{{{q}}}", void 0 !== a && jQuery.extend(t, a), t
                }
            },
            locale: {
                emptyTitle: appLang.search_ajax_empty,
                statusInitialized: appLang.search_ajax_initialized,
                statusSearching: appLang.search_ajax_searching,
                statusNoResults: appLang.not_results_found,
                searchPlaceholder: appLang.search_ajax_placeholder,
                currentlySelected: appLang.currently_selected
            },
            requestDelay: 500,
            cache: !1,
            preprocessData: function(e) {
                for (var t = [], a = e.length, i = 0; i < a; i++) {
                    var n = {
                        value: e[i].id,
                        text: e[i].name
                    };
                    e[i].subtext && (n.data = {
                        subtext: e[i].subtext
                    }), t.push(n)
                }
                return t
            },
            preserveSelectedPosition: "after",
            preserveSelected: !0
        };
        n.data("empty-title") && (s.locale.emptyTitle = n.data("empty-title")), n.selectpicker().ajaxSelectPicker(s)
    }
}

function merge_field_format_url(e, t, a, i) {
    return e.indexOf("{") > -1 && e.indexOf("}") > -1 && (e = "{" + e.split("{")[1]), e
}

function _dropzone_defaults() {
    return {
        createImageThumbnails: !0,
        dictDefaultMessage: appLang.drop_files_here_to_upload,
        dictFallbackMessage: appLang.browser_not_support_drag_and_drop,
        dictFileTooBig: appLang.file_exceeds_maxfile_size_in_form,
        dictCancelUpload: appLang.cancel_upload,
        dictRemoveFile: appLang.remove_file,
        dictMaxFilesExceeded: appLang.you_can_not_upload_any_more_files,
        maxFilesize: (max_php_ini_upload_size_bytes / 1048576).toFixed(0),
        acceptedFiles: app_allowed_files,
        error: function(e, t) {
            alert_float("danger", t)
        },
        complete: function(e) {
            this.files.length && this.removeFile(e)
        }
    }
}

function onChartClickRedirect(e, t, a) {
    void 0 === a && (a = "statusLink");
    var i = t.getElementAtEvent(e)[0];
    if (i) {
        var n = t.data.datasets[0][a][i._index];
        n && (window.location.href = n)
    }
}

function slugify(e) {
    return e.toString().trim().toLowerCase().replace(/\s+/g, "-").replace(/[^\w\-]+/g, "").replace(/\-\-+/g, "-").replace(/^-+/, "").replace(/-+$/, "")
}

function requestGet(e, t) {
    t = void 0 === t ? {} : t;
    var a = {
        type: "GET",
        url: e.indexOf(admin_url) > -1 ? e : admin_url + e
    };
    return $.ajax($.extend({}, a, t))
}

function requestGetJSON(e, t) {
    return (t = void 0 === t ? {} : t).dataType = "json", requestGet(e, t)
}
$(window).on("load", function() {
    init_btn_with_tooltips()
}), $.fn.dataTable.ext.errMode = "throw", $.fn.dataTableExt.oStdClasses.sWrapper = "dataTables_wrapper form-inline dt-bootstrap table-loading", Dropzone.options.newsFeedDropzone = !1, Dropzone.options.salesUpload = !1, "Notification" in window && "1" == app_desktop_notifications && Notification.requestPermission(), $.validator.setDefaults({
    highlight: function(e) {
        var t = $(e).parents(".tab-pane");
        t.length && !t.is(":visible") && $('a[href="#' + t.attr("id") + '"]').css("border-bottom", "1px solid red").css("color", "red").addClass("tab-validation"), setTimeout(function() {
            $(e).closest(".form-group").addClass("has-error")
        }, 200)
    },
    unhighlight: function(e) {
        var t = (e = $(e)).parents(".tab-pane");
        t.length && $('a[href="#' + t.attr("id") + '"]').removeAttr("style").removeClass("tab-validation"), e.closest(".form-group").removeClass("has-error")
    },
    errorElement: "p",
    errorClass: "text-danger",
    errorPlacement: function(e, t) {
        t.parent(".input-group").length || t.parents(".chk").length ? t.parents(".chk").length ? e.insertAfter(t.parents(".chk")) : e.insertAfter(t.parent()) : t.is("select") && (t.hasClass("selectpicker") || t.hasClass("ajax-search")) ? e.insertAfter(t.parents(".form-group *").last()) : e.insertAfter(t)
    }
}), $.validator.addMethod("filesize", function(e, t, a) {
    return this.optional(t) || t.files[0].size <= a
}, appLang.file_exceeds_max_filesize), $.validator.addMethod("extension", function(e, t, a) {
    return a = "string" == typeof a ? a.replace(/,/g, "|") : "png|jpe?g|gif", this.optional(t) || e.match(new RegExp("\\.(" + a + ")$", "i"))
}, $.validator.format(appLang.validation_extension_not_allowed));
var original_top_search_val, table_leads, table_activity_log, table_estimates, table_invoices, table_tasks, menu_href_selector, tab_active = get_url_param("tab"),
    tab_group = get_url_param("group"),
    side_bar = $("#side-menu"),
    content_wrapper = $("#wrapper"),
    setup_menu = $("#setup-menu-wrapper"),
    calendar_selector = $("#calendar"),
    notifications_wrapper = $("#header").find("li.notifications-wrapper"),
    doc_initial_title = document.title,
    newsfeed_posts_page = 0,
    track_load_post_likes = 0,
    track_load_comment_likes = 0,
    post_likes_total_pages = 0,
    comment_likes_total_pages = 0,
    select_picker_validated_event = !1,
    postid = 0,
    setup_menu_item = $("#setup-menu-item");
$("body").on("loaded.bs.select change", "select.ajax-search", function(e) {
    if ($(this).selectpicker("val") && !$(this).is(":disabled")) {
        var t = $(this).parents(".bootstrap-select.ajax-search");
        if (0 === t.find(".ajax-clear-values").length) {
            var a = $(this).attr("id");
            t.addClass("ajax-remove-values-option").find("button.dropdown-toggle").after('<span class="pointer ajax-clear-values" onclick="deselect_ajax_search(this); return false;" data-id="' + a + '"><i class="fa fa-remove"></i></span>')
        }
    }
}), $("body").on("rendered.bs.select", "select", function() {
    $(this).parents().removeClass("select-placeholder"), $(this).parents(".form-group").find(".select-placeholder").removeClass("select-placeholder")
}), $("body").on("loaded.bs.select", "._select_input_group", function(e) {
    $(this).parents(".form-group").find(".input-group-select .input-group-addon").css("opacity", "1")
}), $(window).on("load resize", function(e) {
    $("body").hasClass("page-small") || set_body_small(), setTimeout(function() {
        mainWrapperHeightFix()
    }, "load" == e.type ? 150 : 0)
}), $(function() {
    totalUnreadNotifications > 0 && (document.title = "(" + totalUnreadNotifications + ") " + doc_initial_title), $(".screen-options-btn").on("click", function() {
        $(".screen-options-area").slideToggle()
    }), add_hotkey("Shift+C", function() {
        var e = $("#lead-modal"),
            t = $("#task-modal");
        if (e.is(":visible")) convert_lead_to_customer(e.find('input[name="leadid"]').val());
        else if (t.is(":visible")) {
            var a = t.find(".tasks-comments");
            a.is(":visible") || a.css("display", "block"), init_new_task_comment()
        } else window.location.href = admin_url + "clients/client"
    }), add_hotkey("Shift+I", function() {
        window.location.href = admin_url + "invoices/invoice"
    }), add_hotkey("Shift+E", function() {
        var e = $("#lead-modal"),
            t = $("#task-modal");
        e.is(":visible") || t.is(":visible") ? e.is(":visible") ? $("a[lead-edit]").click() : t.is(":visible") && edit_task(t.find("[data-task-single-id]").attr("data-task-single-id")) : window.location.href = admin_url + "estimates/estimate"
    }), add_hotkey("Shift+F", function() {
        var e = $("#task-modal");
        if (e.is(":visible")) {
            var t = e.find("[data-task-single-id]");
            5 != t.attr("data-status") && mark_complete(t.attr("data-task-single-id"))
        }
    }), add_hotkey("Ctrl+Shift+P", function() {
        window.location.href = admin_url + "proposals/proposal"
    }), add_hotkey("Ctrl+Shift+E", function() {
        window.location.href = admin_url + "expenses/expense"
    }), add_hotkey("Shift+L", function() {
        init_lead()
    }), add_hotkey("Shift+T", function() {
        var e = $(".new-task-relation");
        e.length > 0 ? new_task(admin_url + "tasks/task?rel_id=" + e.attr("data-rel-id") + "&rel_type=" + e.attr("data-rel-type")) : $("body").hasClass("project") ? new_task(admin_url + "tasks/task?rel_id=" + project_id + "&rel_type=project") : new_task()
    }), add_hotkey("Shift+P", function() {
        window.location.href = admin_url + "projects/project"
    }), add_hotkey("Shift+S", function() {
        window.location.href = admin_url + "tickets/add"
    }), add_hotkey("Ctrl+Shift+S", function() {
        window.location.href = admin_url + "staff/member"
    }), add_hotkey("Ctrl+Shift+L", function() {
        logout()
    }), add_hotkey("Alt+D", function() {
        window.location.href = admin_url
    }), add_hotkey("Alt+C", function() {
        window.location.href = admin_url + "clients"
    }), add_hotkey("Alt+T", function() {
        window.location.href = admin_url + "tasks/list_tasks"
    }), add_hotkey("Alt+I", function() {
        window.location.href = admin_url + "invoices/list_invoices"
    }), add_hotkey("Alt+E", function() {
        window.location.href = admin_url + "estimates/list_estimates"
    }), add_hotkey("Alt+P", function() {
        window.location.href = admin_url + "projects"
    }), add_hotkey("Alt+L", function() {
        window.location.href = admin_url + "leads"
    }), add_hotkey("Ctrl+Alt+T", function() {
        window.location.href = admin_url + "tickets"
    }), add_hotkey("Ctrl+Alt+E", function() {
        window.location.href = admin_url + "expenses/list_expenses"
    }), add_hotkey("Alt+R", function() {
        window.location.href = admin_url + "reports/sales"
    }), add_hotkey("Alt+S", function() {
        window.location.href = admin_url + "settings"
    }), add_hotkey("Shift+K", function() {
        $("#search_input").focus()
    }), add_hotkey("Shift+D", function() {
        $("body .dataTables_wrapper").eq(0).find(".dataTables_filter input").focus()
    }), $.Shortcuts.start(), $(document).on("focusin", function(e) {
        $(e.target).closest(".mce-window").length && e.stopImmediatePropagation()
    }), 1 != app_show_setup_menu_item_only_on_hover || is_mobile() || side_bar.hover(function() {
        setTimeout(function() {
            setup_menu_item.css("display", "block")
        }, 200)
    }, function() {
        setTimeout(function() {
            setup_menu_item.css("display", "none")
        }, 1e3)
    });
    var e = $("body").find("ul.nav-tabs");
    tab_active && e.find('[href="#' + tab_active + '"]').click(), tab_group && (e.find("li").not('[role="presentation"]').removeClass("active"), e.find('[data-group="' + tab_group + '"]').parents("li").addClass("active")), moment.locale(locale), moment().tz(app_timezone).format(), jQuery.datetimepicker.setLocale(locale), init_editor(), $("body").on("click", "#started-timers-top,.popover-top-timer-note", function(e) {
        e.stopPropagation()
    }), init_tags_inputs(), init_color_pickers(), initDataTableInline(), $("body").on("change", ".onoffswitch input", function(e, t) {
        $(this).data("switch-url") && switch_field(this)
    }), custom_fields_hyperlink(), init_lightbox(), init_progress_bars(), init_datepicker(), $(document).on("app.form-validate", function(e, t) {
        if (!0 === select_picker_validated_event) return !0;
        select_picker_validated_event = !0, $(t).on("change", "select.ajax-search, select.selectpicker", function(e) {
            if ($(this).selectpicker("val") && !$(this).is(":disabled") && void 0 !== $(this).rules() && 1 === Object.keys($(this).rules()).length && $(this).rules().hasOwnProperty("required")) {
                var t = $(this).parents(".form-group");
                t.find("#" + $(this).attr("name") + "-error").remove(), t.removeClass("has-error")
            }
        })
    }), init_selectpicker(), set_body_small(), init_form_reminder(), init_ajax_search("customer", "#clientid.ajax-search");
    var t = side_bar.find('li > a[href="' + location + '"]');
    if (t.length && (t.parents("li").not(".quick-links").addClass("active"), t.prop("aria-expanded", !0), t.parents("ul.nav-second-level").prop("aria-expanded", !0), t.parents("li").find("a:first-child").prop("aria-expanded", !0)), setup_menu.hasClass("display-block")) {
        var a = setup_menu.find('li > a[href="' + location + '"]');
        a.length && (a.parents("li").addClass("active"), a.prev("active"), a.parents("ul.nav-second-level").prop("aria-expanded", !0), a.parents("li").find("a:first-child").prop("aria-expanded", !0))
    }
    side_bar.metisMenu(), setup_menu.metisMenu(), $(".hide-menu").click(function(e) {
        e.preventDefault(), $("body").hasClass("hide-sidebar") ? $("body").removeClass("hide-sidebar").addClass("show-sidebar") : $("body").removeClass("show-sidebar").addClass("hide-sidebar"), setup_menu.hasClass("display-block") && $(".close-customizer").click()
    }), is_mobile() && content_wrapper.on("click", function() {
        $("body").hasClass("show-sidebar") && $(".hide-menu").click(), setup_menu.hasClass("display-block") && $(".close-customizer").click()
    }), "safari" == app_user_browser && $("body").on("input", ".bootstrap-select .bs-searchbox input", function() {
        $(this).trigger("keyup")
    }), mainWrapperHeightFix(), init_tabs_scrollable(), $("#top-timers").on("click", function() {
        init_timers()
    }), $("#search_input").on("keyup paste" + ("safari" == app_user_browser ? " input" : ""), function() {
        var e = $(this).val().trim(),
            t = $("#search_results"),
            a = $("#top_search_button button");
        if ("" === e) return content_wrapper.unhighlight(), t.html(""), original_top_search_val = "", void a.html('<i class="fa fa-search"></i>').removeClass("search_remove");
        e.length < 2 && -1 === app_language.indexOf("chinese") && -1 === app_language.indexOf("japanese") || (a.html('<i class="fa fa-remove"></i>').addClass("search_remove"), delay(function() {
            e != original_top_search_val && $.post(admin_url + "misc/search", {
                q: e
            }).done(function(a) {
                content_wrapper.unhighlight(), t.html(a), content_wrapper.highlight(e), original_top_search_val = e
            })
        }, 700))
    });
    var i = get_url_param("q");
    if (i && $("#search_input").val(i).trigger("keyup"), $("body").on("blur", "#timesheet_duration", function() {
            var e = $(this),
                t = $(this).val();
            if ((t = t.replace(/[^0-9:]/gi, "")).indexOf(":") > -1) {
                var a = t.split(":");
                if (0 === a[0].length && (a[0] = "00"), a[1] >= 60) {
                    var i = Math.floor(parseInt(a[1] / 60));
                    a[0] = i + parseInt(a[0]), a[1] = a[1] - 60 * i
                }
                1 === a[0].toString().length && (a[0] = "0" + a[0]), 1 === a[1].toString().length ? a[1] = "0" + a[1] : 0 === a[1].toString().length && (a[1] = "00"), t = a[0] + ":" + a[1]
            } else 1 === t.length && -1 === t.indexOf(":") ? t = "0" + t + ":00" : t.length >= 2 && -1 === t.indexOf(":") && (t += ":00");
            t = "00:00" == t ? "" : t, e.val(t)
        }), $("body").on("click", ".timesheet-toggle-enter-type", function(e) {
            e.preventDefault();
            var t = $(this).find("span.switch-to").removeClass("switch-to").addClass("hide");
            $(this).find("span").not(t).removeClass("hide").addClass("switch-to"), $(".timesheet-start-end-time, .timesheet-duration").toggleClass("hide"), $(".timesheet-start-end-time input").val(""), $(".timesheet-duration input").val("")
        }), $("body").on("hidden.bs.modal", ".modal-reminder", function(e) {
            var t = $(this),
                a = t.find('input[name="rel_id"]').val(),
                i = t.find('input[name="rel_type"]').val();
            t.find("form").attr("action", admin_url + "misc/add_reminder/" + a + "/" + i), t.find(":input:not([type=hidden]), textarea").val(""), t.find('input[type="checkbox"]').prop("checked", !1), t.find("select").selectpicker("val", "")
        }), $("body").on("click", ".delete-reminder", function() {
            return confirm_delete() && requestGetJSON($(this).attr("href")).done(function(e) {
                alert_float(e.alert_type, e.message), reload_reminders_tables()
            }), !1
        }), $("body").on("keypress", 'textarea[name="checklist-description"]', function(e) {
            if ("13" == e.which) {
                var t = $(this);
                return update_task_checklist_item(t).done(function() {
                    add_task_checklist_item(t.attr("data-taskid"))
                }), !1
            }
        }), $("body").on("blur paste", 'textarea[name="checklist-description"]', function() {
            update_task_checklist_item($(this))
        }), $("body").on("show.bs.select", "select.checklist-items-template-select", _make_task_checklist_items_deletable), $("body").on("refreshed.bs.select", "select.checklist-items-template-select", _make_task_checklist_items_deletable), $("body").on("changed.bs.select", "select.custom-field-multi-select", function(e) {
            var t = $(this).val();
            $(this).find('option[value=""]').prop("selected", 0 === t.length), $(this).selectpicker("refresh")
        }), $("body").on("change", ".task-single-inline-field", function() {
            var e = $("body").find(".task-single-inline-field"),
                t = {};
            $.each(e, function() {
                var e = $(this).attr("name"),
                    a = $(this).val(),
                    i = $(this).parents(".task-single-inline-wrap");
                "startdate" == e && "" === a ? i.addClass("text-danger") : "startdate" == e && "" !== a && i.removeClass("text-danger"), ("startdate" == e && "" !== a || "startdate" != e) && (t[$(this).attr("name")] = a, "startdate" != e && "" === a ? i.css("opacity", .5) : i.css("opacity", 1))
            });
            var a = $("#task-modal").find("[data-task-single-id]").attr("data-task-single-id");
            $.post(admin_url + "tasks/task_single_inline_update/" + a, t)
        }), $("body").on("change", "#task-modal #checklist_items_templates", function() {
            var e = $(this).val(),
                t = $(this).find('option[value="' + e + '"]').html().trim();
            "" !== t && (add_task_checklist_item($("#task-modal").find("[data-task-single-id]").attr("data-task-single-id"), t), $(this).selectpicker("val", ""))
        }), $("body").on("click", ".task-date-as-comment-id", function(e) {
            e.preventDefault();
            var t = $(this).attr("href").split("#"),
                a = $("#" + t[t.length - 1]).position();
            $("#task-modal").scrollTop(a.top)
        }), $("body").on("click", "table.dataTable tbody .tags-labels .label-tag", function() {
            $(this).parents("table").DataTable().search($(this).find(".tag").text()).draw(), $("div.dataTables_filter input").focus()
        }), $("body").on("click", "table.dataTable tbody .customer-group-list", function() {
            $(this).parents("table").DataTable().search($(this).text()).draw(), $("div.dataTables_filter input").focus()
        }), $("[data-can-view-own], [data-can-view]").on("change", function() {
            var e = $(this).attr("data-can-view-own");
            view_chk_selector = $(this).parents("tr").find("td input[" + (void 0 !== e && !1 !== e ? "data-can-view" : "data-can-view-own") + "]"), view_chk_selector.prop("checked", !1), view_chk_selector.prop("disabled", !0 === $(this).prop("checked"))
        }), "undefined" != typeof taskid && "" !== taskid && init_task_modal(taskid), $("body").on("change", 'input[name="checklist-box"]', function() {
            requestGet(admin_url + "tasks/checkbox_action/" + $(this).parents(".checklist").data("checklist-id") + "/" + (!0 === $(this).prop("checked") ? 1 : 0)), recalculate_checklist_items_progress()
        }), $("body").on("keyup paste click", "textarea[name='checklist-description']", function(e) {
            do_task_checklist_items_height($(this))
        }), $("body").on("click focus", "#task_comment", function(e) {
            init_new_task_comment()
        }), $("body").on("click", ".task-single-delete-timesheet", function(e) {
            if (e.preventDefault(), confirm_delete()) {
                var t = $(this).data("task-id");
                requestGet($(this).attr("href")).done(function(e) {
                    init_task_modal(t), setTimeout(function() {
                        reload_tasks_tables(), init_timers()
                    }, 20)
                })
            }
        }), $("body").on("click", ".task-single-add-timesheet", function(e) {
            e.preventDefault();
            var t = $("body").find('#task-modal input[name="timesheet_start_time"]').val(),
                a = $("body").find('#task-modal input[name="timesheet_end_time"]').val(),
                i = $("body").find('#task-modal input[name="timesheet_duration"]').val();
            if ("" !== t && "" !== a || "" !== i) {
                var n = {};
                n.timesheet_duration = i, n.start_time = t, n.end_time = a, n.timesheet_task_id = $(this).data("task-id"), n.note = $("body").find("#task_single_timesheet_note").val(), n.timesheet_staff_id = $("body").find('#task-modal select[name="single_timesheet_staff_id"]').val(), $.post(admin_url + "tasks/log_time", n).done(function(e) {
                    !0 === (e = JSON.parse(e)).success || "true" == e.success ? (init_task_modal(n.timesheet_task_id), alert_float("success", e.message), setTimeout(function() {
                        reload_tasks_tables()
                    }, 20)) : alert_float("warning", e.message)
                })
            }
        }), $("body").on("click", ".copy_task_action", function() {
            var e = {};
            return $(this).prop("disabled", !0), e.copy_from = $(this).data("task-copy-from"), e.copy_task_assignees = $("body").find("#copy_task_assignees").prop("checked"), e.copy_task_followers = $("body").find("#copy_task_followers").prop("checked"), e.copy_task_checklist_items = $("body").find("#copy_task_checklist_items").prop("checked"), e.copy_task_attachments = $("body").find("#copy_task_attachments").prop("checked"), e.copy_task_status = $("body").find('input[name="copy_task_status"]:checked').val(), $.post(admin_url + "tasks/copy", e).done(function(e) {
                if (!0 === (e = JSON.parse(e)).success || "true" == e.success) {
                    var t = $("#_task_modal");
                    t.is(":visible") && t.modal("hide"), init_task_modal(e.new_task_id), reload_tasks_tables()
                }
                alert_float(e.alert_type, e.message)
            }), !1
        }), $("body").on("click", ".new-task-to-milestone", function(e) {
            e.preventDefault();
            var t = $(this).parents(".milestone-column").data("col-status-id");
            new_task(admin_url + "tasks/task?rel_type=project&rel_id=" + project_id + "&milestone_id=" + t), $('body [data-toggle="popover"]').popover("hide")
        }), $("body").on("shown.bs.modal", "#_task_modal", function(e) {
            $(e.currentTarget).hasClass("edit") ? "" !== $(this).find(".tinymce-task").val().trim() && init_editor(".tinymce-task", {
                height: 200
            }) : $("body").find("#_task_modal #name").focus(), init_tags_inputs()
        }), $("body").on("hidden.bs.modal", "#_task_modal", function() {
            tinyMCE.remove(".tinymce-task"), "undefined" != typeof _ticket_message && (_ticket_message = void 0), null == $(this).attr("data-lead-id") || $(this).attr("data-task-created") || init_lead($(this).attr("data-lead-id")), $("body #_task_modal .datepicker").datetimepicker("destroy"), $("#_task").empty()
        }), $("body").on("hide.bs.modal", "#task-modal", function() {
            if (1 == $("#lightbox").is(":visible")) return !1;
            "undefined" != typeof taskAttachmentDropzone && taskAttachmentDropzone.destroy(), tinyMCE.remove("#task_view_description")
        }), $("body").on("hidden.bs.modal", "#task-modal", function() {
            $(this).find(".data").empty()
        }), $("body").on("shown.bs.modal", "#task-modal", function() {
            do_task_checklist_items_height(), init_tags_inputs(), fix_task_modal_left_col_height(), $(document).off("focusin.modal");
            var e = window.location.href;
            if (e.indexOf("#comment_") > -1) {
                var t = e.split("#comment_");
                t = t[t.length - 1], $('[data-task-comment-href-id="' + t + '"]').click()
            }
        }), $("body").on("blur", "#task-modal ul.tagit li.tagit-new input", function() {
            setTimeout(function() {
                task_single_update_tags()
            }, 100)
        }), $("body").on("change", 'select[name="select-assignees"]', function() {
            $("body").append('<div class="dt-loader"></div>');
            var e = {};
            e.assignee = $('select[name="select-assignees"]').val(), "" !== e.assignee && (e.taskid = $(this).attr("data-task-id"), $.post(admin_url + "tasks/add_task_assignees", e).done(function(e) {
                $("body").find(".dt-loader").remove(), e = JSON.parse(e), reload_tasks_tables(), _task_append_html(e.taskHtml)
            }))
        }), $("body").on("change", 'select[name="select-followers"]', function() {
            var e = {};
            e.follower = $('select[name="select-followers"]').val(), "" !== e.follower && (e.taskid = $(this).attr("data-task-id"), $("body").append('<div class="dt-loader"></div>'), $.post(admin_url + "tasks/add_task_followers", e).done(function(e) {
                e = JSON.parse(e), $("body").find(".dt-loader").remove(), _task_append_html(e.taskHtml)
            }))
        }), $("body").on("click", ".close-task-stats", function() {
            $("#task-tracking-stats-modal").modal("hide")
        }), $("body").on("hidden.bs.modal", "#task-tracking-stats-modal", function() {
            $("#tracking-stats").remove()
        }), $("body").on("show.bs.modal", "#task-tracking-stats-modal", function() {
            var e = $("body").find("#task-tracking-stats-chart");
            setTimeout(function() {
                "undefined" != typeof taskTrackingChart && taskTrackingChart.destroy(), taskTrackingChart = new Chart(e, {
                    type: "line",
                    data: taskTrackingStatsData,
                    options: {
                        legend: {
                            display: !1
                        },
                        responsive: !0,
                        maintainAspectRatio: !1,
                        tooltips: {
                            enabled: !0,
                            mode: "single",
                            callbacks: {
                                label: function(e, t) {
                                    return decimalToHM(e.yLabel)
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: !0,
                                    min: 0,
                                    userCallback: function(e, t, a) {
                                        return decimalToHM(e)
                                    }
                                }
                            }]
                        }
                    }
                })
            }, 800)
        }), $("body").on("shown.bs.modal", "#sync_data_proposal_data", function() {
            "lead" == $("#sync_data_proposal_data").data("rel-type") && $("#lead-modal .data").eq(0).css("height", $("#sync_data_proposal_data .modal-content").height() + 80 + "px").css("overflow-x", "hidden")
        }), $("body").on("hidden.bs.modal", "#sync_data_proposal_data", function() {
            "lead" == $("#sync_data_proposal_data").data("rel-type") && $("#lead-modal .data").prop("style", "")
        }), "undefined" != typeof openLeadID && "" !== openLeadID && init_lead(openLeadID, !!get_url_param("edit")), $("body").on("click", ".leads-kan-ban .cpicker", function() {
            var e = $(this).data("color"),
                t = $(this).parents(".panel-heading-bg").data("status-id");
            $.post(admin_url + "leads/change_status_color", {
                color: e,
                status_id: t
            })
        }), $("body").on("click", "[lead-edit]", function(e) {
            e.preventDefault();
            var t = $("body .lead-edit");
            if ($("body .lead-view").toggleClass("hide"), t.toggleClass("hide"), !t.hasClass("hide")) {
                var a = $("#lead-modal").find("#address"),
                    i = a[0].scrollHeight;
                a.is("textarea") && (a.height(0).height(i - 15), a.css("padding-top", "9px"))
            }
        }), $("body").on("click", ".new-lead-from-status", function(e) {
            e.preventDefault();
            var t = $(this).parents(".kan-ban-col").data("col-status-id");
            init_lead_modal_data(void 0, admin_url + "leads/lead?status_id=" + t), $('body [data-toggle="popover"]').popover("hide")
        }), $("body").on("change", "input.include_leads_custom_fields", function() {
            var e = $(this).val(),
                t = $(this).data("field-id");
            2 == e ? $("#merge_db_field_" + t).removeClass("hide") : $("#merge_db_field_" + t).addClass("hide"), 3 == e ? $("#merge_db_contact_field_" + t) : $("#merge_db_contact_field_" + t).addClass("hide")
        }), calendar_selector.length > 0) {
        validate_calendar_form();
        var n = {
            themeSystem: "bootstrap3",
            customButtons: {},
            header: {
                left: "prev,next today",
                center: "title",
                right: "month,agendaWeek,agendaDay,viewFullCalendar,calendarFilter"
            },
            editable: !1,
            eventLimit: parseInt(app_calendar_events_limit) + 1,
            views: {
                day: {
                    eventLimit: !1
                }
            },
            defaultView: app_default_view_calendar,
            isRTL: "true" == isRTL,
            eventStartEditable: !1,
            timezone: app_timezone,
            firstDay: parseInt(app_calendar_first_day),
            year: moment.tz(app_timezone).format("YYYY"),
            month: moment.tz(app_timezone).format("M"),
            date: moment.tz(app_timezone).format("DD"),
            loading: function(e, t) {
                e && $("#calendar .fc-header-toolbar .btn-default").addClass("btn-info").removeClass("btn-default").css("display", "block"), e ? $(".dt-loader").removeClass("hide") : $(".dt-loader").addClass("hide")
            },
            eventSources: [{
                url: admin_url + "utilities/get_calendar_data",
                data: function() {
                    var e = {};
                    return $("#calendar_filters").find("input:checkbox:checked").map(function() {
                        e[$(this).attr("name")] = !0
                    }).get(), jQuery.isEmptyObject(e) || (e.calendar_filters = !0), e
                },
                type: "POST",
                error: function() {
                    console.error("There was error fetching calendar data")
                }
            }],
            eventLimitClick: function(e, t) {
                $("#calendar").fullCalendar("gotoDate", e.date), $("#calendar").fullCalendar("changeView", "basicDay")
            },
            eventRender: function(e, t) {
                t.attr("title", e._tooltip), t.attr("onclick", e.onclick), t.attr("data-toggle", "tooltip"), e.url || t.click(function() {
                    view_event(e.eventid)
                })
            },
            dayClick: function(e, t, a) {
                var i = e.format();
                $.fullCalendar.moment(i).hasTime() || (i += " 00:00");
                var n = 24 == app_time_format ? app_date_format + " H:i" : app_date_format + " g:i A",
                    s = (new DateFormatter).formatDate(new Date(i), n);
                return $("input[name='start'].datetimepicker").val(s), $("#newEventModal").modal("show"), !1
            }
        };
        if ($("body").hasClass("dashboard") && (n.customButtons.viewFullCalendar = {
                text: appLang.calendar_expand,
                click: function() {
                    window.location.href = admin_url + "utilities/calendar"
                }
            }), n.customButtons.calendarFilter = {
                text: appLang.filter_by.toLowerCase(),
                click: function() {
                    slideToggle("#calendar_filters")
                }
            }, 1 == is_staff_member && ("" !== google_api && (n.googleCalendarApiKey = google_api), "" !== calendarIDs && (calendarIDs = JSON.parse(calendarIDs), 0 != calendarIDs.length)))
            if ("" !== google_api)
                for (var s = 0; s < calendarIDs.length; s++) {
                    var o = {};
                    o.googleCalendarId = calendarIDs[s], n.eventSources.push(o)
                } else console.error("You have setup Google Calendar IDs but you dont have specified Google API key. To setup Google API key navigate to Setup->Settings->Misc");
        calendar_selector.fullCalendar(n), get_url_param("new_event") && ($("input[name='start'].datetimepicker").val(get_url_param("date")), $("#newEventModal").modal("show"))
    }
    $("body").on("change", 'select[name="tax"]', function() {
        var e = $("body").find('select[name="tax2"]'),
            t = $(this);
        "" !== t.val() ? e.prop("disabled", !1) : (e.prop("disabled", !0), "" !== e.val() && (t.val(e.val()), e.val(""), t.selectpicker("refresh"))), e.selectpicker("refresh")
    }), $("body").on("click", "#invoice_create_credit_note", function(e) {
        if (2 == $(this).attr("data-status")) return !0;
        var t = $("#confirm_credit_note_create_from_invoice");
        t.modal("show"), t.find("#confirm-invoice-credit-note").attr("href", $(this).attr("href")), e.preventDefault()
    }), $("body").on("change blur", ".apply-credits-to-invoice .apply-credits-field", function() {
        var e = $("#apply_credits"),
            t = e.find("input.apply-credits-field"),
            a = 0,
            i = e.attr("data-credits-remaining");
        $.each(t, function() {
            if (!0 === $(this).valid()) {
                var e = $(this).val();
                e = parseFloat(e), isNaN(e) ? $(this).val(0) : a += e
            }
        }), e.find("#credits-alert").remove(), e.find(".amount-to-credit").html(accounting.formatNumber(a)), i < a ? ($(".credits-table").before($("<div/>", {
            id: "credits-alert",
            class: "alert alert-danger"
        }).html(appLang.credit_amount_bigger_then_credit_note_remaining_credits)), e.find('[type="submit"]').prop("disabled", !0)) : (e.find(".credit-note-balance-due").html(accounting.formatNumber(i - a)), e.find('[type="submit"]').prop("disabled", !1))
    }), $("body").on("change blur", ".apply-credits-from-invoice .apply-credits-field", function() {
        var e = $("#apply_credits"),
            t = e.find("input.apply-credits-field"),
            a = 0,
            i = e.attr("data-balance-due");
        $.each(t, function() {
            if (!0 === $(this).valid()) {
                var e = $(this).val();
                e = parseFloat(e), isNaN(e) ? $(this).val(0) : a += e
            }
        }), e.find("#credits-alert").remove(), e.find(".amount-to-credit").html(accounting.formatNumber(a)), a > i ? ($(".credits-table").before($("<div/>", {
            id: "credits-alert",
            class: "alert alert-danger"
        }).html(appLang.credit_amount_bigger_then_invoice_balance)), e.find('[type="submit"]').prop("disabled", !0)) : (e.find(".invoice-balance-due").html(accounting.formatNumber(i - a)), e.find('[type="submit"]').prop("disabled", !1))
    }), $('input[name="notify_type"]').on("change", function() {
        var e = $('input[name="notify_type"]:checked').val(),
            t = $("#specific_staff_notify"),
            a = $("#role_notify");
        "specific_staff" == e ? (t.removeClass("hide"), a.addClass("hide")) : "roles" == e ? (t.addClass("hide"), a.removeClass("hide")) : "assigned" == e && (t.addClass("hide"), a.addClass("hide"))
    }), $("body").on("shown.bs.modal", "#lead-modal", function(e) {
        custom_fields_hyperlink(), 0 === $("body").find('#lead-modal input[name="leadid"]').length && $("body").find('#lead-modal input[name="name"]').focus(), init_tabs_scrollable(), $("body").find(".lead-wrapper").hasClass("open-edit") && $("body").find("a[lead-edit]").click()
    }), $("body").on("show.bs.modal", "#lead-modal", function(e) {
        0 == $("#lead-more-dropdown").find("li").length && $("#lead-more-btn").css("opacity", 0).css("pointer-events", "none")
    }), $("#lead-modal").on("hidden.bs.modal", function(e) {
        $(this).data("bs.modal", null), $("#lead_reminder_modal").html(""), $("#lead-modal").is(":visible") || history.pushState("", document.title, window.location.pathname + window.location.search), $("body #lead-modal .datetimepicker").datetimepicker("destroy"), "undefined" != typeof leadAttachmentsDropzone && leadAttachmentsDropzone.destroy()
    });
    var l = 1;
    $("body").on("click", ".add_more_attachments", function() {
        if ($(this).hasClass("disabled")) return !1;
        var e = $('.attachments input[name*="attachments"]').length;
        if ($(this).data("ticket") && e >= app_maximum_allowed_ticket_attachments) return !1;
        var t = $(".attachments").find(".attachment").eq(0).clone().appendTo(".attachments");
        t.find("input").removeAttr("aria-describedby aria-invalid"), t.find(".has-error").removeClass("has-error"), t.find("input").attr("name", "attachments[" + l + "]").val(""), t.find('p[id*="error"]').remove(), t.find("i").removeClass("fa-plus").addClass("fa-minus"), t.find("button").removeClass("add_more_attachments").addClass("remove_attachment").removeClass("btn-success").addClass("btn-danger"), l++
    }), $("body").on("click", ".remove_attachment", function() {
        $(this).parents(".attachment").remove()
    }), $("body").on("hidden.bs.modal", "#convert_lead_to_client_modal", function(e) {
        $(this).data("bs.modal", null)
    }), $("body").on("submit", "#lead-modal .consent-form", function() {
        var e = $(this).serialize();
        return $.post($(this).attr("action"), e).done(function(e) {
            init_lead_modal_data((e = JSON.parse(e)).lead_id)
        }), !1
    }), $("body").on("click", '#lead-modal a[data-toggle="tab"]', function() {
        "#tab_lead_profile" == this.hash || "#attachments" == this.hash || "#lead_notes" == this.hash || "#gdpr" == this.hash || "#lead_activity" == this.hash ? window.location.hash = this.hash : history.pushState("", document.title, window.location.pathname + window.location.search), $(document).resize()
    }), $("body").on("click", "#lead_enter_activity", function() {
        var e = $("#lead_activity_textarea").val(),
            t = $("#lead-modal").find('input[name="leadid"]').val();
        "" !== e && $.post(admin_url + "leads/add_activity", {
            leadid: t,
            activity: e
        }).done(function(e) {
            _lead_init_data(e = JSON.parse(e), e.id)
        }).fail(function(e) {
            alert_float("danger", e.responseText)
        })
    }), $("body").on("submit", "#lead-modal #lead-notes", function() {
        var e = $(this),
            t = $(e).serialize();
        return $.post(e.attr("action"), t).done(function(e) {
            _lead_init_data(e = JSON.parse(e), e.id)
        }).fail(function(e) {
            alert_float("danger", e.responseText)
        }), !1
    });
    var d = {
        custom_view: "[name='custom_view']",
        assigned: "[name='view_assigned']",
        status: "[name='view_status[]']",
        source: "[name='view_source']"
    };
    if ((table_leads = $("table.table-leads")).length) {
        var r = table_leads.find("#th-consent"),
            c = [0],
            p = [0, table_leads.find("#th-assigned").index()];
        r.length > 0 && (c.push(r.index()), p.push(r.index())), _table_api = initDataTable(table_leads, admin_url + "leads/table", p, c, d, [table_leads.find("th.date-created").index(), "desc"]), _table_api && r.length > 0 && _table_api.on("draw", function() {
            var e = table_leads.find("tbody tr");
            $.each(e, function() {
                $(this).find("td:eq(3)").addClass("bg-light-gray")
            })
        }), $.each(d, function(e, t) {
            $("select" + t).on("change", function() {
                "lost" == $(this).val() || "junk" == $(this).val() ? $("[name='view_status[]']").prop("disabled", !0).selectpicker("refresh") : $("[name='view_status[]']").prop("disabled", !1).selectpicker("refresh"), table_leads.DataTable().ajax.reload().columns.adjust().responsive.recalc()
            })
        })
    }
    if ($("body").on("change", 'input[name="contacted_today"]', function() {
            var e = $(this).prop("checked"),
                t = $(".lead-select-date-contacted");
            0 == e ? t.removeClass("hide") : t.addClass("hide")
        }), $("body").on("change", 'input[name="contacted_indicator"]', function() {
            var e = $(".lead-select-date-contacted");
            "yes" == $(this).val() ? e.removeClass("hide") : e.addClass("hide")
        }), $("body").on("click", "table.dataTable tbody td:first-child", function() {
            var e = $(this).parents("tr");
            if ($(this).parents("table").DataTable().row(e).child.isShown()) {
                var t = $(e).next().find("input.onoffswitch-checkbox");
                if (t.length > 0) {
                    var a = Math.random().toString(16).slice(2);
                    t.attr("id", a).next().attr("for", a)
                }
            }
        }), $("body").on("click", "[data-loading-text]", function() {
            var e = $(this).data("form");
            null != e ? $(e).valid() && $(this).button("loading") : $(this).button("loading")
        }), $("body").on("click", ".close-reminder-modal", function() {
            $(".reminder-modal-" + $(this).data("rel-type") + "-" + $(this).data("rel-id")).modal("hide")
        }), $("body").on("shown.bs.tab", 'a[data-toggle="tab"]', function(e) {
            $($.fn.dataTable.tables(!0)).DataTable().responsive.recalc()
        }), $("form").not("#single-ticket-form,#calendar-event-form,#proposal-form").areYouSure(), $("body").on("click", ".editor-add-content-notice", function() {
            var e = $(this);
            setTimeout(function() {
                e.remove(), tinymce.triggerSave()
            }, 500)
        }), $(".bulk_actions").on("change", 'input[name="mass_delete"]', function() {
            var e = $("#bulk_change");
            !0 === $(this).prop("checked") && e.find("select").selectpicker("val", ""), e.toggleClass("hide"), $(".mass_delete_separator").toggleClass("hide")
        }), $(".send-test-sms").on("click", function() {
            var e = $(this).data("id"),
                t = $('#sms_test_response[data-id="' + e + '"]'),
                a = $('textarea[data-id="' + e + '"]').val(),
                i = $('input.test-phone[data-id="' + e + '"]').val(),
                n = $(this);
            t.empty(), "" != (a = a.trim()) && "" != i && (n.prop("disabled", !0), $.post(window.location.href, {
                message: a,
                number: i,
                id: e,
                sms_gateway_test: !0
            }).done(function(e) {
                1 == (e = JSON.parse(e)).success ? t.html('<div class="alert alert-success no-mbot mtop15">SMS Sent Successfully!</div>') : t.html('<div class="alert alert-warning no-mbot mtop15">' + e.error + "</div>")
            }).always(function() {
                n.prop("disabled", !1)
            }))
        }), $("body").on("hidden.bs.modal", "#__todo", function() {
            var e = $("#__todo");
            e.find('input[name="todoid"]').val(""), e.find('textarea[name="description"]').val(""), e.find(".add-title").addClass("hide"), e.find(".edit-title").addClass("hide")
        }), $("body").on("shown.bs.modal", "#__todo", function() {
            var e = $("#__todo");
            e.find('textarea[name="description"]').focus(), "" !== e.find('input[name="todoid"]').val() ? (e.find(".add-title").addClass("hide"), e.find(".edit-title").removeClass("hide")) : (e.find(".add-title").removeClass("hide"), e.find(".edit-title").addClass("hide"))
        }), $("#top_search_button button").on("click", function() {
            var e = $("#search_input");
            e.focus(), $(this).hasClass("search_remove") && ($(this).html('<i class="fa fa-search"></i>').removeClass("search_remove"), original_top_search_val = "", $("#search_results").html(""), e.val(""))
        }), $("body").click(function(e) {
            $(e.target).parents("#top_search_dropdown").hasClass("search-results") || $("#top_search_dropdown").remove()
        }), $("body").tooltip({
            selector: '[data-toggle="tooltip"]'
        }), $("body").popover({
            selector: '[data-toggle="popover"]'
        }), $("body").on("click", function() {
            $(".tooltip").remove()
        }), $("body").on("click", function(e) {
            $('[data-toggle="popover"],.manual-popover').each(function() {
                $(this).is(e.target) || 0 !== $(this).has(e.target).length || 0 !== $(".popover").has(e.target).length || $(this).popover("hide")
            })
        }), $("body").on("click", "._filter_data ul.dropdown-menu li a,.not-mark-as-read-inline,.not_mark_all_as_read a", function(e) {
            e.stopPropagation(), e.preventDefault()
        }), $("body").on("click", "._delete", function(e) {
            return !!confirm_delete()
        }), $("body").on("shown.bs.modal", ".modal", function() {
            $("body").addClass("modal-open"), $("body").find("#started-timers-top").parents("li").removeClass("open")
        }), $("body").on("hidden.bs.modal", ".modal", function(e) {
            $(".modal:visible").length && $(document.body).addClass("modal-open"), $(this).data("bs.modal", null)
        }), $(".datepicker.activity-log-date").on("change", function() {
            table_activity_log.DataTable().ajax.reload()
        }), $("body").on("change", "#unlimited_cycles", function() {
            $(this).parents(".recurring-cycles").find("#cycles").prop("disabled", $(this).prop("checked"))
        }), $("body").on("change", '[name="repeat_every"], [name="recurring"]', function() {
            var e = $(this).val();
            "custom" == e ? $(".recurring_custom").removeClass("hide") : $(".recurring_custom").addClass("hide"), "" !== e && 0 != e ? $("body").find("#cycles_wrapper").removeClass("hide") : ($("body").find("#cycles_wrapper").addClass("hide"), $("body").find("#cycles_wrapper #cycles").val(0), $("#unlimited_cycles").prop("checked", !0).change())
        }), $('select[name="range"]').on("change", function() {
            var e = $(".period");
            "period" == $(this).val() ? e.removeClass("hide") : (e.addClass("hide"), e.find("input").val(""))
        }), $("body").on("change", "#mass_select_all", function() {
            var e, t, a;
            e = $(this).data("to-table"), t = $(".table-" + e).find("tbody tr"), a = $(this).prop("checked"), $.each(t, function() {
                $($(this).find("td").eq(0)).find("input").prop("checked", a)
            })
        }), $("body").on("show.bs.modal", ".modal.email-template", function() {
            init_editor($(this).data("editor-id"), {
                urlconverter_callback: "merge_field_format_url"
            })
        }), $("body").on("hidden.bs.modal", ".modal.email-template", function() {
            tinymce.remove($(this).data("editor-id"))
        }), $(".close-customizer").on("click", function(e) {
            e.preventDefault(), setup_menu.addClass("true" == isRTL ? "fadeOutRight" : "fadeOutLeft"), requestGet("misc/set_setup_menu_closed")
        }), $(".open-customizer").on("click", function(e) {
            e.preventDefault(), setup_menu.hasClass("true" == isRTL ? "fadeOutRight" : "fadeOutLeft") && setup_menu.removeClass("true" == isRTL ? "fadeOutRight" : "fadeOutLeft"), setup_menu.addClass("display-block " + ("true" == isRTL ? "fadeInRight" : "fadeInLeft")), is_mobile() || requestGet("misc/set_setup_menu_open"), mainWrapperHeightFix()
        }), $("body").on("click", ".cpicker", function() {
            var e = $(this).data("color");
            if ($(this).hasClass("cpicker-big")) return !1;
            $(this).parents(".cpicker-wrapper").find(".cpicker-big").removeClass("cpicker-big").addClass("cpicker-small"), $(this).removeClass("cpicker-small", "fast").addClass("cpicker-big", "fast"), $(this).hasClass("kanban-cpicker") ? ($(this).parents(".panel-heading-bg").css("background", e), $(this).parents(".panel-heading-bg").css("border", "1px solid " + e)) : $(this).hasClass("calendar-cpicker") && $("body").find('._event input[name="color"]').val(e)
        }), $("body").on("click", ".notification_link", function() {
            var e = $(this).data("link");
            e.split("#")[1] || (window.location.href = e)
        }), $("body").on("click", ".notifications a.notification-top, .notification_link", function(e) {
            e.preventDefault();
            var t, a, i = $(this),
                n = !0;
            (t = (t = i.hasClass("notification_link") ? i.data("link") : e.currentTarget.href).split("#"))[1] && t[1].indexOf("=") > -1 && (n = !1, a = t[1].split("=")[1], t[1].indexOf("postid") > -1 ? (postid = a, $(".open_newsfeed").click()) : t[1].indexOf("taskid") > -1 ? init_task_modal(a) : t[1].indexOf("leadid") > -1 ? init_lead(a) : t[1].indexOf("eventid") > -1 && view_event(a)), i.hasClass("desktopClick") || i.parent("li").find(".not-mark-as-read-inline").click(), n && setTimeout(function() {
                window.location.href = t
            }, 50)
        }), $(".notifications-wrapper").on("show.bs.dropdown", function() {
            notifications_wrapper.find(".notifications").attr("data-total-unread") > 0 && $.post(admin_url + "misc/set_notifications_read").done(function(e) {
                !0 !== (e = JSON.parse(e)).success && "true" != e.success || (document.title = doc_initial_title, $(".icon-notifications").addClass("hide"))
            })
        }), init_table_tickets(), init_table_announcements(), init_table_staff_projects(), (table_activity_log = $("table.table-activity-log")).length) {
        var _ = [];
        _.activity_log_date = '[name="activity_log_date"]', initDataTable(table_activity_log, window.location.href, "undefined", "undefined", _, [1, "desc"])
    }
    if (table_invoices = $("table.table-invoices"), table_estimates = $("table.table-estimates"), table_invoices.length > 0 || table_estimates.length > 0) {
        var m = {},
            u = $("._hidden_inputs._filters input");
        $.each(u, function() {
            m[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]'
        }), table_invoices.length && initDataTable(table_invoices, admin_url + "invoices/table" + ($("body").hasClass("recurring") ? "?recurring=1" : ""), "undefined", "undefined", m, [
            [3, "desc"],
            [0, "desc"]
        ]), table_estimates.length && initDataTable(table_estimates, admin_url + "estimates/table", "undefined", "undefined", m, [
            [3, "desc"],
            [0, "desc"]
        ])
    }
    if ((table_tasks = $(".table-tasks")).length) {
        var f, h = {};
        f = $("._hidden_inputs._filters._tasks_filters input"), $.each(f, function() {
            h[$(this).attr("name")] = '[name="' + $(this).attr("name") + '"]'
        });
        var v = [0],
            b = admin_url + "tasks/table";
        $("body").hasClass("tasks-page") && (b += "?bulk_actions=true"), _table_api = initDataTable(table_tasks, b, v, v, h, [table_tasks.find("th.duedate").index(), "asc"]), _table_api && $("body").hasClass("dashboard") && _table_api.column(5).visible(!1, !1).column(6).visible(!1, !1).columns.adjust()
    }
    $("#send_file").on("show.bs.modal", function(e) {
        var t = $("#send_file");
        t.find('input[name="filetype"]').val($($(e.relatedTarget)).data("filetype")), t.find('input[name="file_path"]').val($($(e.relatedTarget)).data("path")), t.find('input[name="file_name"]').val($($(e.relatedTarget)).data("file-name")), $('input[name="email"]').length > 0 && t.find('input[name="send_file_email"]').val($('input[name="email"]').val())
    }), $("#send_file form").on("submit", function() {
        $(this).find('button[type="submit"]').prop("disabled", !0)
    }), $("body").on("change", 'input[name="send_set_password_email"]', function() {
        $("body").find(".client_password_set_wrapper").toggleClass("hide")
    }), $("body").on("change", '.todo input[type="checkbox"]', function() {
        var e = !0 === $(this).prop("checked") ? 1 : 0,
            t = $(this).val();
        window.location.href = admin_url + "todo/change_todo_status/" + t + "/" + e
    });
    var g = $(".todos-sortable");
    g.length > 0 && (g = g.sortable({
        connectWith: ".todo",
        items: "li",
        handle: ".dragger",
        appendTo: "body",
        update: function(e, t) {
            this === t.item.parent()[0] && update_todo_items()
        }
    })), $("body").on("click", ".open_newsfeed,.close_newsfeed", function(e) {
        e.preventDefault(), void 0 === $(this).data("close") ? requestGet("newsfeed/get_data").done(function(e) {
            $("#newsfeed").html(e), load_newsfeed(postid), init_newsfeed_form(), init_selectpicker(), init_lightbox()
        }) : !0 === $(this).data("close") && (newsFeedDropzone.destroy(), $("#newsfeed").html(""), newsfeed_posts_page = 0, track_load_post_likes = 0, track_load_comment_likes = 0, postid = void 0), $("#newsfeed").toggleClass("hide"), $("body").toggleClass("noscroll")
    }), $("[data-newsfeed-auto]").length > 0 && $(".open_newsfeed").click(), $("body").on("keyup", ".comment-input input", function(e) {
        13 == e.keyCode && add_comment(this)
    }), $("#modal_post_likes").on("show.bs.modal", function(e) {
        track_load_post_likes = 0, $("#modal_post_likes_wrapper").empty(), $(".likes_modal .modal-footer").removeClass("hide");
        var t = $(e.relatedTarget),
            a = $(t).data("postid");
        post_likes_total_pages = $(t).data("total-pages"), $(".load_more_post_likes").attr("data-postid", a), load_post_likes(a)
    }), $("#modal_post_comment_likes").on("show.bs.modal", function(e) {
        $("#modal_comment_likes_wrapper").empty(), track_load_comment_likes = 0, $(".likes_modal .modal-footer").removeClass("hide");
        var t = $(e.relatedTarget),
            a = $(t).data("commentid");
        comment_likes_total_pages = $(t).data("total-pages"), $(".load_more_post_comment_likes").attr("data-commentid", a), load_comment_likes(a)
    }), $(".load_more_post_likes").on("click", function(e) {
        e.preventDefault(), load_post_likes($(this).data("postid"))
    }), $(".load_more_post_comment_likes").on("click", function(e) {
        e.preventDefault(), load_comment_likes($(this).data("commentid"))
    }), $(".add-attachments").on("click", function(e) {
        e.preventDefault(), $("#post-attachments").toggleClass("hide")
    }), init_invoices_total(), init_expenses_total(), init_estimates_total(), init_items_sortable(), $(".settings-textarea-merge-field").on("click", function(e) {
        e.preventDefault();
        var t = $(this).text().trim(),
            a = $('textarea[name="settings[' + $(this).data("to") + ']"]');
        a.val(a.val() + "\n" + t)
    }), $("body").hasClass("estimates-pipeline") && estimate_pipeline_open($('input[name="estimateid"]').val()), $("body").hasClass("proposals-pipeline") && proposal_pipeline_open($('input[name="proposalid"]').val()), $("body").on("submit", "._transaction_form", function() {
        calculate_total(), $("body").find("#items-warning").remove();
        var e = $(this).find("table.items"),
            t = e.find(".main");
        return t.find('[name="description"]').length && t.find('[name="description"]').val().trim().length > 0 && t.find('[name="rate"]').val().trim().length > 0 ? (e.before('<div class="alert alert-warning mbot20" id="items-warning">' + appLang.item_forgotten_in_preview + '<i class="fa fa-angle-double-down pointer pull-right fa-2x" style="margin-top:-4px;" onclick="add_item_to_table(\'undefined\',\'undefined\',undefined); return false;"></i></div>'), $("html,body").animate({
            scrollTop: $("#items-warning").offset().top
        }), !1) : e.length && 0 === e.find(".item").length ? (e.before('<div class="alert alert-warning mbot20" id="items-warning">' + appLang.no_items_warning + "</div>"), $("html,body").animate({
            scrollTop: $("#items-warning").offset().top
        }), !1) : (reorder_items(), $('select[name="currency"]').prop("disabled", !1), $('select[name="project_id"]').prop("disabled", !1), $('input[name="date"]').prop("disabled", !1), $(this).find(".transaction-submit").prop("disabled", !0), !0)
    }), $("body").on("click", ".transaction-submit", function() {
        var e = $(this),
            t = e.parents("form._transaction_form");
        t.valid() && (e.hasClass("save-as-draft") ? t.append(hidden_input("save_as_draft", "true")) : e.hasClass("save-and-send") && t.append(hidden_input("save_and_send", "true"))), t.submit()
    }), $("body").on("submit", "#sales-notes", function() {
        var e = $(this);
        if ("" !== e.find('textarea[name="description"]').val()) return $.post(e.attr("action"), $(e).serialize()).done(function(t) {
            e.find('textarea[name="description"]').val(""), e.hasClass("estimate-notes-form") ? get_sales_notes(t, "estimates") : e.hasClass("invoice-notes-form") ? get_sales_notes(t, "invoices") : e.hasClass("proposal-notes-form") ? get_sales_notes(t, "proposals") : e.hasClass("contract-notes-form") && get_sales_notes(t, "contracts")
        }), !1
    }), $("body").on("change", 'input[name="show_quantity_as"]', function() {
        $("body").find("th.qty").html($(this).data("text"))
    }), $("body").on("change", 'div.credit_note input[name="date"]', function() {
        do_prefix_year($(this).val())
    }), $("body").on("change", 'div.invoice input[name="date"], div.estimate input[name="date"], div.proposal input[name="date"]', function() {
        var e = $(this).val();
        if (do_prefix_year(e), !($('input[name="isedit"]').length > 0)) {
            var t = "duedate",
                a = admin_url + "invoices/get_due_date";
            $("body").find("div.estimate").length > 0 ? (a = admin_url + "estimates/get_due_date", t = "expirydate") : $("body").find("div.proposal").length > 0 && (a = admin_url + "proposals/get_due_date", t = "open_till"), "" === e && $('input[name="' + t + '"]').val(""), "" !== e && $.post(a, {
                date: e
            }).done(function(e) {
                e && $('input[name="' + t + '"]').val(e)
            })
        }
    }), $("#sales_attach_file").on("hidden.bs.modal", function(e) {
        $("#sales_uploaded_files_preview").empty(), $(".dz-file-preview").empty()
    }), "undefined" != typeof Dropbox && $("#dropbox-chooser-sales").length > 0 && document.getElementById("dropbox-chooser-sales").appendChild(Dropbox.createChooseButton({
        success: function(e) {
            var t = {};
            t.rel_id = $("body").find('input[name="_attachment_sale_id"]').val(), t.type = $("body").find('input[name="_attachment_sale_type"]').val(), t.files = e, t.external = "dropbox", $.post(admin_url + "misc/add_sales_external_attachment", t).done(function() {
                "invoice" == t.type ? init_invoice(t.rel_id) : "credit_note" == t.type ? init_credit_note(t.rel_id) : "estimate" == t.type ? $("body").hasClass("estimates-pipeline") ? estimate_pipeline_open(t.rel_id) : init_estimate(t.rel_id) : "proposal" == t.type && ($("body").hasClass("proposals-pipeline") ? proposal_pipeline_open(t.rel_id) : init_proposal(t.rel_id)), $("#sales_attach_file").modal("hide")
            })
        },
        linkType: "preview",
        extensions: app_allowed_files.split(",")
    })), $("#sales-upload").length > 0 && new Dropzone("#sales-upload", $.extend({}, _dropzone_defaults(), {
        sending: function(e, t, a) {
            a.append("rel_id", $("body").find('input[name="_attachment_sale_id"]').val()), a.append("type", $("body").find('input[name="_attachment_sale_type"]').val())
        },
        success: function(e, t) {
            t = JSON.parse(t);
            var a, i = $("body").find('input[name="_attachment_sale_type"]').val();
            a = "delete_" + i + "_attachment", "invoice" == i ? init_invoice(t.rel_id) : "credit_note" == i ? init_credit_note(t.rel_id) : "estimate" == i ? $("body").hasClass("estimates-pipeline") ? estimate_pipeline_open(t.rel_id) : init_estimate(t.rel_id) : "proposal" == i && ($("body").hasClass("proposals-pipeline") ? proposal_pipeline_open(t.rel_id) : init_proposal(t.rel_id));
            var n = "";
            !0 !== t.success && "true" != t.success || (n += '<div class="display-block sales-attach-file-preview" data-attachment-id="' + t.attachment_id + '">', n += '<div class="col-md-10">', n += '<div class="pull-left"><i class="attachment-icon-preview fa fa-file-o"></i></div>', n += '<a href="' + site_url + "download/file/sales_attachment/" + t.key + '" target="_blank">' + t.file_name + "</a>", n += '<p class="text-muted">' + t.filetype + "</p>", n += "</div>", n += '<div class="col-md-2 text-right">', n += '<a href="#" class="text-danger" onclick="' + a + "(" + t.attachment_id + '); return false;"><i class="fa fa-times"></i></a>', n += "</div>", n += '<div class="clearfix"></div><hr/>', n += "</div>", $("#sales_uploaded_files_preview").append(n))
        }
    })), $("body").on("click", ".invoice-send-to-client", function(e) {
        e.preventDefault(), $("#invoice_send_to_client_modal").modal("show")
    }), $("body").on("click", ".estimate-send-to-client", function(e) {
        e.preventDefault(), $("#estimate_send_to_client_modal").modal("show")
    }), $("body").on("click", ".close-send-template-modal", function() {
        $("#estimate_send_to_client_modal").modal("hide"), $("#proposal_send_to_customer").modal("hide")
    }), $("body").on("change", "#include_shipping", function() {
        var e = $("#shipping_details");
        !0 === $(this).prop("checked") ? e.removeClass("hide") : e.addClass("hide")
    }), $("body").on("click", ".save-shipping-billing", function(e) {
        init_billing_and_shipping_details()
    }), $("body").on("change", 'select[name="currency"]', function() {
        init_currency_symbol()
    }), $("body").on("change", 'input[name="adjustment"],select.tax,input[name="transportation"],input[name="servicecharge"],input[name="packing_and_forwarding"]', function() {
        calculate_total()
    }), $("body").on("click", ".discount-total-type", function(e) {
        e.preventDefault(), $("#discount-total-type-dropdown").find(".discount-total-type").removeClass("selected"), $(this).addClass("selected"), $(".discount-total-type-selected").html($(this).text()), $(this).hasClass("discount-type-percent") ? ($(".input-discount-fixed").addClass("hide").val(0), $(".input-discount-percent").removeClass("hide")) : ($(".input-discount-fixed").removeClass("hide"), $(".input-discount-percent").addClass("hide").val(0), $("#discount_percent-error").remove()), calculate_total()
    }), $("body").on("change", 'select[name="discount_type"]', function() {
        "" === $(this).val() && $('input[name="discount_percent"]').val(0), calculate_total()
    }), $("body").on("change", 'input[name="discount_percent"],input[name="discount_total"]', function() {
        if ("" === $('select[name="discount_type"]').val() && 0 != $(this).val()) return alert("You need to select discount type"), $("html,body").animate({
            scrollTop: 0
        }, "slow"), $("#wrapper").highlight($('label[for="discount_type"]').text()), setTimeout(function() {
            $("#wrapper").unhighlight()
        }, 3e3), !1;
        !0 === $(this).valid() && calculate_total()
    }), $("body").on("change", ".invoice #project_id", function() {
        var e = $(this).selectpicker("val");
        if ("" !== e) requestGetJSON("tasks/get_billable_tasks_by_project/" + e).done(function(t) {
            _init_tasks_billable_select(t, e)
        });
        else {
            var t = $("#clientid").selectpicker("val");
            "" !== t ? requestGetJSON("tasks/get_billable_tasks_by_customer_id/" + t).done(function(e) {
                _init_tasks_billable_select(e)
            }) : _init_tasks_billable_select([], "")
        }
    }), $("body").on("change", 'select[name="task_select"]', function() {
        "" !== $(this).selectpicker("val") && add_task_to_preview_as_item($(this).selectpicker("val"))
    }), $("body").on("change", 'select[name="paymentmode"]', function() {
        var e = $(".do_not_redirect");
        $.isNumeric($(this).val()) ? e.addClass("hide") : e.removeClass("hide")
    }), $("body").on("change", '.f_client_id select[name="clientid"]', function() {
        var e = $(this).val(),
            t = $('select[name="project_id"]'),
            a = t.html("").clone(),
            i = $(".projects-wrapper");
        if (t.selectpicker("destroy").remove(), t = a, $("#project_ajax_search_wrapper").append(a), init_ajax_project_search_by_customer_id(), clear_billing_and_shipping_details(), !e) return $("#merge").empty(), $("#expenses_to_bill").empty(), $("#invoice_top_info").addClass("hide"), i.addClass("hide"), !1;
        var n = $("body").find('input[name="merge_current_invoice"]').val();
        requestGetJSON("invoices/client_change_data/" + e + "/" + (n = void 0 === n ? "" : n)).done(function(e) {
            $("#merge").html(e.merge_info);
            var a = $("#expenses_to_bill");			
			$("body").find('input[name="devide_gst"]').val(e.devide_gst);
            for (var n in 0 === a.length ? e.expenses_bill_info = "" : a.html(e.expenses_bill_info), "" !== e.merge_info || "" !== e.expenses_bill_info ? $("#invoice_top_info").removeClass("hide") : $("#invoice_top_info").addClass("hide"), billingAndShippingFields) billingAndShippingFields[n].indexOf("billing") > -1 && (billingAndShippingFields[n].indexOf("country") > -1 ? $('select[name="' + billingAndShippingFields[n] + '"]').selectpicker("val", e.billing_shipping[0][billingAndShippingFields[n]]) : billingAndShippingFields[n].indexOf("billing_street") > -1 ? $('textarea[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]) : $('input[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]));
            for (var n in empty(e.billing_shipping[0].shipping_street) || $('input[name="include_shipping"]').prop("checked", !0).change(), billingAndShippingFields) billingAndShippingFields[n].indexOf("shipping") > -1 && (billingAndShippingFields[n].indexOf("country") > -1 ? $('select[name="' + billingAndShippingFields[n] + '"]').selectpicker("val", e.billing_shipping[0][billingAndShippingFields[n]]) : billingAndShippingFields[n].indexOf("shipping_street") > -1 ? $('textarea[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]) : $('input[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]));
            init_billing_and_shipping_details();
            var s = e.client_currency,
                o = $("body").find('.accounting-template select[name="currency"]');
            0 != (s = parseInt(s)) ? o.val(s) : o.val(o.data("base")), _init_tasks_billable_select(e.billable_tasks, t.selectpicker("val")), !0 === e.customer_has_projects ? i.removeClass("hide") : i.addClass("hide"), o.selectpicker("refresh"), init_currency_symbol()
        })
    }), 0 === $("body").find('input[name="isedit"]').length && $('.f_client_id select[name="clientid"]').change(), $("body").on("click", "#get_shipping_from_customer_profile", function(e) {
        e.preventDefault();
        var t = $("#include_shipping");
        !1 === t.prop("checked") && (t.prop("checked", !0), $("#shipping_details").removeClass("hide"));
        var a = $('select[name="clientid"]').val();
        "" !== a && requestGetJSON("clients/get_customer_billing_and_shipping_details/" + a).done(function(e) {
            $('textarea[name="shipping_street"]').val(e[0].shipping_street), $('input[name="shipping_city"]').val(e[0].shipping_city), $('input[name="shipping_state"]').val(e[0].shipping_state), $('input[name="shipping_zip"]').val(e[0].shipping_zip), $('select[name="shipping_country"]').selectpicker("val", e[0].shipping_country)
        })
    }), "undefined" != typeof accounting && (accounting.settings.currency.decimal = app_decimal_separator, accounting.settings.currency.thousand = app_thousand_separator, accounting.settings.currency.precision = app_decimal_places, accounting.settings.number.thousand = app_thousand_separator, accounting.settings.number.decimal = app_decimal_separator, accounting.settings.number.precision = app_decimal_places, calculate_total()), $("body").on("change", 'input[name="invoices_to_merge[]"]', function() {
        var e = $(this).prop("checked"),
            t = $(this).val();
        !0 === e ? requestGetJSON("invoices/get_merge_data/" + t).done(function(e) {
            $.each(e.items, function(e, a) {
                "" !== a.rel_type && ("task" == a.rel_type ? $('input[name="task_id"]').val(a.item_related_formatted_for_input) : "expense" == a.rel_type && $('input[name="expense_id"]').val(a.item_related_formatted_for_input)), _set_item_preview_custom_fields_array(a.custom_fields), add_item_to_table(a, "undefined", t)
            })
        }) : $("body").find('[data-merge-invoice="' + t + '"]').remove()
    }), $("body").on("change", 'input[name="bill_expenses[]"]', function() {
        var e = $(this).prop("checked"),
            t = $(this).val();
        !0 === e ? requestGetJSON("invoices/get_bill_expense_data/" + t).done(function(e) {
            $('input[name="expense_id"]').val(t), add_item_to_table(e, "undefined", "undefined", t)
        }) : ($("body").find('[data-bill-expense="' + t + '"]').remove(), $("body").find('#billed-expenses input[value="' + t + '"]').remove())
    }), $("body").on("change", ".invoice_inc_expense_additional_info input", function() {
        var e, t = $(this).attr("data-content"),
            a = $("[data-bill-expense=" + $(this).attr("data-id") + "] .item_long_description");
        current_desc_val = a.val(), current_desc_val = current_desc_val.trim(), "" !== t && (!0 === $(this).prop("checked") ? (e = current_desc_val + "\n" + t, a.val(e.trim())) : (a.val(current_desc_val.replace("\n" + t, "")), a.val(current_desc_val.replace(t, ""))))
    })
}), $(document).keyup(function(e) {
    27 == e.keyCode && ($(".modal").is(":visible") && 1 === $(".modal:visible").length && $("body").find('.modal:visible [onclick^="close_modal_manually"]').eq(0).click(), $(".popup-wrapper").is(":visible") && $(".popup-wrapper").find(".system-popup-close").click())
}), $("#newsfeed").scroll(function(e) {
    var t = $(e.currentTarget);
    t[0].scrollHeight - t.scrollTop() == t.outerHeight() && load_newsfeed(), $("#newsfeed .close_newsfeed").css("top", $(this).scrollTop() + 20 + "px")
});
var delay = function() {
    var e = 0;
    return function(t, a) {
        clearTimeout(e), e = setTimeout(t, a)
    }
}();