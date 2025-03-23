<?php

namespace Src\Validators;
use Src\Validators\BaseValidators;

class TariffValidator implements ModelValidator
{
    public static function validateData(array $data): string
    {
        $name = trim($data['name'] ?? '');
        $base_price = trim($data['base_price'] ?? '');
        $base_dist = trim($data['base_dist'] ?? '');
        $base_time = trim($data['base_time'] ?? '');
        $dist_cost = trim($data['dist_cost'] ?? '');
        $time_cost = trim($data['time_cost'] ?? '');
        $err = "";

        if (strlen($name) == 0 || strlen($name) > 20) {
            $err .= "INVALID name DATA;";
        }

        if (!is_numeric($base_price) or $base_price < 0) {
            $err .= "INVALID base_price DATA;";
        }

        if (!is_numeric($base_dist) or $base_dist < 0) {
            $err .= "INVALID base_dist DATA;";
        }

        if (!is_numeric($base_time) or $base_time < 0) {
            $err .= "INVALID base_time DATA;";
        }

        if (!is_numeric($dist_cost) or $dist_cost < 0) {
            $err .= "INVALID dist_cost DATA;";
        }

        if (!is_numeric($time_cost) or $time_cost < 0) {
            $err .= "INVALID time_cost DATA;";
        }

        return $err;
    }

    public static function validateFilter(array $data): array
    {
        $err = "";
        if (isset($data['name']) and strlen($data['name']) == 0) {
            unset($data['name']);
            $err .= "INVALID name FILTER;";
        }

        if (
            isset($data['base_price']) and (
                !isset($data['intership']['from']) or !is_numeric($data['intership']['from']) or $data['intership']['from'] < 0 or
                !isset($data['intership']['to']) or !is_numeric($data['intership']['to']) or $data['intership']['to'] < 0
            )
        ) {
            unset($data['base_price']);
            $err .= "INVALID base_price FILTER;";
        }

        return array($data, $err);

    }
}
?>