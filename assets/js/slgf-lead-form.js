(function ($) {
    "use strict";

    var slgfLeadForm = {
        init: function () {

            var self = this;

            var delay;
            $(document).on("keypress", ".slgf-field", function (e) {
                clearTimeout(delay);
                delay = setTimeout(function () {
                    var $form = $(e.currentTarget).closest("form");
                    var fieldName = $(e.currentTarget).attr("name");
                    if (slgf_params.fields[fieldName]) {
                        if ($form.find("[name='" + fieldName + "__label']").length) {
                            slgf_params.fields[fieldName].label = $form.find("[name='" + fieldName + "__label']").val();
                        }
                        self.toggleFieldError($form, fieldName, self.isFieldError($(e.currentTarget).val(), slgf_params.fields[fieldName]));
                    }
                }, 400);
            });

            $(document).on("focusout", ".slgf-field", function (e) {
                var $form = $(e.currentTarget).closest("form");
                var fieldName = $(e.currentTarget).attr("name");
                if (slgf_params.fields[fieldName]) {
                    if ($form.find("[name='" + fieldName + "__label']").length) {
                        slgf_params.fields[fieldName].label = $form.find("[name='" + fieldName + "__label']").val();
                    }
                    self.toggleFieldError($form, fieldName, self.isFieldError($(e.currentTarget).val(), slgf_params.fields[fieldName]));
                }
            });

            $('form.slgf-form').submit(function (e) {
                e.preventDefault();

                var $form = $(e.currentTarget);
                var formData = {};
                var errors = {};
                var isFieldError = false;

                $form.find(".slgf-field-error").empty();
                $form.find(".slgf-alertbox").hide().removeClass("success error").empty();

                $.each($form.serializeArray(), function (i, input) {
                    try {
                        if (slgf_params.fields[input.name]) {
                            if ($form.find("[name='" + input.name + "__label']").length) {
                                slgf_params.fields[input.name].label = $form.find("[name='" + input.name + "__label']").val();
                            }
                            isFieldError = self.isFieldError(input.value, slgf_params.fields[input.name]);
                            if (isFieldError) {
                                throw isFieldError;
                            }
                        }
                        formData[input.name] = input.value;
                    } catch (err) {
                        errors[input.name] = err;
                    }
                });

                if (Object.keys(errors).length) {
                    $.each(errors, function (key, errMsg) {
                        self.toggleFieldError($form, key, errMsg);
                    });
                    return;
                }

                if (Object.keys(formData).length) {
                    $form.find("button").addClass("is-loading").prop("disabled", true);
                    self.ajaxGetTime(formData, $form);
                }
            });

        },
        isFieldError: function (value, field) {
            var errorMsg = false;
            try {
                if (field.required && !value.length) {
                    throw field.label + " " + slgf_params.txt_err_required;
                }
                switch (field.type) {
                    case 'email':
                        if (value.length && !value.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)) {
                            throw field.label + " " + slgf_params.txt_err_invalid;
                        }
                        break;
                    case 'number':
                        if (value.length && !value.match(/^[0-9]+$/)) {
                            throw field.label + " " + slgf_params.txt_err_invalid;
                        }
                        break;
                }
            } catch (error) {
                errorMsg = error;
            }
            return errorMsg;
        },
        toggleFieldError: function ($form, fieldName, isFieldError) {
            var self = this;
            if (isFieldError) {
                $form.find("[name='" + fieldName + "']").next(".slgf-field-error").html(isFieldError).closest(".slgf-row").removeClass("valid").addClass("error");
            } else {
                $form.find("[name='" + fieldName + "']").next(".slgf-field-error").empty().closest(".slgf-row").removeClass("error").addClass("valid");
            }
        },
        ajaxGetTime: function (formData, $form) {
            var self = this;
            $.ajax({
                url: slgf_params.api_url,
                method: "GET",
                cache: false,
                crossDomain: true,
                success: function (response) {
                    try {
                        if (
                            typeof response.data === "undefined" ||
                            typeof response.data.datetime === "undefined" ||
                            typeof response.data.datetime.date_time_txt === "undefined"
                        ) {
                            throw "Date time is undefined";
                        }
                        formData['current_date_time'] = response.data.datetime.date_time_txt;
                        self.ajaxSubmit(formData, $form);
                    } catch (error) {
                        self.ajaxSubmit(formData, $form);
                    }
                },
                error: function () {
                    self.ajaxSubmit(formData, $form);
                }
            });
        },
        ajaxSubmit: function (formData, $form) {
            var self = this;
            $.ajax({
                url: slgf_params.ajax_url,
                data: formData,
                method: "POST",
                cache: false,
                success: function (response, textStatus) {
                    $form.find("button").removeClass("is-loading").prop("disabled", false);
                    if (response.msg.length) {
                        var msg_class = response.success ? "success" : "error";
                        $form.find(".slgf-alertbox").addClass(msg_class).html(response.msg).show();
                    }
                    if (response.success) {
                        $form.find(".slgf-field").val("");
                    }
                    $form.find(".slgf-row").removeClass("error valid");
                }
            });
        }
    }

    $(document).ready(function () {
        slgfLeadForm.init();
    });
})(jQuery);