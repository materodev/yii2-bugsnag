<?php

namespace materodev\yii2bugsnag;

use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;

class BugsnagLogTarget extends Target
{
    static $bugsnagMessages = [];

    public function export()
    {
        /** @var BugsnagComponent $bugsnag */
        $bugsnag = \Yii::$app->bugsnag;

        $bugsnag->messages = array_merge($bugsnag->messages, $this->messages);

        $messages = $this->messages;

        foreach ($messages as $messageData) {
            list($message, $level, $category, $timestamp, $traces, $memoryUsage) = $messageData;

            if (!$bugsnag->inException) {
                if ($bugsnag->sendWarnings && $level === Logger::LEVEL_WARNING) {
                    $bugsnag->notifyCustomWarning($message, $traces);
                }
                if ($level === Logger::LEVEL_ERROR) {

                    // TODO: add any other kind like ParseError etc

                    if (is_string($message)) {
                        $bugsnag->notifyCustomError($message, $traces);
                    } elseif ($message instanceof \Throwable) {
                        $bugsnag->notifyException($message);
                    } else {
                        $message = VarDumper::dumpAsString($message);
                        $bugsnag->notifyCustomError($message, $traces);
                    }

//                    if ($message instanceof \Throwable) {
//                        $bugsnag->notifyException($message);
//                    }
//
//                    if ($message instanceof \Exception) {
//                        $bugsnag->notifyException($message);
//                    } elseif ($message instanceof \ParseError) {
//                        $message = $message;
//                        $bugsnag->notifyException($message);
//                    } else {
//                        $bugsnag->notifyCustomError($message, $traces);
//                    }
                }
            }
        }
    }
}
