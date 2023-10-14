<?php

namespace App\Http\Controllers\AgeRv\Builder\Rules\b2c\seller;

use Carbon\Carbon;

class ValuePayment
{
    private $rules = [];
    private $date;
    private $response;
    private $channelId;

    public function __construct($date, $channelId)
    {
        $this->date = $date;
        $this->channelId = $channelId;
    }


    public function response()
    {
        $rules = $this->rules();

        foreach($rules as $key => $value) {

            if($value['channelId'] === $this->channelId) {

                foreach($value['condictions'] as $k => $v) {

                        if($this->date === $v['validity']) {
                            $this->response = $v;
                        }
                }

                if(empty($this->response)) {
                    $countItems = count($value['condictions']);
                    $this->response = $value['condictions'][$countItems - 1];
                }

            }
        }

        return $this->response;
    }

    private function rules()
    {

        $this->rules = [
            0 => [
                'channelId' => 1,
                'condictions' => [
                    0  => [
                        'validity' => '2023-06',
                        'type' => 'stars',
                        'rules' => [
                            0 => [
                                'minPercent' => 0,
                                'maxPercent' => 0,
                                'value' => 0
                            ],
                            1 => [
                                'minPercent' => 70,
                                'maxPercent' => 100,
                                'value' => 0.9
                            ],
                            2 => [
                                'minPercent' => 100,
                                'maxPercent' => 120,
                                'value' => 1.2
                            ],
                            3 => [
                                'minPercent' => 120,
                                'maxPercent' => 141,
                                'value' => 2
                            ],
                            4 => [
                                'minPercent' => 141,
                                'maxPercent' => 1000,
                                'value' => 4.5
                            ],
                        ]
                    ]
                ]
            ],
            1 => [
                'channelId' => 2,
                'condictions' => [
                    0  => [
                        'validity' => '2023-06',
                        'type' => 'stars',
                        'rules' => [
                            0 => [
                                'minPercent' => 0,
                                'maxPercent' => 0,
                                'value' => 0
                            ],
                            1 => [
                                'minPercent' => 70,
                                'maxPercent' => 100,
                                'value' => 2.5
                            ],
                            2 => [
                                'minPercent' => 100,
                                'maxPercent' => 120,
                                'value' => 5
                            ],
                            3 => [
                                'minPercent' => 120,
                                'maxPercent' => 141,
                                'value' => 7
                            ],
                            4 => [
                                'minPercent' => 141,
                                'maxPercent' => 1000,
                                'value' => 8
                            ],
                        ]
                    ]
                ]
            ],
            2 => [
                'channelId' => 3,
                'condictions' => [
                    0  => [
                        'validity' => '2023-06',
                        'type' => 'target',
                        'rules' => [
                            0 => [
                                'minPercent' => 0,
                                'maxPercent' => 0,
                                'value' => 0
                            ],
                            1 => [
                                'minPercent' => 70,
                                'maxPercent' => 100,
                                'value' => 2.5
                            ],
                            2 => [
                                'minPercent' => 100,
                                'maxPercent' => 120,
                                'value' => 5
                            ],
                            3 => [
                                'minPercent' => 120,
                                'maxPercent' => 141,
                                'value' => 7
                            ],
                            4 => [
                                'minPercent' => 141,
                                'maxPercent' => 1000,
                                'value' => 8
                            ],
                        ]
                    ]
                ]
            ],
        ];

        return $this->rules;
    }

}
