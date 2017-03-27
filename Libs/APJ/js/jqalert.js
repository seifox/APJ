/* JAlert Version 1.3.0602
  Author: Ricardo Seiffert
  Replace alert() function
  use: ('title',callback,[param1,param2] are optional except in jPrompt and jConfirm)
  jInfo('Message','title',callback,[param1,param2]);
  jWarnign('Message','title',callback,[param1,param2]);
  jError('Message','title',callback,[param1,param2]);
  jConfirm('Message','title',callback);
  jPrompt('Message','title',callback);
  jProcess('Message','title','blink'); (style 'blink' optional)
*/
var BlinkId=0;
jAlertClosed=true;
(function($) {
// jAlert 
  $.alerts = {
    info: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Información";
      $.alerts._render('info',mensaje,titulo,function(result) {
        if( typeof(callback)=='function' && result ) callback.apply(null, params);
      });
    },
    warning: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Advertencia";
      $.alerts._render('warning',mensaje,titulo,function(result) {
        if( typeof(callback)=='function' && result ) callback.apply(null, params);
      });
    },
    error: function (mensaje,titulo,callback,callback,params) {
      if (titulo==null) titulo="Error";
      $.alerts._render('error',mensaje,titulo,function(result) {
        if( typeof(callback)=='function' && result ) callback.apply(null, params);
      });
    },
    confirm: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Confirmar";
      $.alerts._render('confirm',mensaje,titulo,function(result) {
        if( typeof(callback)=='function' && result ) callback.apply(null, params);
      });
    },
    process: function (mensaje,titulo,style) {
      if (titulo==null) titulo="Proceso en curso";
      $.alerts._render('process',mensaje,titulo,'',style);
    },
    prompt: function (mensaje,titulo,callback,params) {
      if (titulo==null) titulo="Introdusca un valor";
      $.alerts._render('prompt',mensaje,titulo,function(result) {
        if( typeof(callback)=='function' && result ) {
          if(params.length) params.push(result); else params=[result];
          callback.apply(null, params);;
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
    }
  }
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

})(jQuery);