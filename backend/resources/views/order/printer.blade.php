@php
  use Carbon\Carbon;
  use App\Product;
  Carbon::setLocale('es');
@endphp

<!DOCTYPE html>
<html>

<head>
  <style>
    * {
  font-size: 12px;
  font-family: 'Times New Roman';
}

td,
th,
tr,
table {
  border-top: 1px solid black;
  border-collapse: collapse;
}

td.producto,
th.producto {
  width: 75px;
  max-width: 75px;
}

td.cantidad,
th.cantidad {
  width: 40px;
  max-width: 40px;
  word-break: break-all;
}

td.precio,
th.precio {
  width: 40px;
  max-width: 40px;
  word-break: break-all;
}

.header {
  text-align: left;
  align-content: left;
}
.footer {
  text-align: center;
  align-content: center;
}

.ticket {
  width: 155px;
  max-width: 155px;
}

img {
  max-width: inherit;
  width: inherit;
}

@media print{
  .oculto-impresion, .oculto-impresion *{
    display: none !important;
  }
}
  </style>

</head>

<body>
  <div id="ticket" class="ticket">
    <img src="{{ asset('/assets/img/logo.png') }}">
    <div class="header">
      <div>Orden N°: {{ $id }}</div>
      <div>Fecha: {{ Carbon::parse($now->created_at)->format('Y-m-d') }}</div>
      <div>Mesero: {{ $owner }}</div>
      <div>Teléfono: (63) 310921</div>
      <div>Dirección: Avenida Martinez De Rozas 722, Panguipulli, Chile</div>
    </div>
    <table>
      <thead>
        <tr>
          <th class="cantidad">CANT</th>
          <th class="producto">PRODUCTO</th>
          <th class="precio">$</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order as $d)

        @php
            $value = Product::find($d->product_id);
        @endphp

        <tr>
          <td class="cantidad">x{{ $d->num}}</td>
          <td class="producto">{{ $value->name}}</td>
          <td class="precio">${{ $value->price * $d->num }}</td>
        </tr>

        @endforeach
        <tr>
          <td class="cantidad"></td>
          <td class="producto">SubTotal</td>
          <td class="precio">${{ $subtotal }}</td>
        </tr>
        <tr>
          <td class="cantidad"></td>
          <td class="producto">Propina Sugerida (10%)</td>
          <td class="precio">${{ $propina }}</td>
        </tr>
        <tr>
          <td class="cantidad"></td>
          <td class="producto">Total</td>
          <td class="precio">${{ $total }}</td>
        </tr>

      </tbody>
    </table>
    <p class="footer">
      GRACIAS POR SU PREFERENCIA.
   </p>
  </div>

</body>
<script src="{{ asset('/assets/js/core/jquery.min.js') }}" type="text/javascript"></script>

<script>
$(window).on('load', function(){
    window.print();
	setTimeout(window.close, 0);
});
</script>
</html>