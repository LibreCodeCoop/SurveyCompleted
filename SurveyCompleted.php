<?php

/**
 * Class SurveyCompleted
 */
class SurveyCompleted extends PluginBase
{
    /**
     * @var string
     */
    static protected $description = 'Survey completed plugin';

    /**
     * @var string
     */
    static protected $name = 'SurveyCompleted';

    /**
     * @var string
     */
    protected $storage = 'DbStorage';

    protected $settings = [
        'RedirectUrl' => [
            'type' => 'string',
            'label' => 'Redirect url',
            'help' => 'URL to redirect the form when already filled.',
        ],
    ];

    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('onSurveyDenied');
        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveySettings');
    }

    /**
     * @return void
     */
    public function onSurveyDenied()
    {
        $event = $this->getEvent();
        $reason = $event->get('reason');
        if ($reason !== 'alreadyCompleted') {
            return;
        }
        $surveyId = $event->get('surveyId');
        $redirectUrl = $this->get(
            'RedirectUrl',
            'Survey',
            $surveyId, // Survey
            $this->get('RedirectUrl') // Global
        );
        if (!$redirectUrl) {
            return;
        }
        header('Location: ' . $redirectUrl);
    }

    /**
     * @return void
     */
    public function newSurveySettings()
    {
        $event = $this->getEvent();

        foreach ($event->get('settings') as $name => $value) {
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }

    /**
     * @return void
     */
    public function beforeSurveySettings()
    {
        $event = $this->getEvent();
        $event->set(
            "surveysettings.{$this->id}",
            [
                'name' => get_class($this),
                'settings' => [
                    'SettingsInfo' => [
                        'type' => 'info',
                        'content' => '<legend><small>Redirect URL settings</small></legend>'
                    ],
                    'RedirectUrl' => [
                        'type' => 'string',
                        'label' => $this->settings['RedirectUrl']['help'],
                        'help' => $this->settings['RedirectUrl']['help'],
                        'current' => $this->get(
                            'RedirectUrl',
                            'Survey',
                            $event->get('survey'), // Survey
                            $this->get('RedirectUrl') // Global
                        ),
                    ],
                ]
            ]
        );
    }
}
