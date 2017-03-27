// Verificador de tipo <input> tipo "date" para reemplazarlo con datepicker de Jquery UI
function datepicker() {
  if ( $('[type="date"]').prop('type') != 'date' ) {
    $('[type="date"]').datepicker({
      dateFormat: "yy-mm-dd"
    });
    $('[type="date"]').attr("placeholder", "aaaa-mm-dd");
  } 
}
