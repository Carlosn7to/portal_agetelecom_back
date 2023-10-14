<?php

namespace App\Http\Controllers\AgeRv\Builder\Rules\b2c\seller;

class Stars
{

    private $date;

    private $response = [];

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function response()
    {
        $plans = $this->getPlansAndStars();

        foreach ($plans as $key => $plan) {

            if($this->date >= $key) {
                $this->response = $plan;
            }
        }

        return $this->response;
    }


    public function rules() : array
    {
        return $this->getPlansAndStars();
    }

    private function getPlansAndStars() : array
    {
        $plansStars = [
            '2022-06' => [
                'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA' => 5,
                'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER' => 9,
                'PLANO 240 MEGA PROMOCAO LEVE 960 MEGA ' => 9,
                'PLANO 360 MEGA' => 11,
                'PLANO 400 MEGA FIDELIZADO' => 15,
                'PLANO 480 MEGA - FIDELIZADO' => 15,
                'PLANO 720 MEGA ' => 25,
                'PLANO 740 MEGA FIDELIZADO' => 25,
                'PLANO 800 MEGA FIDELIZADO' => 17,
                'PLANO 960 MEGA' => 35,
                'PLANO 960 MEGA TURBINADO + AGE TV + DEEZER PREMIUM' => 35,
                'PLANO EMPRESARIAL 1 GIGA FIDELIZADO' => 35,
                'PLANO EMPRESARIAL 600 MEGA FIDELIZADO' => 9,
                'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO' => 12,
                'PLANO EMPRESARIAL 800 MEGA FIDELIZADO' => 17,
            ],
            '2022-07' => [
                'COMBO EMPRESARIAL 600 MEGA + 1 FIXO BRASIL SEM FIDELIDADE' => 10,
                'COMBO EMPRESARIAL 600 MEGA + 2 FIXOS BRASIL SEM FIDELIDADE' => 13,
                'PLANO 120 MEGA' => 7,
                'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA' => 7,
                'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM' => 9,
                'PLANO 240 MEGA PROMOCAO LEVE 960 MEGA ' => 9,
                'PLANO 240 MEGA SEM FIDELIDADE' => 0,
                'PLANO 400 MEGA FIDELIZADO' => 15,
                'PLANO 480 MEGA - FIDELIZADO' => 15,
                'PLANO 720 MEGA ' => 25,
                'PLANO 800 MEGA - COLABORADOR' => 17,
                'PLANO 800 MEGA FIDELIZADO' => 17,
                'PLANO 960 MEGA' => 35,
                'PLANO 960 MEGA TURBINADO + AGE TV + DEEZER PREMIUM' => 35,
                'PLANO EMPRESARIAL 1 GIGA FIDELIZADO' => 35,
            ],
            '2022-08' => [
                'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE' => 30,
                'PLANO 1 GIGA FIDELIZADO + DEEZER PREMIUM' => 15,
                'PLANO 1 GIGA NÃO FIDELIZADO + DEEZER PREMIUM' => 0,
                'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA' => 7,
                'PLANO 240 MEGA ' => 9,
                'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM' => 9,
                'PLANO 400 MEGA - COLABORADOR' => 0,
                'PLANO 400 MEGA FIDELIZADO' => 7,
                'PLANO 480 MEGA FIDELIZADO' => 7,
                'PLANO 480 MEGA NÃO FIDELIZADO' => 0,
                'PLANO 740 MEGA FIDELIZADO' => 9,
                'PLANO 800 MEGA - CAMPANHA CONDOMÍNIO FIDELIZADO (AMBOS)' => 0,
                'PLANO 800 MEGA - COLABORADOR' => 0,
                'PLANO 800 MEGA FIDELIZADO' => 17,
                'PLANO 960 MEGA' => 35,
                'PLANO 960 MEGA (LOJAS)' => 0,
                'PLANO EMPRESARIAL 1 GIGA FIDELIZADO' => 35,
                'PLANO EMPRESARIAL 600 MEGA FIDELIZADO' => 9,
                'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO' => 12,
                'PLANO EMPRESARIAL 800 MEGA FIDELIZADO' => 15,
                'PLANO 1 GIGA HOTEL LAKE SIDE' => 0,
                'PLANO 480 MEGA FIDELIZADO + DIRECTV GO' => 17,
                'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE + DIRECTV GO' => 22,
                'PLANO 740 MEGA FIDELIZADO + DIRECTV GO' => 18,
                'PLANO 1 GIGA  FIDELIZADO + DEEZER PREMIUM + DIRECTV GO' => 20,
                'PLANO 1 GIGA  FIDELIZADO + DIRECTV GO' => 20,
                'PLANO COLABORADOR 1 GIGA + DEEZER + HBO MAX + DR. AGE' => 0,
                'PLANO EMPRESARIAL 600 MEGA NÃO FIDELIZADO' => 0,
                'PLANO COLABORADOR 1 GIGA + DEEZER' => 0,
                'PLANO 1 GIGA NÃO FIDELIZADO + DEEZER PREMIUM' => 0,
                'PLANO 800 MEGA NÃO FIDELIZADO' => 0,
            ],
        ];


        return $plansStars;
    }

}
