var erpUI = {
    initalPage: function (obj_page) {
        url = obj_page.url;
        page_id = obj_page.page_id;
        // $("select2").select2();
        if (typeof ReorderList != "undefined") Sortable.create(ReorderList);
        if (typeof ReorderCard != "undefined") Sortable.create(ReorderCard);

        $("#searchBox").focus();
        $("#searchBox").select();
        $(".btn-search-control-save-menu").on("click", function (event) {
            saveAjaxMyMenu();
        });
        $("#frmDataCard #code,#frmDataCard #no").focus();
        $(".removeInputSearchIcond").hide();
        $(document)
            .on("click", ".listPaginate ul.pagination>li>a", function (event) {
                event.preventDefault();
                myfn.initUpdateBrowserState(
                    false,
                    ["page"],
                    [$(this).attr("data-page")]
                );
                var page = $(this).attr("data-page");
                erpUI.showAjaxRecords(page);
            })
            .on(
                "blur",
                "#frmDataCard .form_data , .sublist_control",
                function (event) {
                    name = this.name;
                    if ($(this).val() != "") {
                        $(".mandatory-" + name).removeClass("has-error");
                        $("#helpBlock" + name).hide();
                    } else if ($(this).attr("data-mandatory") == 1) {
                        if (!$(".mandatory-" + name).hasClass("has-error")) {
                            $(".mandatory-" + name).addClass("has-error");
                        }
                    }
                }
            )
            .on("click", ".btnSearch", function () {
                search(this);
            })
            .on("change", "#searchBox", function () {
                search(this);
            })
            .on("mouseover input", ".InputSearchBox", function () {
                if ($(this).find("input").val()) {
                    $(".removeInputSearchIcond").show();
                } else {
                    $(".removeInputSearchIcond").hide();
                }
            })
            .on("mouseleave", ".InputSearchBox", function () {
                $(".removeInputSearchIcond").hide();
            })
            .on("click", ".btnAdvanceSearch", function () {
                advanceSearch(1);
            });
    },
    btnEdit: function (ctrl, prefix, response) {
        $.ajax({
            type: "GET",
            url: `${prefix}/show`,
            data: getFormControlAttrParameter($(ctrl)),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".page-content").LoadingOverlay("show");
            },
            success: response,
            error: function (xhr, ajaxOptions, thrownError) {
                $(".page-content").LoadingOverlay("hide", true);
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    delete: function (ctrl, prefix, response) {
        var code = $(ctrl).attr("params-code");
        $.ajax({
            type: "POST",
            url: `${prefix}/delete`,
            data: getFormControlAttrParameter($(ctrl)),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".page-content").LoadingOverlay("show");
            },
            success: response,
            error: function (xhr, ajaxOptions, thrownError) {
                $(".page-content").LoadingOverlay("hide", true);
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    store: function (loading, prefix, response) {
        let validate_field = ValidateMandatoryField(".form_data");
        if (validate_field > 0) return;
        $.ajax({
            type: "POST",
            url: prefix,
            data: $("#frmDataCard").serialize(),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                loading.start();
            },
            success: response,
            error: function (xhr, ajaxOptions, thrownError) {
                $(".page-content").LoadingOverlay("hide", true);
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    duplicate: function (ctrl, prefix, response) {
        if (ctrl.attr("readonly") == "readonly") return;
        $.ajax({
            type: "GET",
            url: `${prefix}/duplicate/${$(ctrl).val()}`,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: response,
            error: function (xhr, ajaxOptions, thrownError) {
                $(".page-content").LoadingOverlay("hide", true);
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    initializeRow: function (data) {
        $.ajax({
            type: "GET",
            url: `${url}/search`,
            data: data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".page-content").LoadingOverlay("show");
            },
            success: function (response) {
                $(".page-content").LoadingOverlay("hide", true);
                $(".dataList").html(response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $(".page-content").LoadingOverlay("hide", true);
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    generateSerial: function (ctrl, event) {
        if (jQuery.type(event) != "undefined" && event.which == 13)
            code = $(ctrl).val();
        else code = $(ctrl).attr("reference");
        if (jQuery.type(code) === "undefined") return;
        $.ajax({
            type: "GET",
            url: `${url}/generate?ns=${code}`,
            success: function (response) {
                if (response.status == "success") {
                    window.location.replace(
                        `${url}/transaction?type=ed&code=${response.noseries}`
                    );
                } else {
                    myfn.showNotify(
                        "Warning",
                        "lemon",
                        "top",
                        "right",
                        response.msg
                    );
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    showAjaxRecords: function showAjaxRecords(page) {
        var data = "action_type=Pagination&page=" + page + "&";
        if ($("#searchBox").val() == "")
            data +=
                $("#frmTableData").serialize() + "&searchtype=advancesearch";
        else data += "value=" + $("#searchBox").val();
        erpUI.initializeRow(data);
    },
    sortTable: function (ctrl) {
        name = $(ctrl).attr("name");
        field_name = $(ctrl).attr("data-field");
        table_name = $(ctrl).attr("data-table_name");
        value = "asc";
        $.ajax({
            type: "post",
            url: "/table-records-sorted",
            data: {
                field_name: field_name,
                table_name: table_name,
                value: value,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response["status"] == "warning") {
                    myfn.showNotify(
                        response["status"],
                        "lemon",
                        "top",
                        "right",
                        response["msg"]
                    );
                } else {
                    erpUI.showAjaxRecords(
                        $(".pagination > .active a").attr("data-page")
                    );
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                myfn.showNotify(
                    "Error",
                    "ruby",
                    "top",
                    "right",
                    "Description: " +
                        thrownError +
                        ", please contact your service provider!"
                );
            },
        });
    },
    formatOption: function (results) {
        if (results.id == undefined) {
            return "No results found";
        }
        var $result = $(
            "<span><b>" + results.id + "</b><br/>" + results.text + "</span>"
        );
        return $result;
    },
    formatDateFormula: function (event, that) {
        $(that).attr("placeholder", "1D,1W,1M,1Y");
        $(that).css("text-transform", "uppercase");
        $(that).val(
            $(that)
                .val()
                .replace(/[^0-9dDwWmMyY]/g, "")
        );
        $(that).parent().removeClass("has-error");
        // 100,68 = d/D
        // 119,87 = w/W
        // 109,77 = m/M
        // 121,89 = y/Y
        let keyCode = [100, 68, 119, 87, 109, 77, 121, 89];
        let allowAlphabets = ["d", "w", "m", "y"];
        if (
            (event.which < 48 || event.which > 57) &&
            !keyCode.includes(event.which)
        ) {
            event.preventDefault();
        }

        let text = $(that).val().replace(/[0-9]/g, "");
        if (text.length > 0) {
            event.preventDefault();
        } else {
            if (event.type != "focusout") return;
            $(that).parent().addClass("has-error");
            // myfn.showNotify('Warning','lemon','top','right','Invalid format. Please enter like this example (1D,1W,1M,1Y)');
        }

        let eventKeyCode = event.originalEvent.key;
        let final_text = $(that).val();
        if (
            final_text.length == 1 &&
            allowAlphabets.includes(eventKeyCode.toLowerCase())
        ) {
            $(that).val("1" + eventKeyCode.toUpperCase());
        }
    },
    comfirmDialog: function (ctrl) {
        let paramaters = getFormControlAttrParameter($(ctrl));
        for (const [key, value] of Object.entries(paramaters)) {
            if (key == "onclick" && value != "") {
                $("#confirm-modal-dialog #btn-modal-id-yes").attr(
                    `${key}`,
                    value
                );
            } else if (value != "" && key != "btnYes") {
                $("#confirm-modal-dialog #btn-modal-id-yes").attr(
                    `params-${key}`,
                    value
                );
            }
        }

        if (!paramaters.message)
            paramaters.message =
                "Do you want to create a copy from this document?";
        if (!paramaters.btnyes) paramaters.btnyes = "modal-btn-yes";

        $("#confirm-modal-dialog #btn-modal-id-yes").removeClass(
            paramaters.btnyes
        );

        if (!paramaters.onclick) {
            $("#confirm-modal-dialog #btn-modal-id-yes").addClass(
                paramaters.btnyes
            );
        }

        $("#confirm-modal-dialog .modal-confirmation-text").text(
            paramaters.message
        );

        $("#confirm-modal-dialog").modal("show");
    },
    datetimepickervalue:function(){

    },
    datatimefiler:function(boxid){
                   
        id=boxid;
        $(`#${id}`).daterangepicker({
       timePicker: true,
       startDate: moment().startOf('hour'),
       endDate: moment().startOf('hour').add(32, 'hour'),
       timePicker24Hour:true,
       timePickerSeconds:true,
       locale: {
       format: 'YYYY-M-DD hh:mm:ss'
         }
         });
        $(`#${id}`).trigger('click');
    }
};
function search(ctrl) {
    var value =
        $("#searchBox").val() == "" || $("#searchBox").val() == " "
            ? "null"
            : $("#searchBox").val();
    var data = {
        value: value,
        action_type: "GlobleSearch",
    };
    erpUI.initializeRow(data);
}
function advanceSearch(ctrl) {
    var data = $("#frmTableData").serialize();
    data += "&action_type=AdvanceSearch";
    data += "&page=1";
    erpUI.initializeRow(data);
}
function getFormControlAttrParameter(ctrl) {
    var params = {};
    ctrl.each(function () {
        $.each(this.attributes, function () {
            if (this.specified) {
                let attr = this.name;
                let value_attr = this.value;
                if (attr.includes("params")) {
                    attr = attr.replace("params-", "");
                    params[attr] = value_attr;
                }
            }
        });
    });
    return params;
}
function callServer(
    configs,
    eventResponse = null,
    beforeSend = null,
    error = null
) {
    const defaultConfig = {
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: beforeSend,
        success: function (response) {
            eventResponse(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("hide", true);
            myfn.SwalMessage(
                "error",
                thrownError + ", Please contact your service provider!"
            );
            error(xhr, ajaxOptions, thrownError);
        },
    };
    var configs = Object.assign({}, configs, defaultConfig);
    $.ajax(configs);
}
function ValidateMandatoryField(className) {
    var invalid = 0;
    $(className).each(function (e) {
        if ($(this).val() === "") {
            if ($(this).attr("data-mandatory") == 1) {
                name = this.name;
                $(".mandatory-" + name).addClass("has-error");
                $("#helpBlock" + name).show();
                invalid = invalid + 1;
            }
        } else {
            name = this.name;
            $(".mandatory-" + name).removeClass("has-error");
            $("#helpBlock" + name).hide();
        }
    });
    return invalid;
}
function saveAjaxMyMenu() {
    $.ajax({
        type: "GET",
        url:
            "/menu/ajaxsave/" +
            encodeURIComponent($(".search-control-new-menu-english").val()) +
            "/" +
            encodeURIComponent(url) +
            "/" +
            encodeURIComponent(page_id),
        data: $("#frmTableData").serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response["status"] == "success") {
                myfn.showNotify(
                    "success",
                    "lime",
                    "top",
                    "right",
                    response["msg"]
                );
                setTimeout(function () {
                    location.href = url;
                }, 2500);
            } else {
                myfn.showNotify(
                    "failed",
                    "lemon",
                    "top",
                    "right",
                    response["msg"]
                );
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("hide", true);
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
}
function ImportExcel(ctrl) {
    $("#import_excel").val("");
    $("#display_file_name").html("");
    $("#btnConfirmForUpload").addClass("hidden");
    $("#import_excel").attr("data-url", `${url}/import_excel`);
    $("#btnConfirmForUpload").attr("data-url", `${url}/do_import`);
    $("#divImport").modal({
        backdrop: "static",
        keyboard: false,
    });
}
function didClickYes(ctrl) {
    if (
        $("#secure_password").val() == "" ||
        $("#secure_password").val() == null
    ) {
        $("#secure_password").css("border", "1px solid red");
        return;
    }
    $("#secure_msg").hide();
    if ($(ctrl).attr("data-state") == "download") excuteDownlaodExcel(ctrl);
    else if ($(ctrl).attr("data-state") == "upload")
        verifyUser($("#secure_password").val());
}
function clearForm() {
    $('#divDataCard input[name="code"]').attr("type", "text");
    $('#divDataCard input[name="code"]').val("");
    $('#divDataCard input[name="code"]').parents(".form-group").show();
    $(".btnSave").attr("params-url", `${url}/store`);
    $(".btnSaveNew").attr("params-url", `${url}/store`);
    $(".modal-card-title").html("Add New");
    $(".form_data").val("");
    $(".checkbox").prop("checked", false);
    $("#code").removeAttr("readonly");
    $(".form_data").each(function (e) {
        if ($(this).attr("data-mandatory") == 1) {
            $(".mandatory-" + this.name).removeClass("has-error");
            $("#helpBlock" + this.name).hide();
        }
        if (this.tagName.toLowerCase() === "select") {
            if ($(this).attr("data-input_type") == "select2")
                $("#" + this.name)
                    .val(null)
                    .trigger("change");
            else AssignValueToSelect2("#" + this.name, "");
        }
    });
    $("#code").focus();
}
function uploadExcel(ctrl) {
    $(".secure_msg").hide();
    $("#divPassword").modal("show");
    $("#secure_password").val("");
    $(".btnSecureYes").attr("data-state", "upload");
}
function downloadExcel(ctrl) {
    $("#secure_password").css("border", "");
    $(".secure_msg").hide();
    $("#divPassword").modal("show");
    $("#secure_password").val("");
    $("#migration_template").prop("checked", true);
    $(".btnSecureYes").attr("data-state", "download");
}
function excuteDownlaodExcel() {
    var remember_password = "No";
    if ($("#remember_password").is(":checked")) remember_password = "Yes";
    migration_template = "off";
    blank_template = "off";
    if ($("#blank_template").is(":checked"))
        blank_template = $("#blank_template:checkbox").val();
    if ($("#migration_template").is(":checked"))
        migration_template = $("#migration_template:checkbox").val();
    var url_param = `${url}/download?secure=${$(
        "#secure_password"
    ).val()}&migration_template=${migration_template}&blank_template=${blank_template}&file_extension=${$(
        "#divPassword #file_extension"
    ).val()}`;

    if ($("#searchBox").val() == "")
        data = `${$(
            "#frmTableData"
        ).serialize()}&searchtype=advancesearch&remember_password=${remember_password}`;
    else
        data = `value=${$(
            "#searchBox"
        ).val()}&searchtype=globalsearch&remember_password=${remember_password}`;

    $.ajax({
        type: "post",
        url: url_param,
        data: data,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response.status == "success") {
                $("#divPassword").modal("hide");
                location.href = response.path;
                myfn.showNotify(
                    "success",
                    "lime",
                    "top",
                    "right",
                    response["message"]
                );
            } else {
                $(".secure_msg").html(response.message);
                $(".secure_msg").show();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("hide", true);
            l.stop();
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
}

//Number Input validate
$(document).on("keypress keyup blur", ".AllowNumberOnly", function (event) {
    $(this).val(
        $(this)
            .val()
            .replace(/[^0-9\,.-]/g, "")
    );
    if (
        (event.which != 46 || $(this).val().indexOf(".") != -1) &&
        (event.which < 48 || event.which > 57) &&
        (event.which != 45 || $(this).val().indexOf("-") != -1) &&
        event.which != 44
    ) {
        event.preventDefault();
    }
});
$(document).on("click", ".documentArchive", function (event) {
    let params = getFormControlAttrParameter($(this));
    $.ajax({
        type: "get",
        url: `${url}/archived-document`,
        data: params,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response.status == "success") {
                myfn.showNotify(
                    "Success",
                    "success",
                    "top",
                    "right",
                    response.msg
                );
            } else {
                myfn.showNotify(
                    "Warning",
                    "lemon",
                    "top",
                    "right",
                    response.msg
                );
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("show");
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
});

$(document).on("click", ".documentArchiveHistory", function (event) {
    let params = getFormControlAttrParameter($(this));
    $.ajax({
        type: "get",
        url: `/get-archived-document`,
        data: params,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response.status == "success") {
                $("#archivedHistoryBody").html(response.view);
                $("#divArchiveHistory").modal("show");
            } else {
                myfn.showNotify(
                    "Warning",
                    "lemon",
                    "top",
                    "right",
                    response.msg
                );
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("show");
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
});
$(document).on("click", ".btnDeleteArchiveHistory", function (event) {
    let params = getFormControlAttrParameter($(this));
    let id = $(this).attr("id");
    $.ajax({
        type: "get",
        url: `/get-archived-document/delete`,
        data: params,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response.status == "success") {
                $(`#divArchiveHistory tr#archive${id}`).remove();
            } else {
                myfn.showNotify(
                    "Warning",
                    "lemon",
                    "top",
                    "right",
                    response.msg
                );
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("show");
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
});
$(document).on("click", ".btnRestoreArchiveHistory", function () {
    let params = getFormControlAttrParameter($(this));
    let no = $("#btnArchiveHistoryNo").attr("no");
    $("#restorDate").text(params.date);
    $("#btnConfirmRestoreArchiveHistoryYes").attr("params-no", no);
    $("#btnConfirmRestoreArchiveHistoryYes").attr(
        "params-number",
        params.number
    );
    $("#btnConfirmRestoreArchiveHistoryYes").attr(
        "params-doc_type",
        params.doc_type
    );
    $("#divConfirmRestoreArchiveHistory").modal("show");
});
$(document).on("click", "#btnConfirmRestoreArchiveHistoryYes", function () {
    let params = getFormControlAttrParameter($(this));
    params.secure = $("#restore_pass").val();
    $.ajax({
        type: "get",
        url: `${url}/restore-archived-document`,
        data: params,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".page-content").LoadingOverlay("show");
        },
        success: function (response) {
            $(".page-content").LoadingOverlay("hide", true);
            if (response.status == "success") {
                myfn.showNotify(
                    "Success",
                    "success",
                    "top",
                    "right",
                    response.msg
                );
                location.reload();
            } else {
                myfn.showNotify(
                    "Warning",
                    "lemon",
                    "top",
                    "right",
                    response.msg
                );
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $(".page-content").LoadingOverlay("show");
            myfn.showNotify(
                "Error",
                "ruby",
                "top",
                "right",
                "Description: " +
                    thrownError +
                    ", please contact your service provider!"
            );
        },
    });
});
$("#divConfirmRestoreArchiveHistory").on("shown.bs.modal", function () {
    $("#restore_pass").focus();
});
