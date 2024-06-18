const xlvo = {


    hiddenId: "",
    parent,
    initMultipleInputs: function (id) {
        xlvo.parent = $("#" + id).parent();
        const input = $("#"+id);
        //const inputs = cont.find("input");

        xlvo.parent.html("");  // Limpia el contenedor

        const newInput = xlvo.addMultipleInput(input, $(".option-input").length + 1);
        xlvo.parent.append(newInput);


        console.log("Cargo input");

    },

    initHiddenInput: function (id) {
        this.hiddenId = "#" + id;

        //Comprobamos si este input tiene datos, si los datos son un array y si lo son, generamos los inputs
        let hiddenInput = $(this.hiddenId).val();
        if(hiddenInput.length!==0){
            try{
                xlvo.parent.html("");
                hiddenInput = JSON.parse(hiddenInput);
                console.log(hiddenInput);
                for(let i = 0; i < hiddenInput.length; i++){
                    const input = $("#"+id);
                    const newInput = xlvo.addMultipleInput(input, i+1);
                    xlvo.parent.append(newInput);
                    $(".option-input").last().val(hiddenInput[i]);
                }
            }catch (e){
                console.log("Parsing input error");
            }
        }

        $(document).on("change" ,".option-input", function(){
            xlvo.updateInputs();
        });

    },

    updateInputs: function(){
        let multipleInputs= [];
        $(".option-input").each(function(i, element){
            if($(element).val() != ""){
                multipleInputs.push($(element).val());
            }
        });
        $(this.hiddenId).val(JSON.stringify(multipleInputs));

    },

    addMultipleInput: function (input, index) {
        const currentId = input.attr('id');
        const newId = currentId + '_' + index;

        // Clonar el input y actualizar su ID
        const newInputHtml = $(input.prop("outerHTML"));
        newInputHtml.attr('id', newId);
        newInputHtml.addClass("option-input")
        xlvo.updateInputs();



        return `
            <div class="row multiple-input">
                <div class="col-sm-10">
                    ${newInputHtml.prop("outerHTML")}  
                </div>
                <div class="col-sm-2">
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
                //Editamos el atributo name para añadir un _2, _3, etc.
                //newInputHTML.attr('name', newInputHTML.attr('name') + '_' + newIndex);
                const newInput = xlvo.addMultipleInput(newInputHTML, newIndex);
                parent.append(newInput);

                break;
            case 'remove':
                if ($(".multiple-input").length > 1) {
                    parent.remove();
                    xlvo.updateInputs();
                }
                break;
            case 'up':
                if ($(".multiple-input").length > 1) {
                    parent.prev().before(parent);
                    xlvo.updateInputs();
                }
                break;
            case 'down':
                if ($(".multiple-input").length > 1) {
                    parent.next().after(parent);
                    xlvo.updateInputs();
                }
                break;
        }
    }
};