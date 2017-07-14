<?php
/**
 * Created by PhpStorm.
 * User: bva
 * Date: 11.07.17
 * Time: 13:58
 */

\OnPhp\RouterRewrite::me()
    ->addRoute(
        'index',
        (new \OnPhp\RouterTransparentRule('/'))
            ->setDefaults(
                [
                    'area' => 'Home',
                ]
            )
    )
    ->addRoute(
        'template',
        (new \OnPhp\RouterTransparentRule('/temp'))
        ->setDefaults(
            [
                'area' => 'Temp'
            ]
        )
    )
    ->addRoute('main',
        (new \OnPhp\RouterTransparentRule('/main'))
        ->setDefaults(
        [
            'area' => 'Main'
        ]
        )
    )

;