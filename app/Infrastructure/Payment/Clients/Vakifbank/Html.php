<?php

namespace App\Infrastructure\Payment\Clients\Vakifbank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Html extends Model
{
    use HasFactory;

    public function html($data)
    {
        $html = '<form id="form" method="POST" action="' . $data->ACSUrl . '">
                    <input type="hidden" name="PaReq" value="'. $data->PaReq .'">
                    <input type="hidden" name="TermUrl" value="'. $data->TermUrl .'">
                    <input type="hidden" name="MD" value="'. $data->MD .'">
                </form>';

        return $html;
    }
}

