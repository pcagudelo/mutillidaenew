$(()=>{

    // TOMA DE: https://es.stackoverflow.com/questions/445/c%C3%B3mo-obtener-valores-de-la-url-get-en-javascript

    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    
    let parameter = getParameterByName("page");

    if(parameter && parameter!="home.php"){
        $("#help-button-inc").append("<box-icon name='help-circle'></box-icon>");
        //Al dar click en Home de la pagina inicial dentro de cualquier otra pagina, generara que el parametro page obtenga el valor de home.php
    }else{
        $("#help-button-inc").append("<box-icon name='help-circle'></box-icon>");
        $("#help-button-inc").append("Ayuda");
    }




});