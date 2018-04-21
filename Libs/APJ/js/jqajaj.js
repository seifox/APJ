/*
 Plugin jQuery PHP
 Plugin jAlert
 Version 1.8.170727
 require jQuery v1.5.x or grather
*/
BlinkId=0;
jAlertClosed=true;
//Jquery
(function($) {
  $.extend({
    ajaj: function (url, params) {
      // Ejecuta un POST AJAX con JSON
      timeout = (typeof(timeout)=='undefined') ? timeout=10000 : timeout;
      ajaj.beforeSend();
      $.ajax({
        url: url,
        async: true,
        timeout: timeout,
        type: 'POST',
        dataType: 'json',
        data: params
      })
      .done(function(response, textStatus, jqXHR){
        return ajaj.success(response, textStatus);
      })
      .fail(function(jqXHR, textStatus, errorThrown){
        return ajaj.error(jqXHR, textStatus, errorThrown);
      })
      .always(function(jqXHR, textStatus, errorThrown){
        return ajaj.complete(jqXHR, textStatus);
      });
    }
  })
  ajaj = {
    // Antes de enviar
    beforeSend:function() {
      jBusy();
      return true;
    },
    /* Exito: objeto response, 
       Analiza la respuesta AJAX enviadas desde PHP
    */
    success:function (response, textStatus) {
      jClose();
      // llama los metodos jQuery y define el selector
      for (var i=0;i<response['query'].length; i++) {
        var selector = $(response['query'][i]['selector']);
        var methods = response['query'][i]['method'];
        var arguments = response['query'][i]['arguments'];
        for (var m=0;m<methods.length; m++) { 
          try {
            var method = methods[m];
            var argument = arguments[m];
            if (method && method!= '' && method!= 'undefined') {
              switch (true) {
                // para 'ready', 'map', 'queue'
                case (method == 'ready' || method == 'map' || method == 'queue'):
                  selector = selector[method](window[argument[0]]);
                  break;
                // para 'bind' and 'one'
                case ((method == 'bind' || method == 'one') && argument.length == 3):
                  selector = selector[method](argument[0],argument[1],window[argument[2]]);
                  break;
                // para 'toggle' and 'hover'
                case ((method == 'toggle' || method == 'hover') && argument.length == 2):
                  selector = selector[method](window[argument[0]],window[argument[1]]);
                  break;
                // para 'filter'
                case (method == 'filter' && argument.length == 1):
                  // trata de ejecutar el metodo
                  if (window[argument[0]] && window[argument[0]] != '' && window[argument[0]] != 'undefined') {
                    selector = selector[method](window[argument[0]]);
                  } else {
                    selector = selector[method](argument[0]);
                  }
                  break;
                // para efectos con callback
                case ((method == 'show' || method == 'hide'
                  || method == 'slideDown' || method == 'slideUp' || method == 'slideToggle'
                  || method == 'fadeIn' || method == 'fadeOut') && argument.length == 2):
                  selector = selector[method](argument[0],window[argument[1]]);
                  break;
                // para eventos con callback
                case ((method == 'blur' || method == 'change' || method == 'click' 
                  || method == 'dblclick' || method == 'error' || method == 'focus'
                  || method == 'keydown'|| method == 'keypress' || method == 'keyup'
                  || method == 'load' || method == 'unload' || method == 'mousedown' 
                  || method == 'mousemove' || method == 'mouseout' || method == 'mouseover' 
                  || method == 'mouseup' || method == 'resize' || method == 'scroll'
                  || method == 'select' || method == 'submit') && argument.length == 1):
                  selector = selector[method](window[argument[0]]);
                  break;
                // para 'fadeTo' con callback
                case (method == 'fadeTo' && argument.length == 3):
                  selector = selector[method](argument[0],argument[1],window[argument[2]]);
                  break;
                // para 'animate' con callback
                case (method == 'animate' && argument.length == 4):
                  selector = selector[method](argument[0],argument[1],argument[2],window[argument[3]]);
                  break;
                // El resto
                case (argument.length == 0):
                  selector = selector[method]();
                  break;
                case (argument.length == 1):
                  selector = selector[method](argument[0]);
                  break;
                case (argument.length == 2):
                  selector = selector[method](argument[0],argument[1]);
                  break;
                case (argument.length == 3):
                  selector = selector[method](argument[0],argument[1],argument[2]);
                  break;
                case (argument.length == 4):
                  selector = selector[method](argument[0],argument[1],argument[2],argument[3]);
                  break;
                default:
                  selector = selector[method](argument);
                  break;
              }
            }
          } catch (error) {
            msg='Acción: $("'+ response['query'][i]['selector'] +'").'+ method +'("'+ argument +'")<br>error: ' + error.message;
            jError(msg,'Error en Respuesta');
          }
        }
      }

      // Acciones predefinidas nombradas como Metodos de respuestas en PHP
      $.each(response['action'], function (func, params) {
        for (var i=0;i<params.length; i++) {
          try {
            ajaj[func](params[i]);
          } catch (error) {
            msg='Acción: ' + func + '('+ params[i] +')<br>error: ' + error.message;
            jError(msg);
          }
        }
      });
    },
    //error: jqXHR, textStatus, errorThrown
    error:function (jqXHR, textStatus, errorThrown) {
      var StatusCode = jqXHR.status;
      switch (StatusCode) {
        case 0:
          statusDesc = "No conectado o Tiempo de espera agotado.<br>Verifique conexión de red";
          break;
        case 200:
          statusDesc = "Error de respuesta desde el servidor o sesion finalizada";
          sessionEnded();
          break;
        case 404: 
          statusDesc = "El controlador no fue encontrado.";
          break;
        case 408: 
          statusDesc = "Tiempo de espera agotado";
          break;
        case StatusCode>=500: 
          statusDesc = "Error del servidor.";
          break;
        default:
          statusDesc = "Error desconocido.<br>"+errorThrown+"<br>"+textStatus;
      }
      var title="Error "+StatusCode;
      jError(statusDesc,title);
      return false;
    },
    // Completado
    complete:function(jqXHR, textStatus) {
      return true;
    },

    // Funciones estáticas llamadas desde php
    // Muestra información
    jInfo:function(obj) {
      var message = obj.msg      || "";
      var title = obj.title      || "";
      var callback = obj.callback || "";
      var params = obj.params   || [];
      jInfo(message,title,callback,params);
    }, 
    // Muestra una advertencia
    jWarning:function(obj) {
      var message = obj.msg      || "";
      var title = obj.title      || "";
      var callback = obj.callback || "";
      var params = obj.params   || [];
      jWarning(message,title,callback,params);
    }, 
    // Muestra un error
    jError:function(obj) {
      var message = obj.msg      || "";
      var title = obj.title      || "";
      var callback = obj.callback || "";
      var params = obj.params   || [];
      jError(message,title,callback,params);
    }, 
    // Confirma una accion
    jConfirm:function(obj) {
      var message = obj.msg      || "";
      var title = obj.title      || "";
      var callback = obj.callback || "";
      var params = obj.params   || [];
      jConfirm(message,title,callback,params);
    }, 
    // Solicita un dato
    jPrompt:function(obj) {
      var message = obj.msg || "";
      var title = obj.title || "";
      var callback = obj.callback || "";
      jPrompt(message,title,callback,params);
    }, 
    // Muestra ventana de Proceso (style puede ser 'blink')    
    jProcess:function(obj) {
      var message = obj.msg || "";
      var title = obj.title || "";
      var style = obj.style || "";
      jProcess(message,title,style);
    }, 
    jBusy:function() {
      jBusy();
    }, 
    // Cierre la ventana de jAlert
    jClose:function() {
      jClose();
    },

    // asigna valores
    setData:function(obj) {
      var callback = obj.callback || "defaultCallBack";
      ajaj.data[callback](obj.k, obj.v);
    },
    
    // Ejecuta un script
    Script:function(obj) {
      var func = obj.scr || '';
      eval(func);
    },

  };

// jAlert
  $.alerts = {
    info: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Información";
      $.alerts._render('info',mensaje,titulo,function(result) {
        if((func = $.alerts._toFunction(callback)) && result ) {
          func.apply(null, params);
        }
      });
    },
    warning: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Advertencia";
      $.alerts._render('warning',mensaje,titulo,function(result) {
        if((func = $.alerts._toFunction(callback)) && result ) {
          func.apply(null, params);
        }
      });
    },
    error: function (mensaje,titulo,callback,callback,params) {
      if (titulo==null) titulo="Error";
      $.alerts._render('error',mensaje,titulo,function(result) {
        if((func = $.alerts._toFunction(callback)) && result ) {
          func.apply(null, params);
        }
      });
    },
    confirm: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Confirmar";
      $.alerts._render('confirm',mensaje,titulo,function(result) {
        if((func = $.alerts._toFunction(callback)) && result ) {
          func.apply(null, params);
        }
      });
    },
    process: function (mensaje,titulo,style) {
      if (titulo==null) titulo="Proceso en curso";
      $.alerts._render('process',mensaje,titulo,'',style);
    },
    prompt: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Introdusca un valor";
      $.alerts._render('prompt',mensaje,titulo,function(result) {
        if((func = $.alerts._toFunction(callback)) && result ) {
          if(params.length) params.push(result); else params=[result];
          func.apply(null, params);
        }
      });
    },
    busy: function () {
      if (jAlertClosed) {
        $.alerts._busy();
      }
    },
    _busy: function() {
      $.alerts._close();
      image="Libs/APJ/images/busy32.gif";
      winW = window.innerWidth;
      winH = window.innerHeight;
      dialogW = 32;
      dialogH = 32;
      dialogL=(winW/2)-(dialogW*.5);
      dialogT=(winH/2)-(dialogH*.5);
      $(document.body).append('<div id="busyoverlay"></div>'+
      '<div id="busybox"><img src="'+image+'"><div>'+
      '<div id="dialogboxbody"></div>');    
      $('#busyoverlay').css({
        height: winH+'px',
      });
      $('#busybox').css({
        width: dialogW+'px',
        height: dialogH+'px',
        left: dialogL+'px',
        top: dialogT+'px'
      });
      jAlertClosed=false;
      $('#busyoverlay').show();
      $('#busybox').show();
    },
    _render: function(Type,Message,Header,callback,style){
      $.alerts._close();
      // Crea Los contenedores de mensajes
      $(document.body).append(
        '<div id="dialogoverlay"></div>'+
        '<div id="dialogbox"><div>'+
        '<div id="dialogboxhead"></div>'+
        '<div id="dialogboxbody"></div>'+
        '<div id="dialogboxfoot"></div>'+
        '</div>'+'</div>');
      text='<div id="dialogboxtext">'+Message;
      // Calcula tamaños y ubicación
      winW = window.innerWidth;
      winH = window.innerHeight;
      dialogW = winW/4;
      dialogH = winH/4;
      if (dialogW<300) dialogW=300;
      dialogL=(winW/2)-(dialogW*.5);
      dialogT=(winH/2)-(dialogH*.5);
      // Crea dialogo según tipo
      switch (Type) {
        case 'info':
          Header = (Header.length>0) ? Header : "Información";
          image="Libs/APJ/images/info32.png";
          clase="infoBody";
          $('#dialogboxfoot').html('<button id="dialogOk">Aceptar</button>');
          $("#dialogOk").click( function() {
            $.alerts._close();
            if( callback ) callback(true);
          });
          break;
        case 'warning':
          Header = (Header.length>0) ? Header : "Advertencia";
          image="Libs/APJ/images/warning32.png";
          clase="warningBody";
          $('#dialogboxfoot').html('<button id="dialogOk">Aceptar</button>');
          $("#dialogOk").click( function() {
            $.alerts._close();
            if( callback ) callback(true);
          });
          break;
        case 'error':
          Header = (Header.length>0) ? Header : "Error";
          image="Libs/APJ/images/error32.png";
          clase="errorBody";
          $('#dialogboxfoot').html('<button id="dialogOk">Aceptar</button>');
          $("#dialogOk").click( function() {
            $.alerts._close();
            if( callback ) callback(true);
          });
          break;
        case 'confirm':
          Header = (Header.length>0) ? Header : "Confirmaci&oacute;n";
          Param = typeof Param !== 'undefined' ? Param : false;
          image="Libs/APJ/images/question32.png";
          clase="confirmBody";
          $('#dialogboxfoot').html('<button id="dialogOk">Si</button> <button id="dialogCancel">No</button>');
          $("#dialogOk").click( function() {
            $.alerts._close();
            if( callback ) callback(true);
          });
          $("#dialogCancel").click( function() {
            $.alerts._close();
            if( callback ) callback(false);
          });
          break;
        case 'prompt':
          Header = (Header.length>0) ? Header : "Introdusca un valor";
          image="Libs/APJ/images/question32.png";
          clase="promptBody";
          text=text+': <input id="prompt_value" placeholder="valor">';
          $('#dialogboxfoot').html('<button id="dialogOk">Aceptar</button> <button id="dialogCancel">Cancelar</button>');
          $("#dialogOk").click( function() {
            var val = $("#prompt_value").val();
            $.alerts._close();
            if( callback ) callback( val );
          });
          $("#dialogCancel").click( function() {
            $.alerts._close();
            if( callback ) callback( null );
          });
          break;
        case 'process':
          Header = (Header.length>0) ? Header : "Proceso en curso";
          image="Libs/APJ/images/process32.png";
          clase="processBody";
          break;
      }
      // Aplica formatos css
      $('#dialogoverlay').css({
        height: winH+'px',
      });
      $('#dialogbox').css({
        width: dialogW+'px',
        left: dialogL+'px',
        top: dialogT+'px'
      });
      $('#dialogboxhead').css("background-image","url("+image+")");
      $('#dialogboxbody').addClass(clase);
      $('#dialogboxhead').html(Header);
      $('#dialogboxbody').html(text+'</div>');
      $.alerts._open();
      if (style=='blink') {
        BlinkId=setInterval($.alerts._blinker,1500);
      }
    },
    // Abre dialogo
    _open: function() {
      jAlertClosed=false;
      $('#dialogoverlay').show();
      $('#dialogbox').show();
    },
    // Cierra dialogo
    _close: function() {
      jAlertClosed=true;
      $('#dialogbox').hide().remove();
      $('#busybox').hide().remove();
      $('#dialogoverlay').hide().remove();
      $('#busyoverlay').hide().remove();
      if (BlinkId) {
        clearInterval(BlinkId);
        BlinkId=0;
      }
    },
    // Efecto Blink
    _blinker: function() {
      $('#dialogboxtext').stop().fadeOut(500).fadeIn(500);
    },
    // Retorna la función
    _toFunction: function(func) {
      switch (typeof(func)) {
        case 'string':
          return window[func];
        case 'function':
          return func;
        default:
          return false;
      }
    }
  }
  /*
   Funcion que convierte el formulario en una array de objetos pasado al callback pre-submit 
   ejemplo de resultado: [{name: 'campo1', value: 'mivalor'}, {}]
  */
  $.fn.formToArray = function(semantic) {
      var arr = [];
      if (this.length == 0) {
        return arr;
      }
      var form = this[0];
      var els = semantic ? form.getElementsByTagName('*') : form.elements;
      if (!els) {
        return arr;
      }
      for(var i=0, max=els.length; i < max; i++) {
        var elem = els[i];
        var nam = elem.name;
        if (!nam) {
          continue;
        }
        if (semantic && form.clk && elem.type == "image") {
          // administra campos de imagen si semantic == true
          if(!elem.disabled && form.clk == elem) {
            arr.push({name: nam+'.x', value: form.clk_x}, {name: nam+'.y', value: form.clk_y});
            continue;
          }
        }
        var val = $.fieldValue(elem, true);
        if (val && val.constructor == Array) {
          for(var j=0, jmax=val.length; j < jmax; j++) {
            arr.push({name: nam, value: val[j]});
          }
        } else if (val !== null && typeof val != 'undefined') {
          arr.push({name: nam, value: val});
        }
      }
      if (!semantic && form.clk) {
        // input type=='image' no es encontrado en elem se maneja aquí
        var inputs = form.getElementsByTagName("input");
        for(var i=0, max=inputs.length; i < max; i++) {
          var input = inputs[i];
          var nam = input.name;
          if(nam && !input.disabled && input.type == "image" && form.clk == input) {
            arr.push({name: nam+'.x', value: form.clk_x}, {name: nam+'.y', value: form.clk_y});
          }
        }
      }
      return arr;
  };
  /*
    Helper para serializar el formulario en una cadena
    retorna campo1=valor1&amp;campo2=vbalor2
  */
  $.fn.formSerialize = function(semantic) {
    return $.param(this.formToArray(semantic));
  };

  /*
    Helper para serializar los campos del objeto jQuery en una cadena
    retorna campo1=valor1&amp;campo2=vbalor2
  */
  $.fn.fieldSerialize = function(successful) {
    var arr = [];
    this.each(function() {
      var nam = this.name;
      if (!nam) {
        return;
      }
      var val = $.fieldValue(this, successful);
      if (val && val.constructor == Array) {
        for (var i=0,max=val.length; i < max; i++) {
          arr.push({name: nam, value: val[i]});
        }
      } else if (val !== null && typeof val != 'undefined') {
        arr.push({name: this.name, value: val});
      }
    });
    //hand off to jQuery.param for proper encoding
    return $.param(arr);
  };

  /* 
    Helper para retornar los valores de los elementos seleccionado
    retorna los valores en un array
  */
  $.fn.fieldValue = function(successful) {
    for (var val=[], i=0, max=this.length; i < max; i++) {
      var elem = this[i];
      var val = $.fieldValue(elem, successful);
      if (val === null || typeof val == 'undefined' || (val.constructor == Array && !val.length)) {
        continue;
      }
      val.constructor == Array ? $.merge(val, val) : val.push(val);
    }
    return val;
  };

  /*
   Retorna el valor del campo
  */
  $.fieldValue = function(elem, successful) {
    var nam = elem.name, typ = elem.type, tag = elem.tagName.toLowerCase();
    if (typeof successful == 'undefined') {
      successful = true;
    }
    if (successful && (typ == 'checkbox' || typ == 'radio')) {
      if (elem.checked) {
        return (elem.value) ? elem.value : "1";
      } else {
        return "0";
      }
    }
    if (successful && (!nam || elem.disabled || typ == 'reset' || typ == 'button' || (typ == 'submit' || typ == 'image') && elem.form && elem.form.clk != elem || tag == 'select' && elem.selectedIndex == -1)) {
      return null;
    }
    if (tag == 'select') {
      var index = elem.selectedIndex;
      if (index < 0) {
        return null;
      }
      var arr = [], ops = elem.options;
      var one = (typ == 'select-one');
      var max = (one ? index+1 : ops.length);
      for(var i=(one ? index : 0); i < max; i++) {
        var op = ops[i];
        if (op.selected) {
          // ***** Verificar si requiere filtrar tipo de browser *******
          var val = !(op.attributes['value'].specified) ? op.text : op.value;
          if (one) {
            return val;
          }
          arr.push(val);
        }
      }
      return arr;
    }
    return elem.value;
  };
  // Helper para limpiar el formulario
  $.fn.clearForm = function() {
    return this.each(function() {
      $('input,select,textarea', this).clearFields();
    });
  };
  // Limpia el elemento especifico
  $.fn.clearFields = $.fn.clearInputs = function() {
    return this.each(function() {
      var typ = this.type, tag = this.tagName.toLowerCase();
      if (typ == 'text' || typ == 'password' || tag == 'textarea') {
        this.value = '';
      } else if (typ == 'checkbox' || typ == 'radio') {
        this.checked = false;
      } else if (tag == 'select') {
        this.selectedIndex = -1;
      }
    });
  };
  // Resetea el formulario
  $.fn.resetForm = function() {
    return this.each(function() {
    // Verifica que reset no se una funcion u objeto
    if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
      this.reset();
    });
  };
  // Habiita/Deshabilita un elemento
  $.fn.enable = function(boo) { 
    if (boo == undefined) {
      boo = true;
    }
    return this.each(function() { 
      this.disabled = !boo 
    });
  };
  // Marca o desmarca checkbox, radio y select del elemento seleccionado
  $.fn.select = function(select) {
    if (select == undefined) {
      select = true;
    }
    return this.each(function() { 
      var type = this.type;
      if (type == 'checkbox' || type == 'radio') {
        this.checked = select;
      } else if (this.tagName.toLowerCase() == 'option') {
        var $sel = $(this).parent('select');
        if (select && $sel[0] && $sel[0].type == 'select-one') {
          $sel.find('option').select(false);
        }
        this.selected = select;
      }
    });
  };
  // helper para log por consola (activado por $.fn.ajajSubmit.debug = true)
  function log() {
    if ($.fn.ajajSubmit.debug && window.console && window.console.log) {
      window.console.log('[jqajaj] ' + Array.prototype.join.call(arguments,''));
    }
  };
  // helper para jInfo
  jInfo = function(message, title, callback, params) {
    $.alerts.info(message, title, callback, params);
    $("#dialogOk").focus().select();
  }
  // helper para jWarning
  jWarning = function(message, title, callback, params) {
    $.alerts.warning(message, title, callback, params);
    $("#dialogOk").focus().select();
  }
  // helper para jError
  jError = function(message, title, callback, params) {
    $.alerts.error(message, title, callback, params);
    $("#dialogOk").focus().select();
  }
  // helper para jConfirm
  jConfirm = function(message, title, callback, params) {
    $.alerts.confirm(message, title, callback, params);
    $("#dialogOk").focus().select();
  };
  // helper para jPrompt
  jPrompt = function(message, title, callback, params) {
    $.alerts.prompt(message, title, callback, params);
    $("#prompt_value").focus().select();
  };
  // helper para jProcess
  jProcess = function(message, title, style) {
    $.alerts.process(message, title, style);
  };
  // helper para jBusy
  jBusy = function() {
    $.alerts.busy();
  };
  // Cierra ventanas de jAlert
  jClose = function() {
    $.alerts._close();
  };
  // Verifica si hay una funcion para el control de sesión
  sessionEnded = function() {
    if (typeof toLogin === "function") { 
      toLogin();
    }
  }
  // Muestra una lista bajo un control. Para hacer busquedas por texto
  jShowDown = function(input,container) {
    var contElem = $("#"+container);
    var inputElem = $("#"+input);
    if (contElem.width() < inputElem.width()) {
      contElem.width(inputElem.width());
    }
    var offset = inputElem.offset();
    var top = offset.top;
    var left = offset.left;
    var bottom = top + inputElem.outerHeight() + 1;
    if (contElem.is(':visible')) {
      contElem.hide();
    } else {
      contElem.show();
      contElem.offset({top: bottom, left: left});
    }
  }
  /* 
    Helper para enviar formulario por AJAX y ejecutar el metodo dado
    ejemplo onclick="APJSubmit('idFormulario','NombreMetodo','param1',param2);return false;"
    envia el formulario por POST como array de objetos
  */
  APJSubmit = function (form, action) {
      var args = Array.prototype.slice.call(arguments);
      action = (typeof(action)==='undefined' || action=='') ? 'main' : action;
      var formSelector='form#'+form;
      var params = $(formSelector).formToArray(true);
      var actfound = false;
      // Verifica si action está definido
      for (var i=0; i < params.length; i++) {
        if (params[i].name === 'action') {
          actfound = true;
        }
      }
      // define el objeto action y lo agrega al array
      if (!actfound) {
        var obj = {name:"action",value:action};
        params.push(obj);
      }
      // Si tiene más de 2 agumentos los agrega como un array al parametro 'parameters'
      if (args.length>2) {
        var p=0
        var parameters = [];
        for (a=2;a<args.length;a++) {
          parameters[p]=args[a];
          p++;
        }
        obj = {name:'parameters',value:JSON.stringify(parameters)};
        params.push(obj);
      }
      $.ajaj(url, params);
      return false;
  }
  /* 
    Helper para llamada de metodos php
    ejemplo: onclick="APJCall('NombreMetodo',param1,param2,this.value);return false;"
    envia los parametros al metodo como array
  */
  APJCall = function() {
    var args = Array.prototype.slice.call(arguments);
    $.ajaj(url,{action:'_APJCall',data:JSON.stringify(args)});
  }
})(jQuery);