// JavaScript Document
function validar_formulario(oForm,excepciones,adicional) {
	var frm_elements = oForm.elements;
	exelem = excepciones.split(',');
	for(i=0; i<frm_elements.length; i++) {
		x=i+'';
		field_type = frm_elements[i].type.toLowerCase();
		switch(field_type) {
			case "text":
			case "password":
			case "textarea":
				if (frm_elements[i].value=="" && !inarray(x,exelem)) {
					msg="El campo " + frm_elements[i].name + " es obligatorio.";
					alert(msg);
					return false;
				}
				break;
			case "select-one":
			case "select-multi":
				if (frm_elements[i].selectedIndex == 0 && !inarray(x,exelem)) {
					msg="El campo " + frm_elements[i].name + " es obligatorio.";
					alert(msg);
					return false;
				}
				break;
			default:
				break;
		}
	}
	if (adicional) {
		if (validacion_adicional()) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}
function inarray(element,arry) {
	for (x=0;x<=arry.length;x++) {
		if (element===arry[x]) {
			return true;
		}
	}
	return false;
}