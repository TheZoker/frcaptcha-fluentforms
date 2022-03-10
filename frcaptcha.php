<?php

class FriendlyCaptcha extends \FluentForm\App\Services\FormBuilder\BaseFieldManager {

    public function __construct()
    {
        parent::__construct(
            'frcaptcha',
            'Friendly Captcha',
            [],
            'advanced'
        );

        add_filter('fluentform_validate_input_item_' . $this->key, array($this, 'validateInput'), 10, 5);
    }

    function getComponent() {
        return [
            'index' => 2,
            'element' => $this->key,
            'attributes' => array('name' => $this->key),
            'settings' => array(
                'label' => '',
                'label_placement' => '',
                'validation_rules' => array(),
            ),
            'editor_options' => array(
                'title' => __($this->title, 'fluentform'),
                'icon_class' => 'ff-edit-recaptha',
                'why_disabled_modal' => 'frCaptcha',
                'template' => 'frCaptcha',
            ),
        ];
    }

	public function render($data, $form) {
        $elementName = $data['element'];
        $data = apply_filters('fluentform_rendering_field_data_'.$elementName, $data, $form);

        frcaptcha_enqueue_widget_scripts();

		$label = '';
		if (!empty($data['settings']['label'])) {
			$label = "<div class='ff-el-input--label'><label>{$data['settings']['label']}</label></div>";
		}

        $plugin = FriendlyCaptcha_Plugin::$instance;
        if (!$plugin->is_configured() or !$plugin->get_fluentform_active()) {
            return;
        }

        $frCaptchaBlock = frcaptcha_generate_widget_tag_from_plugin($plugin);

        // it just slightly overflows..
        echo "<style>.frc-captcha {max-width:100%; margin-bottom: 1em}</style>";

		$el = "<div class='ff-el-input--content'><div data-fluent_id='".$form->id."' name='fr-captcha-response'>{$frCaptchaBlock}</div></div>";
		$atts = $this->buildAttributes(
			\FluentForm\Framework\Helpers\ArrayHelper::except($data['attributes'], 'name')
		);
		$html = "<div class='ff-el-group' {$atts}>{$label}{$el}</div>";
        echo apply_filters('fluentform_rendering_field_html_'.$elementName, $html, $data, $form);
    }

    public function getGeneralEditorElements() {
        return [
            'label',
            'label_placement',
            'name',
        ];
    }

    public function getAdvancedEditorElements() {
        return [];
    }

    public function validateInput($errorMessage, $field, $formData, $fields, $form) {
        $plugin = FriendlyCaptcha_Plugin::$instance;
        if (!$plugin->is_configured() or !$plugin->get_fluentform_active()) {
            return;
        }

        $solution = frcaptcha_get_sanitized_frcaptcha_solution_from_post();

        if ( empty( $solution ) ) {
            $error_message = FriendlyCaptcha_Plugin::default_error_user_message() . __(" (captcha missing)", "frcaptcha");
        }

        $verification = frcaptcha_verify_captcha_solution($solution, $plugin->get_sitekey(), $plugin->get_api_key());

        if (!$verification["success"]) {
            $error_message = FriendlyCaptcha_Plugin::default_error_user_message();
        }
        return [$errorMessage];
    }
}

add_action('fluentform_loaded', function () {
    new FriendlyCaptcha();
});
