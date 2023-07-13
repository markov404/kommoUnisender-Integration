<?php

namespace Interfaces;

use ArrayAccess;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;


/**
 * Interface for all models in sync application.
 */
interface ModelInterface extends 
    Arrayable, 
    ArrayAccess, 
    CanBeEscapedWhenCastToString, 
    HasBroadcastChannel, 
    Jsonable, 
    JsonSerializable, 
    QueueableEntity, 
    UrlRoutable 
{
    public static function getFillableColumns(): array;

}