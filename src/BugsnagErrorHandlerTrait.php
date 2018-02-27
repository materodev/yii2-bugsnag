<?php

namespace materodev\yii2bugsnag;

/**
 * @mixin \yii\web\ErrorHandler
 * @mixin \yii\console\ErrorHandler
 *
 * Trait BugsnagErrorHandlerTrait
 *
 * @package themroc\yii2bugsnag
 */
trait BugsnagErrorHandlerTrait
{
    public function logException($exception)
    {
        parent::logException($exception);
        /** @var BugsnagComponent $bugsnag */
        $bugsnag = \Yii::$app->bugsnag;
        if ($bugsnag) {
            $bugsnag->flush();
        }
    }

    public function handleException($exception)
    {
        /** @var BugsnagComponent $bugsnag */
//        $bugsnag = \Yii::$app->bugsnag;
//        if ($bugsnag) {
//            $bugsnag->notifyException($exception);
//        }

        parent::handleException($exception);
    }

    public function handleFatalError()
    {
        /** @var BugsnagComponent $bugsnag */
//        $bugsnag = \Yii::$app->bugsnag;
//        if ($bugsnag) {
//            $bugsnag->flush();
//        }

        parent::handleFatalError();
    }
}
