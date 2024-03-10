<?php

namespace common\models;

enum ReviewStatus: int
{
    case CREATED = 1;
    case SENT = 2;
    case ACCEPTED = 3;
    case DECLINED = 4;
}
