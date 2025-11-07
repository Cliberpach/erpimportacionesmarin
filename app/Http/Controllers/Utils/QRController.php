<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa as EmpresaEmpresa;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class QRController extends Controller
{
    /**
     * Genera un QR a partir de un objeto JSON y lo guarda en storage
     */
    public static function generateQr($data)
    {
        try {
            $company = EmpresaEmpresa::find(1);

            $directory = public_path('storage/qr/');

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            $data_object = json_decode($data);
            $data_array = [];

            foreach ($data_object as $key => $value) {
                $data_array[] = $value;
            }

            $data_string = implode('|', $data_array) . '|';

            $qr_name = $data_object->serie . '-' . $data_object->correlativo . '.png';
            $path = $directory . $qr_name;

            // Generar el QR y guardarlo como PNG
            QrCode::format('png')->size(400)->generate($data_string, $path);

            return response()->json([
                'success' => true,
                'data' => [
                    'ruta_qr' => 'storage/' . $company->files_route . '/qr/' . $qr_name
                ]
            ]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Genera un QR simple con un nombre y cadena especificados
     */
    public static function generarQrSimple(string $qr_nombre, string $cadena)
    {
        $directory = public_path('storage/qr/');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $qr_name = $qr_nombre . '.svg';
        $path = $directory . $qr_name;

        QrCode::size(400)->format('svg')->generate($cadena, $path);

        return 'storage/qr/' . $qr_name;
    }
}
