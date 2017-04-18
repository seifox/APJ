// Masked Date/Time and Datepicker 1.1
$(document).ready(function() {
  verifyDateTime();
});

verifyDateTime=function() {
  if ($('[type="date"]').prop('type') != 'date') {
    $('head').append('<script type="text/javascript" src="Libs/jQuery/jquery-ui/jquery-ui.min.js"></script>');
    $('head').append('<script type="text/javascript" src="Libs/jQuery/jquery.mask.min.js"></script>');
    $('head').append('<link rel="stylesheet" href="Libs/jQuery/jquery-ui/jquery-ui.min.css" type="text/css" />');
    maskDatepicker();
  }
}

maskDatepicker=function() {
  var dates=$('[type="date"]');
  var datetimes=$('[type="datetime"]');
  var times=$('[type="time"]');
  if ( dates.prop('type') != 'date' ) {
    dates.mask('0000-00-00')
    dates.attr("placeholder","yyyy-mm-dd");
    dates.datepicker({
      dateFormat: "yy-mm-dd"
    });
  }
  if ( datetimes.prop('type') != 'datetime' ) {
    datetimes.mask('0000-00-00 00:00')
    datetimes.attr("placeholder","yyyy-mm-dd hh:mm");
  }
  if (times.prop('type') != 'time' ) {
    times.mask('00:00')
    times.attr("placeholder","hh:mm");
  }
}