# Versiones
## APJ 1.7.1812
- Meloras en APJCommon
  - Mejora en convertDateTime
## APJ 1.7.1808
- Mejoras en APJController
  - Se agregó el metodo setFieldType para asignar los valores de la propiedad fieldTypes[]
- Mejoras en APJModel
  - en el método basicValidation se agregó la asignación de los valores Default cuando los valores no están definidos, antes de validar
  - Se corrigieron los métodos min, max, sum y count, para permitir el uso de columnas no definidas en la estructura
- Nueva versión de APJHtmlGen
  - Mejoras en documentación de métodos
  - Se agregó el método style()
## APJ 1.7.0529
- Mejoras en APJController
  - Se mejoró el método setFormValues(), permitiendo definir un formulario y asignar valores por nombre
  - Se agregó el método selector(), que define un selector por id o por formulario y nombre
- Mejoras en APJModel
  - Se agregó el método objectTomodel()
  - Mejoras y Correcciones en el método basicValidacion()
  - Se mejoraron los métodos min(),max(),avg(),sum(),count() xon opción de aplicar condición.
## APJ 1.7.1804
- Mejoras en APJController
  - Se agregró la propiedad useParametersAsArray para definir como se comportan los parámetros en las llamadas a métodos por APJCall
- Corrección en basicValidation de APJModel
- Mejora en método attr de APJHtmlGen
## APJ 1.7.1707
- Mejoras en APJHtmlGen y APJController
- Se agregó el método like($field, $search) al modelo
- Se incorporo el manual de referencia en PDF
## APJ 1.7.1706
- Se creó APJHtmlGen: Generador de HTML para un código más limpio en en el controlador
- Cambios en APJPDO
  - Se agregó un instanciador singleton del objeto PDO
- Se cambió la numeración de versiones.
- Se modificó el proyecto de ejemplo
## APJ 1.6.1706
- Mejoras en la documentación de los métodos
- Mejoras en APJAutolad.php
- Cambios en APJController
  - Se eliminó el método setFormat(), es equivalente a format()
  - Se cambio el orden de los parámetros de format()
  - Mejoras en el renderizador de las vistas
  - Cambios de visibilidad en algunos métodos
  - Se eliminaron métodos comunes
  - Se modificó el método jShowDown(), ahora tiene un tercer parámetro con el contenido a mostrar
  - Se agregó el método arrayToForm($array) que permite pasar un array al objeto Form
- Mejoras en APJPDO
  - Se agregó el chequeo de la disponibilidad de PDO
  - Se arrojar una excepción de error en caso de fallo en la conexión
- Se modifico el proyecto ejemplo "Contactos"
  - Se utiliza el nuevo método jShowDown()
- Cambios en APJModel
  - Se cambió la visibilidad de algunos métodos y propiedades
  - Se eliminaron métodos comunes
- Se agregó la clase (trait) APJCommon.class
  - Contiene los metodos que mantentenían en común el controlador y el modelo
- Cambios menores en jqajaj.js
- Cambios en init.php
  - Se agregaron constantes y la autocarga de Vendor
- Cambios en APJSession
  - La duración de la sesión es configurable por la constante SESSION_LIMIT
## APJ 1.5.1704
- Mejoras en APJController
  - Se agregó el método showDown(input, container) a APJController  y su función javascript jShowDown
    input, container) a jqajaj, que permite hacer listas desplegables de búsqueda bajo un elemento como <input>.
- Se agregó la clase css showDown para definirla en el contenedor de la lista desplegable
- Se agregó jqajaj.min.css, la versión compactada de jqajaj.css
- Mejoras en APJModel
  - Se agregaron las propiedades toLower (array), toUpper (array) a APJModel, que permite definir que       columnas deben guardarse como mayúsculas o minúsculas de forma automática.
- Mejoras menores en comentario de métodos y propiedades
## APJ 1.4.1703
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
## APJ 1.3.1607
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
## APJ 1.0.1511
  - Primera versión 1.0.1511 Beta
