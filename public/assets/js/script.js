    $(document).ready(function() {
      //init DateTimePickers
      materialKit.initFormExtendedDatetimepickers();

      // Sliders Init
      materialKit.initSliders();


    });


function setFormValidation(id){
  $(id).validate({
    highlight: function(element) {
        $(element).closest('.input-text').addClass('border border-danger');
    },
    success: function(element) {
        $(element).closest('.input-text').removeClass('border border-danger');
    },
    errorPlacement : function(error, element) {
        $(element).append(error);
    },
  });
}

$(document).ready(function(){
  setFormValidation('#form-new-category');
  setFormValidation('#form-new-product');
});
    
$(document).ready(function() {

    $('#datatables').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Buscar",
        }

    });

    $('#datatables1').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Buscar",
        }

    });

    $('#datatables2').DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Buscar",
        }

    });

        $('#datainfo').DataTable({
        "paging": false,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Buscar",
        }

    });

      $('#datesearch').DataTable({
        "paging": false,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Buscar",
        }

    });

    $('.card .material-datatables label').addClass('form-group');

    $('#menu li').find('a').each(function (index, value) {
      if (document.location.href == $(this).attr('href')) {
        //alert( index + ": " + value );
        $(this).parents().addClass("active");
        $(this).addClass("active");
                // add class as you need ul or li or a 
        }
    });

});

// external js: isotope.pkgd.js

// init Isotope
var $grid = $('.grid').isotope({
  itemSelector: '.element-item',
  layoutMode: 'fitRows'
});
// filter functions
var filterFns = {
  // show if number is greater than 50
  numberGreaterThan50: function() {
    var number = $(this).find('.number').text();
    return parseInt( number, 10 ) > 50;
  },
  // show if name ends with -ium
  ium: function() {
    var name = $(this).find('.name').text();
    return name.match( /ium$/ );
  }
};
// bind filter button click
$('.filters-button-group').on( 'click', 'button', function() {
  var filterValue = $( this ).attr('data-filter');
  // use filterFn if matches value
  filterValue = filterFns[ filterValue ] || filterValue;
  $grid.isotope({ filter: filterValue });
});
// change is-checked class on buttons
$('.button-group').each( function( i, buttonGroup ) {
  var $buttonGroup = $( buttonGroup );
  $buttonGroup.on( 'click', 'button', function() {
    $buttonGroup.find('.btn-default').removeClass('btn-default');
    $( this ).addClass('btn-default');
  });
});


$('#datefilter').daterangepicker({
    showDropdowns: true,
    autoUpdateInput: false,
    locale: {
      "format": "YYYY-MM-DD",
      "separator": " HASTA ",
      "applyLabel": "Aceptar",
      "cancelLabel": "Cancelar",
      "fromLabel": "From",
      "toLabel": "To",
      "customRangeLabel": "Custom",
      "weekLabel": "W",
      "daysOfWeek": [
          "Do",
          "Lu",
          "Ma",
          "Mi",
          "Ju",
          "Vi",
          "Sa"
      ],
      "monthNames": [
          "Enero",
          "Febrero",
          "Marzo",
          "Abril",
          "Mayo",
          "Junio",
          "Julio",
          "Agosto",
          "Septiembre",
          "Octubre",
          "Noviembre",
          "Diciembre"
      ],
      "firstDay": 1
    },
});


$('#datefilter').on('apply.daterangepicker', function(ev, picker) {
    $("[name='start']").val('');
    $("[name='end']").val('');
    $(this).val('');

    $("[name='start']").val(picker.startDate.format('YYYY-MM-DD'));
    $("[name='end']").val(picker.endDate.format('YYYY-MM-DD'));

    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' HASTA ' + picker.endDate.format('YYYY-MM-DD'));


});

$('#datefilter').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
});

  
moment.locale('es');
var start = moment().subtract(29, 'days');
var end = moment();

function cb(start, end) {
    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}

$('#reportrange').daterangepicker({
    startDate: start,
    endDate: end,
    showCustomRangeLabel: false,

    locale: {
    "format": "YYYY-MM-DD",
    "separator": " HASTA ",
    "applyLabel": "Aceptar",
    "cancelLabel": "Cancelar",
    "fromLabel": "From",
    "toLabel": "To",
    "customRangeLabel": "Custom",
    "weekLabel": "W",
    "daysOfWeek": [
        "Do",
        "Lu",
        "Ma",
        "Mi",
        "Ju",
        "Vi",
        "Sa"
    ],
    "monthNames": [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    ],
    "firstDay": 1
  },

    ranges: {
       'Hoy': [moment(), moment()],
       'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
       'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
       'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
       'Éste Mes': [moment().startOf('month'), moment().endOf('month')],
       'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
}, cb);

$('#reportrange').on('apply.daterangepicker', function(ev, picker) {
  $("[name='start_alternative']").val('');
  $("[name='end_alternative']").val('');
  $(this).val('');

  $("[name='start_alternative']").val(picker.startDate.format('YYYY-MM-DD'));
  $("[name='end_alternative']").val(picker.endDate.format('YYYY-MM-DD'));


});

cb(start, end);