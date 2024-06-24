const xlvo = {
    inputs:  [],
    hiddenId: "",
    parent,
    initMultipleInputs: function (id) {
        xlvo.parent = $("#" + id).parent();
        const input = $("#"+id);
        xlvo.parent.html("");  // Limpia el contenedor

        if(xlvo.inputs.length>0){
            for(let i = 0; i < xlvo.inputs.length; i++){
                console.log(xlvo.inputs);
                const data =  xlvo.inputs[i];

                const newInput = xlvo.addMultipleInput(input, i+1, parseInt(data.id) ?? 0);

                xlvo.parent.append(newInput);

                $(".option-input").last().val(data.text ?? data);
            }
        } else {
            const newInput = xlvo.addMultipleInput(input, $(".option-input").length + 1, 0);
            xlvo.parent.append(newInput);
        }

        $(document).on("keyup" ,".option-input", function(){
            xlvo.updateMultipleInputs();
        });

    },

    initCorrectOrder: function (id) {
        xlvo.parent = $("#" + id).parent();
        const input = $("#"+id);

        xlvo.parent.html("");

        if(xlvo.inputs.length>0){
            for(let i = 0; i < xlvo.inputs.length; i++){
                console.log(xlvo.inputs);
                const data =  xlvo.inputs[i];

                const newInput = xlvo.addCorrectOrderInput(input, i+1, parseInt(data.order) ?? 1);

                xlvo.parent.append(newInput);

                $(".option-input").last().val(data.text ?? data);
                $(".order-input").last().val(data.order ?? 1);
            }
        } else {
            const newInput = xlvo.addCorrectOrderInput(input, $(".option-input").length + 1, 1);

            xlvo.parent.append(newInput);
        }

        $(document).on("keyup" ,".order-input", function(){
            xlvo.updateOrderInputs();
        });

        $(document).on("keyup" ,".option-input", function(){
            xlvo.updateOrderInputs();
        });


    },

    initHiddenInput: function (id) {
        xlvo.hiddenId = "#" + id;

        let hiddenInput = $(this.hiddenId).val();
        if(hiddenInput.length!==0){
            try{
                xlvo.inputs = JSON.parse(hiddenInput.replace(/\\'/g, "\""));
            }catch (e){
                console.log("Parsing input error");
            }
        }
    },

    updateMultipleInputs: function(){
        xlvo.inputs = [];
        $(".option-input").each(function(i, element){
            if($(element).val() != ""){
                xlvo.inputs.push({
                    'id': $(element).attr("option-id") ?? 0,
                    'text': $(element).val(),
                });
            }
        });

        let jsonString = JSON.stringify(xlvo.inputs).replace(/"/g, "\\'");

        $(this.hiddenId).val(jsonString);

        return xlvo.inputs;
    },

    updateOrderInputs: function(){
        xlvo.inputs = [];
        $(".option-input").each(function(i, element){
            console.log($(element).parent().parent().find(".order-input").val());
            if($(element).val() != "" || $(element).parent().parent().find(".order-input").val() != ""){
                xlvo.inputs.push({
                    'id': $(element).attr("option-id") ?? 0,
                    'order': parseInt($(element).parent().parent().find(".order-input").val()) ?? 0,
                    'text': $(element).val(),
                });
            }
        });

        let jsonString = JSON.stringify(xlvo.inputs).replace(/"/g, "\\'");

        $(this.hiddenId).val(jsonString);

        return xlvo.inputs;
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
                    <button type="button" name="Add" class="btn btn-link" onclick="xlvo.manageMultipleInputs('add', $(this).parent().parent().parent())"><span class="sr-only">Add</span><span class="glyphicon glyphicon-plus"></span></button>
                    <button type="button" name="Remove" class="btn btn-link" onclick="xlvo.manageMultipleInputs('remove', $(this).parent().parent())"><span class="sr-only">Remove</span><span class="glyphicon glyphicon-minus"></span></button>
                    <button type="button" name="Down" class="btn btn-link" onclick="xlvo.manageMultipleInputs('down', $(this).parent().parent())"><span class="sr-only">Down</span><span class="glyphicon glyphicon-chevron-down"></span></button>
                    <button type="button" name="Up" class="btn btn-link" onclick="xlvo.manageMultipleInputs('up', $(this).parent().parent())"><span class="sr-only">Up</span><span class="glyphicon glyphicon-chevron-up"></span></button>
                </div>
            </div>
        `;
    },

    addCorrectOrderInput: function (input, index, option_id) {
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
                            Correct position
                            <input type="number" class="form-control form-control-sm order-input" size="2" min="1" max="999" value="${option_id}">
                        </div>
                        <div class="flex-col term-input">
                            Term
                            ${newInputHtml.prop("outerHTML")}  
                        </div>
                    </div>
                </div>
                <div class="action-buttons shrink-0">
                    <button type="button" name="Add" class="btn btn-link" onclick="xlvo.manageCorrectOrder('add', $(this).parent().parent().parent())"><span class="sr-only">Add</span><span class="glyphicon glyphicon-plus"></span></button>
                    <button type="button" name="Remove" class="btn btn-link" onclick="xlvo.manageCorrectOrder('remove', $(this).parent().parent())"><span class="sr-only">Remove</span><span class="glyphicon glyphicon-minus"></span></button>
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
                const newInput = xlvo.addMultipleInput(newInputHTML, newIndex);
                parent.append(newInput);
                $(this.hiddenId).val(JSON.stringify(xlvo.updateMultipleInputs()));
                break;
            case 'remove':
                if ($(".multiple-input").length > 1) {
                    parent.remove();
                    xlvo.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateMultipleInputs()));


                }
                break;
            case 'up':
                if ($(".multiple-input").length > 1) {
                    parent.prev().before(parent);
                    xlvo.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateMultipleInputs()));
                    xlvo.updateMultipleInputs();

                }
                break;
            case 'down':
                if ($(".multiple-input").length > 1) {
                    parent.next().after(parent);
                    xlvo.updateMultipleInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateMultipleInputs()));
                    xlvo.updateMultipleInputs();

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
                const newInput = xlvo.addCorrectOrderInput(newInputHTML, newIndex, newIndex);
                parent.append(newInput);
                xlvo.updateOrderInputs();
                break;
            case 'remove':
                if ($(".order-input").length > 1) {
                    parent.remove();
                    xlvo.updateOrderInputs();
                }
                break;
        }
    }
};