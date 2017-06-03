<?php
class Classes_FuncionesFecha
{
  public static $dia;
  public static $mes;
  public static $ano;
  public static $fechaHora;
  public static $formato_fecha_local="d-m-Y";
  public static $formato_fecha_hora_local="d-m-Y H:i:s";
  public static $formato_fecha_base="Y-m-d";
  public static $formato_fecha_hora_base="Y-m-d H:i:s";
  public static $formato_defecto="d-m-Y";
  private static $formato;
  
  // Reemplaza / por - en Fechas
  public static function reemplazaDiagonales($fecha) {
    return str_replace('/','-',$fecha);
  }
  
  public static function esFinDeSemana($fecha) {
    $dw=date("w",strtotime($fecha));
    if ($dw>5 or $dw==0) {
      return true;
    }
    return false;
  }
  
  public static function convierteFecha($fecha, $format) {
    $arraymeses=array();
    $meses[0]=array('ninguno','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');
    $meses[1]=array('ninguno','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic');
    $meses[2]=array('ninguno','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    if (strpos($fecha,' ')>0) {
      $fa=explode(' ',strtolower($fecha));
      foreach ($fa as $part) {
        foreach ($meses as $mm) {
          if (in_array($part,$mm)) {
            $arraymeses=$mm;
            break;
          }
        }
        if (count($arraymeses)) {
          break;
        }
      }
    }
    if (count($arraymeses)) {
      $dia='';
      $mes='';
      $anio='';
      foreach ($fa as $part) {
        if (is_numeric($part)) {
          if (strlen($part)<=2) {
            $dia=$part;
          }
          if (strlen($part)==4) {
            $anio=$part;
          }
        } elseif (in_array($part,$arraymeses)!==false) {
          $mes=array_search($part,$arraymeses,false);
        }
      }
      if ($dia and $mes and $anio) {
        $fecha=$mes.'/'.$dia.'/'.$anio;
      }
    }
    return date($format, strtotime($fecha));
  }
  
  public static function fechaLargaES($fecha) {
    $fechaES="";
    if ($fecha) {
      setlocale(LC_TIME, LOCALTIMESYMBOL);
      $fechaES = utf8_encode(strftime("%A %d-%m-%Y",strtotime($fecha)));
    }
    return $fechaES;
  }
  
  public static function masmenosDias($fecha,$dias) {
    $unixtime=strtotime($fecha.$dias.' day');
    return date('Y-m-d',$unixtime);
  }
  
  public static function serieDeFechas($desde,$hasta) {
    $serie=array($desde);
    while ($desde<$hasta) {
      $desde=self::masmenosDias($desde,1);
      $serie[]=$desde;
    }
    return $serie;
  }
  
  public static function diferenciaFechasHoras($inicio,$termino,$formato='%H:%i') 
  {
    $dteStart = new DateTime($inicio); 
    $dteEnd   = new DateTime($termino);
    $dteDiff  = $dteStart->diff($dteEnd);
    return $dteDiff->format($formato);
  }
  
  // CONVIERTE UNA CADENA A FECHA Y/O FECHA Y HORA SEGUN FORMATO
  public static function convierteFechaHora($fechahora,$formato) {
    if (self::verifNull($fecha)) return NULL;
    self::set_formato($formato);
    return self::timestamp($fecha);
  }
  
  // CONVIERTE TIMESTAMP A FECHA CON FORMATO
  public static function convierteTimestampFecha($timestamp,$formato='') {
    if (self::verifNull($timestamp)) return NULL;
    if ($formato) {
      $fecha=date($formato,$timestamp);
    } else {
      $fecha=date(self::$formato_fecha_hora_base);
    }
    return $fecha;
  }
  
  // CONVIERTE FECHA DE FORMATO LOCAL A YYYY/MM/DD
  public static function fechaLocalBase($fecha) {
    if (self::verifNull($fecha)) return NULL;
    $nuevaFecha=self::timestamp($fecha);
    return date(self::$formato_fecha_base,$nuevaFecha);
  }
  
  // CONVIERTE FECHA DE FORMATO YYYY/MM/DD A LOCAL
  public static function fechaBaseLocal($fecha) {
    if (self::verifNull($fecha)) return NULL;
    $nuevaFecha=self::timestamp($fecha);
    return date(self::$formato_fecha_local,$nuevaFecha);
  }
  
  // CONVIERTE FECHA Y HORA DE FORMATO LOCAL A YYYY/MM/DD
  public static function fechaHoraLocalBase($fecha) {
    if (self::verifNull($fecha)) return NULL;
    $nuevaFechaHora=self::timestamp($fecha);
    return date(self::$formato_fecha_hora_base,$nuevaFechaHora);
  }
  
  // CONVIERTE FECHA Y HORA DE FORMATO YYYY/MM/DD A LOCAL
  public static function fechaHoraBaseLocal($fecha) {
    if (self::verifNull($fecha)) return NULL;
    $nuevaFechaHora=self::timestamp($fecha);
    return date(self::$formato_fecha_hora_local,$nuevaFechaHora);
  }
  
  // CONVIERTE FECHA Y HORA DE UN FORMATO A OTRO
  public static function fechaFormato($fecha,$formatoDestino) {
    if (self::verifNull($fecha)) return NULL;
    $nuevaFechaHora=self::timestamp($fecha);
    return date($formatoDestino,$nuevaFechaHora);
  }
  
  // VALIDA FECHA (returna veraddero si la fecha es válida)
  public static function validaFecha($fecha) {
    if (self::verifNull($fecha)) return NULL;
    self::timestamp($fecha);
    if (self::$fechaHora==false) {
      return false;
    }
    return checkdate(self::get_mesFecha(),self::get_diaFecha(),self::get_anoFecha());
  }
  
  // SUMA FECHA
  public static function sumarFecha($fecha,$dias=0,$meses=0,$anos=0,$horas=0,$minutos=0,$segundos=0,$formato='') {
    if (self::verifNull($fecha)) return NULL;
    self::set_formato($formato);
    $timestamp=self::timestamp($fecha);
    $nuevaFecha=date(self::$formato_fecha_hora_base,$timestamp);
    if ($dias) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("$nuevaFecha +$dias day"));
    }
    if ($meses) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("$nuevaFecha +$meses month"));
    }
    if ($anos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("$nuevaFecha +$anos year"));
    }
    if ($horas) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("$nuevaFecha +$horas hour"));
    }
    if ($minutos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("$nuevaFecha +$minutos minute"));
    }
    if ($segundos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("$nuevaFecha +$segundos second"));
    }
    return self::fechaFormato($nuevaFecha,self::$formato);
  }
  
  // RESTA FECHA
  public static function restarFecha($fecha,$dias=0,$meses=0,$anos=0,$horas=0,$minutos=0,$segundos=0,$formato='') {
    if (self::verifNull($fecha)) return NULL;
    self::set_formato($formato);
    $timestamp=self::timestamp($fecha);
    $nuevaFecha=date(self::$formato_fecha_hora_base,$timestamp);
    if ($dias) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("{$nuevaFecha} -{$dias} day"));
    }
    if ($meses) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("{$nuevaFecha} -{$meses} month"));
    }
    if ($anos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base,strtotime("{$nuevaFecha} -{$anos} year"));
    }
    if ($horas) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("{$nuevaFecha} -{$horas} hour"));
    }
    if ($minutos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("{$nuevaFecha} -{$minutos} minute"));
    }
    if ($segundos) {
      $nuevaFecha=date(self::$formato_fecha_hora_base, strtotime("{$nuevaFecha} -{$segundos} second"));
    }
    return self::fechaFormato($nuevaFecha,self::$formato);
  }
  
  // CALCULA EDAD
  public static function edad($fecha,$desde='') {
    if (self::verifNull($fecha)) return NULL;
    self::timestamp($fecha);
    if ($desde) {
      $dia_dif=date('d',strtotime($desde))-self::get_diaFecha();
      $mes_dif=date('m',strtotime($desde))-self::get_mesFecha();
      $ano_dif=date('Y',strtotime($desde))-self::get_anoFecha();
    } else {
      $dia_dif=date('d')-self::get_diaFecha();
      $mes_dif=date('m')-self::get_mesFecha();
      $ano_dif=date('Y')-self::get_anoFecha();
    }
    if ($dia_dif<0) {
      $mes_dif--;
    }
    if ($mes_dif<0) {
      $ano_dif--;
    }
    if ($ano_dif<0) {
      $ano_dif=0;
    }
    return $ano_dif;
  }
  
  // DIAS TRANSCURRIDOS ENTRE DOS FECHAS
  public static function diasTranscurridos($fecha_i,$fecha_f) {
    if (self::verifNull($fecha_i) or self::verifNull($fecha_f)) return NULL;
    $dias = (strtotime($fecha_i)-strtotime($fecha_f))/86400;
    $dias = abs($dias); 
    $dias = floor($dias);    
    return $dias;
  }
  // RETORNA EL DIA DE UNA FECHA
  public static function dia($fecha) {
    if (self::verifNull($fecha)) return NULL;
    self::timestamp($fecha);
    return self::get_diaFecha();
  }
  
  // RETORNA EL MES DE UNA FECHA
  public static function mes($fecha) {
    if (self::verifNull($fecha)) return NULL;
    self::timestamp($fecha);
    return self::get_mesFecha();
  }
  
  // RETORNA EL AÑO DE UNA FECHA
  public static function ano($fecha) {
    if (self::verifNull($fecha)) return NULL;
    self::timestamp($fecha);
    return self::get_anoFecha();
  }
  
  // VERIFICA SI LA FECHA / HORA ES NULA
  public static function verifNull($fecha) {
    if (is_null($fecha) or empty($fecha)) 
      return true;
    else 
      return false;
  }
  // Convierte una cadena a formato fecha
  private static function timestamp($fecha) {
    if (strpos($fecha,"/")!==false) {
      $fecha=str_replace("/","-",$fecha);
    }
    self::$fechaHora=strtotime($fecha);
    return self::$fechaHora;
  }
  
  // Define el formato
  private static function set_formato($formato) {
    if ($formato) {
      self::$formato=$formato;
    } else {
      self::$formato=self::$formato_defecto;
    }
  }
  
  // Retorna el día de un timestamp
  private static function get_diaFecha() {
    self::$dia=date('d',self::$fechaHora);
    return self::$dia;
  }
  
  // Retorna el mes de un timestamp
  private static function get_mesFecha() {
    self::$mes=date('m',self::$fechaHora);
    return self::$mes;
  }
  
  // Retorna el año de un timestamp
  private static function get_anoFecha() {
    self::$ano=date('Y',self::$fechaHora);
    return self::$ano;
  }
  
}