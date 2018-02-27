<?php

namespace materodev\yii2bugsnag;

use yii\base\Component;

class BugsnagSetup extends Component
{
    public $apiKey;
    public $stage;
    public $endpoint;
    public $sendWarnings = false;
    public $except = [];
    public $identityClass;

    public function init()
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key required!');
        }

        if (empty($this->identityClass)) {
            throw new \Exception('Identity class required!');
        }
    }

    private function attachComponent(&$yiiConfig)
    {
        $config = [
            'class' => BugsnagComponent::class,
            'apiKey' => $this->apiKey,
            'stage' => $this->stage,
            'endpoint' => $this->endpoint,
            'sendWarnings' => $this->sendWarnings,
            'identityClass' => $this->identityClass,
        ];

        $yiiConfig['components']['bugsnag'] = $config;
    }

    private function attachWebErrorHandler(&$yiiConfig)
    {
        $yiiConfig['components']['errorHandler']['class'] = BugsnagWebErrorHandler::class;
    }

    private function attachConsoleErrorHandler(&$yiiConfig)
    {
        $yiiConfig['components']['errorHandler']['class'] = BugsnagConsoleErrorHandler::class;
    }

    private function attachLogTarget(&$yiiConfig)
    {
        $target = [
            'class' => BugsnagLogTarget::class,
            'levels' => ['error', 'warning', 'info', 'trace'],
            'logVars' => [],
            'except' => $this->except
        ];

        if (!isset($yiiConfig['components']['log']['targets'])) {
            $yiiConfig['components']['log']['targets'] = [];
        }

        $yiiConfig['components']['log']['targets'][] = $target;
    }

    public function attachForWeb(&$yiiConfig)
    {
        $this->attachComponent($yiiConfig);
        $this->attachWebErrorHandler($yiiConfig);
        $this->attachLogTarget($yiiConfig);
    }

    public function attachForConsole(&$yiiConfig)
    {
        $this->attachComponent($yiiConfig);
        $this->attachConsoleErrorHandler($yiiConfig);
        $this->attachLogTarget($yiiConfig);
    }

}
