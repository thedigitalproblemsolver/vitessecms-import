<?php declare(strict_types=1);

namespace VitesseCms\Import\Enum;

use VitesseCms\Core\AbstractEnum;

enum ImportTypeEnum : string
{
    case IMPORTTYPE_LISTENER = 'importTypeListener';
    case GET_REPOSITORY = 'importTypeListener:getRepository';
}