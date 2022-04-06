<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarcodeController extends Controller
{
    public function edit(Request $request, Barcode $barcode)
    {
        $this->authorize("edit", $barcode);
        // TODO show editor
    }

    public function update(Request $request, Barcode $barcode)
    {
        $this->authorize("edit", $barcode);

        $validator = Validator::make(
            $request->only(["name", "data", "generator",]),
            [
                "name" => ["string", "min:1", "max:200",],
                "data" => ["string", "min:1",],
                "generator" => ["string", "min:1",],
            ],
            [
                // TODO translations
                "name:min" => "The name for the barcode must not be empty.",
                "name:max" => "The name for the barcode cannot be more than 200 characters.",
                "data:min" => "The barcode data must not be empty.",
                "generator:min" => "The barcode type must be given.",
            ]
        );

        $barcode->update($validator->validate());

        // TODO send (AJAX) response
    }

    public function view(Request $request, Barcode $barcode)
    {
        $this->authorize("view", $barcode);
        // TODO send view
    }

    public function delete(Request $request, Barcode $barcode)
    {
        $this->authorize("delete", $barcode);
        $barcode->delete();
        // TODO send (AJAX) response
    }
}
