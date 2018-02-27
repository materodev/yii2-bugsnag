<?php

namespace materodev\yii2bugsnag;

class BugsnagWebErrorHandler extends \yii\web\ErrorHandler
{
    use BugsnagErrorHandlerTrait;
}
