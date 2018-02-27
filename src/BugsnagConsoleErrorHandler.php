<?php

namespace materodev\yii2bugsnag;

class BugsnagConsoleErrorHandler extends \yii\console\ErrorHandler
{
    use BugsnagErrorHandlerTrait;
}
