<?php

return [
    'consumption' => [
        'oxygen' => 0.84, // Litros por astronauta por hora
        'water' => 2.50, // Litros por astronauta por hora
    ],

    'status' => [
        'critical' => 1.0, // quantity <= threshold * critical => Critico
        'low' => 2.0, // quantity <= threshold * low => Bajo
    ],
];
