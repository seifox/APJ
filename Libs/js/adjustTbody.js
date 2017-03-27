// Ajusta las columnas del la grilla scrollv
adjutTbody=function(headerTable,bodyTable) {
  $(headerTable+' > thead > tr > th').each(function(index) {
    var $htd=$(this);
    var $btd=$(bodyTable+' > tbody > tr > td').eq(index);
    if ($htd.width()>$btd.width()) {
      $btd.width($htd.width());
    } else if ($btd.length) {
      $htd.width($btd.width());
    }
  });
}
