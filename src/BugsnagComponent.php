<?php

namespace materodev\yii2bugsnag;

use Bugsnag\Client;
use Bugsnag\Report;
use yii\base\Component;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;
use yii\log\Logger;

class BugsnagComponent extends Component
{
    public $apiKey;
    public $stage;
    public $messages = [];
    public $endpoint;
    public $sendWarnings;
    public $identityClass;

    public $inException = false;

    /** @var  Client */
    private $client;

    public function init()
    {
        $this->prepareClient();
    }

    public function prepareClient()
    {
        if ($this->client === null) {
            $this->client = Client::make($this->apiKey, $this->endpoint);

            if (!is_null($this->stage)) {
                $this->client->getConfig()->setReleaseStage($this->stage);
            }

            $this->client->registerCallback([$this, 'attachMeta']);
        }
    }

    private function getLogs()
    {
        $result = [];
        $index = 0;

        $messages = $this->messages;
        foreach ($messages as $messageData) {
            list($message, $level, $category, $timestamp, $traces, $memoryUsage) = $messageData;
            if (!is_string($message)) {
                continue;
            }

            $betterTraces = [];

            foreach ($traces as $trace) {
                $betterTraces[] = "{$trace['file']}:{$trace['line']} - {$trace['class']}{$trace['type']}{$trace['function']}()";
            }

            $result[str_pad($index, 2, '0', STR_PAD_LEFT)] = VarDumper::dumpAsString([
                'message' => $message,
                'level' => Logger::getLevelName($level),
                'category' => $category,
                'timestamp' => $timestamp,
                'time' => date('Y-m-d H:i:s', $timestamp) . '.' . substr(fmod($timestamp, 1), 2, 4),
                'traces' => $betterTraces,
                'memoryUsage' => $memoryUsage,
            ]);
            $index++;
        }

        return $result;
    }


    private function getFiles()
    {
        $files = [];

        foreach ($_FILES as $fileData) {
            try {
                $files[$fileData['name']] = @file_get_contents($fileData['tmp_name']);
            } catch (\Exception $e) {
                // ...
            }
        }

        return $files;
    }

    private function replaceTrace(Report $report, $newTrace)
    {
        $stacktrace = $report->getStacktrace();
        while (count($stacktrace->getFrames()) > 0) {
            $stacktrace->removeFrame(0);
        }
        foreach ($newTrace as $traceEntry) {
            $stacktrace->addFrame($traceEntry['file'], $traceEntry['line'], $traceEntry['function'], $traceEntry['class']);
        }
    }

    public function attachMeta(Report $report)
    {
        if (!$this->identityClass->isGuest) {
            /** @var ActiveRecord $user */
            $user = $this->identityClass->identity;
            if ($user) {
                $report->setUser($this->identityClass->identity->toArray());
            }
        }

        $report->setMetaData([
            'log' => $this->getLogs(),
            'files' => $this->getFiles(),
        ]);
    }

    public function flush()
    {
        $logger = \Yii::getLogger();
        if ($logger) {
            $logger->flush(true);
        }
        $this->client->flush();
    }

    public function notifyException($message)
    {
        $this->inException = true;
        $this->prepareClient();
        $this->client->notifyException($message, function (\Bugsnag\Report $report) use ($message) {
            $report->setSeverity('error');
        });
    }

    public function notifyCustomError($message, $trace)
    {
        $this->inException = true;
        $this->prepareClient();
        $this->client->notifyError('Error', $message, function (\Bugsnag\Report $report) use ($message, $trace) {
            $report->setSeverity('error');
            $this->replaceTrace($report, $trace);
        });
    }

    public function notifyCustomWarning($message, $trace)
    {
        $this->prepareClient();

        $this->client->notifyError('Warning', $message, function (\Bugsnag\Report $report) use ($message, $trace) {
            $report->setSeverity('warning');
            $this->replaceTrace($report, $trace);
        });
    }
}
