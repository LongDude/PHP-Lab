<?php

namespace Src\Validators;


class DriverValidator implements ModelValidator
{
    public static function validateData(array $data): string
    {
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $intership = trim($data['intership'] ?? '');
        $car_license = trim($data['car_license'] ?? '');
        $car_brand = trim($data['car_brand'] ?? '');
        $tariff_id = trim($data['tariff_id'] ?? '');
        $err = "";

        $err .= BaseValidators::nameValidator($name);
        $err .= BaseValidators::phoneValidator($phone);
        $err .= BaseValidators::emailValidator($email);

        // Валидация стажа
        if (strlen($intership) == 0 || $intership <= 0 || $intership >= 100) {
            $err .= "INVALID intership DATA;";
        }

        // Валидация регистрационного номера машины
        if (!preg_match("/^[а-яA-Z0-9]{4,8}[ -][а-яЫA-Z0-9]{2,4}$/ui", $car_license)) {
            $err .= "INVALID car_license DATA;";
        }

        if (strlen($car_brand) == 0 || strlen($car_brand) > 50) {
            $err .= "INVALID CAR car_brand DATA;";
        }

        if (!is_numeric($tariff_id) || $tariff_id < 0) {
            $err .= "INVALID tariff_id DATA;";
        }
        return $err;
    }

    public static function validateFilter(array $data): array
    {
        // Удаляет некорректные фильтры
        $err = "";

        // Фильтрация стажа
        if (
            isset($data['intership']) and (
                !isset($data['intership']['from']) or !is_numeric($data['intership']['from']) or $data['intership']['from'] < 0 or $data['intership']['from'] > 100 or
                !isset($data['intership']['to']) or !is_numeric($data['intership']['to']) or $data['intership']['to'] < 0 or $data['intership']['to'] > 100
            )
        ) {
            unset($data['intership']);
            $err .= "INVALID intership FILTER;";
        }

        if (isset($data['name']) and strlen($data['name']) == 0) {
            unset($data['name']);
            $err .= "INVALID name FILTER;";
        }
        if (isset($data['phone']) and strlen($data['phone']) == 0) {
            unset($data['phone']);
            $err .= "INVALID phone FILTER;";
        }
        if (isset($data['email']) and strlen($data['email']) == 0) {
            unset($data['email']);
            $err .= "INVALID email FILTER;";
        }
        if (isset($data['car_brand']) and strlen($data['car_brand']) == 0) {
            unset($data['car_brand']);
            $err .= "INVALID car_brand FILTER;";
        }

        // Фильтрация лицензионой платы
        if (
            isset($data['car_license']) and (
                !preg_match("/^[а-яA-Z0-9]{4,8}[ -][а-яЫA-Z0-9]{2,4}$/ui", $data['car_license'])
            )
        ) {
            unset($data['car_license']);
            $err .= "INVALID car_license FILTER;";
        }

        // Фильтрация тарифа
        if (
            isset($data['tariff_id']) and (
                !is_numeric($data['tariff_id']) || $data['tariff_id'] < 0
            )
        ) {
            unset($data['tariff_id']);
            $err .= "INVALID tariff_id FILTER;";
        }

        return array($data, $err);
    }
}
?>