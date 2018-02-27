# Scope

Implements Bugsnag bug reporting in Yii2 project. 

## Installation

The preferred way of installation is through Composer. If you don't have Composer you can get it here: https://getcomposer.org/.
To install the package add the following to the ```require``` section of your composer.json:

```
"require": {
    "materodev/yii2-bugsnag": "~1.0.0"
}
```

Define the repository path in `repositories` section of composer.json:

```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/materodev/yii2-bugsnag"
    }
]
```

## Configuration

You can either attach the error handling for web or console applications.
Just add the following to the respective `main-local.php` config file:

```$php
$config = [
    'components' => [
        ...
    ],
];

(new \materodev\yii2bugsnag\BugsnagSetup([
    'apiKey' => 'YOUR_API_KEY',
    'identityClass' => \common\models\User::class, // if you have a special identity class, provide the class name
]))->attachForWeb($config);

return $config;
```

Check BugsnagSetup.php for more config options.

## Change log

### 1.0.0 - 2018-02-27
* requires Bugsnag version 3.x
* initial release
