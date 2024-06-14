const xlvo = {
    initMultipleInputs: function (id) {
        const cont = $("#" + id).parent();
        const inputs = cont.find("input");

        cont.html("");  // Limpia el contenedor

        // Recorre todos los inputs existentes
        inputs.each(function (index) {
            const newInput = xlvo.addMultipleInput($(this), index + 1);
            cont.append(newInput);
        });

    },

    addMultipleInput: function (input, index) {
        const currentId = input.attr('id');
        const newId = currentId + '_' + index;

        // Clonar el input y actualizar su ID
        const newInputHtml = $(input.prop("outerHTML"));
        newInputHtml.attr('id', newId);

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
                }
                break;
            case 'up':
                if ($(".multiple-input").length > 1) {
                    parent.prev().before(parent);
                }
                break;
            case 'down':
                if ($(".multiple-input").length > 1) {
                    parent.next().after(parent);
                }
                break;
        }
    }
};