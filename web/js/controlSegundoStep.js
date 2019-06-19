$(document).ready(function() {
    //Remuevo la clase "next-step" en el primer paso para que no puedan pasar de step sin antes controlar los datos
    $('#stepwizard_step2_next').removeClass('next-step');

    //Valido el ingreso cuando hay un cambio en nombre calle
    $('#calle').keyup(function() {
        controlNombreCalle();
    })

    //Valido el ingreso cuando hay un cambio en numero calle
    $('#numero').keyup(function() {
        controlNumeroCalle();
    })

    //Valido el ingreso cuando hay un cambio en telefono calle
    $('#persona-telefonopersona').change(function() {
        controlTelefono();
    })
})

//Se ejecuta cada vez que hago click en el boton "siguiente" del segundo step
$('#stepwizard_step2_next').click(function() {
    var validoTelefono = controlTelefono(); //Valido el telefono
    var validoNombreCalle = controlNombreCalle(); //Valido el nombre
    var validoNumeroCalle = controlNumeroCalle(); //Valido el numero
    //Si los campos estan correcto agrego la clase "next-step" para pasar al siguiente step
    if (validoTelefono && validoNumeroCalle && validoNombreCalle) {
        $('#stepwizard_step2_next').addClass('next-step'); //Agrego la clase
    } else {
        $('#stepwizard_step2_next').removeClass('next-step'); //Remuevo la clase
    }
})

//Control ingreso telefono de contacto
function controlTelefono() {
    var telContacto = $('#persona-telefonopersona').val(); //Valor del telefono de contacto
    siguiente = false;
    if (telContacto == "") {
        //Si esta vacio agrego un borde de color rojo para indicar que hay un error
        $('#persona-telefonopersona').css('border', '1px solid #a94442');
        siguiente = false; //Seteo la variable
    } else {
        //En caso contrario borro el borde
        $('#persona-telefonopersona').css('border', 'none');
        siguiente = true; //Seteo la variable
    }
    return siguiente;
}

//Control ingreso numero de calle.
function controlNumeroCalle() {
    var numero = $('#numero').val(); //Valor del numero de la calle
    siguiente = false;
    if (numero == "") {
        //Si el campo esta vacio dejo un msj y agrego el color rojo en el borde para indicar que hay un error
        //Primero pruebo que no haya un msj ya existente
        if ($('#msjErrorNumero').children().length < 1) {
            $('#msjErrorNumero').append("<small style='color:#a94442'>Este campo es obligatorio.</small>");
            $('#numero').css('border', '1px solid #a94442');
            siguiente = false;
        }
    } else {
        //Si no esta vacio valido lo ingresado
        $('#msjErrorNumero').empty(); //Borro el msj de error
        if (/^([0-9])*$/.test(numero)) {
            //Si es correcto borro el borde para mostrar que no hay mas error
            $('#numero').css('border', 'none');
            siguiente = true; //Seteo la variable
        } else {
            //Si es incorrecto agrego el borde de color rojo e incrusto un msj de error
            $('#numero').css('border', '1px solid #a94442');
            $("#msjErrorNumero").append("<small style='color:#a94442'>Debe contener solo numeros.</small>");
            siguiente = false; //Seteo la variable
        }
    }
    return siguiente;
}

//Controla el ingreso del nombre de la clase
function controlNombreCalle() {
    var calle = $('#calle').val(); //Valor nombre de la calle
    patron = /^[A-Za-z0-9^ ]+$/; //Patron que debe respetar. El ingreso puede ser alfa numerico
    siguiente = false;
    if (calle == "") {
        //Si el campo esta vacio dejo un msj y agrego el color rojo en el borde para indicar que hay un error
        //Primero pruebo que no haya un msj ya existente
        if ($('#msjErrorCalle').children().length < 1) {
            $("#msjErrorCalle").append("<small style='color:#a94442'>Este campo es obligatorio.</small>");
            $('#calle').css('border', '1px solid #a94442');
            siguiente = false;
        }
    } else {
        //Si el campo no esta vacio valido lo ingresado
        $("#msjErrorCalle").empty(); //Borro el msj de campo vacio
        //Valido el patron ingresado
        if (patron.test(calle)) {
            //Si el patron es correcto elimino el borde rojo para indicar que esta correcto
            $('#calle').css('border', 'none');
            siguiente = true; //Seteo la variable en true
        } else {
            //Si el patron es incorrecto agrego al borde de color rojo para indicar el error
            $('#calle').css('border', '1px solid #a94442');
            $("#msjErrorCalle").append("<small style='color:#a94442'>Debe contener solo letras y numeros.</small>"); //y ademas agrego el msj del error
            siguiente = false;
        }
    }
    return siguiente;
}