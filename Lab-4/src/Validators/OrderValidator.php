<?php

namespace src\Validators;
require_once 'BaseValidators.php';

class OrderValidator  implements ModelValidator
{
    public static function validateData(array $data): string
    {
        $phone = trim($data['phone'] ?? '');
        $from_loc = trim($data['from_loc'] ?? '');
        $dest_loc = trim($data['dest_loc'] ?? '');
        $distance = trim($data['distance'] ?? '');
        $driver_id = trim($data['driver_id'] ?? '');
        $tariff_id = trim($data['tariff_id'] ?? '');
        $err = "";
        
        $err .= BaseValidators::phoneValidator($phone);

        if(!preg_match("/-?\d{1,3}\.\d{6};-?\d{1,3}\.\d{6}/", $from_loc)){
            $err .= "INVALID from_loc DATA;";
        }

        if(!preg_match("/-?\d{1,3}\.\d{6};-?\d{1,3}\.\d{6}/", $dest_loc)){
            $err .= "INVALID dest_loc DATA;";
        }

        if(!is_numeric($distance) or $distance < 0){
            $err .= "INVALID distance DATA;";
        }

        if(!is_numeric($driver_id) or $driver_id < 0){
            $err .= "INVALID driver_id DATA;";
        }

        if(!is_numeric($tariff_id) or $tariff_id < 0){
            $err .= "INVALID tariff_id DATA;";
        }

        return $err;
    }
    public static function validateFilter(array $data): array
    {
        $err = "";

        // Формат даты: YYYY-MM-DD hh:mm:ss
        if (isset($data['orderedAt_from'])){
            $formattedDateFrom = BaseValidators::formatDate($data['orderedAt_from']);
            if ($formattedDateFrom == ""){
                $err .= "INVALID orderedAt_from FILTER;";
                
            } else {
                $data['orderedAt']['from'] = $formattedDateFrom; 
            }
            unset($data['orderedAt_from']);
        }

        if (isset($data['orderedAt_to'])){
            $formattedDateTo = BaseValidators::formatDate($data['orderedAt_to']);
            if ($formattedDateTo == ""){
                $err .= "INVALID orderedAt_to FILTER;";
                
            } else {
                $data['orderedAt']['to'] = $formattedDateTo; 
            }
            unset($data['orderedAt_to']);
        }
        if (
            isset($data['tariff_id']) and (
                !is_numeric($data['tariff_id']) || $data['tariff_id'] < 0
            )
        ) {
            unset($data['tariff_id']);
            $err .= "INVALID tariff_id FILTER;";
        }

        if (
            isset($data['driver_id']) and (
                !is_numeric($data['driver_id']) || $data['driver_id'] < 0
            )
        ) {
            unset($data['driver_id']);
            $err .= "INVALID driver_id FILTER;";
        }
        return array($data, $err);
    }
}
?>