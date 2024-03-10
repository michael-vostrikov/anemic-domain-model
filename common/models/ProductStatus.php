<?php

namespace common\models;

enum ProductStatus: int
{
    case HIDDEN = 1;
    case ON_REVIEW = 2;
    case PUBLISHED = 3;
}
