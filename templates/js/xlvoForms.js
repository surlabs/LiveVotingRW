const xlvoForms = {
    inputs:  [],
    hiddenId: "",
    parent,
    initMultipleInputs: function (id) {
        xlvoForms.parent = $("#" + id).parent();
        const input = $("#"+id);
        xlvoForms.parent.html("");  // Limpia el contenedor

        if(xlvoForms.inputs.length>0){
            for(let i = 0; i < xlvoForms.inputs.length; i++){
                const data =  xlvoForms.inputs[i];

                const newInput = xlvoForms.addMultipleInput(input, i+1, parseInt(data.id) ?? 0);

                xlvoForms.parent.append(newInput);

                $(".option-input").last().val(data.text ?? data);
            }
        } else {
            const newInput = xlvoForms.addMultipleInput(input, $(".option-input").length + 1, 0);
            xlvoForms.parent.append(newInput);
        }

        $(document).on("keyup" ,".option-input", function(){
            xlvoForms.updateMultipleInputs();
        });

    },

    initCorrectOrder: function (id, number_input_label, text_input_label) {
        xlvoForms.parent = $("#" + id).parent();
        const input = $("#"+id);

        xlvoForms.parent.html("");

        if(xlvoForms.inputs.length>0){
            for(let i = 0; i < xlvoForms.inputs.length; i++){
                const data =  xlvoForms.inputs[i];

                const newInput = xlvoForms.addCorrectOrderInput(input, i+1, parseInt(data.order) ?? 1, number_input_label, text_input_label);

                xlvoForms.parent.append(newInput);

                $(".option-input").last().val(data.text ?? data);
                $(".order-input").last().val(data.order ?? 1);
            }
        } else {
            const newInput = xlvoForms.addCorrectOrderInput(input, $(".option-input").length + 1, 1, number_input_label, text_input_label);

            xlvoForms.parent.append(newInput);
        }

        $(document).on("keyup" ,".order-input", function(){
            xlvoForms.updateOrderInputs();
        });

        $(document).on("keyup" ,".option-input", function(){
            xlvoForms.updateOrderInputs();
        });


    },

    initHiddenInput: function (id) {
        xlvoForms.hiddenId = "#" + id;

        let hiddenInput = $(this.hiddenId).val();
        if(hiddenInput.length!==0){
            try{
                xlvoForms.inputs = JSON.parse(hiddenInput.replace(/\\'/g, "\""));
            }catch (e){
                console.log("Parsing input error");
            }
        }
    },

    updateMultipleInputs: function(){
        xlvoForms.inputs = [];
        $(".option-input").each(function(i, element){
            if($(element).val() != ""){
                xlvoForms.inputs.push({
                    'id': $(element).attr("option-id") ?? 0,
                    'text': $(element).val(),
                });
            }
        });

        let jsonString = JSON.stringify(xlvoForms.inputs).replace(/"/g, "\\'");

        $(this.hiddenId).val(jsonString);

        return xlvoForms.inputs;
    },

    updateOrderInputs: function(){
        xlvoForms.inputs = [];
        $(".option-input").each(function(i, element){
            if($(element).val() != "" || $(element).parent().parent().find(".order-input").val() != ""){
                xlvoForms.inputs.push({
                    'id': $(element).attr("option-id") ?? 0,
                    'order': parseInt($(element).parent().parent().find(".order-input").val()) ?? 0,
                    'text': $(element).val(),
                });
            }
        });

        let jsonString = JSON.stringify(xlvoForms.inputs).replace(/"/g, "\\'");

        $(this.hiddenId).val(jsonString);

        return xlvoForms.inputs;
    },

    addMultipleInput: function (input, index, option_id) {
        const currentId = input.attr('id');
        const newId = currentId + '_' + index;

        const newInputHtml = $(input.prop("outerHTML"));
        newInputHtml.attr('id', newId);
        newInputHtml.addClass("option-input")

        if(option_id && option_id !== 0) {
            newInputHtml.attr('option-id', option_id);
        }

        return `
            <div class="multiple-input gap-1">
                <div class="w-full">
                    ${newInputHtml.prop("outerHTML")}  
                </div>
                <div class="action-buttons shrink-0">
                    <button type="button" name="Add" class="btn btn-link" onclick="xlvoForms.manageMultipleInputs('add', $(this).parent().parent().parent())"><span class="sr-only">Add</span><span class="glyphicon glyphicon-plus"></span></button>
                    <button type="button" name="Remove" class="btn btn-link" onclick="xlvoForms.manageMultipleInputs('remove', $(this).parent().parent())"><span class="sr-only">Remove</span><span class="glyphicon glyphicon-minus"></span></button>
                    <button type="button" name="Down" class="btn btn-link" onclick="xlvoForms.manageMultipleInputs('down', $(this).parent().parent())"><span class="sr-only">Down</span><span class="glyphicon glyphicon-chevron-down"></span></button>
                    <button type="button" name="Up" class="btn btn-link" onclick="xlvoForms.manageMultipleInputs('up', $(this).parent().parent())"><span class="sr-only">Up</span><span class="glyphicon glyphicon-chevron-up"></span></button>
                </div>
            </div>
        `;
    },

    addCorrectOrderInput: function (input, index, option_id, number_input_label, text_input_label) {
        const currentId = input.attr('id');
        const newId = currentId + '_' + index;

        const newInputHtml = $(input.prop("outerHTML"));
        newInputHtml.attr('id', newId);
        newInputHtml.addClass("option-input")

        if(option_id && option_id !== 0) {
            newInputHtml.attr('option-id', option_id);
        }

        return `
            <div class="order-input-container gap-1">
                <div class="inputs">
                    <div class="d-flex gap-1">
                        <div class="flex-col shrink-0">
                            ${number_input_label}
                            <input type="number" class="form-control form-control-sm order-input" size="2" min="1" max="999" value="${option_id}">
                        </div>
                        <div class="flex-col term-input">
                            ${text_input_label}
                            ${newInputHtml.prop("outerHTML")}  
                        </div>
                    </div>
                </div>
                <div class="action-buttons shrink-0">
                    <button type="button" name="Add" class="btn btn-link" onclick="xlvoForms.manageCorrectOrder('add', $(this).parent().parent().parent())"><span class="sr-only">Add</span><span class="glyphicon glyphicon-plus"></span></button>
                    <button type="button" name="Remove" class="btn btn-link" onclick="xlvoForms.manageCorrectOrder('remove', $(this).parent().parent())"><span class="sr-only">Remove</span><span class="glyphicon glyphicon-minus"></span></button>
                </div>
            </div>
        `;
    },

    manageMultipleInputs: function (action, parent) {
        switch (action) {
            case 'add':
                const firstInput = parent.find("input").first();
                const newIndex = $(".multiple-input").length + 1;
                const newInputHTML = firstInput.clone();
                newInputHTML.attr('value', "");
                const newInput = xlvoForms.addMultipleInput(newInputHTML, newIndex);
                parent.append(newInput);
                $(this.hiddenId).val(JSON.stringify(xlvoForms.updateMultipleInputs()));
                break;
            case 'remove':
                if ($(".multiple-input").length > 1) {
                    parent.remove();
                    xlvoForms.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvoForms.updateMultipleInputs()));


                }
                break;
            case 'up':
                if ($(".multiple-input").length > 1) {
                    parent.prev().before(parent);
                    xlvoForms.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvoForms.updateMultipleInputs()));
                    xlvoForms.updateMultipleInputs();

                }
                break;
            case 'down':
                if ($(".multiple-input").length > 1) {
                    parent.next().after(parent);
                    xlvoForms.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvoForms.updateMultipleInputs()));
                    xlvoForms.updateMultipleInputs();

                }
                break;
        }
    },
    manageCorrectOrder: function (action, parent) {
        switch (action) {
            case 'add':
                const firstInput = parent.find(".option-input").first();
                const newIndex = $(".order-input").length + 1;
                const newInputHTML = firstInput.clone();
                newInputHTML.attr('value', "");
                const newInput = xlvoForms.addCorrectOrderInput(newInputHTML, newIndex, newIndex);
                parent.append(newInput);
                xlvoForms.updateOrderInputs();
                break;
            case 'remove':
                if ($(".order-input").length > 1) {
                    parent.remove();
                    xlvoForms.updateOrderInputs();
                }
                break;
        }
    }
};