<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 13:40
 */

namespace Felican\Controllers;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Felican\Configurations\Rendering\TemplateRenderer;
use Dompdf\Dompdf;
use Felican\Models\Cliente\Cliente;
use Felican\Models\Cliente\ClienteDAO;




final class AvisosController
{
    private $templateRenderer;
    private $clienteDAO;


    public function __construct(TemplateRenderer $templateRenderer) {
        $this->templateRenderer = $templateRenderer;
        $this->clienteDAO = new ClienteDAO();
    }

    public function showAvisosForm(): Response
    {
        $content = $this->templateRenderer->render('AvisosView.html.twig');
        return new Response($content);
    }

    public function makeAvisosPDF(Request $request)
    {
        $start = join('-', array_reverse(explode('/', $request->get('datepicker_one'))));
        $end = join('-', array_reverse(explode('/', $request->get('datepicker_two'))));

        $start_formatted = join('-', array_reverse(explode('-', $start)));
        $end_formatted = join('-', array_reverse(explode('-', $end)));
        $selected_clientes = $this->clienteDAO->readClientsByDate($start, $end);
        $order = 1;
        // instantiate and use the dompdf class
        //$dompdf = new Dompdf();
        //var_dump($dompdf->getOptions()->getChroot());
        //exit(0);

        $s = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <title>Tabla Avisos</title>
        <link rel="stylesheet" type="text/css" href="/css/pdf_avisos.css">
    </head>
    <body>
    <div class="container-fluid">
        <h1 class="text-center">AVISOS</h1>
        <p class="text-center"> Del <i><strong>' . $start_formatted . '</strong></i> al <i><strong>' . $end_formatted . '</strong></i></p>'.
        '<p class="text-center"> Encontrados <strong>' . count($selected_clientes) . '</strong> resultados';
        if (!empty($selected_clientes)) {
            $s .=
                '<table class="table w-100">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Mascota</th>
                        <th>Igualada</th>
                        <th>Aviso</th>
                        <th>Fecha Aviso</th>
                    </tr>
                </thead>
                <tbody>';
            foreach($selected_clientes as $cliente) {
                $tels = '';
                foreach ($cliente->getTels() as $tlf) {
                    $tels .= $tlf . ' - ';
                }
                $igualadaformatted = $cliente->getMascotas()->getIgualada() == 'si' ? 'Sí' : 'No';
                $tels = substr($tels, 0, -2);
                $s .=
                    '<tr>
                        <td>' .$order.'. '. $cliente->getApellidos() . ', ' . $cliente->getNombre() . '</td>
                        <td>' . $tels . '</td>
                        <td>' . $cliente->getMascotas()->getNombre() . '</td>
                        <td>' . $igualadaformatted . '</td>
                        <td>' . ucfirst($cliente->getMascotas()->getRecordatorios()->getConcepto()) . '</td>
                        <td>' . join('-', array_reverse(explode('-', $cliente->getMascotas()->getRecordatorios()->getFecharecordatorio()))) . '</td>
                    </tr>';
                $order = $order +1;
            }

            $s.='</tbody></table>';
        }
        else{
            $s.= '<h4 class="text-center">No se encontraron coincidencias para las fechas seleccionadas.</h4>';

        }
        $s.='</div>';
        $s.='<script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script(\'
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $size = 12;
                    $pageText = "Página " . $PAGE_NUM . " de " . ($PAGE_COUNT);
                    $y = 570;
                    $x = 725;
                    $pdf->text($x, $y, $pageText, $font, $size);
                }
            \');
        }
        </script>';
        $html = $s;

        $options = new \Dompdf\Options();

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();

        // PDF config
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->getOptions()->setChroot($_SERVER['DOCUMENT_ROOT'].'/public');
        $dompdf->set_protocol(realpath($_SERVER['DOCUMENT_ROOT'].'/public'));
        //$dompdf->set_base_path('/');

        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('avisos_generados.pdf', array('Attachment' => 0));

        return new Response(1);
    }


}