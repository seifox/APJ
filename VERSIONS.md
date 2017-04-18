# Versiones
## APJ 1.15.11
  - Primera versión 1.15.11 Beta
## APJ 1.16.07
- Se cambio el nombre de el objeto PDO de MyPDO.class a APJPDO.class
- Se cambio el nombre del archivo MyLog.class a APJLog.class
- Mejoras en jqajaj.js 
	- Estilos reemplazados por clases
	- Se agregaron funcionex ajax
	- Se quitaron los mensajes a la consola
- Mejoras en el Autoloader
	- mejoras menores
- Mejoras en APJController
	- Propiead $canRender: define si se puede mostrar la vista
	- Asignación automática del formulario al objeto $Form
	- Mejoras en los metodos: 
		Render()
		Session()
		getForm()
		modelToFrom()
		convertDateTime()
		options()
	- Se agregaron los siguiente metodos: 
		getParameters(): Permite el paso de parametros adicionales al metodo APJSubmit()
		formObjectToModel(): Asigna las propiedades coincidentes del objeto Form al Modelo
		setFormValues(): Asigna los valores del formulario segun objeto Form o Array
		_unsetAction(): Uso interno, limpia las propiedades de acciones
		format() y setFormat(): Formatea el valor según el tipos de datos definido en el archivo init.php
		showMessages(): Despliega un array de Errores, Advertencias o Información
		showErrors(): Despliega un array de errores en una alerta de Error (jError)
		showWarnings(): Despliega un array de advertencias de un modelo en una alerta de Advertencia (jWarning)
		arrayToObject(): Devuelve un objeto a partir de un array
		_getAlias(): Uso interno. Obtiene los alias o comment de un campo de un modelo.

- Mejoras en APJModel
	- Se agregaron las propiedades:
		$alias: Array con los alias/comentario de las columnas del modelo
		$values: Array asociativo de valores para update, insert y otros
		$where: cadena de condiciones para consultas, proveniente de condiciones mixtas (arreglos, id, vacio, cadena)
	- Se agregaron los metodos:
		setAlias(): Define los Alias manualmente por medio de un array
		showStructure(): Muestra la estructura de la tabla
		clearValues(): Inicializa la propiedad $variables
		_values(): Uso interno. Asigna valores a la propiedad $values con campos coincidentes de la estructura
		_condition(): Uso interno. Analiza las condiciones mixtas de una consulta para retornar la propiedad $where como una cadena de condición
	- Mejoras en los metodos:
		defineModel()
		showModel()
		__get()
		basicValidation()
		setFormat()
		verifyDate()
		update()
		insert()
		replace()
		delete()
		find()
		all()
		min(), avg(), sum(), count()

## APJ 1.17.03
- Mejoras en APJController
	- Se agregaron propiedades de paginación de arreglos
	- La propiedad $where se cambio a privada
	- Mejoras en metodos:
		render()
		getContent()
		convertDateTime()
	- Se agregaron los metodos:
		clearForm(): Limpia el objeto Form
		paging(): Retorna un arreglo con resultado paginados de un arreglo de datos
	- El metodo setFormat() apunta a format() (era redundante, se mantuvo por compatibilidad)
- Mejoras en APJModel
	- Se agregaron propiedades de paginación de datos
	- Se agregaron los metodos:
		paging() que retorna un array con resultado de datos paginados
		currentDateTime() Retorna la fecha y hora actual según formato
	- Mejoras en metodos:
		_condition()
- Mejoras en APJPDO
	- Se agregaron los metodos:
		query(): Ejecuta una consulta sin enlazar parámetros (binding)
	- Mejoras en metodos:
		execute()
		getValue()

## APJ 1.17.04
- Mejoras en APJController
  - Se agregó el método showDown(input, container) a APJController  y su función javascript jShowDown
    input, container) a jqajaj, que permite hacer listas desplegables de búsqueda bajo un elemento como <input>.
  - Se agregó la clase css showDown para definirla en el contenedor de la lista desplegable
  - Se agregó jqajaj.min.css, la versión compactada de jqajaj.css
  - Se agregaron las propiedades toLower (array), toUpper (array) a APJModel, que permite definir que       columnas deben guardarse como mayúsculas o minúsculas de forma automática.
  - Mejoras menores en comentario de métodos y propiedades
