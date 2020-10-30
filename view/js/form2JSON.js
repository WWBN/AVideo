
function jsonToForm(json, helper) {
    console.log("jsonToForm json", json);
    console.log("jsonToForm helper", helper);
    $('#jsonElements').empty();
    if (typeof helper["avideoPluginDescription"] === "object") {
        labelText = helper["avideoPluginDescription"].name;
        labelDescription = helper["avideoPluginDescription"].description;
        div = $('<div />', {"class": 'alert alert-info'});
        strong = $('<strong />', {"html": labelText + "<hr>"});
        p = $('<p />', {"html": labelDescription});
        div.append(div);
        div.append(strong);
        div.append(p);
        $('#jsonElements').append(div);
    }
    $.each(json, function (i, val) {
        var div;
        var label;
        var input;

        var labelText = i;
        var labelDescription = '';
        if (typeof helper[i] === "object") {
            labelText = helper[i].name;
            labelDescription = helper[i].description;
            if (validURL(labelDescription)) {
                labelDescription = "<a data-toggle='tooltip' title=\"Click here for help\" href='" + labelDescription + "' target='_blank'><i class='fas fa-info-circle'></i></a>";
            } else if (labelDescription) {
                labelDescription = "<span data-toggle='tooltip' title=\"" + labelDescription + "\"><i class='fas fa-info-circle'></i></span>";
            }
            labelText += " " + labelDescription;
        }
        if (typeof (val) === "object") {// checkbox
            div = $('<div />', {"class": 'form-group'});
            label = $('<label />', {"html": labelText + ": "});
            if (val.type === 'textarea') {
                input = $('<textarea />', {"class": 'form-control jsonElement', "name": i, "pluginType": "object"});

                input.text(val.value);
            } else if (typeof val.type === 'object') {
                input = $('<select />', {"class": 'form-control jsonElement', "name": i, "pluginType": "select"});

                $.each(val.type, function (index, value) {
                    var select = "";
                    if (val.value == index) {
                        select = "selected";
                    }
                    $(input).append('<option value="' + index + '" ' + select + '>' + value + '</option>');
                });

            } else {
                input = $('<input />', {"class": 'form-control jsonElement', "type": val.type, "name": i, "value": val.value, "pluginType": "object"});
            }
            div.append(label);
            div.append(input);
        } else if (typeof (val) === "boolean") {// checkbox
            div = $('<div />', {"class": 'form-group'});
            label = $('<label />', {"class": "checkbox-inline"});
            input = $('<input />', {"class": 'jsonElement', "type": 'checkbox', "name": i, "value": 1, "checked": val});
            label.append(input);
            label.append(" " + labelText);
            div.append(label);
        } else {
            div = $('<div />', {"class": 'form-group'});
            label = $('<label />', {"html": labelText + ": "});
            input = $('<input />', {"class": 'form-control jsonElement', "name": i, "type": 'text', "value": val});
            div.append(label);
            div.append(input);
        }
        $('#jsonElements').append(div);
        $('.jsonElement').change(function () {
            var json = formToJson();
            json = JSON.stringify(json);
            $('#inputData').val(json);
        });

        $('[data-toggle="tooltip"]').tooltip({container: 'body'});
    })
}

function formToJson() {
    var json = {};
    $(".jsonElement").each(function (index) {
        var name = $(this).attr("name");
        var type = $(this).attr("type");
        var pluginType = $(this).attr("pluginType");
        if (pluginType === 'object') {
            if (typeof type === 'undefined') {
                type = 'textarea';
            }
            json [name] = {type: type, value: $(this).val()};
        } else if (pluginType === 'select') {
            console.log(type);
            type = {};
            $(this).find("option").each(function (i) {
                type[$(this).val()] = $(this).text();
            });
            console.log(type);
            json [name] = {type: type, value: $(this).val()};
        } else if (type === 'checkbox') {
            json [name] = $(this).is(":checked");
        } else {
            json [name] = $(this).val();
        }
    });
    //console.log(json);
    return json;
}