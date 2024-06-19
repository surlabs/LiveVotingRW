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
                const newInput = xlvo.addMultipleInput(input, i+1);
                xlvo.parent.append(newInput);
                $(".option-input").last().val(xlvo.inputs[i]);
            }
        } else {
            const newInput = xlvo.addMultipleInput(input, $(".option-input").length + 1);
            xlvo.parent.append(newInput);
        }

    },

    initHiddenInput: function (id) {
        xlvo.hiddenId = "#" + id;

        console.log(this.hiddenId, $(this.hiddenId).val());

        let hiddenInput = $(this.hiddenId).val();
        if(hiddenInput.length!==0){
            try{
                xlvo.inputs = JSON.parse(hiddenInput);
                console.log(hiddenInput);

            }catch (e){
                console.log("Parsing input error");
            }
        }

        $(document).on("change" ,".option-input", function(){
            xlvo.updateInputs();
        });

    },

    updateInputs: function(){
        xlvo.inputs = [];
        $(".option-input").each(function(i, element){
            if($(element).val() != ""){
                xlvo.inputs.push($(element).val());
            }
        });

        // Convierte el array a JSON
        let jsonString = JSON.stringify(xlvo.inputs);

        // Escapa las comillas simples en el JSON
        //jsonString = jsonString.replaceAll("\"", "'");

        // Asigna el JSON escapado al valor del input oculto
        $(this.hiddenId).val(jsonString);

        return xlvo.inputs;
    },

    addMultipleInput: function (input, index) {
        const currentId = input.attr('id');
        const newId = currentId + '_' + index;

        const newInputHtml = $(input.prop("outerHTML"));
        newInputHtml.attr('id', newId);
        newInputHtml.addClass("option-input")



        return `
            <div class="row multiple-input">
                <div class="col-sm-10">
                    ${newInputHtml.prop("outerHTML")}  
                </div>
                <div class="col-sm-2 action-buttons">
                    <button type="button" name="Add" class="btn btn-link" onclick="xlvo.manageMultipleInputs('add', $(this).parent().parent().parent())"><span class="sr-only">Add</span><span class="glyphicon glyphicon-plus"></span></button>
                    <button type="button" name="Remove" class="btn btn-link" onclick="xlvo.manageMultipleInputs('remove', $(this).parent().parent())"><span class="sr-only">Remove</span><span class="glyphicon glyphicon-minus"></span></button>
                    <button type="button" name="Down" class="btn btn-link" onclick="xlvo.manageMultipleInputs('down', $(this).parent().parent())"><span class="sr-only">Down</span><span class="glyphicon glyphicon-chevron-down"></span></button>
                    <button type="button" name="Up" class="btn btn-link" onclick="xlvo.manageMultipleInputs('up', $(this).parent().parent())"><span class="sr-only">Up</span><span class="glyphicon glyphicon-chevron-up"></span></button>
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
                $(this.hiddenId).val(JSON.stringify(xlvo.updateInputs()));


                break;
            case 'remove':
                if ($(".multiple-input").length > 1) {
                    parent.remove();
                    xlvo.updateInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateInputs()));


                }
                break;
            case 'up':
                if ($(".multiple-input").length > 1) {
                    parent.prev().before(parent);
                    xlvo.updateInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateInputs()));
                    xlvo.updateInputs();

                }
                break;
            case 'down':
                if ($(".multiple-input").length > 1) {
                    parent.next().after(parent);
                    xlvo.updateInputs();
                    $(this.hiddenId).val(JSON.stringify(xlvo.updateInputs()));
                    xlvo.updateInputs();

                }
                break;
        }
    }
};