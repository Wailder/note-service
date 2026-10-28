<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Notes API',
    version: '1.0.0',
    description: 'Мини-сервис заметок'
)]
#[OA\Server(url: '/', description: 'Текущий хост')]
class OpenApi
{
    //
}