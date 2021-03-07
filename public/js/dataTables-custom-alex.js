
$(document).ready(function () {
    // Call the dataTables jQuery plugin
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "zeroRecords": "No se encontraron resultados.",
                "info": "Mostrando _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty": "No se mostraron registros",
                "infoFiltered": " (filtrados de un total de  _MAX_ registros)",
                "lengthMenu":" Mostrar _MENU_ registros.",
                "search": "Buscar",
                "paginate": {
                    "previous": "Anterior",
                    "next": "Siguiente"
                }
            }
        });
    });

});