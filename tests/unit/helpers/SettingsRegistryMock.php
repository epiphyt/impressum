<?php

use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Setting;

$setting = \Mockery::mock('alias:' . Setting::class);
$setting->shouldReceive('get_value')->andReturn('');
$setting_address = clone $setting;
$setting_email = clone $setting;
$setting_name = clone $setting;
$setting_phone = clone $setting;
$setting_contact_form_page = clone $setting;
$setting_vat_id = clone $setting;
$setting_business_id = clone $setting;
$setting_page = clone $setting;
$setting_country = clone $setting;
$setting_legal_entity = clone $setting;
$setting_address_alternative = clone $setting;
$setting_fax = clone $setting;
$setting_press_law_checkbox = clone $setting;
$setting_press_law_person = clone $setting;
$setting_address->name = 'address';
$setting_address
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_address
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_address
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['messages' => ['required' => 'required'], 'required' => true]);
$setting_address
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_address
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_address
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Address');
$setting_address
    ->shouldReceive('get_title')
    ->andReturn('Address');
$setting_email->name = 'email';
$setting_email
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_email
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_email
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['messages' => ['required' => 'required'], 'required' => true]);
$setting_email
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_email
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_email
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Email Address');
$setting_email
    ->shouldReceive('get_title')
    ->andReturn('Email Address');
$setting_name->name = 'name';
$setting_name
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_name
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_name
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['messages' => ['required' => 'required'], 'required' => true]);
$setting_name
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_name
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_name
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Name');
$setting_name
    ->shouldReceive('get_title')
    ->andReturn('Name');
$setting_phone->name = 'phone';
$setting_phone
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_phone
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_phone
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([
        'client_only' => true,
        'pair_key' => 'phone_contact_form',
        'pair_title' => '%1$s or %2$s',
        'pair_with' => 'contact_form_page',
    ]);
$setting_phone
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_phone
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_phone
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Phone');
$setting_phone
    ->shouldReceive('get_title')
    ->andReturn('Phone');
$setting_contact_form_page->name = 'contact_form_page';
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['client_only' => true]);
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_contact_form_page
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Contact Form Page');
$setting_contact_form_page
    ->shouldReceive('get_title')
    ->andReturn('Contact Form Page');
$setting_vat_id->name = 'vat_id';
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['pattern' => '^(|ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|HU[0-9]{8}|IE[0-9]S[0-9]{5}L|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9+*]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$']);
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_vat_id
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('VAT ID');
$setting_vat_id
    ->shouldReceive('get_title')
    ->andReturn('VAT ID');
$setting_business_id->name = 'business_id';
$setting_business_id
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_business_id
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_business_id
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['pattern' => '^(|DE[0-9]{9}-[0-9]{5})$']);
$setting_business_id
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_business_id
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_business_id
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Business ID');
$setting_business_id
    ->shouldReceive('get_title')
    ->andReturn('Business ID');
$setting_page->name = 'page';
$setting_page
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_page
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_page
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_page
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_page
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(true);
$setting_page
    ->shouldReceive('get_data')
    ->with('setting_callback')
    ->andReturn('page');
$setting_page
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Imprint Page');
$setting_page
    ->shouldReceive('get_title')
    ->andReturn('Imprint Page');
$setting_country->name = 'country';
$setting_country
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_country
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_country
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn(['client_only' => true, 'required' => true]);
$setting_country
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_country
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(true);
$setting_country
    ->shouldReceive('get_data')
    ->with('setting_callback')
    ->andReturn('country');
$setting_country
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Country');
$setting_country
    ->shouldReceive('get_title')
    ->andReturn('Country');
$setting_legal_entity->name = 'legal_entity';
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(true);
$setting_legal_entity
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Legal Entity');
$setting_legal_entity
    ->shouldReceive('get_title')
    ->andReturn('Legal Entity');
$setting_address_alternative->name = 'address_alternative';
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn('Address');
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_address_alternative
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Address');
$setting_address_alternative
    ->shouldReceive('get_title')
    ->andReturn('Alternative Address');
$setting_fax->name = 'fax';
$setting_fax
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_fax
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_fax
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_fax
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_fax
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(false);
$setting_fax
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Fax');
$setting_fax
    ->shouldReceive('get_title')
    ->andReturn('Fax');
$setting_press_law_checkbox->name = 'press_law_checkbox';
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(true);
$setting_press_law_checkbox
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Journalistic/Editorial Content');
$setting_press_law_checkbox
    ->shouldReceive('get_title')
    ->andReturn('Journalistic/Editorial Content');
$setting_press_law_person->name = 'press_law_person';
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('conditions')
    ->andReturn([]);
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('notices')
    ->andReturn([]);
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('validation')
    ->andReturn([]);
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('field_title')
    ->andReturn(null);
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('hide_output')
    ->andReturn(true);
$setting_press_law_person
    ->shouldReceive('get_data')
    ->with('title')
    ->andReturn('Responsible for content');
$setting_press_law_person
    ->shouldReceive('get_title')
    ->andReturn('Responsible for content');
$settings_registry = Mockery::mock('alias:' . Registry::class);
$settings_registry
    ->shouldReceive('get_settings')
    ->andReturn([
        'impressum_imprint_options_page' => $setting_page,
        'impressum_imprint_options_country' => $setting_country,
        'impressum_imprint_options_legal_entity' => $setting_legal_entity,
        'impressum_imprint_options_name' => $setting_name,
        'impressum_imprint_options_address' => $setting_address,
        'impressum_imprint_options_address_alternative' => $setting_address_alternative,
        'impressum_imprint_options_email' => $setting_email,
        'impressum_imprint_options_phone' => $setting_phone,
        'impressum_imprint_options_contact_form_page' => $setting_contact_form_page,
        'impressum_imprint_options_fax' => $setting_fax,
        'impressum_imprint_options_press_law_checkbox' => $setting_press_law_checkbox,
        'impressum_imprint_options_press_law_person' => $setting_press_law_person,
        'impressum_imprint_options_vat_id' => $setting_vat_id,
        'impressum_imprint_options_business_id' => $setting_business_id,
    ]);
$settings_registry
    ->shouldReceive('register_multiple');
$settings_registry
    ->shouldReceive('get_setting_types')
    ->andReturn(['impressum_imprint_options']);

return $settings_registry;
